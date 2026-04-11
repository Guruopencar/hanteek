<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\UserResource;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('profile')
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->search, fn($q) => $q->where('email', 'like', '%'.$request->search.'%')
                ->orWhereHas('profile', fn($p) => $p->where('first_name', 'like', '%'.$request->search.'%')
                    ->orWhere('last_name', 'like', '%'.$request->search.'%')))
            ->when($request->status === 'banned', fn($q) => $q->where('is_active', false))
            ->orderByDesc('created_at');

        $users = $query->paginate(20);

        return $this->paginated($users, UserResource::class);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['profile', 'developerResume', 'recruiterProfile', 'wallet']);
        return $this->success(new UserResource($user));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'is_active'   => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
            'role'        => 'sometimes|in:programmer,project_owner,super_admin',
        ]);

        AdminLog::record('update_user', 'User', $user->id, null, $user->toArray(), $data);
        $user->update($data);

        return $this->success(new UserResource($user->fresh('profile')));
    }

    public function ban(Request $request, User $user): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $user->update(['is_active' => false]);
        AdminLog::record('ban_user', 'User', $user->id, $request->reason);
        return $this->success(null, 'Користувача заблоковано');
    }

    public function unban(User $user): JsonResponse
    {
        $user->update(['is_active' => true]);
        AdminLog::record('unban_user', 'User', $user->id);
        return $this->success(null, 'Користувача розблоковано');
    }

    public function verify(User $user): JsonResponse
    {
        $user->update(['is_verified' => true, 'email_verified_at' => now()]);
        AdminLog::record('verify_user', 'User', $user->id);
        return $this->success(null, 'Акаунт верифіковано');
    }

    public function destroy(User $user): JsonResponse
    {
        AdminLog::record('delete_user', 'User', $user->id, null, $user->toArray());
        $user->delete();
        return $this->success(null, 'Акаунт видалено');
    }

    public function resetRating(User $user): JsonResponse
    {
        $user->profile?->update(['average_rating' => 0, 'reviews_count' => 0]);
        AdminLog::record('reset_rating', 'User', $user->id);
        return $this->success(null, 'Рейтинг скинуто');
    }
}
