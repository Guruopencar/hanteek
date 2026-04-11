<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTimeLog extends Model
{
    protected $fillable = [
        'contract_id', 'task_id', 'user_id',
        'hours', 'description', 'logged_date',
    ];

    protected function casts(): array
    {
        return [
            'hours'       => 'decimal:2',
            'logged_date' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ContractTask::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
