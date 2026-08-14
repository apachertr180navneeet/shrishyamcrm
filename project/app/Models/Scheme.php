<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function ageSlabs()
    {
        return $this->hasMany(AgeSlab::class)->where('status', 'Active')->orderBy('min_age');
    }

    public function allAgeSlabs()
    {
        return $this->hasMany(AgeSlab::class)->orderBy('min_age');
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function eventBillings()
    {
        return $this->hasMany(EventBilling::class);
    }
}
