<?php

namespace Tests\Unit\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = new AttendanceService();
    }

    /** @test */
    public function it_can_check_in_an_employee()
    {
        $employee = Employee::factory()->create();

        $attendance = $this->attendanceService->checkIn($employee, [
            'check_in_time' => '2024-01-15 09:00:00',
        ]);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertEquals($employee->id, $attendance->employee_id);
        $this->assertEquals('2024-01-15', $attendance->date);
        $this->assertEquals('present', $attendance->status);
        $this->assertNotNull($attendance->check_in_time);
        $this->assertNull($attendance->check_out_time);
    }

    /** @test */
    public function it_can_check_out_an_employee()
    {
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'check_in_time' => '2024-01-15 09:00:00',
            'date' => '2024-01-15',
        ]);

        $updatedAttendance = $this->attendanceService->checkOut($employee, [
            'check_out_time' => '2024-01-15 17:00:00',
        ]);

        $this->assertNotNull($updatedAttendance->check_out_time);
        $this->assertEquals(8.0, $updatedAttendance->total_hours);
    }

    /** @test */
    public function it_prevents_double_check_in()
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'check_in_time' => '2024-01-15 09:00:00',
            'date' => '2024-01-15',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Employee is already checked in for today.');

        $this->attendanceService->checkIn($employee, [
            'date' => '2024-01-15',
        ]);
    }

    /** @test */
    public function it_prevents_check_out_without_check_in()
    {
        $employee = Employee::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No check-in record found for today.');

        $this->attendanceService->checkOut($employee);
    }

    /** @test */
    public function it_marks_employee_as_late_when_checking_in_after_9am()
    {
        $employee = Employee::factory()->create();

        $attendance = $this->attendanceService->checkIn($employee, [
            'check_in_time' => '2024-01-15 09:30:00',
        ]);

        $this->assertTrue($attendance->isLate());
        $this->assertEquals('late', $attendance->status);
    }

    /** @test */
    public function it_marks_employee_as_half_day_for_late_check_in_and_early_check_out()
    {
        $employee = Employee::factory()->create();

        $attendance = $this->attendanceService->checkIn($employee, [
            'check_in_time' => '2024-01-15 09:30:00',
        ]);

        $updatedAttendance = $this->attendanceService->checkOut($employee, [
            'check_out_time' => '2024-01-15 13:00:00', // Early departure
        ]);

        $this->assertEquals('half_day', $updatedAttendance->status);
    }

    /** @test */
    public function it_can_get_attendance_summary()
    {
        $employee = Employee::factory()->create();

        // Create attendance records
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2024-01-01',
            'status' => 'present',
            'check_in_time' => '2024-01-01 09:00:00',
            'check_out_time' => '2024-01-01 17:00:00',
        ]);

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2024-01-02',
            'status' => 'absent',
        ]);

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => '2024-01-03',
            'status' => 'late',
            'check_in_time' => '2024-01-03 09:30:00',
            'check_out_time' => '2024-01-03 17:00:00',
        ]);

        $summary = $this->attendanceService->getAttendanceSummary(
            $employee,
            '2024-01-01',
            '2024-01-03'
        );

        $this->assertEquals(3, $summary['total_days']);
        $this->assertEquals(1, $summary['present_days']);
        $this->assertEquals(1, $summary['absent_days']);
        $this->assertEquals(1, $summary['late_days']);
        $this->assertEquals(16.0, $summary['total_hours']);
        $this->assertEquals(33.33, $summary['attendance_percentage']);
    }

    /** @test */
    public function it_can_mark_employee_as_absent()
    {
        $employee = Employee::factory()->create();

        $attendance = $this->attendanceService->markAbsent($employee, '2024-01-15', 'Sick leave');

        $this->assertEquals('absent', $attendance->status);
        $this->assertEquals('Sick leave', $attendance->notes);
    }

    /** @test */
    public function it_can_bulk_mark_attendance()
    {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        $attendanceData = [
            [
                'employee_id' => $employee1->id,
                'date' => '2024-01-15',
                'status' => 'present',
                'check_in_time' => '2024-01-15 09:00:00',
                'check_out_time' => '2024-01-15 17:00:00',
            ],
            [
                'employee_id' => $employee2->id,
                'date' => '2024-01-15',
                'status' => 'absent',
                'notes' => 'Vacation',
            ],
        ];

        $results = $this->attendanceService->bulkMarkAttendance($attendanceData);

        $this->assertEquals(2, $results['success']);
        $this->assertEquals(0, $results['failed']);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee1->id,
            'date' => '2024-01-15',
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee2->id,
            'date' => '2024-01-15',
            'status' => 'absent',
            'notes' => 'Vacation',
        ]);
    }

    /** @test */
    public function it_can_get_current_attendance_status()
    {
        $employee = Employee::factory()->create();

        // No attendance record
        $status = $this->attendanceService->getCurrentStatus($employee);
        $this->assertEquals('not_checked_in', $status['status']);
        $this->assertTrue($status['can_check_in']);
        $this->assertFalse($status['can_check_out']);

        // Checked in
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => today()->format('Y-m-d'),
            'check_in_time' => now()->subHours(2),
        ]);

        $status = $this->attendanceService->getCurrentStatus($employee);
        $this->assertEquals('checked_in', $status['status']);
        $this->assertFalse($status['can_check_in']);
        $this->assertTrue($status['can_check_out']);
    }

    /** @test */
    public function it_can_get_attendance_report_with_filters()
    {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        Attendance::factory()->create([
            'employee_id' => $employee1->id,
            'date' => '2024-01-15',
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'employee_id' => $employee2->id,
            'date' => '2024-01-15',
            'status' => 'absent',
        ]);

        $report = $this->attendanceService->getAttendanceReport([
            'status' => 'present',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $this->assertEquals(1, $report->total());
        $this->assertEquals('present', $report->first()->status);
    }

    /** @test */
    public function attendance_model_scopes_work_correctly()
    {
        $employee = Employee::factory()->create();

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => today()->format('Y-m-d'),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date' => today()->subDays(1)->format('Y-m-d'),
            'status' => 'absent',
        ]);

        $todayAttendances = Attendance::today()->get();
        $this->assertEquals(1, $todayAttendances->count());
        $this->assertEquals('present', $todayAttendances->first()->status);

        $presentAttendances = Attendance::byStatus('present')->get();
        $this->assertEquals(1, $presentAttendances->count());
    }
}