<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\VacancyResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->status ?? 'active';

        $projects = Project::with(['vacancies'])
            ->where('owner_id', $request->user()->id)
            ->where('status', $status)
            ->withCount('vacancies')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $projects->map(fn($p) => [
                'id'               => $p->id,
                'title'            => $p->title,
                'description'      => $p->description,
                'cover_image'      => $p->cover_image,
                'cover_color'      => $p->cover_color,
                'status'           => $p->status,
                'vacancies_count'  => $p->vacancies_count,
                'created_at'       => $p->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page'    => $projects->lastPage(),
                'total'        => $projects->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:300',
            'description' => 'nullable|string|max:3000',
            'cover_image' => 'nullable|url',
            'cover_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $project = Project::create([
            ...$data,
            'owner_id' => $request->user()->id,
            'status'   => 'active',
        ]);

        return $this->created($project);
    }

    public function show(Project $project): JsonResponse
    {
        if ($project->owner_id !== auth()->id()) {
            return $this->forbidden();
        }

        $project->load(['vacancies' => fn($q) => $q->withCount('applications')]);

        return $this->success($project);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'title'       => 'sometimes|string|max:300',
            'description' => 'nullable|string|max:3000',
            'cover_image' => 'nullable|url',
            'cover_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $project->update($data);

        return $this->success($project->fresh());
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $project->delete();

        return $this->success(null, 'Проект видалено');
    }

    public function archive(Request $request, Project $project): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $project->update(['status' => 'archived']);

        return $this->success(null, 'Проект архівовано');
    }

    public function restore(Request $request, Project $project): JsonResponse
    {
        if ($project->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $project->update(['status' => 'active']);

        return $this->success(null, 'Проект відновлено');
    }
}
