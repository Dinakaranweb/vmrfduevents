<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Get the value of a system setting by key, with optional default fallback.
     */
    public static function getValue($key, $default = null)
    {
        try {
            return Cache::remember('system_setting_' . $key, 3600, function () use ($key, $default) {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Exception $e) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        }
    }

    /**
     * Set or update the value of a system setting by key.
     */
    public static function setValue($key, $value, $group = 'general', $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description ?? 'System Variable'
            ]
        );

        Cache::forget('system_setting_' . $key);

        return $setting;
    }
}
