<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'contract_id'  => $this->contract_id,
            'rating'       => $this->rating,
            'comment'      => $this->comment,
            'status'       => $this->status,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toISOString(),
            'created_at'   => $this->created_at->toISOString(),

            'reviewer' => $this->whenLoaded('reviewer', fn() => [
                'id'        => $this->reviewer->id,
                'full_name' => $this->reviewer->profile?->full_name,
                'avatar'    => $this->reviewer->profile?->avatar,
                'role'      => $this->reviewer->role,
            ]),

            'contract' => $this->whenLoaded('contract', fn() => [
                'id'    => $this->contract->id,
                'title' => $this->contract->title,
            ]),
        ];
    }
}
