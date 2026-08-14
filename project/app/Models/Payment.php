<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\IndianCurrencyHelper;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function ledger()
    {
        return $this->hasOne(Ledger::class);
    }

    public function commission()
    {
        return $this->hasOne(AgentCommission::class);
    }

    public function getAmountInWordsAttribute(): string
    {
        return IndianCurrencyHelper::formatToWords($this->amount);
    }

    public function getFormattedAmountAttribute(): string
    {
        return IndianCurrencyHelper::formatINR($this->amount);
    }
}
