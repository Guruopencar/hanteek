<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\VacancyResource;
use App\Http\Resources\ResumeResource;
use App\Models\Vacancy;
use App\Models\DeveloperResume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends ApiController
{
    // Стрічка вакансій для програміста
    public function vacancies(Request $request): JsonResponse
    {
        $query = Vacancy::with(['owner.profile', 'project'])
            ->where('status', 'published');

        // Фільтри
        if ($request->work_type) {
            $query->where('work_type', $request->work_type);
        }
        if ($request->contract_type) {
            $query->where('contract_type', $request->contract_type);
        }
        if ($request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        if ($request->min_budget) {
            $query->where('budget', '>=', $request->min_budget);
        }
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Сортування
        $query->orderByDesc('published_at');

        $vacancies = $query->paginate(
            \App\Models\PlatformSetting::get('pagination_per_page', 12)
        );

        return $this->paginated($vacancies, VacancyResource::class);
    }

    // Стрічка резюме для рекрутера
    public function resumes(Request $request): JsonResponse
    {
        $query = DeveloperResume::with(['user.profile'])
            ->where('is_published', true);

        if ($request->work_type) {
            $query->where('work_type', $request->work_type);
        }
        if ($request->employment_type) {
            $query->where('employment_type', $request->employment_type);
        }
        if ($request->min_rate) {
            $query->where('hourly_rate', '>=', $request->min_rate);
        }
        if ($request->search) {
            $query->where('position', 'like', '%' . $request->search . '%');
        }

        $resumes = $query->paginate(
            \App\Models\PlatformSetting::get('pagination_per_page', 12)
        );

        return $this->paginated($resumes, ResumeResource::class);
    }
}
