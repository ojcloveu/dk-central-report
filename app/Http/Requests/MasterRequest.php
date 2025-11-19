<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        // Rule for 'name' to be unique, ignoring the current ID when updating
        $id = $this->route('master'); 

        return [
            'name' => 'required|min:2|max:100|unique:masters,name,' . $id,
            'description' => 'nullable|max:500',
            'is_active' => 'boolean',
        ];
    }
}
