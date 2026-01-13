<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceReview extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period',
        'review_date',
        'overall_rating',
        'category_ratings',
        'strengths',
        'areas_for_improvement',
        'goals',
        'reviewer_comments',
        'employee_comments',
        'status',
        'due_date',
        'attachments',
    ];

    protected $casts = [
        'review_date' => 'date',
        'due_date' => 'date',
        'overall_rating' => 'decimal:1',
        'category_ratings' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Get the employee being reviewed.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the reviewer (manager or HR).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Check if the review is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the review is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if the review is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the review is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isCompleted();
    }

    /**
     * Get overall rating as stars
     */
    public function getRatingStarsAttribute(): string
    {
        if (!$this->overall_rating) {
            return '';
        }

        $fullStars = floor($this->overall_rating);
        $hasHalfStar = ($this->overall_rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

        return str_repeat('★', $fullStars) .
               ($hasHalfStar ? '☆' : '') .
               str_repeat('☆', $emptyStars);
    }

    /**
     * Get rating color class
     */
    public function getRatingColorAttribute(): string
    {
        if (!$this->overall_rating) {
            return 'secondary';
        }

        return match (true) {
            $this->overall_rating >= 4.5 => 'success',
            $this->overall_rating >= 3.5 => 'info',
            $this->overall_rating >= 2.5 => 'warning',
            default => 'danger',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'reviewed' => 'warning',
            'completed' => 'success',
            default => 'light',
        };
    }

    /**
     * Get category ratings as formatted array
     */
    public function getFormattedCategoryRatingsAttribute(): array
    {
        if (!$this->category_ratings) {
            return [];
        }

        $formatted = [];
        foreach ($this->category_ratings as $category => $rating) {
            $formatted[] = [
                'category' => ucwords(str_replace('_', ' ', $category)),
                'rating' => $rating,
                'stars' => $this->formatRatingStars($rating),
            ];
        }

        return $formatted;
    }

    /**
     * Format rating as stars
     */
    private function formatRatingStars(float $rating): string
    {
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

        return str_repeat('★', $fullStars) .
               ($hasHalfStar ? '☆' : '') .
               str_repeat('☆', $emptyStars);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by reviewer
     */
    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    /**
     * Scope for filtering by review period
     */
    public function scopeByPeriod($query, $period)
    {
        return $query->where('review_period', $period);
    }

    /**
     * Scope for overdue reviews
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())
                    ->whereNotIn('status', ['completed']);
    }

    /**
     * Scope for pending reviews (draft or submitted)
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }

    /**
     * Scope for completed reviews
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for reviews requiring action by reviewer
     */
    public function scopeRequiringReview($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId)
                    ->whereIn('status', ['submitted', 'reviewed']);
    }

    /**
     * Scope for reviews requiring action by employee
     */
    public function scopeRequiringEmployeeAction($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId)
                    ->whereIn('status', ['draft', 'reviewed']);
    }
}