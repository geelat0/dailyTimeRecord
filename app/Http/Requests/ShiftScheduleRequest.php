<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return true;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shift_id' => 'required|exists:shift,id',
            'remarks' => 'required|string',
            'am_time_in' => 'required_if:shift_id,4|nullable|date_format:H:i',
            'am_time_out' => 'required_if:shift_id,4|nullable|date_format:H:i',
            'pm_time_in' => 'required_if:shift_id,4|nullable|date_format:H:i',
            'pm_time_out' => 'required_if:shift_id,4|nullable|date_format:H:i',
        ];
    }
}
