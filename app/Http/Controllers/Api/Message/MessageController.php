<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends ApiController
{
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->participant_1 !== $userId && $conversation->participant_2 !== $userId) {
            return $this->forbidden();
        }

        // Позначити всі як прочитані
        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender.profile')
            ->orderByDesc('created_at')
            ->paginate(30);

        return $this->paginated($messages, MessageResource::class);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->participant_1 !== $userId && $conversation->participant_2 !== $userId) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'content'  => 'required|string|max:5000',
            'type'     => 'nullable|in:text,file,image',
            'file_url' => 'nullable|url',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'type'      => $data['type'] ?? 'text',
            'content'   => $data['content'],
            'file_url'  => $data['file_url'] ?? null,
        ]);

        // Оновити last_message_at
        $conversation->update(['last_message_at' => now()]);

        // Broadcast через Pusher (автоматично через ShouldBroadcast)
        broadcast($message)->toOthers();

        return $this->created(new MessageResource($message->load('sender.profile')));
    }

    public function markRead(Request $request, Message $message): JsonResponse
    {
        $message->markAsRead();
        return $this->success(null, 'Прочитано');
    }
}
