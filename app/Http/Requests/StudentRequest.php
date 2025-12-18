<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Http\Requests\Traits\ProfileValidationTrait;
use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
                    'string', 
                    Rule::in(StatusEnum::values()),
                ],
            ]
        );
    }

    public function prepareForValidation()
    {
        if ($this->has('birth_date') && $this->birth_date) {        
            $birthDate = DateTimeService::dateFromLocalizedString($this->birth_date);
        }

        $this->merge([
            'profile' => $this->student?->profile,
            'birth_date' => $birthDate ?? null,
        ]);
    }
}
