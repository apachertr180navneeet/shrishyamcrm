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
        if (array_key_exists('expected_sum', $this->attributes)) {
            return (float)$this->attributes['expected_sum'];
        }
        if (array_key_exists('contributions_sum_contribution_amount', $this->attributes)) {
            return (float)$this->attributes['contributions_sum_contribution_amount'];
        }
        return (float)$this->contributions()->sum('contribution_amount');
    }

    public function getTotalCollectedContributionAttribute(): float
    {
        if (array_key_exists('collected_sum', $this->attributes)) {
            return (float)$this->attributes['collected_sum'];
        }
        return (float)$this->contributions()->where('payment_status', 'Paid')->sum('contribution_amount');
    }

    public function getTotalPendingContributionAttribute(): float
    {
        if (array_key_exists('pending_sum', $this->attributes)) {
            return (float)$this->attributes['pending_sum'];
        }
        return (float)$this->contributions()->where('payment_status', 'Pending')->sum('contribution_amount');
    }

    public function getPaidCountAttribute(): int
    {
        if (array_key_exists('paid_count', $this->attributes)) {
            return (int)$this->attributes['paid_count'];
        }
        return (int)$this->contributions()->where('payment_status', 'Paid')->count();
    }

    public function getPendingCountAttribute(): int
    {
        if (array_key_exists('pending_count', $this->attributes)) {
            return (int)$this->attributes['pending_count'];
        }
        return (int)$this->contributions()->where('payment_status', 'Pending')->count();
    }
}
