<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required.'),
            'email.string' => __('Email must be a valid string.'),
            'email.email' => __('Please provide a valid email address.'),
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
