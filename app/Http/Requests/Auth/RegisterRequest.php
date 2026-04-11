<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:programmer,project_owner',
            'locale'   => 'nullable|string|in:uk,en',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ];
    }
}
