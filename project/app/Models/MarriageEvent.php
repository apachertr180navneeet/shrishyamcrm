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

    public function contributions()
    {
        return $this->hasMany(EventContribution::class, 'event_id')->orderBy('member_name');
    }

    public function getTotalExpectedContributionAttribute(): float
    {
        return (float)$this->contributions()->sum('contribution_amount');
    }

    public function getTotalCollectedContributionAttribute(): float
    {
        return (float)$this->contributions()->where('payment_status', 'Paid')->sum('contribution_amount');
    }

    public function getTotalPendingContributionAttribute(): float
    {
        return (float)$this->contributions()->where('payment_status', 'Pending')->sum('contribution_amount');
    }

    public function getPaidCountAttribute(): int
    {
        return $this->contributions()->where('payment_status', 'Paid')->count();
    }

    public function getPendingCountAttribute(): int
    {
        return $this->contributions()->where('payment_status', 'Pending')->count();
    }
}
