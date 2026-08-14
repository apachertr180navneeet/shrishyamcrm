<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'collection_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payout_date' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
