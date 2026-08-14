<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'am' => ['required', 'string', Rule::unique('teachers', 'am')->ignore($teacher->id)],
            'afm' => ['required', 'string', Rule::unique('teachers', 'afm')->ignore($teacher->id)],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
