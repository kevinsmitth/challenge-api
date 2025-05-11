<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users,email,'.$this->route('user')],
            'password' => ['confirmed', 'min:8', 'max:255'],
            'cpf' => ['string', 'max:255', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/'],
            'phone' => ['string', 'max:255', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
        ];
    }
}
