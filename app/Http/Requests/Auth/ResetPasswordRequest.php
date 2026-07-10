<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->applyRequestLocale($this->input('locale'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => __('Reset token is required.'),
            'token.string' => __('Reset token must be a valid string.'),
            'email.required' => __('Email is required.'),
            'email.string' => __('Email must be a valid string.'),
            'email.email' => __('Please provide a valid email address.'),
            'password.required' => __('Password is required.'),
            'password.string' => __('Password must be a valid string.'),
            'password.min' => __('Password must be at least :min characters long.'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ];
    }

    protected function applyRequestLocale(?string $locale): void
    {
        $allowedLocales = config('app.available_locales', ['en', 'es', 'pt']);

        if (is_string($locale) && in_array($locale, $allowedLocales, true)) {
            App::setLocale($locale);
        }
    }
}
