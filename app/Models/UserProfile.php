<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'avatar',
        'bio', 'country', 'city', 'telegram',
        'linkedin', 'github', 'website',
        'average_rating', 'reviews_count', 'contracts_count',
    ];

    protected function casts(): array
    {
        return [
            'average_rating' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getLocationAttribute(): string
    {
        return implode(', ', array_filter([$this->country, $this->city]));
    }

    // Перерахунок рейтингу
    public function recalculateRating(): void
    {
        $reviews = Review::where('reviewee_id', $this->user_id)
            ->where('is_published', true)
            ->get();

        $this->update([
            'average_rating' => $reviews->avg('rating') ?? 0,
            'reviews_count'  => $reviews->count(),
        ]);
    }
}
