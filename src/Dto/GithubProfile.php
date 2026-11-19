<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class GithubProfile
{
    public function __construct(
        public string $login,
        public ?string $name,
        public int $publicRepos,
        public int $followers,
        public string $profileUrl,
    ) {
    }
}
