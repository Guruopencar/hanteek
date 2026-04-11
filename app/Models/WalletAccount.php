<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletAccount extends Model
{
    protected $fillable = [
        'wallet_id', 'name', 'type',
        'balance', 'currency', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'balance'    => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transactionsOut(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function transactionsIn(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }
}
