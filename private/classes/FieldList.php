<?php
/**
* glFusion CMS
*
* glFusion Data Filtering
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

namespace glFusion;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class FieldList
{

    public static function edit($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('edit','fieldlist.thtml');

        $t->set_block('edit','field-edit');
        if (isset($args['url'])) {
            $t->set_var('edit_url',$args['url']);
        } else {
            $t->set_var('edit_url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-edit','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-edit');
        return $t->finish($t->get_var('output'));
    }

    public static function approve($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('approve','fieldlist.thtml');

        $t->set_block('edit','field-approve');
        if (isset($args['url'])) {
            $t->set_var('approve_url',$args['url']);
        } else {
            $t->set_var('approve_url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-approve','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-approve');
        return $t->finish($t->get_var('output'));
    }

    public static function delete($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('edit','fieldlist.thtml');

        $t->unset_var('output');
        $t->unset_var('delete');

        $t->set_block('delete','field-delete');

        if (isset($args['delete_url'])) {
            $t->set_var('delete_url',$args['delete_url']);
        } else {
            $t->set_var('delete_url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-delete','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-delete',true);
        return $t->finish($t->get_var('output'));
    }

    public static function approveButton($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('approve','fieldlist.thtml');
        $t->set_block('approve-button','field-approve-button');

        $t->set_var('name',$args['name']);
        $t->set_var('text',$args['text']);

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-approve-button','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-approve-button',true);
        return $t->finish($t->get_var('output'));
    }


    public static function deleteButton($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('delete','fieldlist.thtml');
        $t->set_block('delete-button','field-delete-button');

        $t->set_var('name',$args['name']);
        $t->set_var('text',$args['text']);

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-delete-button','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-delete-button',true);
        return $t->finish($t->get_var('output'));
    }

    public static function emailButton($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('email','fieldlist.thtml');
        $t->set_block('email-button','field-email-button');

        $t->set_var('name',$args['name']);
        $t->set_var('text',$args['text']);

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-email-button','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-email-button',true);
        return $t->finish($t->get_var('output'));
    }

    public static function userPhoto()
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('userphoto','fieldlist.thtml');
        $t->set_block('userphoto','field-userphoto');
        $t->parse('output','field-userphoto',true);
        return $t->finish($t->get_var('output'));
    }


    public static function checkmark($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('field','fieldlist.thtml');

        $t->set_block('field','field-checkmark');

        if (isset($args['active']) && $args['active'] === true) {
            $t->set_var('style','active');
        } else {
            $t->set_var('style','disabled');
        }

        $t->parse('output','field-checkmark',true);
        return $t->finish($t->get_var('output'));
    }

    public static function minus($args = array())
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('field','fieldlist.thtml');

        $t->set_block('field','field-minus');

        $t->parse('output','field-minus',true);
        return $t->finish($t->get_var('output'));
    }



    public static function copy($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('copy','fieldlist.thtml');

        $t->set_block('copy','field-copy');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-copy','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-copy');
        return $t->finish($t->get_var('output'));
    }

    public static function up($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('up','fieldlist.thtml');

        $t->set_block('up','field-up');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-up','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-up');
        return $t->finish($t->get_var('output'));
    }

    public static function down($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('down','fieldlist.thtml');

        $t->set_block('down','field-down');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-down','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-down');
        return $t->finish($t->get_var('output'));
    }

    public static function email($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('email','fieldlist.thtml');

        $t->set_block('email','field-email');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-email','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-email');
        return $t->finish($t->get_var('output'));
    }

    public static function user($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('user','fieldlist.thtml');

        $t->set_block('user','field-user');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-email','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-user');
        return $t->finish($t->get_var('output'));
    }



    public static function editusers($args = array())
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('edit','fieldlist.thtml');

        $t->set_block('edit','field-edit-users');
        if (isset($args['url'])) {
            $t->set_var('edit_url',$args['url']);
        } else {
            $t->set_var('edit_url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-edit-users','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-edit-users');
        return $t->finish($t->get_var('output'));
    }

    public static function info($args)
    {
        global $_CONF;

        if (!isset($args['title'])) {
            return '';
        }

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('field','fieldlist.thtml');

        $t->set_block('field','field-info');
        if (isset($args['title'])) {
            $t->set_var('title',$args['title']);
        }

        $t->set_var('tooltip_style',COM_getToolTipStyle());

        $t->parse('output','field-info');
        return $t->finish($t->get_var('output'));
    }

    public static function cog($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('cog','fieldlist.thtml');

        $t->set_block('cog','field-cog');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-cog','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-cog');
        return $t->finish($t->get_var('output'));
    }

    public static function ping($args)
    {
        global $_CONF;

        $t = new \Template($_CONF['path_layout'].'/admin/lists/');
        $t->set_file('ping','fieldlist.thtml');

        $t->set_block('ping','field-ping');
        if (isset($args['url'])) {
            $t->set_var('url',$args['url']);
        } else {
            $t->set_var('url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-edit','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-ping');
        return $t->finish($t->get_var('output'));
    }
}
?>
