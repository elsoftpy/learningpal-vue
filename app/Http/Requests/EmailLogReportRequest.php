<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EmailLogReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_field' => ['nullable', 'string', 'in:id,email_destino,course_name,teacher_name,student_name,action_type,estado,created_at'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'filters' => ['nullable', 'string'],
        ];
    }
}
