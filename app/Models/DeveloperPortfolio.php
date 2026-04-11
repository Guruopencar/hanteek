<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperPortfolio extends Model
{
    protected $fillable = [
        'resume_id', 'title', 'description',
        'url', 'image_url', 'technologies', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(DeveloperResume::class);
    }
}
