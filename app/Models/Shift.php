<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'shift';

    protected $fillable = [
        'shift_name',
        'am_time_in',
        'am_time_out',
        'pm_time_in',
        'pm_time_out',
        'am_late_threshold',
        'pm_late_threshold',
        'is_flexi_schedule',
    ];

    public function shiftSchedules()
    {
        return $this->hasMany(ShiftSchedule::class);
    }
}
