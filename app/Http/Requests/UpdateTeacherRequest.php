<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $teacher = $this->route('teacher');

        return [
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'am' => ['required', 'integer', Rule::unique('teachers', 'am')->ignore($teacher->id)],
            'afm' => ['required', 'integer', Rule::unique('teachers', 'afm')->ignore($teacher->id)],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
