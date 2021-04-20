<?php
/**
* glFusion CMS - CKEditor Plugin
*
* glFusion Integration Functions
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

// load the language file(s), including custom strings if any

$langfile = $_CONF['path'].'plugins/ckeditor/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'].'plugins/ckeditor/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/ckeditor/language/english_utf-8.php';
    $custfile = $_CONF['path'].'plugins/ckeditor/language/custom/english_utf-8.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}
if (!isset($_CK_CONF['enable_story'])) {
    $tm_config = config::get_instance();
    $_CK_CONF = $tm_config->get_config('ckeditor');
}
require_once $_CONF['path'].'plugins/ckeditor/ckeditor.php';

if (defined ('DEMO_MODE')) {
    $_CK_CONF['filemanager_browse_only'] = true;
}

$_editorRegistration = array();

/**
 * Returns URL for the plugin's icon
 *
 * @return  string  URL to icon
 */
function plugin_geticon_ckeditor ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/ckeditor/ckeditor.png';
}


/**
 * Set template variable with ckeditor editor
 *
 * This plugin uses the 'templatesetvars' hook to insert the WYSIWYG
 * editor code into the template. This allows users to create
 * or edit content of type specified ($ctype). The template handle
 * relating to the input/edit form for the content type is passed
 * to allow the corresponding ckeditor template variable to be set.
 *
 * @param   string  Template type - comment, registration, etc.
 * @param   class   Template class handle
 * @return  string  blank
 */
function plugin_templatesetvars_ckeditor($ctype, &$template)
{
    global $_CONF, $_CK_CONF, $_USER, $_TABLES, $LANG_CK00, $_editorRegistration, $_SESSION;

    $setupEditor = 0;
    $templateFile = 'ckeditor.thtml';
    $templatePath = $_CONF['path'] . 'plugins/ckeditor/templates';

    switch( $ctype ) {
        case 'comment' :
            if ( $_CK_CONF['enable_comment'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_comment.thtml';
            break;
        case 'storyeditor' :
            if ( $_CK_CONF['enable_story'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_story.thtml';
            if (!isset($_CONF['default_story_editor'])) {
                $template->set_var('default_visual_editor',true);
            } else if ($_CONF['default_story_editor'] == 1 ) {
                $template->set_var('default_visual_editor',true);
            }
            break;
        case 'story' :
            if ( $_CK_CONF['enable_submitstory'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_submitstory.thtml';
            break;
        case 'contact' :
            if ( $_CK_CONF['enable_contact'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_contact.thtml';
            break;
        case 'emailstory' :
            if ( $_CK_CONF['enable_emailstory'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_email.thtml';
            break;
        case 'sp_editor' :
            if ( $_CK_CONF['enable_sp'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_sp.thtml';
            break;

        case 'blockeditor' :
            if ( $_CK_CONF['enable_block'] != 1 ) {
                $template->unset_var('wysiwyg');
                return;
            }
            $templateFile = 'ckeditor_block.thtml';
            break;

        case 'general' :
            $templateFile = 'ckeditor.thtml';
            break;
        default :
            foreach ( $_editorRegistration AS $_pi => $data ) {
                if ( $data['function'] == $ctype ) {
                    $templateFile = $data['template'];
                    $templatePath = $_CONF['path'].'plugins/'.$_pi.'/templates';
                    $setupEditor = 1;
                    break;
                }
            }
            if ( $setupEditor == 0 ) {
//                $template->unset_var('wysiwyg');
                return '';
            }
            break;
    }

    $inRoot = SEC_inGroup('Root');

    $urlparts = parse_url($_CONF['site_url']);
    if ( isset($urlparts['path']) ) {
        $relRoot = $urlparts['path'];
        $relRoot = trim($relRoot);
        if ( $relRoot[strlen($relRoot)-1] != '/' ) {
            $relRoot = $relRoot.'/';
        }
    } else {
        $relRoot = '/';
    }

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    // removes the leading '/'
    $imagePath = substr($_CONF['path_images'],strlen($_CONF['path_html']));
    $imagePath = rtrim($imagePath, '/\\');

    if ($inRoot ) {
        $_SESSION['fileman_files_root'] = $relRoot . $imagePath;
    } else {
        $userImagePath = $imagePath . '/library/Image';
        $_SESSION['fileman_files_root'] = $relRoot . $userImagePath;

        if ( $_CK_CONF['filemanager_per_user_dir'] ) {
            $filePath = $relRoot . $imagePath.'/library/userfiles/'.$uid;

            $_SESSION['fileman_files_root'] = $filePath;

            if ( !is_dir($_CONF['path_html'].$imagePath.'/library/userfiles/'.$uid) ) {
                $rc = @mkdir($_CONF['path_html'].$imagePath.'/library/userfiles/'.$uid, 0755, true);
                if ( $rc === false ) {
                    $_CK_CONF['filemanager_per_user_dir'] = false;
                    $_SESSION['fileman_files_root'] = $relRoot . $userImagePath;
                }
            }
        }
    }

    $T = new Template($templatePath);
    $T->set_file('page', $templateFile);
    if ( file_exists($_CONF['path_layout'].'ck_styles.js') ) {
        $T->set_var('styleset',"stylesSet: '".$_USER['theme'].":".$_CONF['layout_url']."/ck_styles.js',");
    }
    list($cacheFile,$cacheURL) = COM_getStyleCacheLocation();
    $T->set_var(array(
                'theme' => $_USER['theme'],
                'path_html' => $_CONF['path_html'],
                'css_url'   => $cacheURL,
                ));
    if ( !extension_loaded('mbstring') ) {
        $T->set_var('mbmissing',true);
        Log::write('system',Log::WARNING,'WARNING: mbstring extension not installed - disabling FileMan plugin in CKEditor');
    } else {
        $T->unset_var('mbmissing');
    }
// put a check for story editor...
    if (!isset($_CONF['default_story_editor'])) {
        $T->set_var('default_visual_editor',true);
    } else if ($_CONF['default_story_editor'] == 1 ) {
        $T->set_var('default_visual_editor',true);
    }

    PLG_templateSetVars('ckeditor',$T);
    $T->parse('output','page');
    $wysiwyg = $T->finish($T->get_var('output'));
    $template->set_var(array(
        'wysiwyg'   => $wysiwyg,
        'html_editor' => $LANG_CK00['html'],
        'visual_editor' => $LANG_CK00['visual'],
    ));

    return '';
}

/**
 * Return plugin's version information
 *
 * @return  string   plugin version number
 */
function plugin_chkVersion_ckeditor() {
    global $_CK_CONF;

    return $_CK_CONF['pi_version'];
}

/**
 * Upgrade plugin
 *
 * @return  string   plugin version number
 */
function plugin_upgrade_ckeditor() {
    global $_CONF, $_TABLES;

    require_once $_CONF['path'].'plugins/ckeditor/upgrade.php';

    return ckeditor_upgrade();
}

function plugin_requestEditor_ckeditor($plugin, $ctype, $template)
{
    global $_editorRegistration;

    $_editorRegistration[$plugin] = array('function' => $ctype,'template' => $template);
    return true;
}

function plugin_getEditorType_ckeditor()
{
    return 'ckeditor';
}

function plugin_configmanager_filemanager_fileroot_ckeditor_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
?>