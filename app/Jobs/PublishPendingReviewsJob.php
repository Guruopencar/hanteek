<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishPendingReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $contractId) {}

    public function handle(): void
    {
        // Публікуємо всі pending відгуки по контракту (якщо минуло 14 днів)
        $published = Review::where('contract_id', $this->contractId)
            ->where('status', 'pending')
            ->update([
                'status'       => 'published',
                'is_published' => true,
                'published_at' => now(),
            ]);

        if ($published > 0) {
            // Перерахувати рейтинги
            $contract = Contract::with('owner.profile', 'developer.profile')->find($this->contractId);
            $contract?->owner->profile?->recalculateRating();
            $contract?->developer->profile?->recalculateRating();
        }
    }
}
