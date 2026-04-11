<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VacancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'project_id'         => $this->project_id,
            'title'              => $this->title,
            'description'        => $this->description,
            'requirements'       => $this->requirements,
            'work_type'          => $this->work_type,
            'location'           => $this->location,
            'contract_type'      => $this->contract_type,
            'budget'             => $this->budget,
            'hourly_rate'        => $this->hourly_rate,
            'estimated_hours'    => $this->estimated_hours,
            'currency'           => $this->currency,
            'skills_required'    => $this->skills_required ?? [],
            'status'             => $this->status,
            'applications_count' => $this->applications_count,
            'views_count'        => $this->views_count,
            'published_at'       => $this->published_at?->toISOString(),
            'created_at'         => $this->created_at->toISOString(),

            'owner'   => $this->whenLoaded('owner', fn() => [
                'id'             => $this->owner->id,
                'name'           => $this->owner->name,
                'full_name'      => $this->owner->profile?->full_name,
                'avatar'         => $this->owner->profile?->avatar,
                'average_rating' => $this->owner->profile?->average_rating,
                'reviews_count'  => $this->owner->profile?->reviews_count,
            ]),

            'project' => $this->whenLoaded('project', fn() => [
                'id'    => $this->project->id,
                'title' => $this->project->title,
                'cover' => $this->project->cover_image,
            ]),
        ];
    }
}
