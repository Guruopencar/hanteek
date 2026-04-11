<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    protected $fillable = [
        'code', 'name', 'native_name',
        'flag_emoji', 'is_active', 'is_default', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
