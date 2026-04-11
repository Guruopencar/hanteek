<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'contract_id'      => $this->contract_id,
            'title'            => $this->title,
            'description'      => $this->description,
            'priority'         => $this->priority,
            'status'           => $this->status,
            'due_date'         => $this->due_date?->format('Y-m-d'),
            'estimated_hours'  => $this->estimated_hours,
            'sort_order'       => $this->sort_order,
            'is_overdue'       => $this->isOverdue(),
            'completed_at'     => $this->completed_at?->toISOString(),
            'created_at'       => $this->created_at->toISOString(),

            'assignee' => $this->whenLoaded('assignee', fn() => [
                'id'       => $this->assignee->id,
                'name'     => $this->assignee->name,
                'full_name'=> $this->assignee->profile?->full_name,
                'avatar'   => $this->assignee->profile?->avatar,
            ]),
            'creator' => $this->whenLoaded('creator', fn() => [
                'id'       => $this->creator->id,
                'full_name'=> $this->creator->profile?->full_name,
            ]),
        ];
    }
}
