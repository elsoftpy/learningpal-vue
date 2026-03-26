<?php

namespace App\Http\Requests\Traits;

use App\Enums\GenderEnum;
use App\Enums\ProfileTypeEnum;
use App\Models\Profile;
use Illuminate\Validation\Rule;

trait ProfileValidationTrait
{
    public function profileRules(): array
    {
        return [
            'type' => ['required', Rule::in(ProfileTypeEnum::values())],
            'first_name' => [
                'nullable', 
                'string', 
                'max:255', 
                Rule::requiredIf(fn () => $this->input('type') === ProfileTypeEnum::PERSON->value),
            ],
            'last_name' => [
                'nullable', 
                'string', 
                'max:255',
                Rule::requiredIf(fn () => $this->input('type') === ProfileTypeEnum::PERSON->value),
            ],
            'company_name' => [
                'nullable', 
                'string', 
                'max:255',
                Rule::requiredIf(fn () => $this->input('type') === ProfileTypeEnum::COMPANY->value),
            ],
            'personal_id' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::requiredIf(fn () => $this->input('type') === ProfileTypeEnum::PERSON->value),
            ],
            'ruc' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::requiredIf(fn () => $this->input('type') === ProfileTypeEnum::COMPANY->value),
            ],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('profiles', 'email')->ignore($this->profile?->id),
            ],
            'phone' => [
                'nullable', 
                'string', 
                'max:20',
            ],
            'address' => [
                'nullable', 
                'string', 
                'max:500',
            ],
            'gender' => [
                'nullable', 
                'string', 
                Rule::in(GenderEnum::values()),
            ],
            'avatar' => [
                'nullable',
                'file',
                'image',
                'max:2048',
                'mimes:jpeg,png,jpg,gif,webp',
            ],
            'payment_receipt' => [
                'nullable',
                'file',
                'max:4096',
                'mimes:jpeg,png,jpg,gif,webp,pdf',
            ],
        ];
    }

    public function profileMessages(): array
    {
        return [
            'type.required' => __('Profile type is required.'),
            'type.in' => __('Selected profile type is invalid.'),
            'first_name.required' => __('First name is required.'),
            'first_name.string' => __('First name must be a valid string.'),
            'first_name.max' => __('First name may not be greater than :max characters.'),
            'last_name.required' => __('Last name is required.'),
            'last_name.string' => __('Last name must be a valid string.'),
            'last_name.max' => __('Last name may not be greater than :max characters.'),
            'company_name.required' => __('Company name is required.'),
            'company_name.string' => __('Company name must be a valid string.'),
            'company_name.max' => __('Company name may not be greater than :max characters.'),
            'personal_id.required' => __('Identity document is required.'),
            'personal_id.string' => __('Identity document must be a valid string.'),
            'personal_id.max' => __('Identity document may not be greater than :max characters.'),
            'ruc.required' => __('RUC is required.'),
            'ruc.string' => __('RUC must be a valid string.'),
            'ruc.max' => __('RUC may not be greater than :max characters.'),
            'email.required' => __('Email is required.'),
            'email.string' => __('Email must be a valid string.'),
            'email.email' => __('Please provide a valid email address.'),
            'email.max' => __('Email may not be greater than :max characters.'),
            'email.unique' => __('An account with this email already exists.'),
            'phone.required' => __('Phone number is required.'),
            'phone.string' => __('Phone number must be a valid string.'),
            'phone.max' => __('Phone number may not be greater than :max characters.'),
            'address.string' => __('Address must be a valid string.'),
            'address.max' => __('Address may not be greater than :max characters.'),
            'gender.in' => __('Selected gender is invalid.'),
            'gender.string' => __('Gender must be a valid string.'),
            'avatar.file' => __('Avatar must be a file.'),
            'avatar.image' => __('Avatar must be an image.'),
            'avatar.max' => __('Avatar may not be greater than :max kilobytes.'),
            'avatar.mimes' => __('Avatar must be a file of type: :values.'),
            'payment_receipt.file' => __('Payment receipt must be a file.'),
            'payment_receipt.max' => __('Payment receipt may not be greater than :max kilobytes.'),
            'payment_receipt.mimes' => __('Payment receipt must be a file of type: :values.'),
        ];
    }

    public function profileByIdNumber(string $idNumber): Profile|null
    {
        return Profile::where('personal_id', $idNumber)
            ->orWhere('ruc', $idNumber)
            ->first();
    }
}
