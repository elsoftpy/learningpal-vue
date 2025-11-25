<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', Password::default()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required.'),
            'email.email' => __('Please provide a valid email address.'),
            'email.exists' => __('No account found with this email.'),
            'password.required' => __('Password is required.'),
            'password.string' => __('Password must be a valid string.'),
        ];
    }
}
