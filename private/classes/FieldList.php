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
            $t = new \Template($_CONF['path_layout'] . '/admin/lists/');
            $t->set_file('field', 'fieldlist.thtml');
        } else {
            $t->unset_var('output');
            $t->unset_var('attributes');
        }
        return $t;
    }


    public static function edit($args)
    {
        $t = self::init();
        $t->set_block('field','field-edit');
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
        $t = self::init();
        $t->set_block('field','field-approve');

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
        $t = self::init();
        $t->set_block('field','field-delete');

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

    public static function refresh($args)
    {
        $t = self::init();
        $t->set_block('field','field-refresh');

        if (isset($args['url'])) {
            $t->set_var('refresh_url',$args['url']);
        } else {
            $t->set_var('refresh_url','#');
        }

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-refresh','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-refresh',true);
        return $t->finish($t->get_var('output'));
    }

    public static function approveButton($args)
    {
        $t = self::init();
        $t->set_block('field','field-approve-button');

        $t->set_var('button_name',$args['name']);
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
        $t = self::init();
        $t->set_block('field','field-delete-button');

        $t->set_var('button_name',$args['name']);
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
        $t = self::init();
        $t->set_block('field','field-email-button');

        $t->set_var('button_name',$args['name']);
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
        $t = self::init();
        $t->set_block('field','field-userphoto');
        $t->parse('output','field-userphoto',true);
        return $t->finish($t->get_var('output'));
    }


    public static function checkmark($args)
    {
        $t = self::init();
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
        $t = self::init();
        $t->set_block('field','field-minus');

        $t->parse('output','field-minus',true);
        return $t->finish($t->get_var('output'));
    }



    public static function copy($args)
    {
        $t = self::init();
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
        $t = self::init();
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
        $t = self::init();
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
        $t = self::init();
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
        $t = self::init();
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
        $t = self::init();
        $t->set_block('field','field-edit-users');

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
        if (!isset($args['title'])) {
            return '';
        }

        $t = self::init();
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
        $t = self::init();
        $t->set_block('field','field-cog');

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
        $t = self::init();
        $t->set_block('field','field-ping');

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

    public static function rootUser($args)
    {
        $t = self::init();
        $t->set_block('field','field-root-user');

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-root-user','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-root-user');
        return $t->finish($t->get_var('output'));
    }


    public static function checkbox($args)
    {
        $t = self::init();
        $t->set_block('field','field-checkbox');

        // Go through the required or special options
        $t->set_block('field', 'attr', 'attributes');
        foreach ($args as $name => $value) {
            switch ($name) {
            case 'checked':
            case 'disabled':
                if ($value) {
                    $value = $name;
                } else {
                    continue 2;
                }
                break;
            }
            $t->set_var(array(
                'name' => $name,
                'value' => $value,
            ) );
            $t->parse('attributes', 'attr', true);
        }
        $t->parse('output', 'field-checkbox');
        return $t->finish($t->get_var('output'));
    }


    public static function radio($args)
    {
        $t = self::init();
        $t->set_block('field','field-radio');

        // Go through the required or special options
        $t->set_block('field', 'attr', 'attributes');
        foreach ($args as $name=>$value) {
            switch ($name) {
            case 'checked':
            case 'disabled':
                if ($value) {
                    $value = $name;
                } else {
                    continue 2;
                }
                break;
            }
            $t->set_var(array(
                'name' => $name,
                'value' => $value,
            ) );
            $t->parse('attributes', 'attr', true);
        }
        $t->parse('output', 'field-radio');
        return $t->finish($t->get_var('output'));
    }

    /**
     * Create a selection dropdown.
     * Options can be in a string named `option_list` or an array of
     * separate properties.
     *
     *  $opts = array(
     *      'name' => 'testoption',
     *      'onchange' => "alert('here');",
     *      'options' => array(
     *          'option1' => array(
     *              'disabled' => true,
     *              'value' => 'value1',
     *          ),
     *          'option2' => array(
     *              'selected' => 'something',
     *              'value' => 'value2',
     *          ),
     *          'option3' => array(
     *              'selected' => '',
     *              'value' => 'XXXXX',
     *          ),
     *      )
     *  );
     *
     *  @param  array   $args   Array of properties to use
     *  @return string      HTML for select element
     */
    public static function select($args)
    {
        if (!isset($args['options']) && !isset($args['option_list'])) {
            return '';
        }

        $t = self::init();
        $t->set_block('field','field-select');

        $def_opts = array(
            'value' => '',
            'selected' => false,
            'disabed' => false,
        );

        // Create the main selection element.
        $t->set_block('field', 'attr', 'attributes');
        foreach ($args as $name=>$value) {
            switch ($name) {
            case 'options':
            case 'option_list':
                continue 2;
            default:
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value,
                ) );
                $t->parse('attributes', 'attr', true);
                break;
            }
        }

        // Now loop through the options.
        // If an option_list string was supplied, use it as-is.
        // Otherwise, construct the option strings from the supplied array.
        if (isset($args['option_list'])) {
            $t->set_var('option_list', $args['option_list']);
        } elseif (isset($args['options']) && is_array($args['options'])) {
            $t->set_block('select', 'options', 'opts');
            foreach ($args['options'] as $name=>$data) {
                $t->set_var('opt_name', $name);
                // Go through the required or special options
                foreach ($def_opts as $optname=>$def_val) {
                    if (isset($data[$optname])) {
                        $t->set_var($optname, $data[$optname]);
                        unset($data[$optname]);
                    } else {
                        $t->set_var($optname, $def_val);
                    }
                }
                // Now go through the remaining supplied args for this option
                $str = '';
                foreach ($data as $name=>$value) {
                    $str .= "$name=\"$value\" ";
                }
                $t->set_var('other', $str);
                $t->parse('opts', 'options', true);
            }
        }
        $t->parse('output', 'field-select');
        $t->clear_var('opts');
        return $t->finish($t->get_var('output'));
    }

    public static function button($args)
    {
        $def_args = array(
            'name' => '',
            'value' => '',
            'size' => '',   // mini
            'style' => '',  // success, danger, etc.
            'type' => '',   // submit, reset, etc.
            'class' => '',  // additional classes
        );
        $args = array_merge($def_args, $args);

        $t = self::init();
        $t->set_block('field','field-button');

        $t->set_var(array(
            'button_name' => $args['name'],
            'button-value' => $args['value'],
            'size' => $args['size'],
            'style' => $args['style'],
            'type' => $args['type'],
            'other_cls' => $args['class'],
        ) );
        $t->set_var('text',$args['text']);

        if (isset($args['attr']) && is_array($args['attr'])) {
            $t->set_block('field-button','attr','attributes');
            foreach($args['attr'] AS $name => $value) {
                $t->set_var(array(
                    'name' => $name,
                    'value' => $value)
                );
                $t->parse('attributes','attr',true);
            }
        }
        $t->parse('output','field-button',true);
        return $t->finish($t->get_var('output'));
    }

}
?>
