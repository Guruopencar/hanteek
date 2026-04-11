<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Models\RecruiterProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecruiterProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->recruiterProfile;

        if (!$profile) {
            return $this->notFound('Профіль рекрутера не знайдено');
        }

        return $this->success($profile);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->recruiterProfile) {
            return $this->error('Профіль вже існує');
        }

        $data = $request->validate([
            'company_name'    => 'nullable|string|max:200',
            'company_website' => 'nullable|url',
            'company_size'    => 'nullable|in:1-10,11-50,51-200,201-500,500+',
            'industry'        => 'nullable|string|max:100',
            'position'        => 'nullable|string|max:200',
            'description'     => 'nullable|string|max:3000',
        ]);

        $profile = $request->user()->recruiterProfile()->create($data);

        return $this->created($profile);
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $request->user()->recruiterProfile;

        if (!$profile) {
            return $this->notFound('Профіль не знайдено');
        }

        $data = $request->validate([
            'company_name'    => 'nullable|string|max:200',
            'company_website' => 'nullable|url',
            'company_size'    => 'nullable|in:1-10,11-50,51-200,201-500,500+',
            'industry'        => 'nullable|string|max:100',
            'position'        => 'nullable|string|max:200',
            'description'     => 'nullable|string|max:3000',
            'is_published'    => 'boolean',
        ]);

        $profile->update($data);

        return $this->success($profile->fresh());
    }
}
