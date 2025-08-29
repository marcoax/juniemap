<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DataTransferObjects\LocationSearchDto;
use App\Enums\LocationStato;
use App\Exceptions\LocationNotFoundException;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private LocationService $service;

    private CacheRepository $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new CacheRepository(new ArrayStore);
        $this->service = new LocationService($this->cache);
    }

    public function test_search_returns_locations(): void
    {
        // Arrange
        Location::factory()->create([
            'titolo' => 'Test Location',
            'stato' => LocationStato::Attivo->value,
        ]);

        $searchDto = new LocationSearchDto('Test', LocationStato::Attivo);

        // Act
        $result = $this->service->search($searchDto);

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('Test Location', $result->first()->titolo);
    }

    public function test_search_caches_results(): void
    {
        // Arrange
        Location::factory()->create([
            'titolo' => 'Test Location',
            'stato' => LocationStato::Attivo->value,
        ]);

        $searchDto = new LocationSearchDto('Test', LocationStato::Attivo);
        $cacheKey = 'locations.search:'.$searchDto->toCacheKey();

        // Act
        $this->service->search($searchDto);

        // Assert
        $this->assertTrue($this->cache->has($cacheKey));
    }

    public function test_get_location_details_returns_location(): void
    {
        // Arrange
        $location = Location::factory()->create([
            'titolo' => 'Test Location',
        ]);

        // Act
        $result = $this->service->getLocationDetails($location->id);

        // Assert
        $this->assertSame($location->id, $result->id);
        $this->assertSame('Test Location', $result->titolo);
    }

    public function test_get_location_details_caches_result(): void
    {
        // Arrange
        $location = Location::factory()->create();
        $cacheKey = 'locations.details:'.$location->id;

        // Act
        $this->service->getLocationDetails($location->id);

        // Assert
        $this->assertTrue($this->cache->has($cacheKey));
    }

    public function test_get_location_details_throws_exception_for_non_existent_location(): void
    {
        // Arrange & Act & Assert
        $this->expectException(LocationNotFoundException::class);
        $this->service->getLocationDetails(99999);
    }

    public function test_get_all_for_map_returns_locations(): void
    {
        // Arrange
        Location::factory()->count(3)->create();

        // Act
        $result = $this->service->getAllForMap();

        // Assert
        $this->assertCount(3, $result);

        // Check that only map fields are selected
        $location = $result->first();
        $this->assertNotNull($location->id);
        $this->assertNotNull($location->titolo);
        $this->assertNotNull($location->latitude);
        $this->assertNotNull($location->longitude);

        // These fields should not be loaded (they're not in the forMap scope)
        $this->assertFalse(array_key_exists('descrizione', $location->getAttributes()));
    }

    public function test_get_all_for_map_caches_result(): void
    {
        // Arrange
        Location::factory()->create();

        // Act
        $this->service->getAllForMap();

        // Assert
        $this->assertTrue($this->cache->has('locations.all_for_map'));
    }

    public function test_clear_location_cache_removes_cache_entries(): void
    {
        // Arrange
        $location = Location::factory()->create();
        $detailsCacheKey = 'locations.details:'.$location->id;
        $mapCacheKey = 'locations.all_for_map';

        // Pre-populate cache
        $this->cache->put($detailsCacheKey, $location, 60);
        $this->cache->put($mapCacheKey, collect([$location]), 60);

        // Act
        $this->service->clearLocationCache($location->id);

        // Assert
        $this->assertFalse($this->cache->has($detailsCacheKey));
        $this->assertFalse($this->cache->has($mapCacheKey));
    }

    public function test_get_nearby_returns_locations_within_radius(): void
    {
        // Arrange - Create locations at different distances
        $centerLat = 45.0;
        $centerLng = 9.0;

        // Close location (should be returned)
        $closeLocation = Location::factory()->create([
            'titolo' => 'Close Location',
            'latitude' => 45.001, // ~100m away
            'longitude' => 9.001,
        ]);

        // Far location (should not be returned with default 10km radius)
        Location::factory()->create([
            'titolo' => 'Far Location',
            'latitude' => 46.0, // ~111km away
            'longitude' => 10.0,
        ]);

        // Act
        $result = $this->service->getNearby($centerLat, $centerLng, 10.0);

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('Close Location', $result->first()->titolo);
    }

    public function test_get_cache_stats_returns_stats(): void
    {
        // Act
        $stats = $this->service->getCacheStats();

        // Assert
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('map_cache_exists', $stats);
        $this->assertArrayHasKey('cache_driver', $stats);
        $this->assertIsBool($stats['map_cache_exists']);
        $this->assertIsString($stats['cache_driver']);
    }
}
