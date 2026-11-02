<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone' => ['required', 'string', 'min:10', 'max:20', Rule::unique('users')->ignore($userId)],
            'avatar' => ['nullable', 'url'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'kyc_document_type' => ['nullable', 'string', 'max:50'],
            'kyc_document_number' => ['nullable', 'string', 'max:100'],
            'kyc_document_url' => ['nullable', 'string', 'max:2000'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ];
    }
}
