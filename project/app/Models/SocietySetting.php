<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocietySetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setVal(string $key, $value, string $group = 'general', ?string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'description' => $description]
        );
    }
}
