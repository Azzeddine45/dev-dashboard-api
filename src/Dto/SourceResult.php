<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class SourceResult
{
    public function __construct(
        public string $status,
        public GithubProfile|WeatherReport|null $data,
    ) {
    }

    public function isAvailable(): bool
    {
        return null !== $this->data;
    }
}
