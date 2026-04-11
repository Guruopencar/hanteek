<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key', 'is_enabled', 'description', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public static function isEnabled(string $key): bool
    {
        return Cache::rememberForever(
            "feature.{$key}",
            fn() => (bool) static::where('key', $key)->value('is_enabled')
        );
    }

    public static function enable(string $key): void
    {
        static::where('key', $key)->update(['is_enabled' => true, 'updated_by' => auth()->id()]);
        Cache::forget("feature.{$key}");
    }

    public static function disable(string $key): void
    {
        static::where('key', $key)->update(['is_enabled' => false, 'updated_by' => auth()->id()]);
        Cache::forget("feature.{$key}");
    }
}
