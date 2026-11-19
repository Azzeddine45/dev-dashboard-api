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
        public bool $stale = false,
    ) {
    }

    public function asStale(): self
    {
        return new self(
            $this->latitude,
            $this->longitude,
            $this->temperatureCelsius,
            $this->windSpeedKmh,
            $this->weatherCode,
            true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'temperatureCelsius' => $this->temperatureCelsius,
            'windSpeedKmh' => $this->windSpeedKmh,
            'weatherCode' => $this->weatherCode,
        ];
    }
}
