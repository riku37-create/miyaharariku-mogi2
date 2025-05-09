<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_time_id',
        'correction_request_id',
        'original_break_start',
        'original_break_end',
        'corrected_break_start',
        'corrected_break_end',
    ];

    protected $casts = [
        'original_break_start' => 'datetime',
        'original_break_end' => 'datetime',
        'corrected_break_start' => 'datetime',
        'corrected_break_end' => 'datetime',
    ];
}
