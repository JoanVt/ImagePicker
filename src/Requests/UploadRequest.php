<?php

namespace Joanvt\ImagePicker\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'action' => 'required|string|in:preview,crop,upload,delete',
            'file' => 'required_if:action,upload|image|dimensions:min_width=200,min_height=200',
            'rotate' => 'in:0,90,180,270',
            'image' => 'required_if:action,crop|string',
            'path' => 'required_if:action,crop|required_if:action,preview|string',
            'coords' => 'array',
            'coords.*' => 'numeric',
        ];
    }
}
