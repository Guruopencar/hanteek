<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use App\Models\WalletAccount;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): User
    {
        // Знайти реферера якщо є код
        $referredBy = null;
        if (!empty($data['referral_code'])) {
            $referredBy = User::where('referral_code', $data['referral_code'])->value('id');
        }

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => $data['password'],
            'phone'         => $data['phone'] ?? null,
            'role'          => $data['role'],
            'locale'        => $data['locale'] ?? 'uk',
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by'   => $referredBy,
            'is_active'     => true,
        ]);

        // Призначити роль Spatie
        $user->assignRole($data['role']);

        // Створити базовий профіль
        UserProfile::create([
            'user_id'    => $user->id,
            'first_name' => $data['name'],
            'last_name'  => '',
        ]);

        // Створити гаманець
        $wallet = Wallet::create([
            'user_id'  => $user->id,
            'balance'  => 0.00,
            'frozen'   => 0.00,
            'currency' => 'USD',
        ]);

        // Основний рахунок
        WalletAccount::create([
            'wallet_id'  => $wallet->id,
            'name'       => 'Основний рахунок',
            'type'       => 'main',
            'balance'    => 0.00,
            'currency'   => 'USD',
            'is_default' => true,
        ]);

        // Відправити код підтвердження
        $this->sendVerificationCode($user, 'email');

        // Записати реферала
        if ($referredBy) {
            \App\Models\Referral::create([
                'referrer_id' => $referredBy,
                'referred_id' => $user->id,
                'status'      => 'pending',
            ]);
        }

        return $user->load('profile', 'wallet');
    }

    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->where('is_active', true)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $user->update(['last_login_at' => now()]);

        return [
            'user'  => $user->load('profile', 'wallet'),
            'token' => $user->createToken('mobile')->plainTextToken,
        ];
    }

    public function sendVerificationCode(User $user, string $type): VerificationCode
    {
        VerificationCode::where('user_id', $user->id)->where('type', $type)->delete();

        return VerificationCode::create([
            'user_id'    => $user->id,
            'code'       => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type'       => $type,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function sendResetCode(string $email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->sendVerificationCode($user, 'password_reset');
            // TODO: Mail::to($user->email)->send(new PasswordResetMail($code));
        }
    }

    public function resetPassword(string $email, string $code, string $password): bool
    {
        $user = User::where('email', $email)->first();
        if (!$user) return false;

        $verCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $code)
            ->where('type', 'password_reset')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verCode) return false;

        $verCode->update(['used_at' => now()]);
        $user->update(['password' => $password]);

        return true;
    }
}
