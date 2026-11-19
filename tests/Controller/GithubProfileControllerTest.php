<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GithubProfileControllerTest extends WebTestCase
{
    public function testReturnsProfile(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/github/octocat');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertSame('octocat', $data['login']);
        self::assertSame(42, $data['followers']);
    }

    public function testReturns503WhenProviderFails(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/github/someone');

        self::assertResponseStatusCodeSame(503);
    }

    public function testRejectsInvalidUsername(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/github/bad_name!');

        self::assertResponseStatusCodeSame(404);
    }
}
