<?php

namespace App\Http\Controllers\Api\Rating;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\UserResource;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = UserProfile::with('user')
            ->whereHas('user', function ($q) use ($request) {
                $q->where('is_active', true);
                if ($request->role) {
                    $q->where('role', $request->role);
                }
            })
            ->where('reviews_count', '>', 0);

        // Сортування
        $sortBy = $request->sort_by ?? 'average_rating';
        $query->orderByDesc(match($sortBy) {
            'reviews_count'   => 'reviews_count',
            'contracts_count' => 'contracts_count',
            default           => 'average_rating',
        });

        $profiles = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $profiles->map(fn($p) => [
                'user'            => new UserResource($p->user),
                'average_rating'  => $p->average_rating,
                'reviews_count'   => $p->reviews_count,
                'contracts_count' => $p->contracts_count,
            ]),
            'meta'    => [
                'current_page' => $profiles->currentPage(),
                'last_page'    => $profiles->lastPage(),
                'total'        => $profiles->total(),
            ],
        ]);
    }
}
