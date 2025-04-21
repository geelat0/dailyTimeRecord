<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ShiftSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'shift_schedule';

    protected $fillable = [
        'start_date',
        'end_date',
        'user_id',
        'shift_id',
        'remarks',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function approvedAttendance()
    {
        return $this->hasMany(ApproveAttendance::class);
    }

    public static function hasCurrentMonthShiftSchedule()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        return self::where('user_id', Auth::user()->id)
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->first();
    }

    public static function getCurrentMonthSchedules()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        return self::where('user_id', Auth::user()->id)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
            ->get();
    }

    public static function getShiftSchedule($date ,$user_id)
    {
        return self::whereNull('deleted_at')
            ->where('user_id', $user_id)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }
    
}
