<?php

namespace App\Http\Controllers\Api\Contract;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\Conversation;
use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends ApiController
{
    public function __construct(private ContractService $contractService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->status;

        $query = Contract::with(['owner.profile', 'developer.profile', 'tasks'])
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhere('developer_id', $user->id);
            });

        if ($status) {
            $query->where('status', $status);
        }

        $contracts = $query->orderByDesc('created_at')->paginate(15);

        return $this->paginated($contracts, ContractResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'developer_id'  => 'required|exists:users,id',
            'vacancy_id'    => 'nullable|exists:vacancies,id',
            'type'          => 'required|in:vacancy,resume,agreement',
            'title'         => 'required|string|max:300',
            'description'   => 'nullable|string',
            'contract_type' => 'required|in:fixed_price,time_material',
            'total_amount'  => 'nullable|numeric|min:0',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'estimated_hours'=> 'nullable|integer|min:1',
            'currency'      => 'nullable|string|size:3',
            'deadline_at'   => 'nullable|date|after:today',
        ]);

        $contract = $this->contractService->create($request->user(), $data);

        return $this->created(new ContractResource($contract));
    }

    public function show(Contract $contract): JsonResponse
    {
        $this->authorizeContract($contract);
        $contract->load(['owner.profile', 'developer.profile', 'tasks', 'timeLogs']);
        return $this->success(new ContractResource($contract));
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeContract($contract);

        if (!in_array($contract->status, ['draft', 'pending_signature'])) {
            return $this->error('Активний контракт не можна редагувати');
        }

        $data = $request->validate([
            'title'         => 'sometimes|string|max:300',
            'description'   => 'nullable|string',
            'total_amount'  => 'nullable|numeric|min:0',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'deadline_at'   => 'nullable|date|after:today',
        ]);

        $contract->update($data);
        return $this->success(new ContractResource($contract));
    }

    public function sign(Request $request, Contract $contract): JsonResponse
    {
        $user = $request->user();

        if ($contract->owner_id !== $user->id && $contract->developer_id !== $user->id) {
            return $this->forbidden();
        }

        $result = $this->contractService->sign($contract, $user);

        if (!$result['success']) {
            return $this->error($result['message']);
        }

        return $this->success(new ContractResource($contract->fresh()), $result['message']);
    }

    public function complete(Request $request, Contract $contract): JsonResponse
    {
        if ($contract->owner_id !== $request->user()->id) {
            return $this->forbidden('Тільки власник може завершити контракт');
        }

        if ($contract->status !== 'active') {
            return $this->error('Можна завершити тільки активний контракт');
        }

        $this->contractService->complete($contract);

        return $this->success(new ContractResource($contract->fresh()), 'Контракт завершено');
    }

    public function archive(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeContract($contract);
        $contract->update(['status' => 'archived', 'deleted_at' => now()]);
        return $this->success(null, 'Контракт архівовано');
    }

    public function cancel(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeContract($contract);

        if ($contract->status === 'completed') {
            return $this->error('Завершений контракт не можна скасувати');
        }

        $contract->update(['status' => 'cancelled']);
        return $this->success(null, 'Контракт скасовано');
    }

    private function authorizeContract(Contract $contract): void
    {
        $user = auth()->user();
        if ($contract->owner_id !== $user->id && $contract->developer_id !== $user->id) {
            abort(403);
        }
    }
}
