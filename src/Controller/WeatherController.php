<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ProviderException;
use App\Provider\WeatherProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class WeatherController
{
    public function __construct(private readonly WeatherProviderInterface $provider)
    {
    }

    #[Route('/api/weather', name: 'api_weather', methods: ['GET'])]
    public function __invoke(
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] float $lat,
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] float $lon,
    ): JsonResponse {
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return new JsonResponse(['error' => 'Invalid coordinates.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $report = $this->provider->fetchCurrent($lat, $lon);
        } catch (ProviderException) {
            return new JsonResponse(
                ['error' => 'Upstream provider unavailable.'],
                Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        return new JsonResponse([
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'temperatureCelsius' => $report->temperatureCelsius,
            'windSpeedKmh' => $report->windSpeedKmh,
            'weatherCode' => $report->weatherCode,
            'stal' => $report->stale,
        ]);
    }
}
