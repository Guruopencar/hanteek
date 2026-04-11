<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\AdminLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminFinanceController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::with(['fromWallet.user.profile', 'toWallet.user.profile'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function wallets(Request $request): JsonResponse
    {
        $wallets = Wallet::with('user.profile')
            ->orderByDesc('balance')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $wallets]);
    }

    public function adjustBalance(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'amount'      => 'required|numeric',
            'type'        => 'required|in:credit,debit',
            'description' => 'required|string|max:500',
        ]);

        $wallet = $user->wallet;

        if (!$wallet) {
            return $this->error('Гаманець не знайдено');
        }

        DB::transaction(function () use ($wallet, $data) {
            if ($data['type'] === 'credit') {
                $wallet->credit(abs($data['amount']));
            } else {
                $wallet->debit(abs($data['amount']));
            }

            Transaction::create([
                'uuid'           => Str::uuid(),
                'to_wallet_id'   => $data['type'] === 'credit' ? $wallet->id : null,
                'from_wallet_id' => $data['type'] === 'debit' ? $wallet->id : null,
                'type'           => 'bonus',
                'amount'         => abs($data['amount']),
                'currency'       => $wallet->currency,
                'status'         => 'completed',
                'description'    => '[ADMIN] ' . $data['description'],
                'processed_at'   => now(),
            ]);
        });

        AdminLog::record('adjust_balance', 'User', $user->id, $data['description'],
            ['balance' => $wallet->getOriginal('balance')],
            ['balance' => $wallet->fresh()->balance]
        );

        return $this->success(null, 'Баланс скориговано');
    }

    public function commission(): JsonResponse
    {
        $stats = [
            'total_commission'   => Transaction::where('status', 'completed')->sum('commission'),
            'this_month'         => Transaction::where('status', 'completed')
                ->whereMonth('created_at', now()->month)->sum('commission'),
            'total_volume'       => Transaction::where('status', 'completed')->sum('amount'),
            'pending_withdrawal' => Transaction::where('type', 'withdrawal')
                ->where('status', 'pending')->sum('amount'),
        ];

        return $this->success($stats);
    }
}
