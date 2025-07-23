<?php

namespace App\Http\Requests;

use App\Models\Author;
use App\Models\Country;
use App\Models\ResearchArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', Rule::unique(Author::class)->ignore($this->user())],
            'highest_qualification' => ['required', 'string', 'max:255'],
            'scopus_id' => ['nullable', 'string', 'max:255'],
            'orcid_id' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'organization_institution' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'country' => ['required', Rule::exists(Country::class, 'id')],
            'research_areas' => ['required', 'array', 'min:3'],
            'research_areas.*' => ['required', Rule::exists(ResearchArea::class, 'id')],
            'subscribe_to_notifications' => ['nullable', 'boolean'],
            'accept_review_request' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation()
    {
        $this->decodeHashId('country', 'research_areas');
    }

    public function safe(array $keys = null)
    {
        $inputs = parent::safe($keys)
            ->merge(['country_id' => $this->input('country')]);

        $inputs->offsetUnset('country');

        return $inputs;
    }
}
