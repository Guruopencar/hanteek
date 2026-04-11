<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'banner_id', 'banned_id', 'reason', 'banned_at', 'unbanned_at',
    ];

    protected function casts(): array
    {
        return [
            'banned_at'   => 'datetime',
            'unbanned_at' => 'datetime',
            'created_at'  => 'datetime',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banner_id');
    }

    public function banned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_id');
    }

    public function isActive(): bool
    {
        return $this->unbanned_at === null;
    }
}
