<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Models\VerificationCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // TODO: відправити email/SMS
        // Mail::to($user->email)->send(new VerificationMail($code->code));

        return $this->success(null, 'Код надіслано повторно');
    }
}
