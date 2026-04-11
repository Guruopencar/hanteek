<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vacancy_id', 'owner_id', 'developer_id',
        'type', 'title', 'description',
        'contract_type', 'total_amount', 'hourly_rate',
        'estimated_hours', 'currency', 'status',
        'owner_signed_at', 'developer_signed_at',
        'started_at', 'deadline_at', 'completed_at', 'document_url',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'        => 'decimal:2',
            'hourly_rate'         => 'decimal:2',
            'owner_signed_at'     => 'datetime',
            'developer_signed_at' => 'datetime',
            'started_at'          => 'datetime',
            'deadline_at'         => 'datetime',
            'completed_at'        => 'datetime',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ContractTask::class)->orderBy('sort_order');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(ContractTimeLog::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isSignedByOwner(): bool
    {
        return $this->owner_signed_at !== null;
    }

    public function isSignedByDeveloper(): bool
    {
        return $this->developer_signed_at !== null;
    }

    public function isFullySigned(): bool
    {
        return $this->isSignedByOwner() && $this->isSignedByDeveloper();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canLeaveReview(): bool
    {
        return $this->isCompleted()
            && $this->completed_at
            && $this->completed_at->diffInDays(now()) <= 14;
    }

    public function getTotalLoggedHoursAttribute(): float
    {
        return (float) $this->timeLogs()->sum('hours');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
