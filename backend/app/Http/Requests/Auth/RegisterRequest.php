<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Checked at DB level via unique rule.
                // We do NOT use UserRepository here — FormRequest should stay
                // as a simple validation layer without injecting services.
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                // confirmed: expects a matching 'password_confirmation' field in the request.
                'confirmed',
                // Laravel's built-in Password rule — readable and future-proof.
                // min(8): minimum length, letters(): at least one letter, numbers(): at least one number
                Password::min(8)->letters()->numbers(),
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'Name is required.',
            'name.min'                       => 'Name must be at least 2 characters.',
            'name.max'                       => 'Name cannot exceed 100 characters.',
            'email.required'                 => 'Email is required.',
            'email.email'                    => 'Please provide a valid email address.',
            'email.unique'                   => 'This email address is already registered.',
            'password.required'              => 'Password is required.',
            'password.confirmed'             => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Please confirm your password.',
        ];
    }
}
