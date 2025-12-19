<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Http\Requests\Traits\ProfileValidationTrait;
use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserProfileRequest extends FormRequest
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
            $this->profileRules(), 
            [
                'name' => [
                    'required', 
                    'string', 
                    'max:255', 
                    Rule::unique('users', 'name')->ignore($this->user?->id),
                ],
                'password' => [
                    'nullable', 
                    'string', 
                    Password::default(), 
                ],
                'roles' => [
                    'sometimes',
                    'required', 
                    'array',
                ],
                'roles.*' => [
                    'string', 
                    'exists:roles,name',
                ],
                'status' => [
                    'sometimes',
                    'required', 
                    'string', 
                    Rule::in(StatusEnum::values()),
                ],
                'birth_date' => [
                    'nullable',
                    'date',
                    'before:today',
                ],
                'avatar' => [
                    'nullable',
                    'file',
                    'image',
                    'max:2048', // Max size in KB
                    'mimes:jpeg,png,jpg,gif,webp',
                ],
                'payment_receipt' => [
                    'nullable',
                    'file',
                    'max:4096', // Max size in KB
                    'mimes:jpeg,png,jpg,gif,webp,pdf',
                ],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->profileMessages(), [
                'name.required' => __('Username is required.'),
                'name.string' => __('Username must be a valid string.'),
                'name.max' => __('Username may not be greater than :max characters.'),
                'name.unique' => __('This username is already taken.'),
                'password.string' => __('Password must be a valid string.'),
                'password.password' => __('Password does not meet the required criteria.'),
            ]
        );
    }

    public function prepareForValidation()
    {
        if ($this->has('birth_date') && $this->birth_date) {
            $birthDate = DateTimeService::dateFromLocalizedString($this->birth_date);
        }

        $profile = $this->profileByIdNumber($this->personal_id ?? $this->ruc);

        $this->merge([
            'profile' => $this->user?->profile ?? $profile,
            'birth_date' => $birthDate ?? null,
        ]);
    }
}
