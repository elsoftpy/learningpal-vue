<?php

namespace App\Http\Requests\Auth;

use App\Enums\GenderEnum;
use App\Enums\ProfileTypeEnum;
use App\Http\Requests\Traits\ProfileValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use ProfileValidationTrait;
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
        return array_merge(
            $this->profileRules(), [
                'password' => ['required', 'string', Password::default(), 'confirmed'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->profileMessages(), [
                'password.required' => __('Password is required.'),
                'password.string' => __('Password must be a valid string.'),
                'password.confirmed' => __('Password confirmation does not match.'),
            ]
        );
    }
}
