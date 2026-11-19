<?php

declare(strict_types=1);

namespace App\Provider;

use App\Dto\WeatherReport;

interface WeatherProviderInterface
{
    public function fetchCurrent(float $latitude, float $longitude): WeatherReport;
}
