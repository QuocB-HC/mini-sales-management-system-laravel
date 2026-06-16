<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Avatar is required.',
            'avatar.image' => 'Avatar must be a image.',
            'avatar.max' => 'Avatar must not exceed 2048 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorMessage = $validator->errors()->first();

        throw new ValidationException($validator,
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $errorMessage)
        );
    }
}
