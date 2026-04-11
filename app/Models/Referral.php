<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referred_id', 'status',
        'reward_amount', 'reward_paid_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount'  => 'decimal:2',
            'reward_paid_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ReferralPayment::class);
    }
}
