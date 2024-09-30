<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
			// -- User
			'username' => 'required|unique:users,username|min:3|max:30',
			'email' => 'required|unique:users,email',
			'password' => 'required|min:6|max:20',

			// -- Person
			'name' => 'required|min:2|max:60',
			'firstname' => 'required|min:2|max:60',
			'lastname' => 'nullable|min:2|max:60',
			'phone' => ['nullable', /*'json',*/],
			'sexe' => [
				'required',
				Rule::in(array (0 => 'm', 1 => 'f')),
			],
		];
	}

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array
	 */
	public function messages() : array
	{
		return [
			// -- User
			'username.required' => __('validation.required'),
			'username.unique' => __('validation.unique'),
			'username.min' => __('validation.min.string'),
			'username.max' => __('validation.max.string'),

			'email.required' => __('validation.required'),
			'email.unique' => __('validation.unique'),

			'password.required' => __('validation.required'),
			'password.min' => __('validation.min.string'),
			'password.max' => __('validation.max.string'),

			// -- Person
			'name.required' => __('validation.required'),
			'name.min' => __('validation.min.string'),
			'name.max' => __('validation.max.string'),

			'firstname.required' => __('validation.required'),
			'firstname.min' => __('validation.min.string'),
			'firstname.max' => __('validation.max.string'),

			'lastname.min' => __('validation.min.string'),
			'lastname.max' => __('validation.max.string'),

			'sexe.required' => __('validation.required'),
			'sexe.in' => __('validation.in'),
		];
	}
}
