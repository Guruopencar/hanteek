<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Contract;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAnalyticsController extends ApiController
{
    public function overview(): JsonResponse
    {
        return $this->success([
            'users' => [
                'total'         => User::count(),
                'programmers'   => User::where('role', 'programmer')->count(),
                'project_owners'=> User::where('role', 'project_owner')->count(),
                'new_today'     => User::whereDate('created_at', today())->count(),
                'new_this_month'=> User::whereMonth('created_at', now()->month)->count(),
            ],
            'contracts' => [
                'total'     => Contract::count(),
                'active'    => Contract::where('status', 'active')->count(),
                'completed' => Contract::where('status', 'completed')->count(),
            ],
            'finance' => [
                'total_volume'   => Transaction::where('status', 'completed')->sum('amount'),
                'total_commission'=> Transaction::where('status', 'completed')->sum('commission'),
                'this_month'     => Transaction::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)->sum('amount'),
            ],
            'reviews' => [
                'total'     => Review::where('is_published', true)->count(),
                'avg_rating'=> round(Review::where('is_published', true)->avg('rating'), 2),
            ],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $days = $request->days ?? 30;

        $registrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->success([
            'registrations_chart' => $registrations,
            'by_role' => [
                'programmer'    => User::where('role', 'programmer')->count(),
                'project_owner' => User::where('role', 'project_owner')->count(),
            ],
            'active_users' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    public function contracts(Request $request): JsonResponse
    {
        $days = $request->days ?? 30;

        $chart = Contract::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->success([
            'chart'          => $chart,
            'by_type'        => [
                'fixed_price'   => Contract::where('contract_type', 'fixed_price')->count(),
                'time_material' => Contract::where('contract_type', 'time_material')->count(),
            ],
            'by_status' => [
                'active'    => Contract::where('status', 'active')->count(),
                'completed' => Contract::where('status', 'completed')->count(),
                'cancelled' => Contract::where('status', 'cancelled')->count(),
            ],
            'avg_amount' => round(Contract::where('status', 'completed')->avg('total_amount'), 2),
        ]);
    }

    public function finance(Request $request): JsonResponse
    {
        $days = $request->days ?? 30;

        $chart = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as volume, SUM(commission) as commission')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->success([
            'chart'      => $chart,
            'by_type'    => Transaction::where('status', 'completed')
                ->selectRaw('type, SUM(amount) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
        ]);
    }
}
