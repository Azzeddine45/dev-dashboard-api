<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Exception\ProviderException;
use App\Provider\WeatherProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WeatherProviderTest extends TestCase
{
    public function testFetchCurrentReturnsNormalizedData(): void
    {
        $body = json_encode([
            'current' => ['temperature_2m' => 12.5, 'wind_speed_10m' => 14.0, 'weather_code' => 3],
        ], JSON_THROW_ON_ERROR);

        $provider = new WeatherProvider(new MockHttpClient(new MockResponse($body, [
            'response_headers' => ['content-type: application/json'],
        ])));

        $report = $provider->fetchCurrent(47.9, 1.9);

        self::assertSame(12.5, $report->temperatureCelsius);
        self::assertSame(14.0, $report->windSpeedKmh);
        self::assertSame(3, $report->weatherCode);
    }

    public function testThrowsOnUpstreamError(): void
    {
        $provider = new WeatherProvider(new MockHttpClient(new MockResponse('{}', ['http_code' => 500])));

        $this->expectException(ProviderException::class);

        $provider->fetchCurrent(47.9, 1.9);
    }

    public function testThrowsOnUnexpectedPayload(): void
    {
        $provider = new WeatherProvider(new MockHttpClient(new MockResponse('{"foo":"bar"}', [
            'response_headers' => ['content-type: application/json'],
        ])));

        $this->expectException(ProviderException::class);

        $provider->fetchCurrent(47.9, 1.9);
    }
}
