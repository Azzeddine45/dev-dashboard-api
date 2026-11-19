<?php

declare(strict_types=1);

namespace App\Provider;

use App\Dto\GithubProfile;

interface GithubProfileProviderInterface
{
    public function fetchProfile(string $username): GithubProfile;
}
