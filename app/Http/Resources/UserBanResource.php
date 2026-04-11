<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'reason'     => $this->reason,
            'banned_at'  => $this->banned_at->toISOString(),
            'is_active'  => $this->isActive(),

            'banned_user' => $this->whenLoaded('banned', fn() => [
                'id'        => $this->banned->id,
                'name'      => $this->banned->name,
                'full_name' => $this->banned->profile?->full_name,
                'avatar'    => $this->banned->profile?->avatar,
                'location'  => $this->banned->profile?->location,
            ]),
        ];
    }
}
