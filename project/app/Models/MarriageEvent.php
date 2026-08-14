<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarriageEvent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
        'target_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'beneficiary_payout_amount' => 'decimal:2',
        'rate_per_event' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'event_id');
    }

    public function billings()
    {
        return $this->hasMany(EventBilling::class, 'event_id');
    }
}
