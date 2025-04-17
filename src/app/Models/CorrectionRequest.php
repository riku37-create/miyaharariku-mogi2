<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'reason',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestAttendances()
    {
        return $this->hasMany(RequestAttendance::class);
    }

    public function requestBreakTimes()
    {
        return $this->hasMany(RequestBreakTime::class);
    }
}
