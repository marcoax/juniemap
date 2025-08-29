<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\LocationStato;

final readonly class LocationSearchDto
{
    public function __construct(
        public ?string $search = null,
        public ?LocationStato $stato = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $search = isset($data['search']) && is_string($data['search'])
            ? trim($data['search'])
            : null;

        $stato = null;
        if (isset($data['stato']) && is_string($data['stato'])) {
            $stato = LocationStato::tryFromValue(trim($data['stato']));
        }

        return new self(
            search: $search !== '' ? $search : null,
            stato: $stato,
        );
    }

    /**
     * Convert to cache key components.
     */
    public function toCacheKey(): string
    {
        return md5(($this->search ?? '').'|'.($this->stato?->value ?? ''));
    }

    /**
     * Check if search has any filters.
     */
    public function hasFilters(): bool
    {
        return $this->search !== null || $this->stato !== null;
    }

    /**
     * Get search term or empty string.
     */
    public function getSearchTerm(): string
    {
        return $this->search ?? '';
    }

    /**
     * Get stato value or null.
     */
    public function getStatoValue(): ?string
    {
        return $this->stato?->value;
    }
}
