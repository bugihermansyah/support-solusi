<?php

namespace App\Filament\Resources\DiskUsageResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDiskUsageRequest extends FormRequest
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
			'location_id' => 'required|string',
			'mount_point' => 'required|string',
			'size' => 'required|string',
			'used' => 'required|string',
			'available' => 'required|string',
			'usage_percent' => 'required|string'
		];
    }
}
