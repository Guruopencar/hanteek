<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ResumeResource;
use App\Models\DeveloperResume;
use App\Models\DeveloperWorkExperience;
use App\Models\DeveloperPortfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        $resume = $request->user()
            ->developerResume()
            ->with(['workExperience', 'portfolio'])
            ->first();

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        return $this->success(new ResumeResource($resume));
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->developerResume) {
            return $this->error('Резюме вже існує. Використайте PUT для оновлення');
        }

        $data = $request->validate([
            'position'         => 'required|string|max:200',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'work_type'        => 'nullable|in:remote,office,hybrid',
            'employment_type'  => 'nullable|in:full_time,part_time,freelance',
            'hourly_rate'      => 'nullable|numeric|min:0',
            'fixed_rate'       => 'nullable|numeric|min:0',
            'rate_currency'    => 'nullable|string|size:3',
            'skills'           => 'nullable|array',
            'skills.*'         => 'string|max:50',
            'description'      => 'nullable|string|max:3000',
            'portfolio_url'    => 'nullable|url',
        ]);

        $resume = $request->user()->developerResume()->create($data);

        return $this->created(new ResumeResource($resume));
    }

    public function update(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        $data = $request->validate([
            'position'         => 'sometimes|string|max:200',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'work_type'        => 'nullable|in:remote,office,hybrid',
            'employment_type'  => 'nullable|in:full_time,part_time,freelance',
            'hourly_rate'      => 'nullable|numeric|min:0',
            'fixed_rate'       => 'nullable|numeric|min:0',
            'rate_currency'    => 'nullable|string|size:3',
            'skills'           => 'nullable|array',
            'skills.*'         => 'string|max:50',
            'description'      => 'nullable|string|max:3000',
            'portfolio_url'    => 'nullable|url',
        ]);

        $resume->update($data);

        return $this->success(new ResumeResource($resume->fresh(['workExperience', 'portfolio'])));
    }

    public function publish(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        $resume->update(['is_published' => true]);

        return $this->success(null, 'Резюме опубліковано');
    }

    public function unpublish(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;
        $resume?->update(['is_published' => false]);

        return $this->success(null, 'Резюме приховано');
    }

    // ── Work Experience ────────────────────────────────────────

    public function experience(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        return $this->success($resume->workExperience()->orderByDesc('started_at')->get()->map(fn($e) => [
            'id'          => $e->id,
            'company'     => $e->company,
            'position'    => $e->position,
            'description' => $e->description,
            'started_at'  => $e->started_at->format('Y-m'),
            'ended_at'    => $e->ended_at?->format('Y-m'),
            'is_current'  => $e->is_current,
            'duration'    => $e->duration,
        ]));
    }

    public function addExperience(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        $data = $request->validate([
            'company'     => 'required|string|max:200',
            'position'    => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'started_at'  => 'required|date',
            'ended_at'    => 'nullable|date|after:started_at',
            'is_current'  => 'boolean',
        ]);

        if (!empty($data['is_current'])) {
            $data['ended_at'] = null;
        }

        $experience = $resume->workExperience()->create($data);

        return $this->created($experience);
    }

    public function updateExperience(Request $request, DeveloperWorkExperience $experience): JsonResponse
    {
        if ($experience->resume->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'company'     => 'sometimes|string|max:200',
            'position'    => 'sometimes|string|max:200',
            'description' => 'nullable|string|max:2000',
            'started_at'  => 'sometimes|date',
            'ended_at'    => 'nullable|date',
            'is_current'  => 'boolean',
        ]);

        $experience->update($data);

        return $this->success($experience->fresh());
    }

    public function deleteExperience(Request $request, DeveloperWorkExperience $experience): JsonResponse
    {
        if ($experience->resume->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $experience->delete();

        return $this->success(null, 'Запис видалено');
    }

    // ── Portfolio ──────────────────────────────────────────────

    public function portfolio(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        return $this->success($resume->portfolio()->orderBy('sort_order')->get());
    }

    public function addPortfolio(Request $request): JsonResponse
    {
        $resume = $request->user()->developerResume;

        if (!$resume) {
            return $this->notFound('Резюме не знайдено');
        }

        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string|max:2000',
            'url'          => 'nullable|url',
            'image_url'    => 'nullable|url',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:50',
        ]);

        $portfolio = $resume->portfolio()->create([
            ...$data,
            'sort_order' => $resume->portfolio()->count(),
        ]);

        return $this->created($portfolio);
    }

    public function updatePortfolio(Request $request, DeveloperPortfolio $portfolio): JsonResponse
    {
        if ($portfolio->resume->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'title'        => 'sometimes|string|max:200',
            'description'  => 'nullable|string|max:2000',
            'url'          => 'nullable|url',
            'image_url'    => 'nullable|url',
            'technologies' => 'nullable|array',
        ]);

        $portfolio->update($data);

        return $this->success($portfolio->fresh());
    }

    public function deletePortfolio(Request $request, DeveloperPortfolio $portfolio): JsonResponse
    {
        if ($portfolio->resume->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $portfolio->delete();

        return $this->success(null, 'Проект видалено');
    }
}
