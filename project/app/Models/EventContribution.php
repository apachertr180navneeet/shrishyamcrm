<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventContribution extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
        'payment_date' => 'date',
        'contribution_amount' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(MarriageEvent::class, 'event_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
