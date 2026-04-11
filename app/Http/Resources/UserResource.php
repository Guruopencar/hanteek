<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'role'           => $this->role,
            'active_profile' => $this->active_profile,
            'locale'         => $this->locale,
            'is_verified'    => $this->is_verified,
            'is_active'      => $this->is_active,
            'referral_code'  => $this->referral_code,
            'last_login_at'  => $this->last_login_at?->toISOString(),
            'created_at'     => $this->created_at->toISOString(),

            // Relations (завантажуються тільки якщо є)
            'profile'          => $this->whenLoaded('profile', fn() => [
                'first_name'     => $this->profile->first_name,
                'last_name'      => $this->profile->last_name,
                'full_name'      => $this->profile->full_name,
                'avatar'         => $this->profile->avatar,
                'bio'            => $this->profile->bio,
                'country'        => $this->profile->country,
                'city'           => $this->profile->city,
                'location'       => $this->profile->location,
                'telegram'       => $this->profile->telegram,
                'linkedin'       => $this->profile->linkedin,
                'github'         => $this->profile->github,
                'website'        => $this->profile->website,
                'average_rating' => $this->profile->average_rating,
                'reviews_count'  => $this->profile->reviews_count,
                'contracts_count'=> $this->profile->contracts_count,
            ]),

            'developer_resume'  => $this->whenLoaded('developerResume', fn() =>
                new ResumeResource($this->developerResume)
            ),

            'recruiter_profile' => $this->whenLoaded('recruiterProfile', fn() => [
                'company_name'    => $this->recruiterProfile->company_name,
                'company_website' => $this->recruiterProfile->company_website,
                'company_size'    => $this->recruiterProfile->company_size,
                'industry'        => $this->recruiterProfile->industry,
                'position'        => $this->recruiterProfile->position,
                'description'     => $this->recruiterProfile->description,
            ]),

            'wallet' => $this->whenLoaded('wallet', fn() => [
                'balance'           => $this->wallet->balance,
                'frozen'            => $this->wallet->frozen,
                'available_balance' => $this->wallet->available_balance,
                'currency'          => $this->wallet->currency,
            ]),
        ];
    }
}
