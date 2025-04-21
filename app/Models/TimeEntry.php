<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TimeEntry extends Model
{
    protected $table = 'time_entries';

    protected $fillable = [
        'user_id',
        'shift_schedule_id',
        'approved_attendance',
        'date',
        'am_time_in',
        'am_time_out',
        'pm_time_in',
        'pm_time_out',
        'rendered_hours',
        'late_hours',
        'undertime',
        'excess_minutes',
        'remarks',
    ];

    public function shiftSchedule()
    {
        return $this->belongsTo(ShiftSchedule::class);
    }

    public function approvedAttendance()
    {
        return $this->hasMany(ApproveAttendance::class);
    }

    public function attendanceType()
    {
        return $this->hasOneThrough(
            AttendanceType::class,
            ApproveAttendance::class,
            'id',
            'id',
            'approved_attendance',
            'attendance_type'
        );
    }

    public static function getTimeEntries()
    {
        return self::where('user_id', Auth::user()->id)
            ->whereDate('date', today())
            ->first();
    }

    public static function TimeEntries()
    {
        return self::where('user_id', Auth::user()->id)
            ->get();
    }

    public static function TimeEntriesByID($id)
    {
        return self::where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->get();
    }
}
