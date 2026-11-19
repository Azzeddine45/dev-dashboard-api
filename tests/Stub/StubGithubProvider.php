<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use App\Dto\GithubProfile;
use App\Exception\ProviderException;
use App\Provider\GithubProfileProviderInterface;

final class StubGithubProvider implements GithubProfileProviderInterface
{
    public function fetchProfile(string $username): GithubProfile
    {
        if ('octocat' !== $username) {
            throw new ProviderException('Stub: provider unavailable.');
        }

        return new GithubProfile('octocat', 'The Octocat', 8, 42, 'https://github.com/octocat');
    }
}
