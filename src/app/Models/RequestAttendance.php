<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'correction_request_id',
        'original_clock_in',
        'original_clock_out',
        'corrected_clock_in',
        'corrected_clock_out',
    ];

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }

    protected $casts = [
        'original_clock_in' => 'datetime',
        'original_clock_out' => 'datetime',
        'corrected_clock_in' => 'datetime',
        'corrected_clock_out' => 'datetime',
    ];
}
