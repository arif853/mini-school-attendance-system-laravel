<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
        $studentId = $this->route('student')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'student_id' => ['sometimes', 'string', 'max:50', 'unique:students,student_id,' . $studentId],
            'class' => ['sometimes', 'string', 'max:50'],
            'section' => ['sometimes', 'string', 'max:50'],
            'photo_path' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
