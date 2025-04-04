<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_time_id',
        'requested_break_start',
        'requested_break_end',
        'status',
        'reason',
    ];

    public function break()
    {
        return $this->belongsTo(BreakTime::class);
    }
}
