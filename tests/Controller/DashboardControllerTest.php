<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    public function testAggregatesBothSources(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/dashboard?github=octocat&lat=47.9&lon=1.9');

        self::assertResponseIsSuccessful();

        $data = $this->decode($client->getResponse()->getContent());

        self::assertSame('ok', $data['github']['status']);
        self::assertSame('octocat', $data['github']['data']['login']);
        self::assertSame('ok', $data['weather']['status']);
        self::assertSame(12.5, $data['weather']['data']['temperatureCelsius']);
    }

    public function testStillRespondsWhenOneSourceFails(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/dashboard?github=someone&lat=47.9&lon=1.9');

        self::assertResponseIsSuccessful();

        $data = $this->decode($client->getResponse()->getContent());

        self::assertSame('unavailable', $data['github']['status']);
        self::assertNull($data['github']['data']);
        self::assertSame('ok', $data['weather']['status']);
    }

    public function testReturns503WhenAllSourcesFail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/dashboard?github=someone&lat=0&lon=0');

        self::assertResponseStatusCodeSame(503);
    }

    public function testRejectsMissingParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/dashboard?github=octocat');

        self::assertResponseStatusCodeSame(400);
    }

    public function testRejectsInvalidUsername(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/dashboard?github=bad_name!&lat=47.9&lon=1.9');

        self::assertResponseStatusCodeSame(400);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function decode(string|false $content): array
    {
        $data = json_decode((string) $content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
