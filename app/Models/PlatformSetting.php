<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key', 'value', 'type', 'group', 'description', 'updated_by',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever(
            "setting.{$key}",
            fn() => static::where('key', $key)->first()
        );

        if (!$setting) return $default;

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'decimal' => (float) $setting->value,
            'boolean' => (bool) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], [
            'value'      => is_array($value) ? json_encode($value) : (string) $value,
            'updated_by' => auth()->id(),
        ]);
        Cache::forget("setting.{$key}");
    }
}
