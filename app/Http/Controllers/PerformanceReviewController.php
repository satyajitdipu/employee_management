<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Services\PerformanceReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PerformanceReviewController extends Controller
{
    protected PerformanceReviewService $service;

    public function __construct(PerformanceReviewService $service)
    {
        $this->service = $service;
    }

    /**
     * Get performance reviews for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = PerformanceReview::with(['employee', 'reviewer']);

        // If user is an employee, show only their reviews
        if ($user->employee) {
            $query->where('employee_id', $user->employee->id);
        } else {
            // If user is a reviewer/manager, show reviews they created
            $query->where('reviewer_id', $user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('review_period')) {
            $query->byPeriod($request->review_period);
        }

        $reviews = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $reviews,
            'message' => 'Performance reviews retrieved successfully.',
        ]);
    }

    /**
     * Get reviews created by the authenticated reviewer
     */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = PerformanceReview::where('reviewer_id', Auth::id())
            ->with(['employee.department', 'employee.designation'])
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->review_period, fn($q) => $q->byPeriod($request->review_period))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $reviews,
            'message' => 'My reviews retrieved successfully.',
        ]);
    }

    /**
     * Show specific performance review
     */
    public function show(PerformanceReview $performanceReview): JsonResponse
    {
        // Check if user can access this review
        if (!$this->canAccessReview($performanceReview)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $performanceReview->load(['employee.department', 'employee.designation', 'reviewer']);

        return response()->json([
            'data' => $performanceReview,
            'message' => 'Performance review retrieved successfully.',
        ]);
    }

    /**
     * Create a new performance review
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'review_period' => 'required|string',
            'review_date' => 'nullable|date',
            'due_date' => 'nullable|date|after:today',
        ]);

        $employee = \App\Models\Employee::findOrFail($request->employee_id);

        try {
            $review = $this->service->createPerformanceReview(
                $employee,
                Auth::user(),
                $request->only(['review_period', 'review_date', 'due_date'])
            );

            return response()->json([
                'data' => $review->load(['employee', 'reviewer']),
                'message' => 'Performance review created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update performance review
     */
    public function update(Request $request, PerformanceReview $performanceReview): JsonResponse
    {
        // Check if user can update this review
        if (!$this->canUpdateReview($performanceReview)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $rules = [];

        // Different validation rules based on user role
        if (Auth::id() === $performanceReview->employee_id) {
            // Employee can update their comments
            $rules = [
                'employee_comments' => 'nullable|string|max:1000',
                'self_rating' => 'nullable|numeric|min:1|max:5',
            ];
        } elseif (Auth::id() === $performanceReview->reviewer_id) {
            // Reviewer can update review content
            $rules = [
                'overall_rating' => 'nullable|numeric|min:1|max:5',
                'category_ratings' => 'nullable|array',
                'strengths' => 'nullable|string|max:1000',
                'areas_for_improvement' => 'nullable|string|max:1000',
                'goals' => 'nullable|string|max:1000',
                'reviewer_comments' => 'nullable|string|max:1000',
                'status' => 'nullable|in:draft,submitted,reviewed,completed',
            ];
        }

        $request->validate($rules);

        try {
            $updatedReview = $this->service->updatePerformanceReview(
                $performanceReview,
                $request->all(),
                Auth::user()
            );

            return response()->json([
                'data' => $updatedReview->load(['employee', 'reviewer']),
                'message' => 'Performance review updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete performance review
     */
    public function destroy(PerformanceReview $performanceReview): JsonResponse
    {
        // Only reviewer can delete draft reviews
        if ($performanceReview->reviewer_id !== Auth::id() || !$performanceReview->isDraft()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $performanceReview->delete();

        return response()->json([
            'message' => 'Performance review deleted successfully.',
        ]);
    }

    /**
     * Get performance statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['review_period', 'department_id']);

        $stats = $this->service->getPerformanceStatistics($filters);

        return response()->json([
            'data' => $stats,
            'message' => 'Performance statistics retrieved successfully.',
        ]);
    }

    /**
     * Get performance reviews report (admin/manager view)
     */
    public function report(Request $request): JsonResponse
    {
        // Check if user has permission to view reports
        if (!Gate::allows('view-performance-reports')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $filters = $request->only([
            'employee_id', 'reviewer_id', 'status', 'review_period',
            'department_id', 'overdue', 'per_page'
        ]);

        $report = $this->service->getPerformanceReviewsReport($filters);

        return response()->json([
            'data' => $report,
            'message' => 'Performance reviews report retrieved successfully.',
        ]);
    }

    /**
     * Get employee's performance history
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        $limit = $request->get('limit', 10);
        $history = $this->service->getEmployeePerformanceHistory($user->employee, $limit);

        return response()->json([
            'data' => $history,
            'message' => 'Performance history retrieved successfully.',
        ]);
    }

    /**
     * Bulk create performance reviews
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'review_period' => 'required|string',
            'due_date' => 'nullable|date|after:today',
        ]);

        $employees = \App\Models\Employee::whereIn('id', $request->employee_ids)->get();

        $results = $this->service->bulkCreatePerformanceReviews(
            $request->employee_ids,
            Auth::user(),
            $request->only(['review_period', 'due_date'])
        );

        return response()->json([
            'data' => $results,
            'message' => 'Bulk performance review creation completed.',
        ]);
    }

    /**
     * Get available review periods
     */
    public function periods(): JsonResponse
    {
        $periods = $this->service->getAvailableReviewPeriods();

        return response()->json([
            'data' => $periods,
            'message' => 'Available review periods retrieved successfully.',
        ]);
    }

    /**
     * Get review categories
     */
    public function categories(): JsonResponse
    {
        $categories = $this->service->getReviewCategories();

        return response()->json([
            'data' => $categories,
            'message' => 'Review categories retrieved successfully.',
        ]);
    }

    /**
     * Submit performance review
     */
    public function submit(PerformanceReview $performanceReview): JsonResponse
    {
        if ($performanceReview->employee_id !== Auth::id() || !$performanceReview->isDraft()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $submittedReview = $this->service->submitPerformanceReview($performanceReview);

            return response()->json([
                'data' => $submittedReview,
                'message' => 'Performance review submitted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete performance review
     */
    public function complete(PerformanceReview $performanceReview): JsonResponse
    {
        if ($performanceReview->reviewer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $completedReview = $this->service->completePerformanceReview($performanceReview);

            return response()->json([
                'data' => $completedReview,
                'message' => 'Performance review completed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check if user can access the review
     */
    private function canAccessReview(PerformanceReview $review): bool
    {
        $user = Auth::user();

        // Employee can access their own reviews
        if ($user->employee && $user->employee->id === $review->employee_id) {
            return true;
        }

        // Reviewer can access reviews they created
        if ($review->reviewer_id === $user->id) {
            return true;
        }

        // Admin/manager can access all reviews
        return Gate::allows('view-all-performance-reviews');
    }

    /**
     * Check if user can update the review
     */
    private function canUpdateReview(PerformanceReview $review): bool
    {
        $user = Auth::user();

        // Employee can update their own draft/submitted reviews
        if ($user->employee && $user->employee->id === $review->employee_id &&
            in_array($review->status, ['draft', 'submitted'])) {
            return true;
        }

        // Reviewer can update reviews they created (except completed ones)
        if ($review->reviewer_id === $user->id && !$review->isCompleted()) {
            return true;
        }

        return false;
    }
}