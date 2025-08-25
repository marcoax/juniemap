<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
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

    public function search(Request $request): JsonResource
    {
        $this->throttle($request);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'stato' => ['nullable', 'in:attivo,disattivo,in_allarme'],
        ]);

        $search = $this->sanitize($validated['search'] ?? null);
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

    protected function throttle(Request $request): void
    {
        $key = 'locations-search:'.$request->ip();
        $allowed = RateLimiter::attempt($key, 60, function (): void {
            // allowed
        });

        if ($allowed === false) {
            abort(429, 'Too Many Requests');
        }
    }

    protected function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim(strip_tags($value));
    }
}
