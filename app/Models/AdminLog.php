<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id', 'action', 'target_type', 'target_id',
        'description', 'old_value', 'new_value', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_value'  => 'array',
            'new_value'  => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $description = null,
        mixed $oldValue = null,
        mixed $newValue = null
    ): void {
        static::create([
            'admin_id'    => auth()->id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'description' => $description,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
            'ip_address'  => request()->ip(),
        ]);
    }
}
