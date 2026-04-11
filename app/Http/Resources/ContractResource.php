<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'type'                 => $this->type,
            'title'                => $this->title,
            'description'          => $this->description,
            'contract_type'        => $this->contract_type,
            'total_amount'         => $this->total_amount,
            'hourly_rate'          => $this->hourly_rate,
            'estimated_hours'      => $this->estimated_hours,
            'currency'             => $this->currency,
            'status'               => $this->status,
            'is_signed_by_owner'   => $this->isSignedByOwner(),
            'is_signed_by_dev'     => $this->isSignedByDeveloper(),
            'is_fully_signed'      => $this->isFullySigned(),
            'can_leave_review'     => $this->canLeaveReview(),
            'owner_signed_at'      => $this->owner_signed_at?->toISOString(),
            'developer_signed_at'  => $this->developer_signed_at?->toISOString(),
            'started_at'           => $this->started_at?->toISOString(),
            'deadline_at'          => $this->deadline_at?->toISOString(),
            'completed_at'         => $this->completed_at?->toISOString(),
            'total_logged_hours'   => $this->total_logged_hours,
            'document_url'         => $this->document_url,
            'created_at'           => $this->created_at->toISOString(),

            'owner'     => $this->whenLoaded('owner', fn() => [
                'id'       => $this->owner->id,
                'name'     => $this->owner->name,
                'full_name'=> $this->owner->profile?->full_name,
                'avatar'   => $this->owner->profile?->avatar,
                'rating'   => $this->owner->profile?->average_rating,
            ]),
            'developer' => $this->whenLoaded('developer', fn() => [
                'id'       => $this->developer->id,
                'name'     => $this->developer->name,
                'full_name'=> $this->developer->profile?->full_name,
                'avatar'   => $this->developer->profile?->avatar,
                'rating'   => $this->developer->profile?->average_rating,
            ]),
            'tasks'     => $this->whenLoaded('tasks', fn() =>
                TaskResource::collection($this->tasks)
            ),
        ];
    }
}
