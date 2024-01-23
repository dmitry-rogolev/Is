<?php 

namespace dmitryrogolev\Is\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array 
	{
		return [
			"name" => ["required", "string", "max:255", "unique:".config("is.tables.roles")], 
			"slug" => ["required", "string", "max:255", "unique:".config("is.tables.roles")], 
			"description" => ["string"], 
			"level" => ["integer"], 
		];
	}
}
