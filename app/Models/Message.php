<?php

namespace App\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model implements ShouldBroadcast
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id', 'sender_id', 'type',
        'content', 'file_url', 'is_read', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read'  => 'boolean',
            'read_at'  => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    // Pusher broadcast channel
    public function broadcastOn(): Channel
    {
        return new Channel('conversation.' . $this->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'new-message';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id'       => $this->sender_id,
            'type'            => $this->type,
            'content'         => $this->content,
            'file_url'        => $this->file_url,
            'created_at'      => $this->created_at->toISOString(),
        ];
    }
}
