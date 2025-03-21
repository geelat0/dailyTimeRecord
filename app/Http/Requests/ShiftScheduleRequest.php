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
            'start_date' => 'required|date|distinct',
            'end_date' => 'required|date|after_or_equal:start_date|distinct',
            'shift_id' => 'required',
            'remarks' => 'required|string',
            'am_time_in' => 'required_if:shift_id,0|nullable|date_format:H:i',
            'am_time_out' => 'required_if:shift_id,0|nullable|date_format:H:i',
            'pm_time_in' => 'required_if:shift_id,0|nullable|date_format:H:i',
            'pm_time_out' => 'required_if:shift_id,0|nullable|date_format:H:i',
        ];
    }

    /**
     * Get the custom validation messages for the defined rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.distinct' => 'The start date must be unique.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'end_date.distinct' => 'The end date must be unique.',
            'shift_id.required' => 'The shift schedule is required.',
            'remarks.required' => 'Remarks are required.',
            'remarks.string' => 'Remarks must be a string.',
            'am_time_in.required_if' => 'AM Time In is required.',
            'am_time_in.date_format' => 'AM Time In must be in the format HH:MM.',
            'am_time_out.required_if' => 'AM Time Out is required.',
            'am_time_out.date_format' => 'AM Time Out must be in the format HH:MM.',
            'pm_time_in.required_if' => 'PM Time In is required.',
            'pm_time_in.date_format' => 'PM Time In must be in the format HH:MM.',
            'pm_time_out.required_if' => 'PM Time Out is required.',
            'pm_time_out.date_format' => 'PM Time Out must be in the format HH:MM.',
        ];
    }
}
