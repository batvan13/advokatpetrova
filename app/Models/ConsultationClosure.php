<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationClosure extends Model
{
    protected $fillable = [
        'starts_at',
        'ends_at',
        'reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];
}
