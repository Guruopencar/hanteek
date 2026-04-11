<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\WalletResource;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends ApiController
{
    public function __construct(private WalletService $walletService) {}

    public function show(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet()->with('accounts')->firstOrFail();
        return $this->success(new WalletResource($wallet));
    }

    public function accounts(Request $request): JsonResponse
    {
        $accounts = $request->user()->wallet->accounts()->get();
        return $this->success($accounts);
    }

    public function deposit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:5',
            'payment_method' => 'required|string',
            'account_id'     => 'nullable|exists:wallet_accounts,id',
        ]);

        $transaction = $this->walletService->deposit($request->user(), $data);

        return $this->created(new TransactionResource($transaction), 'Рахунок поповнено');
    }

    public function withdraw(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'     => 'required|numeric|min:10',
            'requisites' => 'required|array',
            'account_id' => 'nullable|exists:wallet_accounts,id',
        ]);

        $result = $this->walletService->withdraw($request->user(), $data);

        if (!$result['success']) {
            return $this->error($result['message']);
        }

        return $this->success(new TransactionResource($result['transaction']), 'Запит на виведення створено');
    }

    public function transfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'          => 'required|numeric|min:1',
            'from_account_id' => 'required|exists:wallet_accounts,id',
            'to_account_id'   => 'required|exists:wallet_accounts,id|different:from_account_id',
        ]);

        $result = $this->walletService->transfer($request->user(), $data);

        if (!$result['success']) {
            return $this->error($result['message']);
        }

        return $this->success(null, 'Переказ виконано');
    }
}
