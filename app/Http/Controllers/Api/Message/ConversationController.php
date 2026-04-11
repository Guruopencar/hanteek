<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with(['firstParticipant.profile', 'secondParticipant.profile', 'lastMessage'])
            ->where('participant_1', $userId)
            ->orWhere('participant_2', $userId)
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return $this->paginated($conversations, ConversationResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'participant_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'contract_id'    => 'nullable|exists:contracts,id',
            'vacancy_id'     => 'nullable|exists:vacancies,id',
        ]);

        $userId = $request->user()->id;
        $participantId = $data['participant_id'];

        // Знайти або створити діалог
        $conversation = Conversation::where(function ($q) use ($userId, $participantId) {
            $q->where('participant_1', $userId)->where('participant_2', $participantId);
        })->orWhere(function ($q) use ($userId, $participantId) {
            $q->where('participant_1', $participantId)->where('participant_2', $userId);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'participant_1' => $userId,
                'participant_2' => $participantId,
                'contract_id'   => $data['contract_id'] ?? null,
                'vacancy_id'    => $data['vacancy_id'] ?? null,
            ]);
        }

        return $this->success(new ConversationResource($conversation->load('firstParticipant.profile', 'secondParticipant.profile')));
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->participant_1 !== $userId && $conversation->participant_2 !== $userId) {
            return $this->forbidden();
        }

        return $this->success(new ConversationResource($conversation->load('firstParticipant.profile', 'secondParticipant.profile', 'lastMessage')));
    }
}
