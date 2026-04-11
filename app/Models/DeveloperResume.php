<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeveloperResume extends Model
{
    protected $fillable = [
        'user_id', 'position', 'experience_years',
        'work_type', 'employment_type',
        'hourly_rate', 'fixed_rate', 'rate_currency',
        'skills', 'description', 'portfolio_url', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'skills'       => 'array',
            'is_published' => 'boolean',
            'hourly_rate'  => 'decimal:2',
            'fixed_rate'   => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workExperience(): HasMany
    {
        return $this->hasMany(DeveloperWorkExperience::class, 'resume_id')
            ->orderByDesc('started_at');
    }

    public function portfolio(): HasMany
    {
        return $this->hasMany(DeveloperPortfolio::class, 'resume_id')
            ->orderBy('sort_order');
    }
}
