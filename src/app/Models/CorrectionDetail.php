<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'correctable_id',
        'correctable_type',
        'corrected_start',
        'corrected_end'
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }

    public function correctable()
    {
        return $this->morphTo();
    }
}
