<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'price' => ['required', 'numeric', 'min:1000'],
            'property_type' => ['required', 'string', 'in:Flat,Apartment,Villa,Plot,Building,Commercial Space,Land'],
            'bhk' => ['nullable', 'integer', 'min:1', 'max:20'],
            'area_sqft' => ['required', 'integer', 'min:50'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:150'],
            'possession_status' => ['required', 'string', 'in:Ready to Move,Under Construction'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'kyc_document_type' => ['nullable', 'string', 'max:100'],
            'kyc_document_number' => ['nullable', 'string', 'max:100'],
            'kyc_document_url' => ['nullable', 'string', 'max:500'],
            'private_remarks' => ['nullable', 'string', 'max:3000'],
            'private_photos' => ['nullable', 'array'],
            'private_photos.*' => ['nullable', 'string'],
            'private_videos' => ['nullable', 'array'],
            'private_videos.*' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:DRAFT,PENDING_VERIFICATION,PUBLISHED,SOLD'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'string'],
            'property_photos' => ['nullable', 'array'],
            'property_photos.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer'],
            'kyc_document_file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg,webp', 'max:10240'],
            'private_photo_files' => ['nullable', 'array'],
            'private_photo_files.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
            'private_video_files' => ['nullable', 'array'],
            'private_video_files.*' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,m4v', 'max:51200'],
            'target_user_id' => ['nullable', 'exists:users,id'], // for staff/admin creating on behalf of seller
        ];
    }
}
