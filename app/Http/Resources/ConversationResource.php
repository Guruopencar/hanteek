<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId = auth()->id();

        return [
            'id'              => $this->id,
            'contract_id'     => $this->contract_id,
            'vacancy_id'      => $this->vacancy_id,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_count'    => $this->unreadCount($userId),
            'created_at'      => $this->created_at->toISOString(),

            'other_participant' => $this->whenLoaded('firstParticipant', function () use ($userId) {
                $other = $this->getOtherParticipant($userId);
                return $other ? [
                    'id'        => $other->id,
                    'full_name' => $other->profile?->full_name,
                    'avatar'    => $other->profile?->avatar,
                    'role'      => $other->role,
                ] : null;
            }),

            'last_message' => $this->whenLoaded('lastMessage', fn() =>
                $this->lastMessage ? [
                    'content'    => $this->lastMessage->content,
                    'type'       => $this->lastMessage->type,
                    'sender_id'  => $this->lastMessage->sender_id,
                    'created_at' => $this->lastMessage->created_at->toISOString(),
                ] : null
            ),
        ];
    }
}
