<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'reason', 'status'];

    public function details()
    {
        return $this->hasMany(CorrectionDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
