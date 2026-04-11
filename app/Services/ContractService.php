<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Conversation;
use App\Models\User;
use App\Jobs\PublishPendingReviewsJob;

class ContractService
{
    public function create(User $owner, array $data): Contract
    {
        $contract = Contract::create([
            ...$data,
            'owner_id' => $owner->id,
            'status'   => 'pending_signature',
        ]);

        // Автоматично створити чат для контракту
        Conversation::firstOrCreate([
            'participant_1' => $owner->id,
            'participant_2' => $data['developer_id'],
            'contract_id'   => $contract->id,
        ]);

        return $contract->load(['owner.profile', 'developer.profile']);
    }

    public function sign(Contract $contract, User $user): array
    {
        if ($contract->owner_id === $user->id) {
            if ($contract->owner_signed_at) {
                return ['success' => false, 'message' => 'Ви вже підписали контракт'];
            }
            $contract->update(['owner_signed_at' => now()]);
        }

        if ($contract->developer_id === $user->id) {
            if ($contract->developer_signed_at) {
                return ['success' => false, 'message' => 'Ви вже підписали контракт'];
            }
            $contract->update(['developer_signed_at' => now()]);
        }

        // Якщо обидва підписали — активуємо
        $contract->refresh();
        if ($contract->isFullySigned()) {
            $contract->update([
                'status'     => 'active',
                'started_at' => now(),
            ]);
        }

        return ['success' => true, 'message' => 'Контракт підписано'];
    }

    public function complete(Contract $contract): void
    {
        $contract->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Оновити лічильник контрактів обох учасників
        $contract->owner->profile?->increment('contracts_count');
        $contract->developer->profile?->increment('contracts_count');

        // Запустити job перевірки відгуків через 14 днів
        PublishPendingReviewsJob::dispatch($contract->id)
            ->delay(now()->addDays(14));
    }
}
