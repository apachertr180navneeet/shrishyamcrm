<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\IndianCurrencyHelper;

class Payout extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'payout_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(MarriageEvent::class, 'event_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function getAmountInWordsAttribute(): string
    {
        return IndianCurrencyHelper::formatToWords($this->amount);
    }
}
