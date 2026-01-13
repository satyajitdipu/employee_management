<?php

namespace Database\Factories;

use App\Models\PerformanceReview;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformanceReviewFactory extends Factory
{
    protected $model = PerformanceReview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'reviewer_id' => User::factory(),
            'review_period' => $this->faker->randomElement([
                '2024-Q1', '2024-Q2', '2024-Q3', '2024-Q4',
                '2024-H1', '2024-H2', '2024-Annual'
            ]),
            'review_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'due_date' => $this->faker->optional(0.8)->dateTimeBetween('now', '+2 months'),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'reviewed', 'completed']),
            'overall_rating' => $this->faker->optional(0.7)->randomFloat(1, 1, 5),
            'category_ratings' => $this->faker->optional(0.6)->randomElements([
                'job_knowledge' => $this->faker->numberBetween(1, 5),
                'work_quality' => $this->faker->numberBetween(1, 5),
                'productivity' => $this->faker->numberBetween(1, 5),
                'communication' => $this->faker->numberBetween(1, 5),
                'teamwork' => $this->faker->numberBetween(1, 5),
                'leadership' => $this->faker->numberBetween(1, 5),
                'initiative' => $this->faker->numberBetween(1, 5),
                'attendance' => $this->faker->numberBetween(1, 5),
                'punctuality' => $this->faker->numberBetween(1, 5),
                'problem_solving' => $this->faker->numberBetween(1, 5),
            ], $this->faker->numberBetween(3, 8)),
            'employee_comments' => $this->faker->optional(0.8)->paragraph(),
            'reviewer_comments' => $this->faker->optional(0.7)->paragraph(),
            'strengths' => $this->faker->optional(0.9)->sentences(2, true),
            'areas_for_improvement' => $this->faker->optional(0.7)->sentences(2, true),
            'goals' => $this->faker->optional(0.8)->sentences(3, true),
        ];
    }

    /**
     * Indicate that the review is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'overall_rating' => null,
            'reviewer_comments' => null,
        ]);
    }

    /**
     * Indicate that the review is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'employee_comments' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the review is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'overall_rating' => $this->faker->randomFloat(1, 1, 5),
            'reviewer_comments' => $this->faker->paragraph(),
            'strengths' => $this->faker->sentences(2, true),
            'review_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the review is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-2 months', '-1 week'),
            'status' => $this->faker->randomElement(['draft', 'submitted']),
        ]);
    }

    /**
     * Create a review for a specific period.
     */
    public function forPeriod(string $period): static
    {
        return $this->state(fn (array $attributes) => [
            'review_period' => $period,
        ]);
    }

    /**
     * Create a review with high rating.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => $this->faker->randomFloat(1, 4.0, 5.0),
            'status' => 'completed',
        ]);
    }

    /**
     * Create a review with low rating.
     */
    public function lowRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => $this->faker->randomFloat(1, 1.0, 2.5),
            'status' => 'completed',
        ]);
    }
}