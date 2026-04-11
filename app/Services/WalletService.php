<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public function deposit(User $user, array $data): Transaction
    {
        $wallet = $user->wallet;

        return DB::transaction(function () use ($wallet, $data) {
            $transaction = Transaction::create([
                'uuid'           => Str::uuid(),
                'to_wallet_id'   => $wallet->id,
                'to_account_id'  => $data['account_id'] ?? $wallet->accounts()->where('is_default', true)->value('id'),
                'type'           => 'deposit',
                'amount'         => $data['amount'],
                'currency'       => $wallet->currency,
                'status'         => 'pending',
                'payment_method' => $data['payment_method'],
                'description'    => 'Поповнення рахунку',
            ]);

            // В реальному проекті тут інтеграція з платіжною системою
            // Поки що просто завершуємо транзакцію
            $wallet->credit($data['amount']);
            $transaction->update(['status' => 'completed', 'processed_at' => now()]);

            return $transaction;
        });
    }

    public function withdraw(User $user, array $data): array
    {
        $wallet = $user->wallet;
        $minAmount = PlatformSetting::get('min_withdrawal_amount', 10);

        if ($data['amount'] < $minAmount) {
            return ['success' => false, 'message' => "Мінімальна сума виведення: {$minAmount} USD"];
        }

        if (!$wallet->hasEnoughBalance($data['amount'])) {
            return ['success' => false, 'message' => 'Недостатньо коштів'];
        }

        $transaction = DB::transaction(function () use ($wallet, $data) {
            $wallet->freeze($data['amount']);

            return Transaction::create([
                'uuid'           => Str::uuid(),
                'from_wallet_id' => $wallet->id,
                'from_account_id'=> $data['account_id'] ?? null,
                'type'           => 'withdrawal',
                'amount'         => $data['amount'],
                'currency'       => $wallet->currency,
                'status'         => 'pending',
                'meta'           => $data['requisites'],
                'description'    => 'Виведення коштів',
            ]);
        });

        return ['success' => true, 'transaction' => $transaction];
    }

    public function transfer(User $user, array $data): array
    {
        $wallet = $user->wallet;
        $fromAccount = $wallet->accounts()->find($data['from_account_id']);

        if (!$fromAccount || $fromAccount->balance < $data['amount']) {
            return ['success' => false, 'message' => 'Недостатньо коштів на рахунку'];
        }

        DB::transaction(function () use ($wallet, $fromAccount, $data) {
            $fromAccount->decrement('balance', $data['amount']);
            $wallet->accounts()->find($data['to_account_id'])->increment('balance', $data['amount']);

            Transaction::create([
                'uuid'           => Str::uuid(),
                'from_wallet_id' => $wallet->id,
                'to_wallet_id'   => $wallet->id,
                'from_account_id'=> $data['from_account_id'],
                'to_account_id'  => $data['to_account_id'],
                'type'           => 'transfer',
                'amount'         => $data['amount'],
                'currency'       => $wallet->currency,
                'status'         => 'completed',
                'processed_at'   => now(),
                'description'    => 'Внутрішній переказ між рахунками',
            ]);
        });

        return ['success' => true];
    }
}
