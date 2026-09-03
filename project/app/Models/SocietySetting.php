<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SocietySetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static $runtimeCache = null;

    public static function getAllSettings(): array
    {
        if (self::$runtimeCache !== null) {
            return self::$runtimeCache;
        }

        self::$runtimeCache = Cache::remember('society_settings_map', 3600, function () {
            try {
                return self::pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return self::$runtimeCache;
    }

    public static function getVal(string $key, $default = null)
    {
        $all = self::getAllSettings();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function setVal(string $key, $value, string $group = 'general', ?string $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'description' => $description]
        );

        self::$runtimeCache = null;
        Cache::forget('society_settings_map');

        return $setting;
    }
}
