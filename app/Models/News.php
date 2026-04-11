<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'author_id', 'slug', 'cover_image',
        'status', 'published_at', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(NewsTranslation::class);
    }

    public function translation(string $locale = null): ?NewsTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }
}
