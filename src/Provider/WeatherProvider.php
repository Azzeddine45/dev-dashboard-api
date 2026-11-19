<?php

declare(strict_types=1);

namespace App\Provider;

use App\Dto\WeatherReport;
use App\Exception\ProviderException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WeatherProvider implements WeatherProviderInterface
{
    public function __construct(private readonly HttpClientInterface $weatherClient)
    {
    }

    public function fetchCurrent(float $latitude, float $longitude): WeatherReport
    {
        try {
            $data = $this->weatherClient->request('GET', '/v1/forecast', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,wind_speed_10m,weather_code',
                ],
            ])->toArray();
        } catch (ExceptionInterface $e) {
            throw new ProviderException('Weather provider is unavailable.', 0, $e);
        }

        $current = $data['current'] ?? null;
        if (!is_array($current)) {
            throw new ProviderException('Unexpected weather response.');
        }

        return new WeatherReport(
            latitude: $latitude,
            longitude: $longitude,
            temperatureCelsius: (float) ($current['temperature_2m'] ?? 0),
            windSpeedKmh: (float) ($current['wind_speed_10m'] ?? 0),
            weatherCode: (int) ($current['weather_code'] ?? 0),
        );
    }
}
