<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
			'id'        => $this->id,
			'banned_id' => $this->banned_id, // ← для unban
			'reason'    => $this->reason,
			'banned_at' => $this->banned_at->toISOString(),
			'is_active' => $this->isActive(),

			'banned_user' => $this->whenLoaded('banned', fn() => [
				'id'        => $this->banned->id,
				'name'      => $this->banned->name,
				'full_name' => trim(($this->banned->profile?->first_name ?? '') . ' ' . ($this->banned->profile?->last_name ?? '')) ?: $this->banned->name,
				'avatar'    => $this->banned->profile?->avatar,
				'location'  => trim(($this->banned->profile?->city ?? '') . ', ' . ($this->banned->profile?->country ?? ''), ', '),
			]),
		];
    }
}
