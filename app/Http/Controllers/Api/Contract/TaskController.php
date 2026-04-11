<?php

namespace App\Http\Controllers\Api\Contract;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TaskResource;
use App\Models\Contract;
use App\Models\ContractTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends ApiController
{
    public function index(Contract $contract): JsonResponse
    {
        $this->authorizeContract($contract);
        $tasks = $contract->tasks()->with('assignee.profile', 'creator.profile')->get();
        return $this->success(TaskResource::collection($tasks));
    }

    public function store(Request $request, Contract $contract): JsonResponse
    {
        if ($contract->owner_id !== $request->user()->id) {
            return $this->forbidden('Тільки власник може створювати задачі');
        }

        $data = $request->validate([
            'title'           => 'required|string|max:300',
            'description'     => 'nullable|string',
            'priority'        => 'nullable|in:low,medium,high,critical',
            'assigned_to'     => 'nullable|exists:users,id',
            'due_date'        => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0.5',
        ]);

        $task = $contract->tasks()->create([
            ...$data,
            'created_by' => $request->user()->id,
            'sort_order' => $contract->tasks()->count(),
        ]);

        return $this->created(new TaskResource($task));
    }

    public function show(Contract $contract, ContractTask $task): JsonResponse
    {
        $this->authorizeContract($contract);
        return $this->success(new TaskResource($task->load('assignee.profile')));
    }

    public function update(Request $request, Contract $contract, ContractTask $task): JsonResponse
    {
        if ($contract->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'title'           => 'sometimes|string|max:300',
            'description'     => 'nullable|string',
            'priority'        => 'nullable|in:low,medium,high,critical',
            'status'          => 'nullable|in:todo,in_progress,review,done,cancelled',
            'assigned_to'     => 'nullable|exists:users,id',
            'due_date'        => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0.5',
        ]);

        $task->update($data);
        return $this->success(new TaskResource($task->fresh()));
    }

    public function destroy(Request $request, Contract $contract, ContractTask $task): JsonResponse
    {
        if ($contract->owner_id !== $request->user()->id) {
            return $this->forbidden();
        }
        $task->delete();
        return $this->success(null, 'Задачу видалено');
    }

    public function markDone(Request $request, Contract $contract, ContractTask $task): JsonResponse
    {
        $user = $request->user();

        if ($contract->owner_id !== $user->id && $contract->developer_id !== $user->id) {
            return $this->forbidden();
        }

        $task->markAsDone();
        return $this->success(new TaskResource($task->fresh()), 'Задачу виконано');
    }

    public function reorder(Request $request, Contract $contract): JsonResponse
    {
        $request->validate(['tasks' => 'required|array', 'tasks.*' => 'integer']);

        foreach ($request->tasks as $order => $taskId) {
            ContractTask::where('id', $taskId)
                ->where('contract_id', $contract->id)
                ->update(['sort_order' => $order]);
        }

        return $this->success(null, 'Порядок оновлено');
    }

    private function authorizeContract(Contract $contract): void
    {
        $user = auth()->user();
        if ($contract->owner_id !== $user->id && $contract->developer_id !== $user->id) {
            abort(403);
        }
    }
}
