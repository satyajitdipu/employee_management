<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeCreated;
use App\Notifications\EmployeeUpdated;
use Illuminate\Support\Facades\Notification;

class EmployeeNotificationService
{
    /**
     * Send notification when employee is created
     *
     * @param Employee $employee
     * @return void
     */
    public function sendEmployeeCreatedNotification(Employee $employee): void
    {
        // Notify HR administrators
        $hrUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'HR Admin');
        })->get();

        if ($hrUsers->isNotEmpty()) {
            Notification::send($hrUsers, new EmployeeCreated($employee));
        }

        // Notify the employee's manager if exists
        if ($employee->manager && $employee->manager->user) {
            $employee->manager->user->notify(new EmployeeCreated($employee));
        }
    }

    /**
     * Send notification when employee is updated
     *
     * @param Employee $employee
     * @param array $changes
     * @return void
     */
    public function sendEmployeeUpdatedNotification(Employee $employee, array $changes = []): void
    {
        // Notify HR administrators
        $hrUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'HR Admin');
        })->get();

        if ($hrUsers->isNotEmpty()) {
            Notification::send($hrUsers, new EmployeeUpdated($employee, $changes));
        }

        // Notify the employee if they have a user account
        if ($employee->user) {
            $employee->user->notify(new EmployeeUpdated($employee, $changes));
        }

        // Notify the employee's manager if exists and changes are significant
        if ($employee->manager && $employee->manager->user && $this->hasSignificantChanges($changes)) {
            $employee->manager->user->notify(new EmployeeUpdated($employee, $changes));
        }
    }

    /**
     * Check if changes are significant enough to notify manager
     *
     * @param array $changes
     * @return bool
     */
    private function hasSignificantChanges(array $changes): bool
    {
        $significantFields = [
            'status',
            'department_id',
            'designation_id',
            'salary',
            'manager_id'
        ];

        foreach ($changes as $field => $change) {
            if (in_array($field, $significantFields)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send bulk notifications for multiple employees
     *
     * @param array $notifications
     * @return void
     */
    public function sendBulkNotifications(array $notifications): void
    {
        foreach ($notifications as $notification) {
            $type = $notification['type'];
            $employee = $notification['employee'];
            $changes = $notification['changes'] ?? [];

            switch ($type) {
                case 'created':
                    $this->sendEmployeeCreatedNotification($employee);
                    break;
                case 'updated':
                    $this->sendEmployeeUpdatedNotification($employee, $changes);
                    break;
            }
        }
    }

    /**
     * Get notification preferences for a user
     *
     * @param User $user
     * @return array
     */
    public function getNotificationPreferences(User $user): array
    {
        // This could be expanded to store user preferences in database
        return [
            'employee_created' => true,
            'employee_updated' => true,
            'email_notifications' => true,
            'database_notifications' => true,
        ];
    }

    /**
     * Update notification preferences for a user
     *
     * @param User $user
     * @param array $preferences
     * @return void
     */
    public function updateNotificationPreferences(User $user, array $preferences): void
    {
        // This could be expanded to store preferences in database
        // For now, we'll just validate the preferences
        $validPreferences = [
            'employee_created' => filter_var($preferences['employee_created'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'employee_updated' => filter_var($preferences['employee_updated'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'email_notifications' => filter_var($preferences['email_notifications'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'database_notifications' => filter_var($preferences['database_notifications'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        // Store in user meta or preferences table
        $user->update(['notification_preferences' => json_encode($validPreferences)]);
    }
}