<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_type' => ['required', 'string', 'in:Flat,Apartment,Villa,Plot,Building,Commercial Space,Land'],
            'bhk' => ['nullable', 'integer', 'min:1', 'max:20'],
            'budget_min' => ['required', 'numeric', 'min:0'],
            'budget_max' => ['required', 'numeric', 'gte:budget_min'],
            'possession_status' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'locality' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:ACTIVE,MATCHED,CLOSED,EXPIRED'],
            'target_user_id' => ['nullable', 'exists:users,id'], // for staff creating on behalf of buyer
        ];
    }
}
