<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\LocationSearchDto;
use App\Exceptions\LocationNotFoundException;
use App\Http\Requests\Locations\LocationSearchRequest;
use App\Http\Resources\LocationListResource;
use App\Http\Resources\LocationResource;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

final class LocationController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService,
    ) {}

    public function index(Request $request): Response
    {
        $searchDto = LocationSearchDto::fromArray($request->all());
        $locations = $this->locationService->search($searchDto);

        return Inertia::render('map', [
            'filters' => [
                'search' => $searchDto->getSearchTerm(),
                'stato' => $searchDto->getStatoValue(),
            ],
            'locations' => LocationListResource::collection($locations)->toArray(request()),
            'googleMapsApiKey' => config('services.google.maps_key'),
            'googleMapsApiKeyMissing' => empty(config('services.google.maps_key')),
        ]);
    }

    public function search(LocationSearchRequest $request): AnonymousResourceCollection
    {
        $searchDto = $request->toDto();
        $locations = $this->locationService->search($searchDto);

        return LocationListResource::collection($locations);
    }

    /**
     * Get location details.
     *
     * @throws LocationNotFoundException
     */
    public function show(int $id): LocationResource
    {
        $location = $this->locationService->getLocationDetails($id);

        return LocationResource::make($location);
    }

    /**
     * Get detailed location information.
     *
     * @throws LocationNotFoundException
     */
    public function details(int $id): LocationResource
    {
        $location = $this->locationService->getLocationDetails($id);

        return LocationResource::make($location);
    }
}
