<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'recorded_by' => ['nullable', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:50'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'exists:students,id'],
            'entries.*.status' => ['required', 'in:present,absent,late'],
            'entries.*.note' => ['nullable', 'string'],
        ];
    }
}
