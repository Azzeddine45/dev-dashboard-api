<?php

declare(strict_types=1);

namespace App\Provider;

use App\Cache\ResilientCache;
use App\Dto\GithubProfile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CachedGithubProvider implements GithubProfileProviderInterface
{
    public function __construct(
        #[Autowire(service: GithubProvider::class)]
        private readonly GithubProfileProviderInterface $inner,
        private readonly ResilientCache $cache,
    ) {
    }

    public function fetchProfile(string $username): GithubProfile
    {
        $result = $this->cache->get(
            'github_profile_'.strtolower($username),
            fn (): GithubProfile => $this->inner->fetchProfile($username),
        );

        return $result->stale ? $result->value->asStale() : $result->value;
    }
}
