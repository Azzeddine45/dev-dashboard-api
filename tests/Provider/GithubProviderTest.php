<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Exception\ProviderException;
use App\Provider\GithubProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GithubProviderTest extends TestCase
{
    public function testFetchProfileReturnsNormalizedData(): void
    {
        $body = json_encode([
            'login' => 'octocat',
            'name' => 'The Octocat',
            'public_repos' => 8,
            'followers' => 42,
            'html_url' => 'https://github.com/octocat',
        ], JSON_THROW_ON_ERROR);

        $provider = new GithubProvider(new MockHttpClient(new MockResponse($body, [
            'response_headers' => ['content-type: application/json'],
        ])));

        $profile = $provider->fetchProfile('octocat');

        self::assertSame('octocat', $profile->login);
        self::assertSame('The Octocat', $profile->name);
        self::assertSame(8, $profile->publicRepos);
        self::assertSame(42, $profile->followers);
    }

    public function testFetchProfileThrowsWhenGithubFails(): void
    {
        $provider = new GithubProvider(new MockHttpClient(new MockResponse('{"message":"Not Found"}', [
            'http_code' => 404,
        ])));

        $this->expectException(ProviderException::class);

        $provider->fetchProfile('unknown-user');
    }
}
