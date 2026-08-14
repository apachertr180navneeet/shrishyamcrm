<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'joining_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function commissions()
    {
        return $this->hasMany(AgentCommission::class);
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    public function getTotalCollectionAttribute(): float
    {
        return (float)$this->payments()->where('status', 'Verified')->sum('amount');
    }

    public function getTotalCommissionAttribute(): float
    {
        return (float)($this->total_collection * ($this->commission_rate / 100));
    }
}
