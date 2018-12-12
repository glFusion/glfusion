<?php
/**
*   glFusion phpFastCache Interface
*
*   @author     Mark R. Evans <mark@lglfusion.org>
*   @copyright  Copyright (c) 2017-2018 Mark R. Evans <mark@glfusion.org>
*   @package    glFusion
*   @version    0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

namespace glFusion\Cache;

use \Phpfastcache\CacheManager;
use \Phpfastcache\Config\Config;
use \Phpfastcache\Config\ConfigurationOption;

use \Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use \Phpfastcache\Exceptions\{
    PhpfastcacheDriverCheckException, PhpfastcacheInvalidArgumentException, PhpfastcacheLogicException, PhpfastcacheRootException, PhpfastcacheSimpleCacheException
};
use \Phpfastcache\Helper\Psr\SimpleCache\CacheInterface;

use \glFusion\Log\Log;

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
     * @var $namespace
     */

    private $namespace = '';

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
    private function __construct()
    {
        global $_CONF;

        $success = true;

        // validations
        if (!isset($_CONF['cache_driver'])) $_CONF['cache_driver'] = 'files';
        if (!isset($_CONF['cache_host'])) $_CONF['cache_host'] = '127.0.0.1';
        if (!isset($_CONF['cache_port'])) $_CONF['cache_port'] = ($_CONF['cache_driver'] == 'redis') ? 6379 : 11211;
        if (!isset($_CONF['cache_password'])) $_CONF['cache_password'] = '';
        if (!isset($_CONF['cache_database'])) $_CONF['cache_database'] = '0';
        if (!isset($_CONF['cache_timeout'])) $_CONF['cache_timeout'] = 10;

        $validArray = configmanager_select_cache_driver_helper();
        if (!in_array($_CONF['cache_driver'],$validArray)) {
            $_CONF['cache_driver'] = 'files';
        }

        if ($_CONF['cache_driver'] == 'files') {
            $this->namespace = '';
        } else {
            $this->namespace = base_convert(md5($_CONF['site_name']), 10, 36);
        }

        switch ($_CONF['cache_driver']) {

            case 'memcache' :

                if ($_CONF['cache_memcached_username'] != '') {
                    $servers['saslUsername'] = $_CONF['cache_memcached_username'];
                }
                if ($_CONF['cache_memcached_password'] != '') {
                    $servers['saslPassword'] = $_CONF['cache_memcached_password'];
                }
                $servers['host'] = $_CONF['cache_host'];
                $servers['port'] = $_CONF['cache_port'];

                try {
                    $this->internalCacheInstance = CacheManager::getInstance('memcache',new \Phpfastcache\Drivers\Memcache\Config([
                        'host' =>$_CONF['cache_host'],
                        'port' => (int) $_CONF['cache_port'],
                        'servers' => array($servers),
                        'itemDetailedDate' => true
                    ]));
                } catch (\Phpfastcache\Exceptions\PhpfastcacheDriverException $e) {
                    $success = false;
                }
                break;

            case 'memcached' :
                $servers = array();
                if ($_CONF['cache_memcached_username'] != '') {
                    $servers['saslUsername'] = $_CONF['cache_memcached_username'];
                }
                if ($_CONF['cache_memcached_password'] != '') {
                    $servers['saslPassword'] = $_CONF['cache_memcached_password'];
                }
                $servers['host'] = $_CONF['cache_host'];
                $servers['port'] = (int) $_CONF['cache_port'];

                try {
                    $this->internalCacheInstance = CacheManager::getInstance('memcached',new \Phpfastcache\Drivers\Memcached\Config([
                        'host' =>$_CONF['cache_host'],
                        'port' => (int) $_CONF['cache_port'],
                        'servers' => array($servers),
                        'itemDetailedDate' => true
                    ]));
                } catch (\Phpfastcache\Exceptions\PhpfastcacheDriverException $e) {
                    $success = false;
                }
                break;

            case 'redis' :
                if ($_CONF['cache_redis_socket'] != '') {
                    $configInfo['path'] = $_CONF['cache_redis_socket'];
                } else {
                    $configInfo['host'] = $_CONF['cache_host'];
                    $configInfo['port'] = (int) $_CONF['cache_port'];
                    $configInfo['database'] = (int) $_CONF['cache_redis_database'];
                }
                if ($_CONF['cache_redis_password'] != '') {
                    $configInfo['password'] = $_CONF['cache_redis_password'];
                }
                if ($_CONF['cache_timeout'] > 0) {
                    $configInfo['timeout'] = (int) $_CONF['cache_timeout'];
                }
                $configInfo['itemDetailedDate'] = true;

                try {
                    $connected = true;
                    $redis = new \Redis();
                    if ($_CONF['cache_redis_socket'] != '') {
                        $redis->connect($_CONF['cache_redis_socket']);
                    } else {
                        $redis->connect($_CONF['cache_host'], $_CONF['cache_port']);
                    }
                } catch(\RedisException $e) {
                    $success = false;
                }

                if ($success == true) {
                    $this->internalCacheInstance = CacheManager::getInstance('redis', new \Phpfastcache\Drivers\Redis\Config(
                        $configInfo
                    ));
                }
                break;

            case 'apcu' :
                $this->internalCacheInstance = CacheManager::getInstance('apcu',new \Phpfastcache\Drivers\Apcu\Config([
                    'itemDetailedDate' => true
                ]));
                break;

            case 'files' :
                CacheManager::setDefaultConfig(new Config([
                  "path" => $_CONF['path'].'data/cache/',
                  "itemDetailedDate" => true
                ]));
                $this->internalCacheInstance = CacheManager::getInstance('files');
                break;

            case 'Devnull' :
                CacheManager::setDefaultConfig(new Config([
                  "path" => $_CONF['path'].'data/cache/',
                  "itemDetailedDate" => true
                ]));
                $this->internalCacheInstance = CacheManager::getInstance('Devnull');
                break;
        }


        if ($success == false) {
            // fallback to files
            CacheManager::setDefaultConfig(new Config([
              "path" => $_CONF['path'].'data/cache/',
              "itemDetailedDate" => true
            ]));
            $this->internalCacheInstance = CacheManager::getInstance('files');
//            $_CONF['cache_driver'] = 'files';
        }
    }


    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function get($key, $default = null)
    {
        global $_CONF;
        if ($_CONF['cache_driver'] == 'Devnull') return null;

        if ($this->namespace != '') $key = $this->namespace.'_'.$key;
        $key = $this->validateKeyName($key);

        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            if (!$cacheItem->isExpired() && $cacheItem->get() !== null) {
                return $cacheItem->get();
            } else {
                return $default;
            }
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function set($key, $value, $tag = '', $ttl = null)
    {
        global $_CONF;

        if ($this->namespace != '') $key = $this->namespace.'_'.$key;
        $key = $this->validateKeyName($key);

        try {
            $cacheItem = $this->internalCacheInstance
              ->getItem($key)
              ->set($value);
            if (is_int($ttl) && $ttl <= 0) {
                $cacheItem->expiresAt((new \DateTime('@0')));
            } elseif (is_int($ttl) || $ttl instanceof \DateInterval) {
                $cacheItem->expiresAfter($ttl);
            }
            if (is_array($tag)) {
                $nsTags = array_map(
                    function($tag) {
                        $tag = (string) $tag;
                        if ($this->namespace != '') $tag = $this->namespace.'_'.$tag;
                        return $tag;
                    },$tag);

                $cacheItem->addTags($nsTags);
            } elseif ($tag != '' ) {
                if ($this->namespace != '') $tag = $this->namespace.'_'.$tag;
                $cacheItem->addTag($tag);
            }
            return $this->internalCacheInstance->save($cacheItem);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @param string $tag
     * @return none
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function addTag($key, $tag)
    {
        if ($this->namespace != '') {
            $key = $this->namespace.'_'.$key;
            $tag = $this->namespace.'_'.$tag;
            $key = $this->validateKeyName($key);
            $tag = $this->validateKeyName($tag);
        }
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            $cacheItem->addTag($tag);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function delete($key)
    {
        if ($this->namespace != '') $key = $this->namespace.'_'.$key;
        $key = $this->validateKeyName($key);

        try {
            return $this->internalCacheInstance->deleteItem($key);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @return bool
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function clear()
    {
        try {
            return $this->internalCacheInstance->clear();
        } catch (phpFastCacheRootException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $keys
     * @param null $default
     * @return \iterable
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function getMultiple($keys, $default = null)
    {
        $nsKeys = array_map(
            function($key) {
                if ($this->namespace != '') $key = $this->namespace.'_'.$key;
                $key = $this->validateKeyName($key);
                return $key;
            },$keys);

        try {
            return array_map(function (ExtendedCacheItemInterface $item) {
                return $item->get();
            }, $this->internalCacheInstance->getItems($nsKeys));
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function setMultiple($values, $ttl = null)
    {
        try {
            foreach ($values as $key => $value) {
                if ($this->namespace != '') $key = $this->namespace.'_'.$key;

                $key = $this->validateKeyName($key);

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
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string[] $keys
     * @return bool
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function deleteMultiple($keys)
    {
        $nsKeys = array_map(
            function($key) {
                if ($this->namespace != '') $key = $this->namespace.'_'.$key;
                $key = $this->validateKeyName($key);
                return $key;
            },$keys);

        try {
            return $this->internalCacheInstance->deleteItems($nsKeys);
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws \phpFastCache\Exceptions\\Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function has($key)
    {
        global $_CONF;
        if ($_CONF['cache_driver'] == 'Devnull') {
            return false;
        }
        if ($this->namespace != '') $key = $this->namespace.'_'.$key;
        $key = $this->validateKeyName($key);
        try {
            $cacheItem = $this->internalCacheInstance->getItem($key);
            return $cacheItem->isHit() && !$cacheItem->isExpired();
        } catch (PhpfastcacheInvalidArgumentException $e) {
            throw new \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException($e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $key
     * @return int (time)
     */
    public function getModificationDate($key)
    {
        if ($this->namespace != '') $key = $this->namespace.'_'.$key;
        $key = $this->validateKeyName($key);
        $cacheItem = $this->internalCacheInstance->getItem($key);
        $modDate = $cacheItem->getModificationDate();
        if (is_object($modDate) && isset($modDate->date)) {
            return strtotime($modDate->date);
        }
        return 0;
    }

    /**
     * @param string $key
     * @return int (time)
     */
    public function getCreationDate($key)
    {
        if ($this->namespace != '') $key = $this->namespace.'_'.$key;
        $key = $this->validateKeyName($key);
        $cacheItem = $this->internalCacheInstance->getItem($key);
        $createDate = $cacheItem->getCreationDate();
        if (is_object($createDate) && isset($createDate->date)) {
            return strtotime($createDate->date);
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
        $tagArray = explode(',',$tag);
        $this->deleteItemsByTags($tagArray);
        return;
    }

    /**
     * delete cache items tagged by at least one of several tags
     * @param array $tags
     * @return none
     */
    public function deleteItemsByTags($tags)
    {
        if (!is_array($tags)) $tags = array($tags);

        $nsTags = array_map(
            function($tag) {
                if ($this->namespace != '') $tag = $this->namespace.'_'.$tag;
                $tag = $this->validateKeyName($tag);
                return $tag;
            },$tags);

        $this->internalCacheInstance->deleteItemsByTags($nsTags);
    }

    /**
     * delete cache items tagged with all tags
     * @param array $tags
     * @return none
     */
    public function deleteItemsByTagsAll($tags)
    {
        if (!is_array($tags)) $tags = array($tags);

        $nsTags = array_map(
            function($tag) {
                if ($this->namespace != '') $tag = $this->namespace.'_'.$tag;
                $tag = $this->validateKeyName($tag);
                return $tag;
            },$tags);

        $this->internalCacheInstance->deleteItemsByTagsAll($nsTags);
    }

    /**
     * @param string $tag
     * @return mixed items
     */
    public function getItemsByTag($tag)
    {
        if ($this->namespace != '') $tag = $this->namespace.'_'.$tag;
        $tag = $this->validateKeyName($tag);
        return $this->internalCacheInstance->getItemsByTag($tag);
    }

    /**
     * @param string $tag
     * @return string unique key
     */
    public function createKey($tag)
    {
        global $_USER;

        $tag = $this->validateKeyName($tag);

        $key = $tag.'__' . $this->securityHash(true,true);
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

    public function getDriverName()
    {
        return $this->internalCacheInstance->getDriverName();
    }

    /**
     * @param string $str
     * @return string
     */
    private function validateKeyName($str)
    {
        $invalid = array('{','}','(',')','/','\\','@',':');
        return str_replace($invalid,'_',$str);
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
