<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use App\Dto\GithubProfile;
use App\Exception\ProviderException;
use App\Provider\GithubProfileProviderInterface;

final class CountingProvider implements GithubProfileProviderInterface
{
    public int $calls = 0;

    public function __construct(private readonly bool $fails = false)
    {
    }

    public function fetchProfile(string $username): GithubProfile
    {
        ++$this->calls;

        if ($this->fails) {
            throw new ProviderException('Upstream down.');
        }

        return new GithubProfile($username, null, 1, 2, 'https://github.com/'.$username);
    }
}
