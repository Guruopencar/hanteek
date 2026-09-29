<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperWorkExperience extends Model
{
	protected $table = 'developer_work_experience';
    protected $fillable = [
        'resume_id', 'company', 'position',
        'description', 'started_at', 'ended_at', 'is_current',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at'   => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(DeveloperResume::class);
    }

    public function getDurationAttribute(): string
    {
        $end = $this->is_current ? now() : $this->ended_at;
        $months = $this->started_at->diffInMonths($end);
        $years = intdiv($months, 12);
        $rem   = $months % 12;

        if ($years > 0 && $rem > 0) return "{$years}р {$rem}м";
        if ($years > 0) return "{$years}р";
        return "{$rem}м";
    }
}
