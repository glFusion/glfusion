<?php
/**
 *
 * This file is part of phpFastCache.
 *
 * @license MIT License (MIT)
 *
 * For full copyright and license information, please see the docs/CREDITS.txt file.
 *
 * @author Khoa Bui (khoaofgod)  <khoaofgod@gmail.com> https://www.phpfastcache.com
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 *
 */
declare(strict_types=1);

namespace Phpfastcache\Helper;

use Phpfastcache\CacheManager;
use Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\{
    PhpfastcacheDriverCheckException, PhpfastcacheInvalidArgumentException, PhpfastcacheLogicException, PhpfastcacheRootException, PhpfastcacheSimpleCacheException
};
use Psr\SimpleCache\CacheInterface;

/**
 * Class Psr16Adapter
 * @package phpFastCache\Helper
 */
class Psr16Adapter implements CacheInterface
{
    /**
     * @var ExtendedCacheItemPoolInterface
     */
    protected $internalCacheInstance;

    /**
     * Psr16Adapter constructor.
     * @param string|ExtendedCacheItemPoolInterface $driver
     * @param array|\Phpfastcache\Config\ConfigurationOption|null $config
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheLogicException
     */
    public function __construct($driver, $config = null)
    {
        if ($driver instanceof ExtendedCacheItemPoolInterface) {
            if ($config !== null) {
                throw new PhpfastcacheLogicException("You can't pass a config parameter along with an non-string '\$driver' parameter.");
            }
            $this->internalCacheInstance = $driver;
        } else {
            $this->internalCacheInstance = CacheManager::getInstance($driver, $config);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function get($key, $default = null)
    {
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            if (!$cacheItem->isExpired() && $cacheItem->get() !== null) {
                return $cacheItem->get();
            }

            return $default;
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function set($key, $value, $ttl = null): bool
    {
        try {
            $cacheItem = $this->internalCacheInstance
                ->getItem($key)
                ->set($value);
            if (\is_int($ttl) && $ttl <= 0) {
                $cacheItem->expiresAt((new \DateTime('@0')));
            } elseif (\is_int($ttl) || $ttl instanceof \DateInterval) {
                $cacheItem->expiresAfter($ttl);
            }
            return $this->internalCacheInstance->save($cacheItem);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function delete($key): bool
    {
        try {
            return $this->internalCacheInstance->deleteItem($key);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @return bool
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function clear(): bool
    {
        try {
            return $this->internalCacheInstance->clear();
        } catch (PhpfastcacheRootException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $keys
     * @param null $default
     * @return \iterable
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function getMultiple($keys, $default = null)
    {
        try {
            return array_map(function (ExtendedCacheItemInterface $item) {
                return $item->get();
            }, $this->internalCacheInstance->getItems($keys));
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            foreach ($values as $key => $value) {
                $cacheItem = $this->internalCacheInstance->getItem($key)->set($value);

                if (\is_int($ttl) && $ttl <= 0) {
                    $cacheItem->expiresAt((new \DateTime('@0')));
                } elseif (\is_int($ttl) || $ttl instanceof \DateInterval) {
                    $cacheItem->expiresAfter($ttl);
                }
                $this->internalCacheInstance->saveDeferred($cacheItem);
                unset($cacheItem);
            }
            return $this->internalCacheInstance->commit();
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $keys
     * @return bool
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function deleteMultiple($keys): bool
    {
        try {
            return $this->internalCacheInstance->deleteItems($keys);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function has($key): bool
    {
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            return $cacheItem->isHit() && !$cacheItem->isExpired();
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * Extra methods that are not part of
     * psr16 specifications
     */

    /**
     * @return \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     */
    public function getInternalCacheInstance(): ExtendedCacheItemPoolInterface
    {
        return $this->internalCacheInstance;
    }
}
