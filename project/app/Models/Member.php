<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
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
        return $this->hasMany(Nominee::class)->orderBy('priority', 'asc');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    public function marriageEvents()
    {
        return $this->hasMany(MarriageEvent::class);
    }

    public function getPrimaryNomineeAttribute()
    {
        return $this->nominees->where('priority', 1)->first();
    }

    public function getSecondaryNomineeAttribute()
    {
        return $this->nominees->where('priority', 2)->first();
    }
}
