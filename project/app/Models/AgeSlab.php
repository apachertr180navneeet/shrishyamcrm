<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeSlab extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'joining_amount' => 'decimal:2',
        'support_amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }
}
