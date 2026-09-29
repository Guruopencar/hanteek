<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use App\Models\PushToken;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        $token = $user->createToken('mobile')->plainTextToken;

        return $this->created([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Реєстрація успішна');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password
        );

        if (!$result) {
            return $this->error('Невірний email або пароль', 401);
        }

        return $this->success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Вийшли успішно');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'developerResume', 'recruiterProfile', 'wallet']);
        return $this->success(new UserResource($user));
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('mobile')->plainTextToken;
        return $this->success(['token' => $token]);
    }

    public function switchProfile(Request $request): JsonResponse
    {
        $request->validate([
            'profile' => 'required|in:developer,recruiter,programmer'
        ]);

        $user = $request->user()->load(['developerResume', 'recruiterProfile']);

        // Маппінг: programmer -> developer (зворотна сумісність)
        $target = $request->profile === 'programmer' ? 'developer' : $request->profile;

        if ($target === 'developer' && !$user->developerResume) {
            return $this->error('PROFILE_NOT_CREATED', 422);
        }

        if ($target === 'recruiter' && !$user->recruiterProfile) {
            return $this->error('PROFILE_NOT_CREATED', 422);
        }

        $user->update(['active_profile' => $target]);

        return $this->success([
            'active_profile' => $target,
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $this->authService->sendResetCode($request->email);
        return $this->success(null, 'Код надіслано на email');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $result = $this->authService->resetPassword(
            $request->email,
            $request->code,
            $request->password
        );

        if (!$result) {
            return $this->error('Невірний або прострочений код');
        }

        return $this->success(null, 'Пароль змінено');
    }

    public function savePushToken(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'platform' => 'required|in:ios,android,web',
        ]);

        PushToken::updateOrCreate(
            ['user_id' => $request->user()->id, 'token' => $request->token],
            ['platform' => $request->platform, 'is_active' => true, 'last_used_at' => now()]
        );

        return $this->success(null, 'Push токен збережено');
    }
}
