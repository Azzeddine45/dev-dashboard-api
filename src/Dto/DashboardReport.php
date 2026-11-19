<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class DashboardReport
{
    public function __construct(
        public SourceResult $github,
        public SourceResult $weather,
    ) {
    }

    public function isFullyUnavailable(): bool
    {
        return !$this->github->isAvailable() && !$this->weather->isAvailable();
    }
}
