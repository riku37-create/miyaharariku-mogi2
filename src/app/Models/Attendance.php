<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break',
        'total_break',
        'total_clock',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    //実働時間
    public function getFormattedTotalClockAttribute()
    {
        $actualWorkingSeconds = $this->total_clock - $this->total_break;
        $actualWorkingSeconds = max(0, $actualWorkingSeconds); //マイナスを防ぐ

        $h = floor($actualWorkingSeconds / 3600);
        $m = floor(($actualWorkingSeconds % 3600) / 60);
        return "{$h}時間{$m}分";
    }

    //休憩時間
    public function getFormattedTotalBreakAttribute()
    {
        $h = floor($this->total_break / 3600);
        $m = floor(($this->total_break % 3600) / 60);
        return "{$h}時間{$m}分";
    }

    //総合時間
    public function getFormattedTotalRawAttribute()
    {
        $h = floor($this->total_clock / 3600);
        $m = floor(($this->total_clock % 3600) / 60);
        return "{$h}時間{$m}分";
    }
}
