<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'owner_id', 'title', 'description',
        'requirements', 'work_type', 'location',
        'contract_type', 'budget', 'hourly_rate',
        'estimated_hours', 'currency', 'skills_required',
        'status', 'applications_count', 'views_count', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'skills_required'    => 'array',
            'budget'             => 'decimal:2',
            'hourly_rate'        => 'decimal:2',
            'published_at'       => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(VacancyApplication::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
