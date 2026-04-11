<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'frozen', 'currency', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance'   => 'decimal:2',
            'frozen'    => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(WalletAccount::class);
    }

    public function transactionsOut(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_wallet_id');
    }

    public function transactionsIn(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_wallet_id');
    }

    public function getAvailableBalanceAttribute(): float
    {
        return (float) bcsub($this->balance, $this->frozen, 2);
    }

    public function hasEnoughBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }

    public function credit(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    public function debit(float $amount): void
    {
        if (!$this->hasEnoughBalance($amount)) {
            throw new \RuntimeException('Insufficient balance');
        }
        $this->decrement('balance', $amount);
    }

    public function freeze(float $amount): void
    {
        $this->increment('frozen', $amount);
    }

    public function unfreeze(float $amount): void
    {
        $this->decrement('frozen', $amount);
    }
}
