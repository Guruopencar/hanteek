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
    private function authorizeConversation(Conversation $conversation): bool
    {
        $userId = auth()->id();
        return $conversation->participant_1 === $userId
            || $conversation->participant_2 === $userId;
    }

    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$this->authorizeConversation($conversation)) {
            return $this->forbidden();
        }

        $userId = $request->user()->id;

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
        if (!$this->authorizeConversation($conversation)) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'content'  => 'required|string|max:5000',
            'type'     => 'nullable|in:text,file,image',
            'file_url' => 'nullable|url',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'type'      => $data['type'] ?? 'text',
            'content'   => $data['content'],
            'file_url'  => $data['file_url'] ?? null,
        ]);

        $conversation->update(['last_message_at' => now()]);
        broadcast($message)->toOthers();

        return $this->created(new MessageResource($message->load('sender.profile')));
    }

    public function markRead(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        // Перевірка що conversation належить user
        if (!$this->authorizeConversation($conversation)) {
            return $this->forbidden();
        }

        // Перевірка що message належить цій conversation
        if ($message->conversation_id !== $conversation->id) {
            return $this->forbidden();
        }

        // Тільки отримувач може позначити як прочитане
        if ($message->sender_id === $request->user()->id) {
            return $this->error('Не можна позначити своє повідомлення як прочитане');
        }

        $message->markAsRead();
        return $this->success(null, 'Прочитано');
    }
}
