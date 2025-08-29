<?php

declare(strict_types=1);

namespace App\Constants;

final readonly class CacheKeys
{
    /**
     * Cache key for location search results.
     */
    public static function locationSearch(string $search, string $stato): string
    {
        return sprintf('locations.search:%s', md5($search.'|'.$stato));
    }

    /**
     * Cache key for location details.
     */
    public static function locationDetails(int $locationId): string
    {
        return sprintf('locations.details:%d', $locationId);
    }

    /**
     * Cache key prefix for location-related data.
     */
    public static function locationPrefix(): string
    {
        return 'locations';
    }

    /**
     * Default cache TTL in minutes for location data.
     */
    public static function defaultTtl(): int
    {
        return 15;
    }
}
