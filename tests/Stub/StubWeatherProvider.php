<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use App\Dto\WeatherReport;
use App\Exception\ProviderException;
use App\Provider\WeatherProviderInterface;

final class StubWeatherProvider implements WeatherProviderInterface
{
    public function fetchCurrent(float $latitude, float $longitude): WeatherReport
    {
        if (0.0 === $latitude) {
            throw new ProviderException('Stub: provider unavailable.');
        }

        return new WeatherReport($latitude, $longitude, 12.5, 14.0, 3);
    }
}
