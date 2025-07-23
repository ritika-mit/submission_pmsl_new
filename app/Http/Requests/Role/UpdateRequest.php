<?php

namespace App\Http\Requests\Role;

use App\Enums\Section;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)->ignore($this->route('role'))],
            'section' => ['required', Rule::enum(Section::class)],
            'default' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', Rule::exists(Permission::class, 'id')],
        ];
    }

    public function prepareForValidation()
    {
        $this->decodeHashId('permissions');
    }
}
