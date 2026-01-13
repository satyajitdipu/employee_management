<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public Employee $employee;
    public array $changes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Employee $employee, array $changes = [])
    {
        $this->employee = $employee;
        $this->changes = $changes;
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
        $subject = "Employee Profile Updated: {$this->employee->first_name} {$this->employee->last_name}";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Employee Profile Update Notification')
            ->line("The profile for {$this->employee->first_name} {$this->employee->last_name} has been updated.");

        if (!empty($this->changes)) {
            $message->line('Changes made:');
            foreach ($this->changes as $field => $change) {
                $oldValue = $change['old'] ?? 'Not set';
                $newValue = $change['new'] ?? 'Not set';
                $message->line("• {$field}: {$oldValue} → {$newValue}");
            }
        }

        return $message
            ->action('View Employee Profile', url("/employees/{$this->employee->id}"))
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
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->first_name . ' ' . $this->employee->last_name,
            'employee_code' => $this->employee->employee_code,
            'type' => 'employee_updated',
            'changes' => $this->changes,
            'message' => "Employee profile for {$this->employee->first_name} {$this->employee->last_name} has been updated.",
        ];
    }
}