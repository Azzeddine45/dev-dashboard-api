<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Cache\ResilientCache;
use App\Provider\CachedWeatherProvider;
use App\Tests\Stub\FailingWeatherProvider;
use App\Tests\Stub\StubWeatherProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedWeatherProviderTest extends TestCase
{
    public function testServesStaleDataWhenUpstreamFails(): void
    {
        $adapter = new ArrayAdapter();
        $cache = new ResilientCache($adapter, new NullLogger());

        (new CachedWeatherProvider(new StubWeatherProvider(), $cache))->fetchCurrent(47.9, 1.9);

        // Simule l'expiration des données fraîches : seule la copie périmée reste.
        $adapter->deleteItem('weather_47.9_1.9_fresh');

        $report = (new CachedWeatherProvider(new FailingWeatherProvider(), $cache))->fetchCurrent(47.9, 1.9);

        self::assertTrue($report->stale);
        self::assertSame(12.5, $report->temperatureCelsius);
    }
}
