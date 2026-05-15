<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageLevelRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language_id' => [
                'required',
                'integer',
                'exists:languages,id',
            ],
            'status' => [
                'sometimes',
                'string',
                'max:50',
                Rule::in(StatusEnum::values()),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'level' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}
