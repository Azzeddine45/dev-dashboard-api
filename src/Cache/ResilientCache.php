<?php

declare(strict_types=1);

namespace App\Cache;

use App\Exception\ProviderException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

final class ResilientCache
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @template T of object
     *
     * @param callable(): T $loader
     *
     * @return CacheResult<T>
     */
    public function get(string $key, callable $loader, int $freshTtl = 300, int $staleTtl = 86400): CacheResult
    {
        $fresh = $this->cache->getItem($key.'_fresh');
        $cached = $fresh->get();
        if ($fresh->isHit() && is_object($cached)) {
            /** @var CacheResult<T> $hit */
            $hit = new CacheResult($cached, false);

            return $hit;
        }

        try {
            $value = $loader();
        } catch (ProviderException $e) {
            $stale = $this->cache->getItem($key.'_stale');
            $staleValue = $stale->get();

            if ($stale->isHit() && is_object($staleValue)) {
                $this->logger->warning('Upstream unavailable, serving stale data.', ['key' => $key]);

                /** @var CacheResult<T> $fallback */
                $fallback = new CacheResult($staleValue, true);

                return $fallback;
            }

            throw $e;
        }

        $this->cache->save($fresh->set($value)->expiresAfter($freshTtl));
        $this->cache->save($this->cache->getItem($key.'_stale')->set($value)->expiresAfter($staleTtl));

        return new CacheResult($value, false);
    }
}
