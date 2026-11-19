<?php

declare(strict_types=1);

namespace App\Provider;

use App\Dto\GithubProfile;
use App\Exception\ProviderException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CachedGithubProvider implements GithubProfileProviderInterface
{
    private const FRESH_TTL = 300;
    private const STALE_TTL = 86400;

    public function __construct(
        #[Autowire(service: GithubProvider::class)]
        private readonly GithubProfileProviderInterface $inner,
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetchProfile(string $username): GithubProfile
    {
        $key = 'github_profile_'.strtolower($username);

        $fresh = $this->cache->getItem($key.'_fresh');
        $cached = $fresh->get();
        if ($fresh->isHit() && $cached instanceof GithubProfile) {
            return $cached;
        }

        try {
            $profile = $this->inner->fetchProfile($username);
        } catch (ProviderException $e) {
            $stale = $this->cache->getItem($key.'_stale');
            $staleProfile = $stale->get();

            if ($stale->isHit() && $staleProfile instanceof GithubProfile) {
                $this->logger->warning('GitHub unavailable, serving stale data.', ['username' => $username]);

                return $staleProfile->asStale();
            }

            throw $e;
        }

        $this->cache->save($fresh->set($profile)->expiresAfter(self::FRESH_TTL));
        $this->cache->save(
            $this->cache->getItem($key.'_stale')->set($profile)->expiresAfter(self::STALE_TTL),
        );

        return $profile;
    }
}
