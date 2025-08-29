<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LocationNotFoundException extends Exception
{
    public function __construct(
        int $locationId,
        string $message = 'Location not found',
        int $code = 404,
        ?Exception $previous = null
    ) {
        parent::__construct(
            sprintf('%s (ID: %d)', $message, $locationId),
            $code,
            $previous
        );
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Location not found',
            'error' => 'LOCATION_NOT_FOUND',
        ], 404);
    }
}
