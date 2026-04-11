<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = ['locale', 'group', 'key', 'value', 'status'];

    protected static function booted(): void
    {
        // При зміні перекладу — очищаємо кеш
        static::saved(function (Translation $t) {
            Cache::forget("translations.{$t->locale}.{$t->group}");
        });

        static::deleted(function (Translation $t) {
            Cache::forget("translations.{$t->locale}.{$t->group}");
        });
    }

    // Отримати всі переклади групи (з кешем)
    public static function getGroup(string $locale, string $group): array
    {
        return Cache::rememberForever(
            "translations.{$locale}.{$group}",
            fn() => static::where('locale', $locale)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray()
        );
    }
}
