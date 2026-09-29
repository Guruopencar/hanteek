<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\VacancyResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
                'id'                  => $p->id,
                'title'               => $p->title,
                'description'         => $p->description,
                'cover_image'         => $p->cover_image,
                'cover_color'         => $p->cover_color,
                'status'              => $p->status,
                'customer_first_name' => $p->customer_first_name,
                'customer_last_name'  => $p->customer_last_name,
                'customer_email'      => $p->customer_email,
                'customer_phone'      => $p->customer_phone,
                'card_id'             => $p->card_id,
                'contract_id'         => $p->contract_id,
                'vacancies_count'     => $p->vacancies_count,
                'created_at'          => $p->created_at->toISOString(),
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
            'title'               => 'required|string|max:300',
            'description'         => 'nullable|string|max:3000',
            'cover_image'         => 'nullable|string|max:500',
            'cover_color'         => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'customer_first_name' => 'nullable|string|max:100',
            'customer_last_name'  => 'nullable|string|max:100',
            'customer_email'      => 'nullable|email|max:200',
            'customer_phone'      => 'nullable|string|max:40',
            'card_id'             => 'nullable|integer|exists:wallet_accounts,id',
            'contract_id'         => 'nullable|integer|exists:contracts,id',
        ]);

        // If cover_image is a data URL — save via media library and use its URL.
        if (!empty($data['cover_image']) && str_starts_with($data['cover_image'], 'data:image/')) {
            $data['cover_image'] = $this->storeInlineImage($data['cover_image'], $request->user()->id);
        }

        $project = Project::create([
            ...$data,
            'owner_id' => $request->user()->id,
            'status'   => 'active',
        ]);

        return $this->created($project);
    }

    private function storeInlineImage(string $dataUrl, int $userId): ?string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,(.+)$/', $dataUrl, $m)) {
            return null;
        }
        $ext  = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $bin  = base64_decode($m[2]);
        $name = 'project-covers/'.$userId.'-'.uniqid().'.'.$ext;
        Storage::disk('public')->put($name, $bin);
        return Storage::disk('public')->url($name);
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
            'title'               => 'sometimes|string|max:300',
            'description'         => 'nullable|string|max:3000',
            'cover_image'         => 'nullable|string|max:500',
            'cover_color'         => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'customer_first_name' => 'nullable|string|max:100',
            'customer_last_name'  => 'nullable|string|max:100',
            'customer_email'      => 'nullable|email|max:200',
            'customer_phone'      => 'nullable|string|max:40',
            'card_id'             => 'nullable|integer|exists:wallet_accounts,id',
            'contract_id'         => 'nullable|integer|exists:contracts,id',
        ]);

        if (!empty($data['cover_image']) && str_starts_with($data['cover_image'], 'data:image/')) {
            $data['cover_image'] = $this->storeInlineImage($data['cover_image'], $request->user()->id);
        }

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
