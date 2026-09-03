<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'joining_amount' => 'decimal:2',
        'monthly_support_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function ageSlab()
    {
        return $this->belongsTo(AgeSlab::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function nominees()
    {
        return $this->hasMany(Nominee::class)->orderBy('priority');
    }

    public function documents()
    {
        return $this->hasMany(MemberDocument::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest('payment_date');
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class)->orderBy('transaction_date')->orderBy('id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class)->latest('issue_date');
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function eventContributions()
    {
        return $this->hasMany(EventContribution::class, 'member_id')->latest('event_date');
    }

    public function calculateCurrentBalance(): float
    {
        $lastLedger = $this->ledgers()->latest('id')->first();
        return $lastLedger ? (float)$lastLedger->running_balance : 0.0;
    }
}
