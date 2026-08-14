<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBilling extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rate_per_event' => 'decimal:2',
        'total_per_member' => 'decimal:2',
        'total_billing_amount' => 'decimal:2',
        'billing_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(MarriageEvent::class, 'event_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
