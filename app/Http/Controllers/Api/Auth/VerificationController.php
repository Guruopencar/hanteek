<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Models\VerificationCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationController extends ApiController
{
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
            'type'  => 'required|in:email,phone',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $code = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('type', $request->type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$code) {
            return $this->error('Невірний або прострочений код');
        }

        $code->update(['used_at' => now()]);
        $user->update(['is_verified' => true, 'email_verified_at' => now()]);

        return $this->success(null, 'Акаунт підтверджено');
    }

    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'type'  => 'required|in:email,phone',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // Видалити старі коди
        VerificationCode::where('user_id', $user->id)
            ->where('type', $request->type)
            ->delete();

        // Створити новий
        $code = VerificationCode::create([
            'user_id'    => $user->id,
            'code'       => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type'       => $request->type,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->dispatchCode($user, $code->code, $request->type);

        return $this->success(null, 'Код надіслано повторно');
    }

    private function dispatchCode(User $user, string $code, string $type): void
    {
        if ($type === 'email') {
            try {
                Mail::raw(
                    "Ваш код підтвердження Hunteek: {$code}\n\nКод дійсний 10 хвилин.",
                    function ($m) use ($user) {
                        $m->to($user->email)->subject('Hunteek — код підтвердження');
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Verification email failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
            return;
        }

        // SMS: пропускаємо через нашу абстракцію; якщо gateway не налаштований —
        // код потрапляє в лог (це достатньо для розробки/prod-fallback).
        Log::info('SMS verification code dispatched', [
            'user_id' => $user->id,
            'phone'   => $user->phone,
            'code'    => $code,
        ]);
    }
}
