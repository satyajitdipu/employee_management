<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Services\PerformanceReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $reviewer;
    protected Employee $employee;
    protected PerformanceReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->reviewer = User::factory()->create();
        $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
        $this->service = new PerformanceReviewService();
    }

    /** @test */
    public function employee_can_view_their_performance_reviews()
    {
        $review = PerformanceReview::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/performance-reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'review_period',
                        'status',
                        'overall_rating',
                        'reviewer' => ['name'],
                    ]
                ]
            ]);
    }

    /** @test */
    public function reviewer_can_view_reviews_they_created()
    {
        $review = PerformanceReview::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
        ]);

        $response = $this->actingAs($this->reviewer)
            ->get('/api/performance-reviews/my-reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function employee_can_submit_self_assessment()
    {
        $review = PerformanceReview::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'draft',
        ]);

        $data = [
            'employee_comments' => 'I have been working on improving my skills.',
            'self_rating' => 4.0,
        ];

        $response = $this->actingAs($this->user)
            ->put("/api/performance-reviews/{$review->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'submitted',
                    'employee_comments' => 'I have been working on improving my skills.',
                ]
            ]);
    }

    /** @test */
    public function reviewer_can_complete_performance_review()
    {
        $review = PerformanceReview::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'status' => 'submitted',
        ]);

        $data = [
            'overall_rating' => 4.5,
            'strengths' => 'Excellent technical skills and problem-solving abilities.',
            'areas_for_improvement' => 'Could improve communication with team members.',
            'goals' => 'Take on more leadership responsibilities.',
            'reviewer_comments' => 'Great performance overall.',
            'status' => 'completed',
        ];

        $response = $this->actingAs($this->reviewer)
            ->put("/api/performance-reviews/{$review->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'completed',
                    'overall_rating' => 4.5,
                    'strengths' => 'Excellent technical skills and problem-solving abilities.',
                ]
            ]);
    }

    /** @test */
    public function cannot_update_completed_review()
    {
        $review = PerformanceReview::factory()->create([
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->reviewer)
            ->put("/api/performance-reviews/{$review->id}", [
                'overall_rating' => 3.0,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot update a completed performance review.',
            ]);
    }

    /** @test */
    public function can_get_performance_statistics()
    {
        PerformanceReview::factory()->count(3)->create(['status' => 'completed']);
        PerformanceReview::factory()->count(2)->create(['status' => 'draft']);

        $response = $this->actingAs($this->reviewer)
            ->get('/api/performance-reviews/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_reviews',
                    'completed_reviews',
                    'pending_reviews',
                    'average_rating',
                    'rating_distribution',
                ]
            ]);
    }

    /** @test */
    public function can_filter_performance_reviews_by_status()
    {
        PerformanceReview::factory()->create(['status' => 'completed']);
        PerformanceReview::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->reviewer)
            ->get('/api/performance-reviews?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function can_filter_performance_reviews_by_department()
    {
        $department = \App\Models\Department::factory()->create();
        $employeeInDept = Employee::factory()->create(['department_id' => $department->id]);
        $employeeNotInDept = Employee::factory()->create();

        PerformanceReview::factory()->create(['employee_id' => $employeeInDept->id]);
        PerformanceReview::factory()->create(['employee_id' => $employeeNotInDept->id]);

        $response = $this->actingAs($this->reviewer)
            ->get("/api/performance-reviews?department_id={$department->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function can_get_employee_performance_history()
    {
        PerformanceReview::factory()->count(3)->create([
            'employee_id' => $this->employee->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/api/performance-reviews/history');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_bulk_create_performance_reviews()
    {
        $employees = Employee::factory()->count(3)->create();
        $employeeIds = $employees->pluck('id')->toArray();

        $data = [
            'employee_ids' => $employeeIds,
            'review_period' => '2024-Q2',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->reviewer)
            ->post('/api/performance-reviews/bulk', $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'success' => 3,
                    'failed' => 0,
                ]
            ]);

        $this->assertEquals(3, PerformanceReview::count());
    }

    /** @test */
    public function bulk_create_handles_validation_errors()
    {
        $data = [
            'employee_ids' => [999], // Non-existent employee
            'review_period' => '2024-Q2',
        ];

        $response = $this->actingAs($this->reviewer)
            ->post('/api/performance-reviews/bulk', $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'success' => 0,
                    'failed' => 1,
                ]
            ]);
    }

    /** @test */
    public function can_get_available_review_periods()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/performance-reviews/periods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [] // Array of periods
                ]
            ]);
    }

    /** @test */
    public function can_get_review_categories()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/performance-reviews/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'job_knowledge',
                    'work_quality',
                    'communication',
                ]
            ]);
    }

    /** @test */
    public function unauthorized_user_cannot_access_reviews()
    {
        $review = PerformanceReview::factory()->create();

        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)
            ->get("/api/performance-reviews/{$review->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function reviewer_can_access_reviews_they_created()
    {
        $review = PerformanceReview::factory()->create([
            'reviewer_id' => $this->reviewer->id,
        ]);

        $response = $this->actingAs($this->reviewer)
            ->get("/api/performance-reviews/{$review->id}");

        $response->assertStatus(200);
    }
}