<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSelectionListRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:selection_lists'],
            'id' => ['array'],
            'id.*' => ['string', 'max:255'],
            'value' => ['array'],
            'value.*' => ['string', 'max:255']
        ];
    }
}
