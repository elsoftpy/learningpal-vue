<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class CourseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'language_id' => ['required', 'exists:languages,id'],
            'language_level_id' => [
                'required',
                Rule::exists('language_levels', 'id')->where(function (Builder $query) {
                    $query->where('language_id', $this->input('language_id'));
                }),
            ],
            'chat_room_link' => ['nullable', 'url'],
            'status' => ['required', Rule::in(StatusEnum::values())],
        ];
    }
}
