<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Dto\GithubProfile;
use App\Exception\ProviderException;
use App\Provider\CachedGithubProvider;
use App\Tests\Stub\CountingProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedGithubProviderTest extends TestCase
{
    public function testSecondCallIsServedFromCache(): void
    {
        $inner = new CountingProvider();
        $provider = new CachedGithubProvider($inner, new ArrayAdapter(), new NullLogger());

        $provider->fetchProfile('octocat');
        $second = $provider->fetchProfile('octocat');

        self::assertSame(1, $inner->calls);
        self::assertFalse($second->stale);
    }

    public function testServesStaleDataWhenUpstreamFails(): void
    {
        $cache = new ArrayAdapter();
        $item = $cache->getItem('github_profile_octocat_stale');
        $cache->save($item->set(new GithubProfile('octocat', null, 1, 2, 'https://github.com/octocat')));

        $provider = new CachedGithubProvider(new CountingProvider(fails: true), $cache, new NullLogger());

        $profile = $provider->fetchProfile('octocat');

        self::assertTrue($profile->stale);
        self::assertSame('octocat', $profile->login);
    }

    public function testThrowsWhenUpstreamFailsAndNothingIsCached(): void
    {
        $provider = new CachedGithubProvider(new CountingProvider(fails: true), new ArrayAdapter(), new NullLogger());

        $this->expectException(ProviderException::class);

        $provider->fetchProfile('octocat');
    }
}
