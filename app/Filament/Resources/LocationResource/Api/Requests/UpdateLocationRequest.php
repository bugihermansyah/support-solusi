<?php

namespace App\Filament\Resources\LocationResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
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
			'company_id' => 'required|string',
			'name' => 'required|string',
			'team_id' => 'required|string',
			'bd_id' => 'required|string',
			'area_status' => 'required|string',
			'user_id' => 'required|string',
			'image' => 'required|string',
			'type_contract' => 'required|string',
			'status' => 'required|string',
			'first_project' => 'required|date',
			'address' => 'required|string',
			'description' => 'required|string',
			'deleted_at' => 'required'
		];
    }
}
