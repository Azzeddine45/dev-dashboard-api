<?php

declare(strict_types=1);

namespace App\Provider;

use App\Dto\WeatherReport;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedWeatherProvider implements WeatherProviderInterface
{
    private const TTL = 600;

    public function __construct(
        #[Autowire(service: WeatherProvider::class)]
        private readonly WeatherProviderInterface $inner,
        private readonly CacheInterface $cache,
    ) {
    }

    public function fetchCurrent(float $latitude, float $longitude): WeatherReport
    {
        $key = sprintf('weather_%s_%s', round($latitude, 2), round($longitude, 2));

        return $this->cache->get($key, function (ItemInterface $item) use ($latitude, $longitude): WeatherReport {
            $item->expiresAfter(self::TTL);

            return $this->inner->fetchCurrent($latitude, $longitude);
        });
    }
}
