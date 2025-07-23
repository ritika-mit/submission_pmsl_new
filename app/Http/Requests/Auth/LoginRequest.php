<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'g-recaptcha-response' => ['required', 'recaptcha'],
        ];
    }
}
