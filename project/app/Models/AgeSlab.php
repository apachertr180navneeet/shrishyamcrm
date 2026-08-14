<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeSlab extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
