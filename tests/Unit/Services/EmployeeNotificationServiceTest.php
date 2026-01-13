<?php

namespace Tests\Unit\Services;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeCreated;
use App\Notifications\EmployeeUpdated;
use App\Services\EmployeeNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmployeeNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmployeeNotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new EmployeeNotificationService();
        Notification::fake();
    }

    /** @test */
    public function it_can_send_employee_created_notification()
    {
        $hrUser = User::factory()->create();
        $hrUser->assignRole('HR Admin');

        $employee = Employee::factory()->create();

        $this->notificationService->sendEmployeeCreatedNotification($employee);

        Notification::assertSentTo($hrUser, EmployeeCreated::class, function ($notification) use ($employee) {
            return $notification->employee->id === $employee->id;
        });
    }

    /** @test */
    public function it_sends_employee_created_notification_to_manager()
    {
        $manager = Employee::factory()->create();
        $managerUser = User::factory()->create();
        $manager->user()->associate($managerUser)->save();

        $employee = Employee::factory()->create(['manager_id' => $manager->id]);

        $this->notificationService->sendEmployeeCreatedNotification($employee);

        Notification::assertSentTo($managerUser, EmployeeCreated::class);
    }

    /** @test */
    public function it_can_send_employee_updated_notification()
    {
        $hrUser = User::factory()->create();
        $hrUser->assignRole('HR Admin');

        $employee = Employee::factory()->create();
        $changes = ['status' => ['old' => 'active', 'new' => 'inactive']];

        $this->notificationService->sendEmployeeUpdatedNotification($employee, $changes);

        Notification::assertSentTo($hrUser, EmployeeUpdated::class, function ($notification) use ($employee, $changes) {
            return $notification->employee->id === $employee->id &&
                   $notification->changes === $changes;
        });
    }

    /** @test */
    public function it_sends_employee_updated_notification_to_employee_user()
    {
        $employee = Employee::factory()->create();
        $employeeUser = User::factory()->create();
        $employee->user()->associate($employeeUser)->save();

        $changes = ['first_name' => ['old' => 'John', 'new' => 'Jane']];

        $this->notificationService->sendEmployeeUpdatedNotification($employee, $changes);

        Notification::assertSentTo($employeeUser, EmployeeUpdated::class);
    }

    /** @test */
    public function it_sends_employee_updated_notification_to_manager_for_significant_changes()
    {
        $manager = Employee::factory()->create();
        $managerUser = User::factory()->create();
        $manager->user()->associate($managerUser)->save();

        $employee = Employee::factory()->create(['manager_id' => $manager->id]);

        // Significant change (status)
        $changes = ['status' => ['old' => 'active', 'new' => 'inactive']];

        $this->notificationService->sendEmployeeUpdatedNotification($employee, $changes);

        Notification::assertSentTo($managerUser, EmployeeUpdated::class);
    }

    /** @test */
    public function it_does_not_send_to_manager_for_insignificant_changes()
    {
        $manager = Employee::factory()->create();
        $managerUser = User::factory()->create();
        $manager->user()->associate($managerUser)->save();

        $employee = Employee::factory()->create(['manager_id' => $manager->id]);

        // Insignificant change (phone)
        $changes = ['phone' => ['old' => '123-456-7890', 'new' => '098-765-4321']];

        $this->notificationService->sendEmployeeUpdatedNotification($employee, $changes);

        Notification::assertNotSentTo($managerUser, EmployeeUpdated::class);
    }

    /** @test */
    public function it_can_send_bulk_notifications()
    {
        $hrUser = User::factory()->create();
        $hrUser->assignRole('HR Admin');

        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        $notifications = [
            ['type' => 'created', 'employee' => $employee1],
            ['type' => 'updated', 'employee' => $employee2, 'changes' => ['status' => ['old' => 'active', 'new' => 'inactive']]],
        ];

        $this->notificationService->sendBulkNotifications($notifications);

        Notification::assertSentTo($hrUser, EmployeeCreated::class);
        Notification::assertSentTo($hrUser, EmployeeUpdated::class);
    }

    /** @test */
    public function it_can_get_notification_preferences()
    {
        $user = User::factory()->create();

        $preferences = $this->notificationService->getNotificationPreferences($user);

        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('employee_created', $preferences);
        $this->assertArrayHasKey('employee_updated', $preferences);
        $this->assertArrayHasKey('email_notifications', $preferences);
        $this->assertArrayHasKey('database_notifications', $preferences);
    }

    /** @test */
    public function it_can_update_notification_preferences()
    {
        $user = User::factory()->create();

        $newPreferences = [
            'employee_created' => false,
            'employee_updated' => true,
            'email_notifications' => false,
            'database_notifications' => true,
        ];

        $this->notificationService->updateNotificationPreferences($user, $newPreferences);

        $user->refresh();
        $storedPreferences = json_decode($user->notification_preferences, true);

        $this->assertFalse($storedPreferences['employee_created']);
        $this->assertTrue($storedPreferences['employee_updated']);
        $this->assertFalse($storedPreferences['email_notifications']);
        $this->assertTrue($storedPreferences['database_notifications']);
    }

    /** @test */
    public function employee_created_notification_has_correct_mail_content()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_code' => 'EMP001',
            'email' => 'john.doe@example.com',
        ]);

        $notification = new EmployeeCreated($employee);
        $user = User::factory()->create();

        $mailMessage = $notification->toMail($user);

        $this->assertStringContains('Welcome to the Company!', $mailMessage->subject);
        $this->assertStringContains('Hello John!', $mailMessage->greeting);
        $this->assertStringContains('Employee Code: EMP001', $mailMessage->introLines[1]);
    }

    /** @test */
    public function employee_updated_notification_has_correct_mail_content()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $changes = ['status' => ['old' => 'active', 'new' => 'inactive']];

        $notification = new EmployeeUpdated($employee, $changes);
        $user = User::factory()->create();

        $mailMessage = $notification->toMail($user);

        $this->assertStringContains('Employee Profile Updated', $mailMessage->subject);
        $this->assertStringContains('Jane Smith', $mailMessage->subject);
    }

    /** @test */
    public function notifications_are_queued()
    {
        $this->assertContains(ShouldQueue::class, class_implements(EmployeeCreated::class));
        $this->assertContains(ShouldQueue::class, class_implements(EmployeeUpdated::class));
    }
}