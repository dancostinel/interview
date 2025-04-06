<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;

class RedisService
{
    public const ONE_WEEK_IN_SECONDS = 7 * 24 * 60 * 60;

    public function __construct(
        private CacheItemPoolInterface $redisCache,
        private LoggerInterface $redisLogger
    ) {
    }

    public function save(string $key, mixed $value, int $lifetime = self::ONE_WEEK_IN_SECONDS): bool
    {
        try {
            /** @var CacheItem $cacheItem */
            $cacheItem = $this->redisCache->getItem($key);
        } catch (InvalidArgumentException $exception) {
            $this->redisLogger->error($exception->getMessage());

            return false;
        }

        $cacheItem->set($value);
        $cacheItem->expiresAfter($lifetime);

        return $this->redisCache->save($cacheItem);
    }

    public function read(string $key): ?CacheItemInterface
    {
        try {
            /** @var CacheItem $cacheItem */
            $cacheItem = $this->redisCache->getItem($key);
        } catch (InvalidArgumentException $exception) {
            $this->redisLogger->error($exception->getMessage());

            return null;
        }

        return $cacheItem->isHit() ? $cacheItem : null;
    }
}
