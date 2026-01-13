<?php

namespace Tests\Unit\Services;

use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Services\PerformanceReviewService;
use App\Notifications\PerformanceReviewCreated;
use App\Notifications\PerformanceReviewCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PerformanceReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceReviewService $service;
    protected User $reviewer;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PerformanceReviewService();

        // Create test data
        $this->reviewer = User::factory()->create();
        $this->employee = Employee::factory()->create();
    }

    /** @test */
    public function it_can_create_a_performance_review()
    {
        Notification::fake();

        $data = [
            'review_period' => '2024-Q1',
            'review_date' => now(),
            'due_date' => now()->addDays(30),
        ];

        $review = $this->service->createPerformanceReview($this->employee, $this->reviewer, $data);

        $this->assertInstanceOf(PerformanceReview::class, $review);
        $this->assertEquals($this->employee->id, $review->employee_id);
        $this->assertEquals($this->reviewer->id, $review->reviewer_id);
        $this->assertEquals('2024-Q1', $review->review_period);
        $this->assertEquals('draft', $review->status);

        Notification::assertSentTo($this->employee->user, PerformanceReviewCreated::class);
    }

    /** @test */
    public function it_prevents_creating_duplicate_reviews_for_same_period()
    {
        // Create first review
        $data = ['review_period' => '2024-Q1'];
        $this->service->createPerformanceReview($this->employee, $this->reviewer, $data);

        // Try to create duplicate
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Performance review already exists for this period.');

        $this->service->createPerformanceReview($this->employee, $this->reviewer, $data);
    }

    /** @test */
    public function it_can_update_performance_review_by_employee()
    {
        $review = PerformanceReview::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'draft',
        ]);

        $data = ['employee_comments' => 'I have been working hard on my projects.'];

        $updatedReview = $this->service->updatePerformanceReview($review, $data, $this->employee->user);

        $this->assertEquals('submitted', $updatedReview->status);
        $this->assertEquals('I have been working hard on my projects.', $updatedReview->employee_comments);
    }

    /** @test */
    public function it_can_update_performance_review_by_reviewer()
    {
        $review = PerformanceReview::factory()->create([
            'reviewer_id' => $this->reviewer->id,
            'status' => 'submitted',
        ]);

        $data = [
            'overall_rating' => 4.5,
            'strengths' => 'Excellent problem-solving skills',
            'areas_for_improvement' => 'Could improve communication',
            'status' => 'completed',
        ];

        Notification::fake();

        $updatedReview = $this->service->updatePerformanceReview($review, $data, $this->reviewer);

        $this->assertEquals(4.5, $updatedReview->overall_rating);
        $this->assertEquals('Excellent problem-solving skills', $updatedReview->strengths);
        $this->assertEquals('completed', $updatedReview->status);

        Notification::assertSentTo($review->employee->user, PerformanceReviewCompleted::class);
    }

    /** @test */
    public function it_prevents_updating_completed_reviews()
    {
        $review = PerformanceReview::factory()->create(['status' => 'completed']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot update a completed performance review.');

        $this->service->updatePerformanceReview($review, ['overall_rating' => 3.0], $this->reviewer);
    }

    /** @test */
    public function it_can_submit_performance_review()
    {
        $review = PerformanceReview::factory()->create(['status' => 'draft']);

        $submittedReview = $this->service->submitPerformanceReview($review);

        $this->assertEquals('submitted', $submittedReview->status);
    }

    /** @test */
    public function it_prevents_submitting_non_draft_reviews()
    {
        $review = PerformanceReview::factory()->create(['status' => 'submitted']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only draft reviews can be submitted.');

        $this->service->submitPerformanceReview($review);
    }

    /** @test */
    public function it_can_complete_performance_review()
    {
        Notification::fake();

        $review = PerformanceReview::factory()->create(['status' => 'reviewed']);

        $completedReview = $this->service->completePerformanceReview($review);

        $this->assertEquals('completed', $completedReview->status);
        $this->assertNotNull($completedReview->review_date);

        Notification::assertSentTo($review->employee->user, PerformanceReviewCompleted::class);
    }

    /** @test */
    public function it_prevents_completing_already_completed_reviews()
    {
        $review = PerformanceReview::factory()->create(['status' => 'completed']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Review is already completed.');

        $this->service->completePerformanceReview($review);
    }

    /** @test */
    public function it_can_get_performance_statistics()
    {
        // Create test reviews
        PerformanceReview::factory()->create(['status' => 'completed', 'overall_rating' => 4.5]);
        PerformanceReview::factory()->create(['status' => 'completed', 'overall_rating' => 3.0]);
        PerformanceReview::factory()->create(['status' => 'draft']);
        PerformanceReview::factory()->create(['status' => 'submitted', 'due_date' => now()->subDays(1)]);

        $stats = $this->service->getPerformanceStatistics();

        $this->assertEquals(4, $stats['total_reviews']);
        $this->assertEquals(2, $stats['completed_reviews']);
        $this->assertEquals(2, $stats['pending_reviews']);
        $this->assertEquals(1, $stats['overdue_reviews']);
        $this->assertEquals(3.75, $stats['average_rating']);
        $this->assertEquals(1, $stats['rating_distribution']['excellent']);
        $this->assertEquals(1, $stats['rating_distribution']['average']);
    }

    /** @test */
    public function it_can_get_performance_reviews_report()
    {
        PerformanceReview::factory()->count(5)->create();

        $report = $this->service->getPerformanceReviewsReport();

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $report);
        $this->assertEquals(5, $report->total());
    }

    /** @test */
    public function it_can_filter_performance_reviews_report()
    {
        $review1 = PerformanceReview::factory()->create(['status' => 'completed']);
        $review2 = PerformanceReview::factory()->create(['status' => 'draft']);

        $report = $this->service->getPerformanceReviewsReport(['status' => 'completed']);

        $this->assertEquals(1, $report->total());
        $this->assertEquals($review1->id, $report->items()[0]->id);
    }

    /** @test */
    public function it_can_get_employee_performance_history()
    {
        $reviews = PerformanceReview::factory()->count(3)->create([
            'employee_id' => $this->employee->id,
            'status' => 'completed',
        ]);

        $history = $this->service->getEmployeePerformanceHistory($this->employee, 2);

        $this->assertCount(2, $history);
        $this->assertEquals($this->employee->id, $history->first()->employee_id);
    }

    /** @test */
    public function it_can_bulk_create_performance_reviews()
    {
        $employees = Employee::factory()->count(3)->create();
        $employeeIds = $employees->pluck('id')->toArray();

        $data = ['review_period' => '2024-Q2'];

        $results = $this->service->bulkCreatePerformanceReviews($employeeIds, $this->reviewer, $data);

        $this->assertEquals(3, $results['success']);
        $this->assertEquals(0, $results['failed']);
        $this->assertCount(3, PerformanceReview::all());
    }

    /** @test */
    public function it_handles_bulk_create_errors_gracefully()
    {
        $validEmployee = Employee::factory()->create();
        $invalidEmployeeId = 999;

        $employeeIds = [$validEmployee->id, $invalidEmployeeId];

        $data = ['review_period' => '2024-Q2'];

        $results = $this->service->bulkCreatePerformanceReviews($employeeIds, $this->reviewer, $data);

        $this->assertEquals(1, $results['success']);
        $this->assertEquals(1, $results['failed']);
        $this->assertCount(1, PerformanceReview::all());
        $this->assertStringContains('not found', $results['errors'][0]);
    }

    /** @test */
    public function it_can_get_available_review_periods()
    {
        $periods = $this->service->getAvailableReviewPeriods();

        $this->assertContains('2024-Q1', $periods);
        $this->assertContains('2024-Q4', $periods);
        $this->assertContains('2024-H1', $periods);
        $this->assertContains('2024-Annual', $periods);
    }

    /** @test */
    public function it_can_get_review_categories()
    {
        $categories = $this->service->getReviewCategories();

        $this->assertArrayHasKey('job_knowledge', $categories);
        $this->assertArrayHasKey('communication', $categories);
        $this->assertArrayHasKey('leadership', $categories);
        $this->assertEquals('Job Knowledge', $categories['job_knowledge']);
    }
}