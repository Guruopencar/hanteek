<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\VacancyResource;
use App\Http\Resources\ResumeResource;
use App\Models\Vacancy;
use App\Models\DeveloperResume;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends ApiController
{
    // Стрічка проектів — усі активні проекти будь-яких власників
    public function projects(Request $request): JsonResponse
    {
        $query = Project::with(['owner.profile'])
            ->withCount('vacancies')
            ->where('status', 'active');

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }

        $query->orderByDesc('created_at');

        $projects = $query->paginate(
            \App\Models\PlatformSetting::get('pagination_per_page', 12)
        );

        return response()->json([
            'success' => true,
            'data'    => $projects->map(fn($p) => [
                'id'                  => $p->id,
                'title'               => $p->title,
                'description'         => $p->description,
                'cover_image'         => $p->cover_image,
                'cover_color'         => $p->cover_color,
                'vacancies_count'     => $p->vacancies_count,
                'customer_first_name' => $p->customer_first_name,
                'customer_last_name'  => $p->customer_last_name,
                'created_at'          => $p->created_at?->toISOString(),
                'owner'               => $p->owner ? [
                    'id'             => $p->owner->id,
                    'full_name'      => trim(($p->owner->profile->first_name ?? '') . ' ' . ($p->owner->profile->last_name ?? '')) ?: $p->owner->name,
                    'avatar'         => $p->owner->profile->avatar ?? null,
                    'average_rating' => $p->owner->profile->average_rating ?? 0,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page'    => $projects->lastPage(),
                'total'        => $projects->total(),
            ],
        ]);
    }

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
