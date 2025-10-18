<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $rules = [
            'full_name' => 'required|string|max:255',
            'device_id' => 'required|string|max:255',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
            'fcm_token' => 'nullable|string',
            'is_guest' => 'required|boolean',
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:3',
        ];

        // If not guest, email and password are required
        if (!$this->boolean('is_guest')) {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
    }
}
