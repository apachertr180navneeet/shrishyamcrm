<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocietySetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $group = 'general', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'description' => $description]
        );
    }
}
