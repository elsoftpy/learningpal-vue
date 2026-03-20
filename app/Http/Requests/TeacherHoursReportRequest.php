<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TeacherHoursReportRequest extends FormRequest
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
            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],
            'from_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:to_date',
            ],
            'to_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:from_date',
                'after_or_equal:from_date',
            ],
            'month_start_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:month_end_date',
            ],
            'month_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:month_start_date',
                'after_or_equal:month_start_date',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:200',
            ],
            'sort_field' => [
                'nullable',
                'string',
                'in:course,date,hours',
            ],
            'sort_order' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
            'selected_row_ids' => [
                'nullable',
                'array',
            ],
            'selected_row_ids.*' => [
                'integer',
                'exists:class_records,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasRangeFilter = $this->filled('from_date') || $this->filled('to_date');
            $hasMonthFilter = $this->filled('month_start_date') || $this->filled('month_end_date');

            if ($hasRangeFilter && $hasMonthFilter) {
                $validator->errors()->add(
                    'filters',
                    __('Use either from/to dates or month start/end dates, not both.')
                );
            }

            if (! $hasRangeFilter && ! $hasMonthFilter) {
                $validator->errors()->add(
                    'filters',
                    __('A date range is required. Provide from/to or month start/end dates.')
                );
            }
        });
    }
}
