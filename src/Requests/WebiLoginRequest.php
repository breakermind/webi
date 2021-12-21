<?php

namespace Webi\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class WebiLoginRequest extends FormRequest
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
			'email' => ['required', $email, 'max:191'],
			'password' => 'required|min:11',
			'remember_me' => 'sometimes|boolean'
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first(), 422);
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only(['email', 'password', 'remember_me'])->toArray()
		);
	}
}