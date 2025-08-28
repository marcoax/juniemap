<?php

namespace App\Http\Requests\Locations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LocationSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $search = $this->input('search');
        if (is_string($search)) {
            $search = trim(strip_tags($search));
        } else {
            $search = null;
        }

        $stato = $this->input('stato');
        if (is_string($stato)) {
            $stato = trim($stato);
        } else {
            $stato = null;
        }

        $this->merge([
            'search' => $search,
            'stato' => $stato,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'stato' => ['nullable', 'in:attivo,disattivo,in_allarme'],
        ];
    }
}
