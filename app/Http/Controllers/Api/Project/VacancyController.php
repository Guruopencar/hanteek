<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\VacancyResource;
use App\Models\Project;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyController extends ApiController
{
    private function ensureVacancyBelongsToProject(Vacancy $vacancy, Project $project): bool
    {
        return $vacancy->project_id === $project->id;
    }

    public function index(Project $project): JsonResponse
    {
        if ($project->owner_id !== auth()->id()) {
            return $this->forbidden();
        }

        $vacancies = $project->vacancies()
            ->withCount('applications')
            ->orderByDesc('created_at')
            ->get();

        return $this->success(VacancyResource::collection($vacancies));
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'title'           => 'required|string|max:300',
            'description'     => 'required|string',
            'requirements'    => 'nullable|string',
            'work_type'       => 'required|in:remote,office,hybrid',
            'location'        => 'nullable|string|max:200',
            'contract_type'   => 'required|in:fixed_price,time_material',
            'budget'          => 'nullable|numeric|min:0',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:1',
            'currency'        => 'nullable|string|size:3',
            'skills_required' => 'nullable|array',
            'skills_required.*' => 'string|max:50',
        ]);

        $vacancy = $project->vacancies()->create([
            ...$data,
            'owner_id' => $request->user()->id,
            'status'   => 'draft',
        ]);

        return $this->created(new VacancyResource($vacancy));
    }

    public function show(Project $project, Vacancy $vacancy): JsonResponse
    {
        if (!$this->ensureVacancyBelongsToProject($vacancy, $project)) {
            return $this->notFound();
        }

        $vacancy->load(['owner.profile', 'project']);
        $vacancy->incrementViews();
        return $this->success(new VacancyResource($vacancy));
    }

    public function update(Request $request, Project $project, Vacancy $vacancy): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        if (!$this->ensureVacancyBelongsToProject($vacancy, $project)) {
            return $this->notFound();
        }

        $data = $request->validate([
            'title'           => 'sometimes|string|max:300',
            'description'     => 'sometimes|string',
            'requirements'    => 'nullable|string',
            'work_type'       => 'sometimes|in:remote,office,hybrid',
            'location'        => 'nullable|string|max:200',
            'contract_type'   => 'sometimes|in:fixed_price,time_material',
            'budget'          => 'nullable|numeric|min:0',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:1',
            'skills_required' => 'nullable|array',
        ]);

        $vacancy->update($data);
        return $this->success(new VacancyResource($vacancy->fresh()));
    }

    public function destroy(Request $request, Project $project, Vacancy $vacancy): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        if (!$this->ensureVacancyBelongsToProject($vacancy, $project)) {
            return $this->notFound();
        }

        $vacancy->delete();
        return $this->success(null, 'Вакансію видалено');
    }

    public function publish(Request $request, Project $project, Vacancy $vacancy): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        if (!$this->ensureVacancyBelongsToProject($vacancy, $project)) {
            return $this->notFound();
        }

        $vacancy->update(['status' => 'published', 'published_at' => now()]);
        return $this->success(null, 'Вакансію опубліковано');
    }

    public function archive(Request $request, Project $project, Vacancy $vacancy): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        if (!$this->ensureVacancyBelongsToProject($vacancy, $project)) {
            return $this->notFound();
        }

        $vacancy->update(['status' => 'archived']);
        return $this->success(null, 'Вакансію архівовано');
    }
}
