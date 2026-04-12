<?php

namespace App\Http\Controllers\Api\Contract;

use App\Http\Controllers\Api\ApiController;
use App\Models\Contract;
use App\Models\ContractTask;
use App\Models\ContractTimeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeLogController extends ApiController
{
    private function authorizeParticipant(Contract $contract): bool
    {
        $user = auth()->user();
        return $contract->owner_id === $user->id || $contract->developer_id === $user->id;
    }

    private function ensureLogBelongsToContract(ContractTimeLog $log, Contract $contract): bool
    {
        return $log->contract_id === $contract->id;
    }

    public function index(Contract $contract): JsonResponse
    {
        if (!$this->authorizeParticipant($contract)) {
            return $this->forbidden();
        }

        $logs = $contract->timeLogs()
            ->with('user.profile', 'task')
            ->orderByDesc('logged_date')
            ->get();

        return $this->success($logs->map(fn($l) => [
            'id'          => $l->id,
            'hours'       => $l->hours,
            'description' => $l->description,
            'logged_date' => $l->logged_date->format('Y-m-d'),
            'user'        => ['full_name' => $l->user->profile?->full_name],
            'task'        => $l->task ? ['id' => $l->task->id, 'title' => $l->task->title] : null,
        ]));
    }

    public function store(Request $request, Contract $contract): JsonResponse
    {
        if ($contract->developer_id !== $request->user()->id) {
            return $this->forbidden('Тільки розробник може логувати час');
        }

        if ($contract->contract_type !== 'time_material') {
            return $this->error('Трекінг часу доступний тільки для T&M контрактів');
        }

        $data = $request->validate([
            'hours'       => 'required|numeric|min:0.5|max:24',
            'description' => 'nullable|string|max:500',
            'logged_date' => 'required|date|before_or_equal:today',
            'task_id'     => 'nullable|integer|exists:contract_tasks,id',
        ]);

        // Перевірка що task_id належить цьому контракту
        if (!empty($data['task_id'])) {
            $taskBelongs = ContractTask::where('id', $data['task_id'])
                ->where('contract_id', $contract->id)
                ->exists();

            if (!$taskBelongs) {
                return $this->error('Задача не належить цьому контракту');
            }
        }

        $log = $contract->timeLogs()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return $this->created($log);
    }

    public function update(Request $request, Contract $contract, ContractTimeLog $log): JsonResponse
    {
        if (!$this->ensureLogBelongsToContract($log, $contract)) {
            return $this->notFound();
        }

        if ($log->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'hours'       => 'sometimes|numeric|min:0.5|max:24',
            'description' => 'nullable|string|max:500',
            'logged_date' => 'sometimes|date|before_or_equal:today',
        ]);

        $log->update($data);
        return $this->success($log->fresh());
    }

    public function destroy(Request $request, Contract $contract, ContractTimeLog $log): JsonResponse
    {
        if (!$this->ensureLogBelongsToContract($log, $contract)) {
            return $this->notFound();
        }

        if ($log->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $log->delete();
        return $this->success(null, 'Запис видалено');
    }
}
