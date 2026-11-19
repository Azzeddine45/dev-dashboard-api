<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use App\Dto\WeatherReport;
use App\Exception\ProviderException;
use App\Provider\WeatherProviderInterface;

final class FailingWeatherProvider implements WeatherProviderInterface
{
    public function fetchCurrent(float $latitude, float $longitude): WeatherReport
    {
        throw new ProviderException('Upstream down.');
    }
}
