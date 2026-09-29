<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Api\ApiController;
use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends ApiController
{
    public function __construct(private StripeService $stripe) {}

    public function index(Request $request): JsonResponse
    {
        $cards = $request->user()->paymentMethods()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PaymentMethod $c) => [
                'id'          => $c->id,
                'brand'       => $c->brand,
                'last4'       => $c->last4,
                'exp_month'   => $c->exp_month,
                'exp_year'    => $c->exp_year,
                'holder_name' => $c->holder_name,
                'is_default'  => $c->is_default,
                'created_at'  => $c->created_at?->toISOString(),
            ]);

        return $this->success($cards);
    }

    public function setupIntent(Request $request): JsonResponse
    {
        try {
            $intent = $this->stripe->createSetupIntent($request->user());
            return $this->success([
                'client_secret' => $intent->client_secret,
                'id'            => $intent->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe setupIntent failed', ['e' => $e->getMessage()]);
            return $this->error('Не вдалося ініціалізувати картку', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_method_id' => 'required|string',
            'holder_name'       => 'nullable|string|max:200',
        ]);

        try {
            $pm = $this->stripe->attachPaymentMethod(
                $request->user(),
                $data['payment_method_id'],
                $data['holder_name'] ?? null,
            );
            return $this->created([
                'id'          => $pm->id,
                'brand'       => $pm->brand,
                'last4'       => $pm->last4,
                'exp_month'   => $pm->exp_month,
                'exp_year'    => $pm->exp_year,
                'holder_name' => $pm->holder_name,
                'is_default'  => $pm->is_default,
            ], 'Картку додано');
        } catch (\Throwable $e) {
            Log::error('Stripe attach failed', ['e' => $e->getMessage()]);
            return $this->error($e->getMessage() ?: 'Не вдалося зберегти картку', 422);
        }
    }

    public function setDefault(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            return $this->forbidden();
        }
        $this->stripe->setDefaultPaymentMethod($paymentMethod);
        return $this->success(null, 'Картку за замовчуванням оновлено');
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            return $this->forbidden();
        }
        $this->stripe->detachPaymentMethod($paymentMethod);
        return $this->success(null, 'Картку видалено');
    }
}
