<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nominee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getNameAttribute(?string $value): ?string
    {
        if (empty($value)) return $value;
        return \App\Services\KrutiDevConverter::cleanHindiText($value);
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = !empty($value) ? \App\Services\KrutiDevConverter::cleanHindiText($value) : null;
    }
}
