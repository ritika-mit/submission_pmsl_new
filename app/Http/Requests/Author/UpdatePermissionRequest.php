<?php

namespace App\Http\Requests\Author;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['array', 'min:0'],
            'roles.*' => ['required', Rule::exists(Role::class, 'id')],
        ];
    }

    public function prepareForValidation()
    {
        $this->decodeHashId('roles');
    }
}
