<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id', 'sender_id', 'message', 'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'created_at'  => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
