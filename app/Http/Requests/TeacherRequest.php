<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Http\Requests\Traits\ProfileValidationTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
                'status' => [
                    'sometimes',
                    'required', 
                    'string', 
                    Rule::in(StatusEnum::values()),
                ],
            ]
        );
    }

    public function prepareForValidation()
    {
        if ($this->has('birth_date') && $this->birth_date) {
            try {
                $birthDate = match(app()->getLocale()) {
                    'es', 'pt' => Carbon::createFromFormat('d/m/Y', $this->input('birth_date'))->format('Y-m-d'),
                    'en' => Carbon::createFromFormat('m-d-Y', $this->input('birth_date'))->format('Y-m-d'),
                    default => $this->input('birth_date'),
                };
            } catch (Exception $e) {
                $birthDate = null;
            }
        }

        $this->merge([
            'profile' => $this->teacher?->profile,
            'birth_date' => $birthDate ?? null,
        ]);
    }
}
