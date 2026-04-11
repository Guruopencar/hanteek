<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ReviewResource;
use App\Models\Contract;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with(['reviewer.profile', 'reviewee.profile', 'contract'])
            ->where('reviewee_id', $request->user()->id)
            ->published()
            ->orderByDesc('published_at')
            ->paginate(15);

        return $this->paginated($reviews, ReviewResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'required|string|min:20|max:2000',
        ]);

        $contract = Contract::findOrFail($data['contract_id']);
        $user = $request->user();

        // Перевірити що юзер є учасником контракту
        if ($contract->owner_id !== $user->id && $contract->developer_id !== $user->id) {
            return $this->forbidden('Ви не є учасником цього контракту');
        }

        // Перевірити що контракт завершено
        if (!$contract->isCompleted()) {
            return $this->error('Можна залишити відгук тільки по завершеному контракту');
        }

        // Перевірити deadline
        if (!$contract->canLeaveReview()) {
            return $this->error('Термін для залишення відгуку минув (14 днів)');
        }

        // Перевірити чи вже залишив відгук
        $exists = Review::where('contract_id', $contract->id)
            ->where('reviewer_id', $user->id)
            ->exists();

        if ($exists) {
            return $this->error('Ви вже залишили відгук по цьому контракту');
        }

        // Визначити reviewee
        $revieweeId = $contract->owner_id === $user->id
            ? $contract->developer_id
            : $contract->owner_id;

        $review = Review::create([
            'contract_id' => $contract->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'rating'      => $data['rating'],
            'comment'     => $data['comment'],
            'status'      => 'pending',
        ]);

        // Перевірити чи обидва залишили відгук
        $review->checkAndPublish();

        return $this->created(new ReviewResource($review), 'Відгук надіслано');
    }

    public function byContract(Contract $contract): JsonResponse
    {
        $reviews = Review::with(['reviewer.profile'])
            ->where('contract_id', $contract->id)
            ->published()
            ->get();

        return $this->success(ReviewResource::collection($reviews));
    }

    public function byUser(User $user): JsonResponse
    {
        $reviews = Review::with(['reviewer.profile', 'contract'])
            ->where('reviewee_id', $user->id)
            ->published()
            ->orderByDesc('published_at')
            ->paginate(15);

        return $this->paginated($reviews, ReviewResource::class);
    }
}
