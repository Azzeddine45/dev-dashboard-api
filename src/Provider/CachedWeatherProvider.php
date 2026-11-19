<?php

declare(strict_types=1);

namespace App\Provider;

use App\Cache\ResilientCache;
use App\Dto\WeatherReport;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CachedWeatherProvider implements WeatherProviderInterface
{
    public function __construct(
        #[Autowire(service: WeatherProvider::class)]
        private readonly WeatherProviderInterface $inner,
        private readonly ResilientCache $cache,
    ) {
    }

    public function fetchCurrent(float $latitude, float $longitude): WeatherReport
    {
        $result = $this->cache->get(
            sprintf('weather_%s_%s', round($latitude, 2), round($longitude, 2)),
            fn (): WeatherReport => $this->inner->fetchCurrent($latitude, $longitude),
            freshTtl: 600,
        );

        return $result->stale ? $result->value->asStale() : $result->value;
    }
}
