<?php
/**
 * Field list extension to the base glFusion FieldList class.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     filemgmt
 * @version     v1.9.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 * FieldList extension class.
 * @package photocomp
 */
class FieldList extends \glFusion\FieldList
{
    /**
     * Return a cached template object to avoid repetitive path lookups.
     *
     * @return  object      Template object
     */
    protected static function init()
    {
        global $_CONF;

        static $t = NULL;

        if ($t === NULL) {
            $t = new \Template($_CONF['path'] . 'plugins/filemgmt/templates');
            $t->set_file('field', 'fieldlist.thtml');
        } else {
            $t->unset_var('output');
            $t->unset_var('attributes');
        }
        return $t;
    }


    /**
     * Create the icon and link for the entry payment status popup.
     *
     * @param   array   $args   Arguments
     * @return  string      HTML for the admin field
     */
    public static function ignore($args)
    {
        global $_CONF, $_FM_CONF;

        $t = self::init();
        $t->set_block('field','field-ignore');

        if (isset($args['url'])) {
            $t->set_var('url', $args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-ignore', 'attr', 'attributes');
            foreach($args['attr'] as $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes', 'attr', true);
            }
        }
        $t->parse('output', 'field-ignore', true);
        return $t->finish($t->get_var('output'));
    }

}
