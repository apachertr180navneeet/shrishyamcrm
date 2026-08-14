<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

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

    public function getTotalCollectionAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getTotalCommissionAttribute()
    {
        return ($this->total_collection * $this->commission_rate) / 100;
    }
}
