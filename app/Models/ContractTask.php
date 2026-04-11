<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTask extends Model
{
    protected $fillable = [
        'contract_id', 'created_by', 'title', 'description',
        'priority', 'status', 'assigned_to',
        'due_date', 'estimated_hours', 'sort_order', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'        => 'date',
            'estimated_hours' => 'decimal:2',
            'completed_at'    => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(ContractTimeLog::class, 'task_id');
    }

    public function markAsDone(): void
    {
        $this->update([
            'status'       => 'done',
            'completed_at' => now(),
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status !== 'done';
    }
}
