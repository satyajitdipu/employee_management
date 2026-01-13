<?php

namespace App\Notifications;

use App\Models\PerformanceReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PerformanceReviewCreated extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Performance Review Created')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new performance review has been created for you.')
            ->line('Review Period: ' . $this->performanceReview->review_period)
            ->line('Due Date: ' . ($this->performanceReview->due_date ? $this->performanceReview->due_date->format('M d, Y') : 'Not specified'))
            ->action('View Review', url('/performance-reviews/' . $this->performanceReview->id))
            ->line('Please complete your self-assessment before the due date.')
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
            'type' => 'performance_review_created',
            'message' => 'A new performance review has been created for you.',
            'review_period' => $this->performanceReview->review_period,
            'due_date' => $this->performanceReview->due_date,
        ];
    }
}