<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'check_in_time',
        'check_out_time',
        'date',
        'status',
        'notes',
        'location',
        'ip_address',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if employee is currently checked in
     */
    public function isCheckedIn(): bool
    {
        return !is_null($this->check_in_time) && is_null($this->check_out_time);
    }

    /**
     * Check if employee is currently checked out
     */
    public function isCheckedOut(): bool
    {
        return !is_null($this->check_in_time) && !is_null($this->check_out_time);
    }

    /**
     * Get total working hours for the day
     */
    public function getTotalHoursAttribute(): ?float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        $hours = $this->check_in_time->diffInMinutes($this->check_out_time) / 60;
        return round($hours, 2);
    }

    /**
     * Check if attendance is late
     */
    public function isLate(): bool
    {
        if (!$this->check_in_time) {
            return false;
        }

        // Assuming standard check-in time is 9:00 AM
        $standardCheckIn = $this->check_in_time->copy()->setTime(9, 0, 0);
        return $this->check_in_time->greaterThan($standardCheckIn);
    }

    /**
     * Check if attendance is early departure
     */
    public function isEarlyDeparture(): bool
    {
        if (!$this->check_out_time) {
            return false;
        }

        // Assuming standard check-out time is 5:00 PM
        $standardCheckOut = $this->check_out_time->copy()->setTime(17, 0, 0);
        return $this->check_out_time->lessThan($standardCheckOut);
    }

    /**
     * Get attendance status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            'holiday' => 'secondary',
            default => 'light',
        };
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for getting today's attendance
     */
    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    /**
     * Scope for getting this week's attendance
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for getting this month's attendance
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }
}