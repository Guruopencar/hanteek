<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\WalletResource;
use App\Http\Resources\TransactionResource;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\StripeService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends ApiController
{
    public function __construct(
        private WalletService $walletService,
        private StripeService $stripe,
    ) {}

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

    public function depositIntent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'            => 'required|numeric|min:1|max:100000',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
        ]);

        $pm = PaymentMethod::where('id', $data['payment_method_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        try {
            $result = $this->stripe->createDepositIntent(
                $request->user(),
                $pm,
                (float) $data['amount'],
            );
            return $this->created($result, 'Платіж створено');
        } catch (\Stripe\Exception\CardException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('Stripe deposit failed', ['e' => $e->getMessage()]);
            return $this->error('Помилка платежу', 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('Stripe-Signature', '');
        try {
            $result = $this->stripe->handleWebhook($request->getContent(), $signature);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['success' => false, 'error' => 'invalid signature'], 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook failed', ['e' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function publicKey(): JsonResponse
    {
        return $this->success(['publishable_key' => config('services.stripe.key')]);
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
