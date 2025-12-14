<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\Media;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:4096',
            ],
            'species_id' => 'required|exists:species,id',
            'description' => 'nullable|string|max:80',
            'tags' => 'nullable|array',
            'source' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'A file is required.',
            'file.file' => 'A file is required.',
            'file.max' => 'The file may not be greater than 4MB.',
        ];
    }
}
