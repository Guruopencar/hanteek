<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralPayment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'referral_id', 'transaction_id',
        'amount', 'period_start', 'period_end',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'period_start' => 'date',
            'period_end'   => 'date',
            'created_at'   => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
