<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public Employee $employee;

    /**
     * Create a new notification instance.
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
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
            ->subject('Welcome to the Company!')
            ->greeting("Hello {$this->employee->first_name}!")
            ->line('Welcome to our company! Your employee account has been created successfully.')
            ->line("Employee Code: {$this->employee->employee_code}")
            ->line("Email: {$this->employee->email}")
            ->line("Department: " . ($this->employee->department ? $this->employee->department->name : 'Not assigned'))
            ->line("Designation: " . ($this->employee->designation ? $this->employee->designation->name : 'Not assigned'))
            ->action('View Your Profile', url("/employees/{$this->employee->id}"))
            ->line('Please contact HR if you have any questions.')
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
            'type' => 'employee_created',
            'message' => "New employee {$this->employee->first_name} {$this->employee->last_name} has been added to the system.",
        ];
    }
}