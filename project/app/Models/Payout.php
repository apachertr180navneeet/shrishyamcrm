<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payout_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(MarriageEvent::class, 'event_id');
    }
}
