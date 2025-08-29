<?php

declare(strict_types=1);

namespace App\Http\Requests\Locations;

use App\DataTransferObjects\LocationSearchDto;
use App\Enums\LocationStato;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'search' => $search ?: null,
            'stato' => $stato ?: null,
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
            'search' => ['nullable', 'string', 'max:255', 'min:1'],
            'stato' => ['nullable', Rule::enum(LocationStato::class)],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.min' => 'Il termine di ricerca deve contenere almeno 1 carattere.',
            'search.max' => 'Il termine di ricerca non può superare i 255 caratteri.',
            'stato.enum' => 'Lo stato selezionato non è valido.',
        ];
    }

    /**
     * Convert validated data to DTO.
     */
    public function toDto(): LocationSearchDto
    {
        return LocationSearchDto::fromArray($this->validated());
    }
}
