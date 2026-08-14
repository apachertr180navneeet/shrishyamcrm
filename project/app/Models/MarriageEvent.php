<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarriageEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'event_id');
    }
}
