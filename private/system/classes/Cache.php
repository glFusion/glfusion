<?php
/**
*   glFusion phpFastCache Interface
*
*   @author     Mark R. Evans <mark@lglfusion.org>
*   @copyright  Copyright (c) 2017-2018 Mark R. Evans <mark@glfusion.org>
*   @package    glFusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

namespace glFusion;

use \phpFastCache\CacheManager;
use \phpFastCache\Core\Item\ExtendedCacheItemInterface;
use \phpFastCache\Core\Pool\ExtendedCacheItemPoolInterface;
use \phpFastCache\Exceptions\phpFastCacheDriverCheckException;
use \phpFastCache\Exceptions\phpFastCacheInvalidArgumentException;
use \phpFastCache\Exceptions\phpFastCacheRootException;
use \phpFastCache\Exceptions\phpFastCacheSimpleCacheException;
use \Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 *
 * @package glFusion
 */
final class Cache
{
    /**
     * @var internalCacheInstance
     */
    protected $internalCacheInstance;

    /**
     * @return Class instance
     */
    public static function getInstance()
    {
        static $inst = null;

        if ($inst === null) {
            $inst = new Cache();
        }
        return $inst;
    }


    /**
     * constructor
     */
    private  function __construct()
    {
        global $_CONF;

        // validations
        if (!isset($_CONF['cache_driver'])) $_CONF['cache_driver'] = 'files';
        if (!isset($_CONF['cache_host'])) $_CONF['cache_host'] = '127.0.0.1';
        if (!isset($_CONF['cache_port'])) $_CONF['cache_port'] = ($_CONF['cache_driver'] == 'redis') ? 6379 : 11211;
        if (!isset($_CONF['cache_password'])) $_CONF['cache_password'] = 'password';
        if (!isset($_CONF['cache_database'])) $_CONF['cache_database'] = 'glfusion';
        if (!isset($_CONF['cache_timeout'])) $_CONF['cache_timeout'] = 60;

        $validArray = configmanager_select_cache_driver_helper();
        if (!in_array($_CONF['cache_driver'],$validArray)) {
            $_CONF['cache_driver'] = 'files';
        }

        $config = array(
            'driver' => $_CONF['cache_driver'],
            'host' => $_CONF['cache_host'],
            'port' => $_CONF['cache_port'],
            'password' => $_CONF['cache_password'],
            'database' => $_CONF['cache_database'],
            'servers' => array(
                            0 => array('host' => $_CONF['cache_host'], 'port' => $_CONF['cache_port'])
                         ),
            'fallback' => 'files',
            'path' => $_CONF['path'].'data/cache/',
            'itemDetailedDate' => true
        );

        $this->internalCacheInstance = CacheManager::getInstance($_CONF['cache_driver'], $config);
    }


    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function get($key, $default = null)
    {
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            if (!$cacheItem->isExpired() && $cacheItem->get() !== null) {
                return $cacheItem->get();
            } else {
                return $default;
            }
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function set($key, $value, $tag = '', $ttl = null)
    {
        global $_CONF;

        try {
            $cacheItem = $this->internalCacheInstance
              ->getItem($key)
              ->set($value);
            if (is_int($ttl) && $ttl <= 0) {
                $cacheItem->expiresAt((new \DateTime('@0')));
            } elseif (is_int($ttl) || $ttl instanceof \DateInterval) {
                $cacheItem->expiresAfter($ttl);
            } else if (strtolower($_CONF['cache_driver']) != 'files') {
                $cacheItem->expiresAfter(86400);
            }
            if ( is_array($tag) ) {
                $cacheItem->addTags($tag);
            } elseif ($tag != '' ) {
                $cacheItem->addTag($tag);
            }
            return $this->internalCacheInstance->save($cacheItem);
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @param string $tag
     * @return none
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function addTag($key, $tag)
    {
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            $cacheItem->addTag($tag);
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function delete($key)
    {
        try {
            return $this->internalCacheInstance->deleteItem($key);
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @return bool
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function clear()
    {
        try {
            return $this->internalCacheInstance->clear();
        } catch (phpFastCacheRootException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $keys
     * @param null $default
     * @return \iterable
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function getMultiple($keys, $default = null)
    {
        try {
            return array_map(function (ExtendedCacheItemInterface $item) {
                return $item->get();
            }, $this->internalCacheInstance->getItems($keys));
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function setMultiple($values, $ttl = null)
    {
        try {
            foreach ($values as $key => $value) {
                $cacheItem = $this->internalCacheInstance->getItem($key)->set($value);

                if (is_int($ttl) && $ttl <= 0) {
                    $cacheItem->expiresAt((new \DateTime('@0')));
                } elseif (is_int($ttl) || $ttl instanceof \DateInterval) {
                    $cacheItem->expiresAfter($ttl);
                }
                $this->internalCacheInstance->saveDeferred($cacheItem);
                unset($cacheItem);
            }
            return $this->internalCacheInstance->commit();
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $keys
     * @return bool
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function deleteMultiple($keys)
    {
        try {
            return $this->internalCacheInstance->deleteItems($keys);
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
     */
    public function has($key)
    {
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            return $cacheItem->isHit() && !$cacheItem->isExpired();
        } catch (phpFastCacheInvalidArgumentException $e) {
            throw new phpFastCacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return int (time)
     */
    public function getModificationDate($key)
    {
        $cacheItem = $this->internalCacheInstance->getItem($key);
        $modDate = $cacheItem->getModificationDate();
        if ( is_object($modDate) && isset($modDate->date)) {
            return strtotime($modDate->date);
        }
        return 0;
    }

    /**
     * delete cache items by tag
     * @param string $tag
     * @return none
     */
    public function deleteItemsByTag($tag)
    {
        $tagArray = $this->getItemsByTag($tag);
        if (is_array($tagArray)) {
            foreach ($tagArray AS $item) {
                $this->internalCacheInstance->deleteItem($item->getKey());
            }
        }
    }

    /**
     * delete cache items tagged by at least one of several tags
     * @param array $tags
     * @return none
     */
    public function deleteItemsByTags($tags)
    {
        if (!is_array($tags)) $tags = array($tags);
        $this->internalCacheInstance->deleteItemsByTags($tags);
    }

    /**
     * delete cache items tagged with all tags
     * @param array $tags
     * @return none
     */
    public function deleteItemsByTagsAll($tags)
    {
        if (!is_array($tags)) $tags = array($tags);
        $this->internalCacheInstance->deleteItemsByTagsAll($tags);
    }

    /**
     * @param string $tag
     * @return mixed items
     */
    public function getItemsByTag($tag)
    {
        return $this->internalCacheInstance->getItemsByTag($tag);
    }

    /**
     * @param string $tag
     * @return string unique key
     */
    public function createKey($tag)
    {
        global $_USER;

        $key = $tag.'__' . $this->securityHash(true);
        return $key;
    }

    /**
     * @param boolean $byTheme
     * @param boolean $byLang
     * @return string
     */
    public function securityHash($byLang = false, $byTheme = false)
    {
        global $_GROUPS, $_RIGHTS, $_USER;

        $hash = '';

        $groups = implode(',',$_GROUPS);
        $rights = implode(',',$_RIGHTS);
        $hash = strtolower(md5($groups).md5($rights));

        if ($byTheme) {
            $hash .= '_'.$_USER['theme'];
        }
        if ($byLang) {
            $hash .= '_'.$_USER['language'];
        }
        return $hash;
    }
}

/* notes

# test memcache
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");
$version = $memcache->getVersion();
echo "Server's version: ".$version."<br/>\n";
exit;

# test memcacheD
$memcache = new Memcached;
$memcache->addServer("127.0.0.1", 11211);
$version = $memcache->getVersion();
echo "Server's version: ".$version['127.0.0.1:11211']."<br/>\n";


*/
