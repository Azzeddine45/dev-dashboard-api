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
        public bool $stale = false,
    ) {
    }

    public function asStale(): self
    {
        return new self(
            $this->login,
            $this->name,
            $this->publicRepos,
            $this->followers,
            $this->profileUrl,
            true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'name' => $this->name,
            'publicRepos' => $this->publicRepos,
            'followers' => $this->followers,
            'profileUrl' => $this->profileUrl,
        ];
    }
}
