<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyApplication extends Model
{
    protected $fillable = [
        'vacancy_id', 'applicant_id', 'cover_letter',
        'proposed_rate', 'status', 'viewed_at', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_rate' => 'decimal:2',
            'viewed_at'     => 'datetime',
            'responded_at'  => 'datetime',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update([
                'status'    => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }
}
