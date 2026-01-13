<?php

namespace App\Services;

use App\Models\PerformanceReview;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PerformanceReviewCreated;
use App\Notifications\PerformanceReviewCompleted;

class PerformanceReviewService
{
    /**
     * Create a new performance review
     *
     * @param Employee $employee
     * @param User $reviewer
     * @param array $data
     * @return PerformanceReview
     */
    public function createPerformanceReview(Employee $employee, User $reviewer, array $data): PerformanceReview
    {
        // Check if review already exists for this period
        $existing = PerformanceReview::where('employee_id', $employee->id)
            ->where('review_period', $data['review_period'])
            ->first();

        if ($existing) {
            throw new \Exception('Performance review already exists for this period.');
        }

        $review = PerformanceReview::create([
            'employee_id' => $employee->id,
            'reviewer_id' => $reviewer->id,
            'review_period' => $data['review_period'],
            'review_date' => $data['review_date'] ?? now(),
            'due_date' => $data['due_date'] ?? null,
            'status' => 'draft',
        ]);

        // Notify employee
        if ($employee->user) {
            $employee->user->notify(new PerformanceReviewCreated($review));
        }

        return $review;
    }

    /**
     * Update performance review
     *
     * @param PerformanceReview $review
     * @param array $data
     * @param User $updater
     * @return PerformanceReview
     */
    public function updatePerformanceReview(PerformanceReview $review, array $data, User $updater): PerformanceReview
    {
        // Only allow updates if review is not completed
        if ($review->isCompleted()) {
            throw new \Exception('Cannot update a completed performance review.');
        }

        $updateData = [];

        // Handle different update scenarios based on user role and current status
        if ($updater->id === $review->employee_id) {
            // Employee can update their comments
            if (isset($data['employee_comments'])) {
                $updateData['employee_comments'] = $data['employee_comments'];
            }
            if ($review->isDraft()) {
                $updateData['status'] = 'submitted';
            }
        } elseif ($updater->id === $review->reviewer_id) {
            // Reviewer can update review content
            $reviewerFields = [
                'overall_rating', 'category_ratings', 'strengths',
                'areas_for_improvement', 'goals', 'reviewer_comments'
            ];

            foreach ($reviewerFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (isset($data['status']) && in_array($data['status'], ['reviewed', 'completed'])) {
                $updateData['status'] = $data['status'];
            }
        }

        if (!empty($updateData)) {
            $review->update($updateData);

            // Notify when review is completed
            if ($review->fresh()->isCompleted() && $review->employee->user) {
                $review->employee->user->notify(new PerformanceReviewCompleted($review));
            }
        }

        return $review->fresh();
    }

    /**
     * Submit performance review for review
     *
     * @param PerformanceReview $review
     * @return PerformanceReview
     */
    public function submitPerformanceReview(PerformanceReview $review): PerformanceReview
    {
        if (!$review->isDraft()) {
            throw new \Exception('Only draft reviews can be submitted.');
        }

        $review->update(['status' => 'submitted']);

        return $review;
    }

    /**
     * Complete performance review
     *
     * @param PerformanceReview $review
     * @return PerformanceReview
     */
    public function completePerformanceReview(PerformanceReview $review): PerformanceReview
    {
        if ($review->isCompleted()) {
            throw new \Exception('Review is already completed.');
        }

        $review->update([
            'status' => 'completed',
            'review_date' => now(),
        ]);

        // Notify employee
        if ($review->employee->user) {
            $review->employee->user->notify(new PerformanceReviewCompleted($review));
        }

        return $review;
    }

    /**
     * Get performance review statistics
     *
     * @param array $filters
     * @return array
     */
    public function getPerformanceStatistics(array $filters = []): array
    {
        $query = PerformanceReview::query();

        if (!empty($filters['review_period'])) {
            $query->byPeriod($filters['review_period']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $reviews = $query->with('employee')->get();

        $stats = [
            'total_reviews' => $reviews->count(),
            'completed_reviews' => $reviews->where('status', 'completed')->count(),
            'pending_reviews' => $reviews->whereIn('status', ['draft', 'submitted'])->count(),
            'overdue_reviews' => $reviews->filter(fn($r) => $r->isOverdue())->count(),
            'average_rating' => $reviews->where('status', 'completed')->avg('overall_rating') ?? 0,
        ];

        // Rating distribution
        $completedReviews = $reviews->where('status', 'completed');
        $stats['rating_distribution'] = [
            'excellent' => $completedReviews->where('overall_rating', '>=', 4.5)->count(),
            'good' => $completedReviews->whereBetween('overall_rating', [3.5, 4.4])->count(),
            'average' => $completedReviews->whereBetween('overall_rating', [2.5, 3.4])->count(),
            'poor' => $completedReviews->where('overall_rating', '<', 2.5)->count(),
        ];

        return $stats;
    }

    /**
     * Get performance reviews report
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPerformanceReviewsReport(array $filters = [])
    {
        $query = PerformanceReview::with(['employee.department', 'employee.designation', 'reviewer']);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['reviewer_id'])) {
            $query->byReviewer($filters['reviewer_id']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['review_period'])) {
            $query->byPeriod($filters['review_period']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['overdue'])) {
            $query->overdue();
        }

        return $query->orderBy('review_date', 'desc')
                    ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Get employee's performance history
     *
     * @param Employee $employee
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmployeePerformanceHistory(Employee $employee, int $limit = 10)
    {
        return PerformanceReview::where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->with('reviewer')
            ->orderBy('review_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk create performance reviews for multiple employees
     *
     * @param array $employees
     * @param User $reviewer
     * @param array $data
     * @return array
     */
    public function bulkCreatePerformanceReviews(array $employees, User $reviewer, array $data): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        DB::beginTransaction();

        try {
            foreach ($employees as $employeeId) {
                try {
                    $employee = Employee::find($employeeId);
                    if (!$employee) {
                        $results['errors'][] = "Employee ID {$employeeId} not found.";
                        $results['failed']++;
                        continue;
                    }

                    $this->createPerformanceReview($employee, $reviewer, $data);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Error creating review for employee ID {$employeeId}: " . $e->getMessage();
                    $results['failed']++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = 'Transaction failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Get available review periods
     *
     * @return array
     */
    public function getAvailableReviewPeriods(): array
    {
        $currentYear = now()->year;
        $periods = [];

        // Quarterly periods
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $periods[] = "{$currentYear}-Q{$quarter}";
        }

        // Half-year periods
        $periods[] = "{$currentYear}-H1";
        $periods[] = "{$currentYear}-H2";

        // Annual period
        $periods[] = "{$currentYear}-Annual";

        return $periods;
    }

    /**
     * Get review categories
     *
     * @return array
     */
    public function getReviewCategories(): array
    {
        return [
            'job_knowledge' => 'Job Knowledge',
            'work_quality' => 'Work Quality',
            'productivity' => 'Productivity',
            'communication' => 'Communication',
            'teamwork' => 'Teamwork',
            'leadership' => 'Leadership',
            'initiative' => 'Initiative',
            'attendance' => 'Attendance',
            'punctuality' => 'Punctuality',
            'problem_solving' => 'Problem Solving',
        ];
    }
}