<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
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
        return [
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'am' => ['required', 'integer', 'unique:teachers,am'],
            'afm' => ['required', 'integer', 'unique:teachers,afm'],
        ];
    }
}
