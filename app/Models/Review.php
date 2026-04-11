<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'contract_id', 'reviewer_id', 'reviewee_id',
        'rating', 'comment', 'status', 'is_published',
        'published_at', 'moderated_by', 'moderated_at', 'moderation_note',
    ];

    protected function casts(): array
    {
        return [
            'is_published'  => 'boolean',
            'published_at'  => 'datetime',
            'moderated_at'  => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Перевірити чи обидві сторони залишили відгук — якщо так, публікуємо обидва
    public function checkAndPublish(): void
    {
        $contract = $this->contract;

        $bothReviewed = Review::where('contract_id', $contract->id)->count() === 2;

        if ($bothReviewed) {
            Review::where('contract_id', $contract->id)->update([
                'status'       => 'published',
                'is_published' => true,
                'published_at' => now(),
            ]);

            // Перерахувати рейтинг обох учасників
            $contract->owner->profile?->recalculateRating();
            $contract->developer->profile?->recalculateRating();
        }
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
