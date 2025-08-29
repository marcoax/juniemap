<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects;

use App\DataTransferObjects\LocationSearchDto;
use App\Enums\LocationStato;
use PHPUnit\Framework\TestCase;

final class LocationSearchDtoTest extends TestCase
{
    public function test_constructor_with_all_parameters(): void
    {
        $dto = new LocationSearchDto('test search', LocationStato::Attivo);

        $this->assertSame('test search', $dto->search);
        $this->assertSame(LocationStato::Attivo, $dto->stato);
    }

    public function test_constructor_with_null_parameters(): void
    {
        $dto = new LocationSearchDto;

        $this->assertNull($dto->search);
        $this->assertNull($dto->stato);
    }

    public function test_from_array_with_valid_data(): void
    {
        $data = [
            'search' => 'test search',
            'stato' => 'attivo',
        ];

        $dto = LocationSearchDto::fromArray($data);

        $this->assertSame('test search', $dto->search);
        $this->assertSame(LocationStato::Attivo, $dto->stato);
    }

    public function test_from_array_with_empty_search(): void
    {
        $data = [
            'search' => '',
            'stato' => 'attivo',
        ];

        $dto = LocationSearchDto::fromArray($data);

        $this->assertNull($dto->search);
        $this->assertSame(LocationStato::Attivo, $dto->stato);
    }

    public function test_from_array_with_whitespace_search(): void
    {
        $data = [
            'search' => '   ',
            'stato' => 'attivo',
        ];

        $dto = LocationSearchDto::fromArray($data);

        $this->assertNull($dto->search);
        $this->assertSame(LocationStato::Attivo, $dto->stato);
    }

    public function test_from_array_with_invalid_stato(): void
    {
        $data = [
            'search' => 'test',
            'stato' => 'invalid_stato',
        ];

        $dto = LocationSearchDto::fromArray($data);

        $this->assertSame('test', $dto->search);
        $this->assertNull($dto->stato);
    }

    public function test_from_array_with_missing_fields(): void
    {
        $data = [];

        $dto = LocationSearchDto::fromArray($data);

        $this->assertNull($dto->search);
        $this->assertNull($dto->stato);
    }

    public function test_to_cache_key(): void
    {
        $dto = new LocationSearchDto('test', LocationStato::Attivo);
        $cacheKey = $dto->toCacheKey();

        $this->assertIsString($cacheKey);
        $this->assertSame(32, strlen($cacheKey)); // MD5 hash length

        // Same data should produce same cache key
        $dto2 = new LocationSearchDto('test', LocationStato::Attivo);
        $this->assertSame($cacheKey, $dto2->toCacheKey());
    }

    public function test_to_cache_key_with_null_values(): void
    {
        $dto = new LocationSearchDto;
        $cacheKey = $dto->toCacheKey();

        $this->assertIsString($cacheKey);
        $this->assertSame(32, strlen($cacheKey));
    }

    public function test_has_filters(): void
    {
        $dtoWithSearch = new LocationSearchDto('test', null);
        $this->assertTrue($dtoWithSearch->hasFilters());

        $dtoWithStato = new LocationSearchDto(null, LocationStato::Attivo);
        $this->assertTrue($dtoWithStato->hasFilters());

        $dtoWithBoth = new LocationSearchDto('test', LocationStato::Attivo);
        $this->assertTrue($dtoWithBoth->hasFilters());

        $dtoEmpty = new LocationSearchDto;
        $this->assertFalse($dtoEmpty->hasFilters());
    }

    public function test_get_search_term(): void
    {
        $dtoWithSearch = new LocationSearchDto('test search', null);
        $this->assertSame('test search', $dtoWithSearch->getSearchTerm());

        $dtoEmpty = new LocationSearchDto;
        $this->assertSame('', $dtoEmpty->getSearchTerm());
    }

    public function test_get_stato_value(): void
    {
        $dtoWithStato = new LocationSearchDto(null, LocationStato::Attivo);
        $this->assertSame('attivo', $dtoWithStato->getStatoValue());

        $dtoEmpty = new LocationSearchDto;
        $this->assertNull($dtoEmpty->getStatoValue());
    }
}
