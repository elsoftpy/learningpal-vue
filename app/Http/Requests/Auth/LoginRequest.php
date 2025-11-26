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
            'name' => ['required', 'string', 'exists:users,name'],
            'password' => ['required', 'string', Password::default()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('name is required.'),
            'name.name' => __('Please provide a valid name address.'),
            'name.exists' => __('No account found with this name.'),
            'password.required' => __('Password is required.'),
            'password.string' => __('Password must be a valid string.'),
        ];
    }
}
