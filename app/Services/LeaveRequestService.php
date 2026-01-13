<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeaveRequestSubmitted;
use App\Notifications\LeaveRequestApproved;
use App\Notifications\LeaveRequestRejected;

class LeaveRequestService
{
    /**
     * Create a new leave request
     *
     * @param Employee $employee
     * @param array $data
     * @return LeaveRequest
     */
    public function createLeaveRequest(Employee $employee, array $data): LeaveRequest
    {
        $this->validateLeaveRequest($employee, $data);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days_requested' => $this->calculateBusinessDays($data['start_date'], $data['end_date']),
            'reason' => $data['reason'],
            'attachments' => $data['attachments'] ?? null,
        ]);

        // Notify manager
        if ($employee->manager && $employee->manager->user) {
            $employee->manager->user->notify(new LeaveRequestSubmitted($leaveRequest));
        }

        return $leaveRequest;
    }

    /**
     * Approve a leave request
     *
     * @param LeaveRequest $leaveRequest
     * @param User $approver
     * @param string $notes
     * @return LeaveRequest
     */
    public function approveLeaveRequest(LeaveRequest $leaveRequest, User $approver, string $notes = ''): LeaveRequest
    {
        if (!$leaveRequest->isPending()) {
            throw new \Exception('Leave request is not in pending status.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // Notify employee
        $leaveRequest->employee->user?->notify(new LeaveRequestApproved($leaveRequest));

        return $leaveRequest;
    }

    /**
     * Reject a leave request
     *
     * @param LeaveRequest $leaveRequest
     * @param User $approver
     * @param string $reason
     * @return LeaveRequest
     */
    public function rejectLeaveRequest(LeaveRequest $leaveRequest, User $approver, string $reason): LeaveRequest
    {
        if (!$leaveRequest->isPending()) {
            throw new \Exception('Leave request is not in pending status.');
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Notify employee
        $leaveRequest->employee->user?->notify(new LeaveRequestRejected($leaveRequest));

        return $leaveRequest;
    }

    /**
     * Cancel a leave request
     *
     * @param LeaveRequest $leaveRequest
     * @return LeaveRequest
     */
    public function cancelLeaveRequest(LeaveRequest $leaveRequest): LeaveRequest
    {
        if (!$leaveRequest->isPending()) {
            throw new \Exception('Only pending leave requests can be cancelled.');
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return $leaveRequest;
    }

    /**
     * Get leave balance for an employee
     *
     * @param Employee $employee
     * @param int $year
     * @return array
     */
    public function getLeaveBalance(Employee $employee, int $year = null): array
    {
        $year = $year ?? now()->year;

        // This is a simplified calculation - in a real system, you'd have leave balance tracking
        $annualLeaveEntitlement = 25; // days per year
        $sickLeaveEntitlement = 10; // days per year

        $approvedLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->approved()
            ->get();

        $annualUsed = $approvedLeaves->where('leave_type', 'annual')->sum('days_requested');
        $sickUsed = $approvedLeaves->where('leave_type', 'sick')->sum('days_requested');

        return [
            'annual' => [
                'entitled' => $annualLeaveEntitlement,
                'used' => $annualUsed,
                'remaining' => $annualLeaveEntitlement - $annualUsed,
            ],
            'sick' => [
                'entitled' => $sickLeaveEntitlement,
                'used' => $sickUsed,
                'remaining' => $sickLeaveEntitlement - $sickUsed,
            ],
        ];
    }

    /**
     * Check if employee has sufficient leave balance
     *
     * @param Employee $employee
     * @param string $leaveType
     * @param float $daysRequested
     * @return bool
     */
    public function hasSufficientBalance(Employee $employee, string $leaveType, float $daysRequested): bool
    {
        $balance = $this->getLeaveBalance($employee);

        return isset($balance[$leaveType]) && $balance[$leaveType]['remaining'] >= $daysRequested;
    }

    /**
     * Get leave requests report
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLeaveRequestsReport(array $filters = [])
    {
        $query = LeaveRequest::with(['employee.department', 'employee.designation', 'approver']);

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

        if (!empty($filters['leave_type'])) {
            $query->byType($filters['leave_type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        if (!empty($filters['manager_id'])) {
            $query->requiringApproval($filters['manager_id']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Validate leave request data
     *
     * @param Employee $employee
     * @param array $data
     * @throws \Exception
     */
    private function validateLeaveRequest(Employee $employee, array $data): void
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($startDate->greaterThan($endDate)) {
            throw new \Exception('Start date cannot be after end date.');
        }

        if ($startDate->isPast() && !$startDate->isToday()) {
            throw new \Exception('Cannot request leave for past dates.');
        }

        // Check for overlapping leave requests
        $overlapping = LeaveRequest::where('employee_id', $employee->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($overlapping) {
            throw new \Exception('You have overlapping leave requests for the selected dates.');
        }

        // Check leave balance for annual and sick leave
        if (in_array($data['leave_type'], ['annual', 'sick'])) {
            $daysRequested = $this->calculateBusinessDays($data['start_date'], $data['end_date']);
            if (!$this->hasSufficientBalance($employee, $data['leave_type'], $daysRequested)) {
                throw new \Exception('Insufficient leave balance for the requested leave type.');
            }
        }
    }

    /**
     * Calculate business days between two dates
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    private function calculateBusinessDays(string $startDate, string $endDate): float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $days = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Get upcoming leaves
     *
     * @param int $daysAhead
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingLeaves(int $daysAhead = 30)
    {
        return LeaveRequest::with('employee')
            ->approved()
            ->where('start_date', '>=', today())
            ->where('start_date', '<=', today()->addDays($daysAhead))
            ->orderBy('start_date')
            ->get();
    }
}