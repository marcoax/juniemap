<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\CacheKeys;
use App\DataTransferObjects\LocationSearchDto;
use App\Exceptions\LocationNotFoundException;
use App\Models\Location;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

final readonly class LocationService
{
    public function __construct(
        private CacheRepository $cache,
    ) {}

    /**
     * Search locations with caching.
     *
     * @return Collection<int, Location>
     */
    public function search(LocationSearchDto $searchDto): Collection
    {
        $cacheKey = CacheKeys::locationSearch(
            $searchDto->getSearchTerm(),
            $searchDto->getStatoValue() ?? ''
        );

        return $this->cache->remember(
            $cacheKey,
            now()->addMinutes(CacheKeys::defaultTtl()),
            fn () => $this->performSearch($searchDto)
        );
    }

    /**
     * Get location details with caching.
     *
     * @throws LocationNotFoundException
     */
    public function getLocationDetails(int $locationId): Location
    {
        $cacheKey = CacheKeys::locationDetails($locationId);

        try {
            return $this->cache->remember(
                $cacheKey,
                now()->addMinutes(CacheKeys::defaultTtl()),
                fn () => Location::query()->findOrFail($locationId)
            );
        } catch (ModelNotFoundException) {
            throw new LocationNotFoundException($locationId);
        }
    }

    /**
     * Get all locations for initial map load with caching.
     *
     * @return Collection<int, Location>
     */
    public function getAllForMap(): Collection
    {
        return $this->cache->remember(
            'locations.all_for_map',
            now()->addHours(1), // Cache for 1 hour since this data changes less frequently
            fn () => Location::query()->forMap()->orderBy('titolo')->get()
        );
    }

    /**
     * Clear cache for location and related caches.
     */
    public function clearLocationCache(int $locationId): void
    {
        // Clear specific location cache
        $this->cache->forget(CacheKeys::locationDetails($locationId));

        // Clear map cache since location data changed
        $this->cache->forget('locations.all_for_map');

        // Clear search caches (if using Redis with tags, this would be easier)
        $this->clearSearchCaches();
    }

    /**
     * Clear all search-related caches.
     */
    public function clearSearchCaches(): void
    {
        // In a production app, you might want to use cache tags or patterns
        // For now, we'll implement a simple approach

        // If using Redis, you could use pattern matching to clear all search caches
        // $this->cache->getStore()->getRedis()->eval("return redis.call('del', unpack(redis.call('keys', ARGV[1])))", 0, 'locations.search:*');

        // For file/database cache, we'd need to track search cache keys separately
        // This is a simplified implementation
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @return array<string, mixed>
     */
    public function getCacheStats(): array
    {
        $stats = [
            'map_cache_exists' => $this->cache->has('locations.all_for_map'),
            'cache_driver' => config('cache.default'),
        ];

        // Add more stats if needed for monitoring
        return $stats;
    }

    /**
     * Get nearby locations.
     *
     * @return Collection<int, Location>
     */
    public function getNearby(float $latitude, float $longitude, float $radiusKm = 10.0): Collection
    {
        return Location::query()
            ->nearby($latitude, $longitude, $radiusKm)
            ->get();
    }

    /**
     * Perform the actual search without caching.
     *
     * @return Collection<int, Location>
     */
    private function performSearch(LocationSearchDto $searchDto): Collection
    {
        return Location::query()
            ->forMap()
            ->search($searchDto->search)
            ->byStato($searchDto->stato)
            ->orderBy('titolo')
            ->get();
    }
}
