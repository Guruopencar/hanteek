<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'uuid', 'from_wallet_id', 'to_wallet_id',
        'from_account_id', 'to_account_id', 'contract_id',
        'type', 'amount', 'commission', 'currency',
        'status', 'description', 'payment_method',
        'external_id', 'meta', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'commission'   => 'decimal:2',
            'meta'         => 'array',
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (!$transaction->uuid) {
                $transaction->uuid = Str::uuid()->toString();
            }
        });
    }

    public function fromWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'to_account_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getNetAmountAttribute(): float
    {
        return (float) bcsub($this->amount, $this->commission, 2);
    }
}
