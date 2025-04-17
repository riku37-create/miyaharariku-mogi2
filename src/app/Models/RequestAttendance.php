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

}
