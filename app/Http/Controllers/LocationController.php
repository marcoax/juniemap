<?php

namespace App\Http\Controllers;

use App\Http\Requests\Locations\LocationSearchRequest;
use App\Models\Location;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(public CacheRepository $cache) {}

    public function index(Request $request): Response
    {
        $search = $this->sanitize($request->string('search')->toString());
        $stato = $request->string('stato')->toString();

        $locations = Location::query()
            ->search($search)
            ->byStato($stato)
            ->orderBy('titolo')
            ->get(['id', 'titolo', 'indirizzo', 'latitude', 'longitude', 'stato']);

        return Inertia::render('map', [
            'filters' => [
                'search' => $search,
                'stato' => $stato,
            ],
            'locations' => $locations,
            'googleMapsApiKey' => config('services.google.maps_key'),
            'googleMapsApiKeyMissing' => empty(config('services.google.maps_key')),
        ]);
    }

    public function search(LocationSearchRequest $request): JsonResource
    {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $stato = $validated['stato'] ?? null;

        $cacheKey = 'locations.search:'.md5(($search ?? '').'|'.($stato ?? ''));

        $locations = $this->cache->remember($cacheKey, now()->addMinutes(15), function () use ($search, $stato) {
            return Location::query()
                ->search($search)
                ->byStato($stato)
                ->orderBy('titolo')
                ->get(['id', 'titolo', 'indirizzo', 'latitude', 'longitude', 'stato']);
        });

        return JsonResource::collection($locations);
    }

    public function show(int $id): JsonResource
    {
        $location = Location::query()->findOrFail($id);

        return JsonResource::make($location);
    }

    public function details(int $id): JsonResource
    {
        $cacheKey = 'locations.details:'.$id;

        $location = $this->cache->remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            return Location::query()->findOrFail($id);
        });

        return JsonResource::make($location);
    }

    protected function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim(strip_tags($value));
    }
}
