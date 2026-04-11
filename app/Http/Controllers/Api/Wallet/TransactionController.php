<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $walletId = $request->user()->wallet?->id;

        if (!$walletId) {
            return $this->success([]);
        }

        $query = Transaction::where(function ($q) use ($walletId) {
            $q->where('from_wallet_id', $walletId)
              ->orWhere('to_wallet_id', $walletId);
        });

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->from) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->where('created_at', '<=', $request->to);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20);

        return $this->paginated($transactions, TransactionResource::class);
    }

    public function show(string $uuid): JsonResponse
    {
        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();

        $walletId = auth()->user()->wallet?->id;

        if ($transaction->from_wallet_id !== $walletId && $transaction->to_wallet_id !== $walletId) {
            return $this->forbidden();
        }

        return $this->success(new TransactionResource($transaction));
    }
}
