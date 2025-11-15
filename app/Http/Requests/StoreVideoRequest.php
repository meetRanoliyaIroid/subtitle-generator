<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
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
        return [
            'video' => ['required', 'file', 'mimes:mp4,avi,mov,wmv,flv,webm', 'max:102400'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'video.required' => 'Please select a video file to upload.',
            'video.file' => 'The uploaded file must be a valid file.',
            'video.mimes' => 'The video must be a file of type: mp4, avi, mov, wmv, flv, webm.',
            'video.max' => 'The video may not be greater than 100MB.',
        ];
    }
}
