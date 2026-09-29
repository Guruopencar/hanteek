<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\SetupIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function ensureCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email'    => $user->email,
            'name'     => $user->name,
            'phone'    => $user->phone,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);
        return $customer->id;
    }

    public function createSetupIntent(User $user): SetupIntent
    {
        return $this->stripe->setupIntents->create([
            'customer'       => $this->ensureCustomer($user),
            'payment_method_types' => ['card'],
            'usage'          => 'off_session',
        ]);
    }

    public function attachPaymentMethod(User $user, string $paymentMethodId, string $holderName = null): PaymentMethod
    {
        $customerId = $this->ensureCustomer($user);

        $spm = $this->stripe->paymentMethods->retrieve($paymentMethodId);
        if ($spm->customer !== $customerId) {
            $spm = $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $customerId,
            ]);
        }

        return DB::transaction(function () use ($user, $spm, $holderName) {
            $isFirst = $user->paymentMethods()->count() === 0;

            if ($isFirst) {
                $user->paymentMethods()->update(['is_default' => false]);
            }

            return PaymentMethod::create([
                'user_id'                  => $user->id,
                'stripe_payment_method_id' => $spm->id,
                'brand'                    => $spm->card->brand,
                'last4'                    => $spm->card->last4,
                'exp_month'                => $spm->card->exp_month,
                'exp_year'                 => $spm->card->exp_year,
                'holder_name'              => $holderName ?: ($spm->billing_details->name ?? null),
                'is_default'               => $isFirst,
            ]);
        });
    }

    public function detachPaymentMethod(PaymentMethod $pm): void
    {
        try {
            $this->stripe->paymentMethods->detach($pm->stripe_payment_method_id);
        } catch (\Throwable $e) {
            Log::warning('Stripe detach failed', ['pm' => $pm->id, 'e' => $e->getMessage()]);
        }
        $pm->delete();
    }

    public function setDefaultPaymentMethod(PaymentMethod $pm): void
    {
        DB::transaction(function () use ($pm) {
            $pm->user->paymentMethods()->update(['is_default' => false]);
            $pm->update(['is_default' => true]);
        });
    }

    /**
     * Deposit funds into user's wallet using a saved payment method.
     * Money is captured immediately; wallet balance is updated on webhook
     * (payment_intent.succeeded) to keep the source of truth in Stripe.
     */
    public function createDepositIntent(User $user, PaymentMethod $pm, float $amount): array
    {
        $wallet = $user->wallet;
        abort_unless($wallet, 400, 'Wallet not initialised');

        $intent = $this->stripe->paymentIntents->create([
            'amount'         => (int) round($amount * 100),
            'currency'       => strtolower($wallet->currency ?: 'usd'),
            'customer'       => $this->ensureCustomer($user),
            'payment_method' => $pm->stripe_payment_method_id,
            'off_session'    => true,
            'confirm'        => true,
            'description'    => 'Wallet deposit',
            'metadata'       => [
                'user_id'           => $user->id,
                'wallet_id'         => $wallet->id,
                'payment_method_id' => $pm->id,
                'kind'              => 'deposit',
            ],
        ]);

        $accountId = $wallet->accounts()->where('is_default', true)->value('id');

        Transaction::create([
            'uuid'                     => Str::uuid(),
            'to_wallet_id'             => $wallet->id,
            'to_account_id'            => $accountId,
            'type'                     => 'deposit',
            'amount'                   => $amount,
            'currency'                 => $wallet->currency,
            'status'                   => $this->mapIntentStatus($intent->status),
            'payment_method'           => 'stripe',
            'stripe_payment_intent_id' => $intent->id,
            'payment_method_id'        => $pm->id,
            'description'              => 'Deposit via Stripe',
        ]);

        return [
            'client_secret' => $intent->client_secret,
            'status'        => $intent->status,
            'id'            => $intent->id,
        ];
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        $secret = config('services.stripe.webhook_secret');
        $event  = Webhook::constructEvent($payload, $signature, $secret);

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->onIntentSucceeded($event->data->object);
                return ['handled' => 'payment_intent.succeeded'];

            case 'payment_intent.payment_failed':
                $this->onIntentFailed($event->data->object);
                return ['handled' => 'payment_intent.payment_failed'];

            case 'payment_method.detached':
                PaymentMethod::where('stripe_payment_method_id', $event->data->object->id)?->delete();
                return ['handled' => 'payment_method.detached'];

            default:
                return ['ignored' => $event->type];
        }
    }

    private function onIntentSucceeded(PaymentIntent $intent): void
    {
        $trx = Transaction::where('stripe_payment_intent_id', $intent->id)->first();
        if (!$trx || $trx->status === 'completed') return;

        DB::transaction(function () use ($trx) {
            $trx->update(['status' => 'completed', 'completed_at' => now()]);

            $wallet = Wallet::find($trx->to_wallet_id);
            if ($wallet) {
                $wallet->increment('balance', $trx->amount);
                if ($trx->to_account_id) {
                    $wallet->accounts()->where('id', $trx->to_account_id)
                        ->increment('balance', $trx->amount);
                }
            }
        });
    }

    private function onIntentFailed(PaymentIntent $intent): void
    {
        Transaction::where('stripe_payment_intent_id', $intent->id)
            ->update(['status' => 'failed']);
    }

    private function mapIntentStatus(string $status): string
    {
        return match ($status) {
            'succeeded'                 => 'completed',
            'processing'                => 'pending',
            'requires_action',
            'requires_confirmation',
            'requires_payment_method'   => 'pending',
            'canceled'                  => 'failed',
            default                     => 'pending',
        };
    }
}
