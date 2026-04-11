<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'contract_id', 'vacancy_id',
        'participant_1', 'participant_2', 'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function firstParticipant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_1');
    }

    public function secondParticipant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_2');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCount(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function getOtherParticipant(int $userId): ?User
    {
        if ($this->participant_1 === $userId) {
            return $this->secondParticipant;
        }
        return $this->firstParticipant;
    }
}
