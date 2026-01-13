<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    /**
     * Check in an employee
     *
     * @param Employee $employee
     * @param array $data
     * @return Attendance
     */
    public function checkIn(Employee $employee, array $data = []): Attendance
    {
        $date = $data['date'] ?? today()->format('Y-m-d');
        $checkInTime = $data['check_in_time'] ?? now();

        // Check if already checked in today
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            throw new \Exception('Employee is already checked in for today.');
        }

        if ($existingAttendance) {
            // Update existing record
            $existingAttendance->update([
                'check_in_time' => $checkInTime,
                'status' => $this->determineStatus($checkInTime, null),
                'location' => $data['location'] ?? null,
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'notes' => $data['notes'] ?? null,
            ]);

            return $existingAttendance;
        }

        // Create new attendance record
        return Attendance::create([
            'employee_id' => $employee->id,
            'check_in_time' => $checkInTime,
            'date' => $date,
            'status' => $this->determineStatus($checkInTime, null),
            'location' => $data['location'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Check out an employee
     *
     * @param Employee $employee
     * @param array $data
     * @return Attendance
     */
    public function checkOut(Employee $employee, array $data = []): Attendance
    {
        $date = $data['date'] ?? today()->format('Y-m-d');
        $checkOutTime = $data['check_out_time'] ?? now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            throw new \Exception('No check-in record found for today.');
        }

        if ($attendance->check_out_time) {
            throw new \Exception('Employee is already checked out for today.');
        }

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'status' => $this->determineStatus($attendance->check_in_time, $checkOutTime),
            'location' => $data['location'] ?? $attendance->location,
            'notes' => $data['notes'] ?? $attendance->notes,
        ]);

        return $attendance;
    }

    /**
     * Get attendance summary for an employee
     *
     * @param Employee $employee
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAttendanceSummary(Employee $employee, string $startDate, string $endDate): array
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->dateRange($startDate, $endDate)
            ->get();

        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $lateDays = $attendances->filter(fn($a) => $a->isLate())->count();
        $totalHours = $attendances->sum('total_hours');

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'total_hours' => round($totalHours, 2),
            'attendance_percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
        ];
    }

    /**
     * Mark employee as absent for a date
     *
     * @param Employee $employee
     * @param string $date
     * @param string $reason
     * @return Attendance
     */
    public function markAbsent(Employee $employee, string $date, string $reason = ''): Attendance
    {
        return Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $date,
            ],
            [
                'status' => 'absent',
                'notes' => $reason,
            ]
        );
    }

    /**
     * Bulk mark attendance for multiple employees
     *
     * @param array $attendanceData
     * @return array
     */
    public function bulkMarkAttendance(array $attendanceData): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($attendanceData as $data) {
            try {
                $employee = Employee::find($data['employee_id']);
                if (!$employee) {
                    $results['errors'][] = "Employee ID {$data['employee_id']} not found.";
                    $results['failed']++;
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $data['employee_id'],
                        'date' => $data['date'],
                    ],
                    [
                        'status' => $data['status'],
                        'check_in_time' => $data['check_in_time'] ?? null,
                        'check_out_time' => $data['check_out_time'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]
                );

                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Error processing employee ID {$data['employee_id']}: " . $e->getMessage();
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get attendance report
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAttendanceReport(array $filters = [])
    {
        $query = Attendance::with('employee.department', 'employee.designation');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->orderBy('date', 'desc')
                    ->orderBy('employee_id')
                    ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Determine attendance status based on check-in/check-out times
     *
     * @param Carbon|null $checkInTime
     * @param Carbon|null $checkOutTime
     * @return string
     */
    private function determineStatus(?Carbon $checkInTime, ?Carbon $checkOutTime): string
    {
        if (!$checkInTime) {
            return 'absent';
        }

        // Check if late (after 9:00 AM)
        $standardCheckIn = $checkInTime->copy()->setTime(9, 0, 0);
        $isLate = $checkInTime->greaterThan($standardCheckIn);

        // Check if early departure (before 5:00 PM)
        $isEarlyDeparture = false;
        if ($checkOutTime) {
            $standardCheckOut = $checkOutTime->copy()->setTime(17, 0, 0);
            $isEarlyDeparture = $checkOutTime->lessThan($standardCheckOut);
        }

        if ($isLate && $isEarlyDeparture) {
            return 'half_day';
        } elseif ($isLate) {
            return 'late';
        } elseif ($checkOutTime) {
            return 'present';
        }

        return 'present';
    }

    /**
     * Get current attendance status for an employee
     *
     * @param Employee $employee
     * @return array
     */
    public function getCurrentStatus(Employee $employee): array
    {
        $today = today()->format('Y-m-d');
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return [
                'status' => 'not_checked_in',
                'can_check_in' => true,
                'can_check_out' => false,
                'attendance' => null,
            ];
        }

        return [
            'status' => $attendance->isCheckedIn() ? 'checked_in' : 'checked_out',
            'can_check_in' => false,
            'can_check_out' => $attendance->isCheckedIn(),
            'attendance' => $attendance,
        ];
    }
}