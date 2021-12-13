<?php

namespace Webi\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class WebiActivateRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return true; // Allow all
	}

	public function rules()
	{
		$email = 'email:rfc,dns';
		if(env('APP_DEBUG') == true) {
			$email = 'email';
		}

		return [
			'id' => 'required|max:128',
			'code' => 'required|string',
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first(), 422);
	}

	function prepareForValidation()
	{
		$this->merge([
			'id' => request()->route('id'),
			'code' => request()->route('code')
		]);
	}
}
