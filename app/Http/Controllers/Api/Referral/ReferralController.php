<?php

namespace App\Http\Controllers\Api\Referral;

use App\Http\Controllers\Api\ApiController;
use App\Models\Referral;
use App\Models\ReferralPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $referrals = Referral::with('referred.profile')
            ->where('referrer_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->success([
            'referral_code'  => $user->referral_code,
            'referral_link'  => config('app.url') . '/register?ref=' . $user->referral_code,
            'total_referrals'=> $referrals->count(),
            'active_referrals'=> $referrals->where('status', 'active')->count(),
            'total_earned'   => ReferralPayment::whereIn('referral_id', $referrals->pluck('id'))->sum('amount'),
            'referrals'      => $referrals->map(fn($r) => [
                'id'         => $r->id,
                'status'     => $r->status,
                'joined_at'  => $r->created_at->toISOString(),
                'user'       => [
                    'full_name' => $r->referred->profile?->full_name,
                    'avatar'    => $r->referred->profile?->avatar,
                ],
            ]),
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $referralIds = Referral::where('referrer_id', $request->user()->id)->pluck('id');

        $payments = ReferralPayment::whereIn('referral_id', $referralIds)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $payments->map(fn($p) => [
                'id'           => $p->id,
                'amount'       => $p->amount,
                'period_start' => $p->period_start->format('Y-m-d'),
                'period_end'   => $p->period_end->format('Y-m-d'),
                'created_at'   => $p->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'total'        => $payments->total(),
            ],
        ]);
    }
}
