<?php

namespace App\Http\Requests;

use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CalendarSessionRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'view' => ['required', 'string', 'in:month,week,day'],
        ];
    }

    public function prepareForValidation()
    {
        if ($this->start_date) {
            $startDate = Carbon::createFromFormat('Y-m-d', $this->start_date);
        }

        if ($this->end_date) {
            $endDate = Carbon::createFromFormat('Y-m-d', $this->end_date);
        }

        $this->merge([
            'start_date' => $startDate ?? null,
            'end_date' => $endDate ?? null,
        ]);
    }
}
