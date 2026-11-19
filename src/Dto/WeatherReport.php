<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class WeatherReport
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public float $temperatureCelsius,
        public float $windSpeedKmh,
        public int $weatherCode,
    ) {
    }
}
