<?php
/**
* glFusion CMS
*
* glFusion DBAL Cache Driver
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*
*/

namespace glFusion;

use \Doctrine\Common\Cache\Cache;

class glFusionCache implements Cache
{
    public function fetch($id)
    {
        // fetch $id from the cache
        $c = \glFusion\Cache::getInstance();
        return $c->get($id);
    }

    public function contains($id)
    {
        // check if $id exists in the cache
        $c = \glFusion\Cache::getInstance();
        return $c->has($id);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $c = \glFusion\Cache::getInstance();
        $tag = '';
        // parse out the first part of the ID upto the underscore and
        // use this as the tag.
        $pos = strpos($id,'_');
        if ($pos > 0) {
            $tag = substr($id,0,$pos);
        }
        return $c->set($id,$data,$tag,$lifeTime);
    }

    public function delete($id)
    {
        // delete $id from the cache
        $c = \glFusion\Cache::getInstance();
        return $c->delete($id);
    }

    public function getStats()
    {
        // get cache stats
        return null;
    }
}
