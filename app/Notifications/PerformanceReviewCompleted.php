<?php

namespace App\Notifications;

use App\Models\PerformanceReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PerformanceReviewCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $performanceReview;

    /**
     * Create a new notification instance.
     */
    public function __construct(PerformanceReview $performanceReview)
    {
        $this->performanceReview = $performanceReview;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $rating = $this->performanceReview->overall_rating;
        $ratingText = $this->getRatingText($rating);

        return (new MailMessage)
            ->subject('Performance Review Completed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your performance review for ' . $this->performanceReview->review_period . ' has been completed.')
            ->line('Overall Rating: ' . number_format($rating, 1) . ' - ' . $ratingText)
            ->action('View Review', url('/performance-reviews/' . $this->performanceReview->id))
            ->line('Please review the feedback and discuss with your manager if needed.')
            ->salutation('Best regards, HR Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'performance_review_id' => $this->performanceReview->id,
            'type' => 'performance_review_completed',
            'message' => 'Your performance review has been completed.',
            'review_period' => $this->performanceReview->review_period,
            'overall_rating' => $this->performanceReview->overall_rating,
            'rating_text' => $this->getRatingText($this->performanceReview->overall_rating),
        ];
    }

    /**
     * Get rating text based on score
     *
     * @param float $rating
     * @return string
     */
    private function getRatingText(float $rating): string
    {
        if ($rating >= 4.5) return 'Excellent';
        if ($rating >= 3.5) return 'Good';
        if ($rating >= 2.5) return 'Average';
        if ($rating >= 1.5) return 'Below Average';
        return 'Poor';
    }
}