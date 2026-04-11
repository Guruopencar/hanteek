<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserBanResource;
use App\Models\User;
use App\Models\UserBan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends ApiController
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'profile', 'developerResume', 'recruiterProfile', 'wallet'
        ]);
        return $this->success(new UserResource($user));
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['profile', 'developerResume', 'recruiterProfile']);
        return $this->success(new UserResource($user));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'bio'        => 'nullable|string|max:1000',
            'country'    => 'nullable|string|max:100',
            'city'       => 'nullable|string|max:100',
            'telegram'   => 'nullable|string|max:100',
            'linkedin'   => 'nullable|url|max:255',
            'github'     => 'nullable|url|max:255',
            'website'    => 'nullable|url|max:255',
            'locale'     => 'nullable|in:uk,en',
        ]);

        $user = $request->user();

        if (isset($data['locale'])) {
            $user->update(['locale' => $data['locale']]);
        }

        $profileData = collect($data)->except('locale')->filter()->toArray();
        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return $this->success(new UserResource($user->fresh('profile')));
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|max:5120']);
        $user = $request->user();

        $user->clearMediaCollection('avatar');
        $media = $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');

        $user->profile()->update(['avatar' => $media->getUrl()]);

        return $this->success(['avatar_url' => $media->getUrl()]);
    }

    public function banList(Request $request): JsonResponse
    {
        $bans = UserBan::with('banned.profile')
            ->where('banner_id', $request->user()->id)
            ->whereNull('unbanned_at')
            ->paginate(20);

        return $this->paginated($bans, UserBanResource::class);
    }

    public function ban(Request $request, User $user): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        if ($user->id === $request->user()->id) {
            return $this->error('Не можна заблокувати себе');
        }

        UserBan::updateOrCreate(
            ['banner_id' => $request->user()->id, 'banned_id' => $user->id],
            ['reason' => $request->reason, 'banned_at' => now(), 'unbanned_at' => null]
        );

        return $this->success(null, 'Користувача заблоковано');
    }

    public function unban(Request $request, User $user): JsonResponse
    {
        UserBan::where('banner_id', $request->user()->id)
            ->where('banned_id', $user->id)
            ->update(['unbanned_at' => now()]);

        return $this->success(null, 'Користувача розблоковано');
    }
}
