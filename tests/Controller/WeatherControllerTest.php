<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WeatherControllerTest extends WebTestCase
{
    public function testReturnsWeather(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/weather?lat=47.9&lon=1.9');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertSame(12.5, $data['temperatureCelsius']);
    }

    public function testRejectsMissingParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/weather');

        self::assertResponseStatusCodeSame(400);
    }

    public function testRejectsOutOfRangeCoordinates(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/weather?lat=200&lon=1.9');

        self::assertResponseStatusCodeSame(400);
    }

    public function testReturns503WhenProviderFails(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/weather?lat=0&lon=0');

        self::assertResponseStatusCodeSame(503);
    }
}
