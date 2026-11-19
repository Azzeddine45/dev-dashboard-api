<?php

declare(strict_types=1);

namespace App\Cache;

/**
 * @template T of object
 */
final readonly class CacheResult
{
    /**
     * @param T $value
     */
    public function __construct(
        public object $value,
        public bool $stale,
    ) {
    }
}
