<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function ageSlabs()
    {
        return $this->hasMany(AgeSlab::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
