<?php

namespace Tests\Unit\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LeaveRequestService $leaveService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaveService = new LeaveRequestService();
    }

    /** @test */
    public function it_can_create_a_leave_request()
    {
        $employee = Employee::factory()->create();

        $data = [
            'leave_type' => 'annual',
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-05',
            'reason' => 'Family vacation',
        ];

        $leaveRequest = $this->leaveService->createLeaveRequest($employee, $data);

        $this->assertInstanceOf(LeaveRequest::class, $leaveRequest);
        $this->assertEquals($employee->id, $leaveRequest->employee_id);
        $this->assertEquals('annual', $leaveRequest->leave_type);
        $this->assertEquals('pending', $leaveRequest->status);
        $this->assertEquals(5, $leaveRequest->days_requested); // 5 business days
    }

    /** @test */
    public function it_can_approve_a_leave_request()
    {
        $employee = Employee::factory()->create();
        $approver = User::factory()->create();

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);

        $approvedRequest = $this->leaveService->approveLeaveRequest($leaveRequest, $approver, 'Approved for vacation');

        $this->assertEquals('approved', $approvedRequest->status);
        $this->assertEquals($approver->id, $approvedRequest->approved_by);
        $this->assertNotNull($approvedRequest->approved_at);
        $this->assertEquals('Approved for vacation', $approvedRequest->approval_notes);
    }

    /** @test */
    public function it_can_reject_a_leave_request()
    {
        $employee = Employee::factory()->create();
        $approver = User::factory()->create();

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);

        $rejectedRequest = $this->leaveService->rejectLeaveRequest($leaveRequest, $approver, 'Insufficient balance');

        $this->assertEquals('rejected', $rejectedRequest->status);
        $this->assertEquals($approver->id, $rejectedRequest->approved_by);
        $this->assertEquals('Insufficient balance', $rejectedRequest->rejection_reason);
    }

    /** @test */
    public function it_can_cancel_a_leave_request()
    {
        $employee = Employee::factory()->create();

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);

        $cancelledRequest = $this->leaveService->cancelLeaveRequest($leaveRequest);

        $this->assertEquals('cancelled', $cancelledRequest->status);
    }

    /** @test */
    public function it_prevents_cancelling_non_pending_requests()
    {
        $employee = Employee::factory()->create();

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'approved',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only pending leave requests can be cancelled.');

        $this->leaveService->cancelLeaveRequest($leaveRequest);
    }

    /** @test */
    public function it_calculates_business_days_correctly()
    {
        // Monday to Friday (5 business days)
        $days = $this->invokePrivateMethod($this->leaveService, 'calculateBusinessDays', ['2024-01-29', '2024-02-02']);
        $this->assertEquals(5, $days);

        // Including weekend (still 5 business days)
        $days = $this->invokePrivateMethod($this->leaveService, 'calculateBusinessDays', ['2024-01-27', '2024-02-02']);
        $this->assertEquals(5, $days);
    }

    /** @test */
    public function it_validates_leave_request_dates()
    {
        $employee = Employee::factory()->create();

        // End date before start date
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Start date cannot be after end date.');

        $this->leaveService->createLeaveRequest($employee, [
            'leave_type' => 'annual',
            'start_date' => '2024-02-05',
            'end_date' => '2024-02-01',
            'reason' => 'Test',
        ]);
    }

    /** @test */
    public function it_prevents_past_date_leave_requests()
    {
        $employee = Employee::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot request leave for past dates.');

        $this->leaveService->createLeaveRequest($employee, [
            'leave_type' => 'annual',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-02',
            'reason' => 'Test',
        ]);
    }

    /** @test */
    public function it_prevents_overlapping_leave_requests()
    {
        $employee = Employee::factory()->create();

        // Create existing approved leave
        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-05',
            'status' => 'approved',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You have overlapping leave requests for the selected dates.');

        $this->leaveService->createLeaveRequest($employee, [
            'leave_type' => 'annual',
            'start_date' => '2024-02-03',
            'end_date' => '2024-02-07',
            'reason' => 'Overlapping leave',
        ]);
    }

    /** @test */
    public function it_can_get_leave_balance()
    {
        $employee = Employee::factory()->create();

        // Create some approved leaves
        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-05',
            'days_requested' => 5,
            'status' => 'approved',
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type' => 'sick',
            'start_date' => '2024-01-10',
            'end_date' => '2024-01-12',
            'days_requested' => 3,
            'status' => 'approved',
        ]);

        $balance = $this->leaveService->getLeaveBalance($employee, 2024);

        $this->assertEquals(25, $balance['annual']['entitled']);
        $this->assertEquals(5, $balance['annual']['used']);
        $this->assertEquals(20, $balance['annual']['remaining']);

        $this->assertEquals(10, $balance['sick']['entitled']);
        $this->assertEquals(3, $balance['sick']['used']);
        $this->assertEquals(7, $balance['sick']['remaining']);
    }

    /** @test */
    public function it_can_get_leave_requests_report()
    {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        LeaveRequest::factory()->create([
            'employee_id' => $employee1->id,
            'leave_type' => 'annual',
            'status' => 'approved',
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $employee2->id,
            'leave_type' => 'sick',
            'status' => 'pending',
        ]);

        $report = $this->leaveService->getLeaveRequestsReport(['status' => 'approved']);

        $this->assertEquals(1, $report->total());
        $this->assertEquals('approved', $report->first()->status);
    }

    /** @test */
    public function it_can_get_upcoming_leaves()
    {
        $employee = Employee::factory()->create();

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'start_date' => today()->addDays(5),
            'end_date' => today()->addDays(7),
            'status' => 'approved',
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'start_date' => today()->addDays(50), // Too far in future
            'end_date' => today()->addDays(52),
            'status' => 'approved',
        ]);

        $upcoming = $this->leaveService->getUpcomingLeaves(30);

        $this->assertEquals(1, $upcoming->count());
        $this->assertEquals(today()->addDays(5)->format('Y-m-d'), $upcoming->first()->start_date->format('Y-m-d'));
    }

    /** @test */
    public function leave_request_model_scopes_work_correctly()
    {
        $employee = Employee::factory()->create();

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'leave_type' => 'annual',
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'approved',
            'leave_type' => 'sick',
        ]);

        $pendingRequests = LeaveRequest::pending()->get();
        $this->assertEquals(1, $pendingRequests->count());
        $this->assertEquals('pending', $pendingRequests->first()->status);

        $approvedRequests = LeaveRequest::approved()->get();
        $this->assertEquals(1, $approvedRequests->count());
        $this->assertEquals('approved', $approvedRequests->first()->status);

        $annualRequests = LeaveRequest::byType('annual')->get();
        $this->assertEquals(1, $annualRequests->count());
        $this->assertEquals('annual', $annualRequests->first()->leave_type);
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod($object, $method, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}