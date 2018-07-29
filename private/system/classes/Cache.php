<?php
/**
*   glFusion Cache Interface
*
*   @author     Mark R. Evans <mark@lglfusion.org>
*   @copyright  Copyright (c) 2017-2018 Mark R. Evans <mark@glfusion.org>
*   @package    glFusion
*   @version    0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

namespace glFusion;

/**
 * Class Cache
 *
 * @package glFusion
 */
final class Cache
{
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
        // nothing to do here...
    }


    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $tag = '', $ttl = null)
    {
        return false;
    }

    /**
     * @param string $key
     * @param string $tag
     * @return none
     */
    public function addTag($key, $tag)
    {
        return;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        return false;
    }

    /**
     * @param string[] $keys
     * @param null $default
     * @return \iterable
     */
    public function getMultiple($keys, $default = null)
    {
        return null;
    }

    /**
     * @param string[] $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        return false;
    }

    /**
     * @param string[] $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return false;
    }

    /**
     * @param string $key
     * @return int (time)
     */
    public function getModificationDate($key)
    {
        return 0;
    }

    /**
     * @param string $key
     * @return int (time)
     */
    public function getCreationDate($key)
    {
        return 0;
    }

    /**
     * delete cache items by tag
     * @param string $tag
     * @return none
     */
    public function deleteItemsByTag($iid)
    {
        global $TEMPLATE_OPTIONS;

        $iid = str_replace(array('..', '/', '\\', ':'), '', $iid);
        $iid = str_replace('-','_',$iid);
        $path_cache = substr($TEMPLATE_OPTIONS['path_cache'], 0, -1);
        CACHE_clean_directories($path_cache, 'instance__'.$iid);
    }

    /**
     * delete cache items tagged by at least one of several tags
     * @param array $tags
     * @return none
     */
    public function deleteItemsByTags($tags)
    {
        if (is_array($tags)) {
            foreach ($tags AS $tag) {
                self::deleteItemsByTag($tag);
            }
        }
    }

    /**
     * delete cache items tagged with all tags
     * @param array $tags
     * @return none
     */
    public function deleteItemsByTagsAll($tags)
    {

        return;
    }

    /**
     * @param string $tag
     * @return mixed items
     */
    public function getItemsByTag($tag)
    {
        return null;
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
        if (!self::$enabled) return null;
        return self::$cacheInstance->getDriverName();
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
