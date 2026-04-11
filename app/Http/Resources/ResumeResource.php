<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'position'         => $this->position,
            'experience_years' => $this->experience_years,
            'work_type'        => $this->work_type,
            'employment_type'  => $this->employment_type,
            'hourly_rate'      => $this->hourly_rate,
            'fixed_rate'       => $this->fixed_rate,
            'rate_currency'    => $this->rate_currency,
            'skills'           => $this->skills ?? [],
            'description'      => $this->description,
            'portfolio_url'    => $this->portfolio_url,
            'is_published'     => $this->is_published,
            'created_at'       => $this->created_at->toISOString(),

            'user'        => $this->whenLoaded('user', fn() => new UserResource($this->user)),
            'experience'  => $this->whenLoaded('workExperience', fn() =>
                $this->workExperience->map(fn($e) => [
                    'id'          => $e->id,
                    'company'     => $e->company,
                    'position'    => $e->position,
                    'description' => $e->description,
                    'started_at'  => $e->started_at->format('Y-m'),
                    'ended_at'    => $e->ended_at?->format('Y-m'),
                    'is_current'  => $e->is_current,
                    'duration'    => $e->duration,
                ])
            ),
            'portfolio'   => $this->whenLoaded('portfolio', fn() =>
                $this->portfolio->map(fn($p) => [
                    'id'           => $p->id,
                    'title'        => $p->title,
                    'description'  => $p->description,
                    'url'          => $p->url,
                    'image_url'    => $p->image_url,
                    'technologies' => $p->technologies ?? [],
                ])
            ),
        ];
    }
}
