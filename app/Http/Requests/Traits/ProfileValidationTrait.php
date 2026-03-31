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
            'profile_id' => [
                'nullable',
                'integer',
                'exists:profiles,id',
            ],
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
            'email_alt' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('profiles', 'email_alt')->ignore($this->profile?->id),
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
            'email_alt.string' => __('Alternative email must be a valid string.'),
            'email_alt.email' => __('Please provide a valid alternative email address.'),
            'email_alt.max' => __('Alternative email may not be greater than :max characters.'),
            'email_alt.unique' => __('This alternative email is already in use.'),
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

    public function profileByReference(?int $profileId, ?string $idNumber): Profile|null
    {
        if ($profileId) {
            return Profile::find($profileId);
        }

        if (!$idNumber) {
            return null;
        }

        return $this->profileByIdNumber($idNumber);
    }

    protected function normalizeOptionalProfileFields(): void
    {
        $normalized = [];

        foreach (['email_alt', 'phone', 'address'] as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $value = $this->input($field);

            if (! is_string($value)) {
                $normalized[$field] = $value;
                continue;
            }

            $cleanValue = trim($value);

            $normalized[$field] = match (strtolower($cleanValue)) {
                '', 'undefined', 'null' => null,
                default => $cleanValue,
            };
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
