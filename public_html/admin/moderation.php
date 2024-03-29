<?php
/**
* glFusion CMS
*
* glFusion submission queue tools
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark Howard     mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs         - tony AT tonybibbs DOT com
*  Mark Limburg       - mlimburg AT users DOT sourceforge DOT net
*  Jason Whittenburg  - jwhitten AT securitygeeks DOT com
*  Dirk Haun          - dirk AT haun-online DOT de
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Article\Article;
use \glFusion\Log\Log;
use \glFusion\Cache\Cache;
use \glFusion\Admin\AdminAction;
use \glFusion\FieldList;

$display = '';
if (!SEC_isModerator()) {
    Log::logAccessViolation('Moderation Admin');
    COM_setMessage(200);
    $display = COM_refresh($_CONF['site_url']);
    echo $display;
    exit;
}

USES_lib_admin();
USES_lib_user();
USES_lib_comment();

/**
* Returns the number of user submissions
*
* Similar to plugin_submissioncount_{plugin} for object type = user
*
*/
function MODERATE_submissioncount_user()
{
    global $_TABLES;

    return DB_count($_TABLES['users'],'status',USER_ACCOUNT_AWAITING_APPROVAL);
}

/**
* Returns whether the current user can moderate user submissions or not
*
* Similar to plugin_ismoderator_{plugin} for object type = user
*
*/
function MODERATE_ismoderator_user()
{
    return (SEC_hasRights('user.edit') AND SEC_hasRights('user.delete'));
}


/**
 * Returns formatted field values for the moderation lists
 *
 */
function MODERATE_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG28, $LANG29, $_IMAGE_TYPE;

    $retval = '';

    $type = '';
    if (isset($A['_type_']) && !empty($A['_type_'])) {
        $type = $A['_type_'];
    } else {
        return $retval; // we can't work without an item type
    }

    $dt = new Date('now',$_USER['tzid']);

    $field = $fieldname;
    $field = ($fieldname == 0 ) ? 'edit' : $field;
    $field = ($type == 'user' && $fieldname == 1) ? 'user' : $field;
    $field = ($type == 'article' && $fieldname == 2) ? 'day' : $field;
    $field = ($type == 'article' && $fieldname == 3) ? 'tid' : $field;
    $field = ($type == 'user' && $fieldname == 3) ? 'email' : $field;
    $field = ($type <> 'user' && $fieldname == 4) ? 'uid' : $field;
    $field = ($type == 'user' && $fieldname == 4) ? 'day' : $field;

    switch ($field) {

        case 'edit':
            $retval = FieldList::edit(
                array(
                    'url' => $A['edit'],
                )
            );
            break;

        case 'user':
            $retval = FieldList::user(array()) . '&nbsp;' . $fieldvalue;
            break;

        case 'day':
            $dt->setTimeStamp($A['day']);
            $retval = $dt->format($_CONF['daytime'],true);
            break;

        case 'tid':
            $retval = DB_getItem($_TABLES['topics'], 'topic',
                                 "tid = '".DB_escapeString($A['tid'])."'");
            break;

        case 'uid':
            if ( !isset($A['uid']) ) {
                $A['uid'] = 1;
            }

            // lookup the username from the uid
            $username = DB_getItem($_TABLES['users'], 'username',
                                   "uid = ". (int) $A['uid']);

            if ($A['uid'] == 1) { // anonymous user
                $retval = $icon_arr['greyuser']
                            . '&nbsp;&nbsp;'
                            . '<span style="vertical-align:top">' . $username . '</span>';
            } else {
                $retval = FieldList::editusers(
                    array(
                        'url' => $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'],
                        'attr' => array(
                            'title' => $LANG28[108],
                        )
                    )
                        );


                $attr['title'] = $LANG28[108];
                $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' .  $A['uid'];
                $retval .= COM_createLink($username, $url, $attr);
            }
            break;

        case 'email':
            $retval = FieldList::email(array(
                'url' => 'mailto:' . $fieldvalue,
                'attr' => array(
                    'title' => $LANG28[111],
                )
            ));
            $retval .= '&nbsp;&nbsp;';
            $attr['title'] = $LANG28[99];
            $url = $_CONF['site_admin_url'] . '/mail.php?uid=' . $A['uid'];
            $retval .= COM_createLink($fieldvalue, $url, $attr);
            break;

        case 'approve':
            $retval = FieldList::approve(array(
                'url' =>  $_CONF['site_admin_url'].'/moderation.php'.'?approve=x'.'&amp;type='.$A['_type_'].'&amp;id=' . $A[0].'&amp;'.CSRF_TOKEN.'='.$token,
                'attr' => array(
                    'title' => $LANG29[1],
                    'onclick' => 'return confirm(\'' . $LANG29[48] . '\');'
                )
            ));
            break;

        case 'delete':
            $retval = FieldList::delete(array(
                'delete_url' => $_CONF['site_admin_url'] . '/moderation.php'.'?delete=x'.'&amp;type=' . $A['_type_'].'&amp;id=' . $A[0].'&amp;' . CSRF_TOKEN . '=' . $token,
                'attr' => array(
                    'title' => $LANG_ADMIN['delete'],
                    'onclick' => 'return confirm(\'' . $LANG29[49] . '\');'
                )
            ));
            break;

        case 'preview' :
            $retval = '
                <a href="#cmtpreview'.$A['cid'].'" rel="modal:open">'.$LANG_ADMIN['preview'].'</a>
                <div id="cmtpreview'.$A['cid'].'" style="display:none;">
                '.$fieldvalue.'
                </div>';
            break;

        default:
            $retval = COM_makeClickableLinks($fieldvalue);
            break;
    }

    return $retval;
}

/**
* Moderates a single item
*
* This will actually perform moderation (approve or delete) one or more items
*
* @param    string  $action     Action to perform ('delete' or 'approve')
* @param    string  $type       Type of item ('user', 'article', etc.)
* @param    string  $id         ID of item to approve or delete
* @return   string              HTML for "command and control" page
*
*/
function MODERATE_item($action='', $type='', $id='')
{
    global $_CONF, $_TABLES, $MESSAGE, $LANG_ADM_ACTIONS;

    $retval = '';

    $db = Database::getInstance();

    if (empty($action)) {
        Log::write('system',Log::ERROR,"Submissions Error: An attempt was made to moderate an item with a null action.");
        // null action
        $retval .= "Submissions Error: An attempt was made to moderate an item with a null action.";
        return $retval;
    }
    if (empty($type)) {
        // null item type
        Log::write('system',Log::ERROR,"Submissions Error: An attempt was made to moderate a null item type.");
        $retval .= "Submissions Error: An attempt was made to moderate a null item type.";
        return $retval;
    }
    if (empty($id)) {
        // null item type
        Log::write('system',Log::ERROR,"Submissions Error: An attempt was made to moderate an item with a null id.");
        $retval .= "Submissions Error: An attempt was made to moderate an item with a null id.";
        return $retval;
    }

    list($key, $table, $fields, $submissiontable) = PLG_getModerationValues($type);

    switch ($action) {

        case 'delete':

            switch ($type) {

                case 'user':
                    // user
                    if ($id > 1) {
                       USER_deleteAccount($id);
                    }
                    break;

                case 'article':
                    // story (needs to move to a plugin)
                    $db->conn->delete(
                        $submissiontable,
                        array($key => $id),
                        array(Database::STRING)
                    );
                    break;

                default:
                    // plugin
                    $retval .= PLG_deleteSubmission($type, $id);
                    if ( $submissiontable != '' ) DB_delete($submissiontable,"$key",$id);
                    break;
            }

            break;


        case 'approve':

            switch ($type) {

                case 'article':
                    // story (needs to move to a plugin)
                    $result = DB_query("SELECT * FROM $submissiontable WHERE $key = '$id'");
                    $A = DB_fetchArray($result);

                    /* Permissions */
                    if (!isset($A['perm_owner'])) {
                        $A['perm_owner'] = $_CONF['default_permissions_story'][0];
                    } else {
                        $A['perm_owner'] = COM_applyBasicFilter($A['perm_owner'], true);
                    }
                    if (!isset($A['perm_group'])) {
                        $A['perm_group'] = $_CONF['default_permissions_story'][1];
                    } else {
                        $A['perm_group'] = COM_applyBasicFilter($A['perm_group'], true);
                    }
                    if (!isset($A['perm_members'])) {
                        $A['perm_members'] = $_CONF['default_permissions_story'][2];
                    } else {
                        $A['perm_members'] = COM_applyBasicFilter($A['perm_members'], true);
                    }
                    if (!isset($A['perm_anon'])) {
                        $A['perm_anon'] = $_CONF['default_permissions_story'][3];
                    } else {
                        $A['perm_anon'] = COM_applyBasicFilter($A['perm_anon'], true);
                    }
                    if (!isset($A['draft_flag'])) {
                        $A['draft_flag'] = $_CONF['draft_flag'];
                    }

                    if (empty($A['frontpage'])) {
                        $A['frontpage'] = $_CONF['frontpage'];
                    }

                    if (empty($A['show_topic_icon'])) {
                        $A['show_topic_icon'] = $_CONF['show_topic_icon'];
                    }
                    $A['owner_id'] = $A['uid'];

                    $article = new Article();
                    $article->retrieveArticleFromVars($A);
                    $rc = $article->save();
                    if ($rc === true) {
                        AdminAction::write('system','article_submission', sprintf($LANG_ADM_ACTIONS['article_approved'],$article->get('sid')));
                        DB_delete($submissiontable,"$key",$id);
                        PLG_itemSaved($A['sid'], 'article');
                        $c = Cache::getInstance()->deleteItemsByTags(array('whatsnew','menu'));
                        COM_rdfUpToDateCheck();
                        COM_olderStuff();
                    } else {
                        $errors = $article->getErrors();
                        $errorMessage = '';
                        foreach ($errors AS $error) {
                            $errorMessage .= $error . '<br>';
                        }
                        COM_setMsg($errorMessage,'error',true);
                        echo COM_refresh($_CONF['site_admin_url'].'/moderation.php');
                    }

                    break;

                case 'user':
                    // user
                    $result = DB_query("SELECT $fields FROM $table WHERE $key = '$id'");
                    $nrows = DB_numRows($result);
                    if ($nrows == 1) {
                        $A = DB_fetchArray($result);
                        if ( !empty($A['remoteservice']) && !empty($A['remoteusername']) ) {
                            $sql = "UPDATE $table SET status=".USER_ACCOUNT_ACTIVE." WHERE $key = '{$A['uid']}'";
                        } else {
                            if ( $_CONF['registration_type'] == 1 ) {
                                $sql = "UPDATE $table SET status=".USER_ACCOUNT_AWAITING_VERIFICATION." WHERE $key = '{$A['uid']}'";
                            } else {
                                $sql = "UPDATE $table SET status=".USER_ACCOUNT_AWAITING_ACTIVATION." WHERE $key = '{$A['uid']}'";
                            }
                        }
                        DB_query($sql);
                        USER_createAndSendPassword($A['username'], $A['email'], $A['uid']);
                    }
                    break;

                default:
                    // plugin
                    if ( $submissiontable != '' ) DB_copy($table,$fields,$fields,$submissiontable,$key,$id);
                    $retval .= PLG_approveSubmission($type,$id);
                    break;
            }

            break;

    } // switch ($action)

    return $retval;
}

/**
* Moderates a list of items as defined by the 'chkall' action
*
* This will actually perform moderation (approve or delete) one or more items
*
* @param    string  $action     Action to perform ('delete' or 'approve')
* @param    string  $type       Type of item ('user','article', etc.)
* @return   string              HTML for "command and control" page
*
*/
function MODERATE_selectedItems($action = '', $type='')
{
    $retval = '';

    $item = (isset($_POST['selitem'])) ? $_POST['selitem'] : array();

    if (isset($item) AND is_array($item)) {
        foreach($item as $selitem) {
            $id = COM_applyFilter($selitem);
            if (empty($id)) {
                Log::write('system',Log::ERROR,"Submissions error: a null item id was specified for action: $action, type: $type");
                $retval .= "Submissions error: a null item id was specified for action: $action, type: $type";
                return $retval; // null id - make an early exit!
            }
            $retval .= MODERATE_item($action, $type, $id);
        }
    }

    return $retval;
}

/**
* Displays items needing moderation
*
* Displays the moderation list of items from the submission tables
*
* @type     string      Type of object to build list for
*
*/
function MODERATE_itemList($type='', $token = '')
{
    global $_CONF, $_TABLES, $LANG01, $LANG24, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $retval = '';

    if (empty($type)) {
        Log::write('system',Log::ERROR,"Submissions Error: Attempted to generate a moderation list for a null item type.");
    } else {

        switch ($type) {

            case 'user': // user -----------------------------------------------

                $result = DB_query ("SELECT uid,username,fullname,email,UNIX_TIMESTAMP(regdate) AS day FROM {$_TABLES['users']} WHERE status = 2");
                $nrows = DB_numRows($result);

                if ($nrows > 0) {
                    $data_arr = array();
                    for ($i = 0; $i < $nrows; $i++) {
                        $A = DB_fetchArray($result);
                        $A['edit'] = $_CONF['site_admin_url'].'/user.php?edit=x&amp;uid='.$A['uid'];
                        $A['fullname'] = $A['fullname'];
                        $A['email'] = $A['email'];
                        $A['_type_'] = 'user';
                        $A['_key_'] = 'uid';
                        $data_arr[$i] = $A;
                    }

                    $header_arr = array(
                        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
                        array('text' => $LANG29[16], 'field' => 1, 'nowrap' => true),
                        array('text' => $LANG29[17], 'field' => 2),
                        array('text' => $LANG29[18], 'field' => 3, 'nowrap' => true),
                        array('text' => $LANG29[47], 'field' => 4, 'align' => 'center'),
                        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center', 'width' => '35px'),
                        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'align' => 'center', 'width' => '35px')
                    );

                    $text_arr = array('has_menu'  => false,
                                      'title'     => $LANG29[40],
                                      'help_url'  => 'ccusersubmission.html',
                                      'no_data'   => '',
                                      'form_url'  => "{$_CONF['site_admin_url']}/moderation.php"
                    );

                    $actions = FieldList::approveButton(array(
                        'name' => 'approve_x',
                        'text' => $LANG29[1],
                        'attr' => array(
                            'title' => $LANG29[44],
                            'onclick' => 'return confirm(\'' . $LANG29[45] . '\');'
                        )
                    ));
                    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';

                    $actions .= FieldList::deleteButton(array(
                        'name' => 'delbutton_x',
                        'text' => $LANG_ADMIN['delete'],
                        'attr' => array(
                            'title' => $LANG01[124],
                            'onclick' => 'return confirm(\'' . $LANG29[45] . '\');'
                        )
                    ));
                    $options = array('chkselect' => true,
                                     'chkfield' => 'uid',
                                     'chkname' => 'selitem',
                                     'chkminimum' => 0,
                                     'chkall' => true,
                                     'chkactions' => $actions
                                     );

                    $form_arr['bottom'] = '<input type="hidden" name="type" value="user"/>' . LB
                            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>' . LB
                            . '<input type="hidden" name="moderation" value="x"/>' . LB
                            . '<input type="hidden" name="count" value="' . $nrows . '"/>';

                    $retval = ADMIN_simpleList('MODERATE_getListField', $header_arr,
                                          $text_arr, $data_arr, $options, $form_arr, $token);
                }
                break;


            default: // plugin -------------------------------------------------
                $function = 'plugin_itemlist_' . $type;
                if (function_exists ($function)) {

                    $plugin = new Plugin();
                    $plugin = $function($token);
                    // if the plugin returns a string, it wants to control it's own
                    // moderation.  as far as I can tell - no plugin has used this yet
                    // it appears to be a feature that was added in glFusion 1.1.0rc1
                    // but never actually used
                    if (is_string($plugin) && !empty($plugin)) {
                        return '<div class="block-box">' . $plugin . '</div>';
                    // otherwise this is a plugin object (historical approach)
                    } elseif (is_object($plugin)) {
                        $helpfile = $plugin->submissionhelpfile;
                        $sql = $plugin->getsubmissionssql;
                        $H = $plugin->submissionheading;
                        $section_title = $plugin->submissionlabel;
                        $section_help = $helpfile;
                        $isplugin = true;
                    }
                }

                // this needs to be removed when story moves into a plugin
                if ($type == 'article') {
                    $isplugin = false;
                }

                // we really only need the id from this list, so that we know key/id field name
                list($key, $table, $fields, $submissiontable) = PLG_getModerationValues($type);

                // the first 4 columns default to Title, Date, Topic and Submitted By unless otherwise
                // specified.  not sure I like this approach - but whatever - it's not
                // breaking anything at the momemnt
                if ( !isset($H[0]) || empty($H[0]) ) {
                    $H[0] = $LANG29[10];
                }
                if ( !isset($H[1]) || empty($H[1]) ) {
                    $H[1] = $LANG29[14];
                }
                if ( !isset($H[2]) || empty($H[2]) ) {
                    $H[2] = $LANG29[15];
                }
                if ( !isset($H[3]) || empty($H[3]) ) {
                    $H[3] = $LANG29[46];
                }

                // run SQL but this time ignore any errors.  note that the max items for
                // each type that can be moderated is limited to 50
                if (!empty($sql)) {
                    $sql .= ' LIMIT 50'; // quick'n'dirty workaround to prevent timeouts
                    $result = DB_query($sql, 1);
                }

                if (empty($sql) || DB_error($sql)) {
                    $nrows = 0; // more than likely a plugin that doesn't need moderation
                } else {
                    $nrows = DB_numRows($result);
                }

                if ($nrows > 0) {  // only generate list html if there are items to moderate
                    $data_arr = array();
                    for ($i = 0; $i < $nrows; $i++) {
                        $A = DB_fetchArray($result);
                        if ($isplugin)  {
                            $A['edit'] = $_CONF['site_admin_url']
                                        . '/plugins/' . $type . '/index.php?moderate=x'
                                        . '&amp;' . $key . '=' . $A[0];
                        } else {
                            $A['edit'] = $_CONF['site_admin_url']
                                        . '/' .  'story' . '.php?moderate=x'
                                        . '&amp;' . $key . '=' . $A[0];
                        }
                        $A['_type_'] = $type;   // type of item
                        $A['_key_'] = $key;      // name of key/id field
                        $data_arr[$i] = $A;     // push row data into array
                    }

                    $header_arr = array(      // display 'text' and use table field 'field'
                        array('text' => $LANG_ADMIN['edit'], 'field' => 0, 'align' => 'center', 'width' => '25px'),
                        array('text' => $H[0], 'field' => 1),
                        array('text' => $H[1], 'field' => 2, 'align' => 'center', 'width' => '15%'),
                        array('text' => $H[2], 'field' => 3, 'width' => '20%'),
                        array('text' => $H[3], 'field' => 4, 'width' => '15%', 'nowrap' => true),
                        array('text' => $LANG29[1], 'field' => 'approve', 'align' => 'center', 'width' => '35px'),
                        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'align' => 'center', 'width' => '35px')
                    );

                    $text_arr = array('has_menu'    => false,
                                      'title'       => $section_title,
                                      'help_url'    => $section_help,
                                      'no_data'   => $LANG29[39],
                                      'form_url'  => "{$_CONF['site_admin_url']}/moderation.php"
                    );
                    $actions = FieldList::approveButton(array(
                        'name' => 'approve_x',
                        'text' => $LANG29[1],
                        'attr' => array(
                            'title' => $LANG29[44],
                            'onclick' => 'return confirm(\'' . $LANG29[45] . '\');'
                        )
                    ));
                    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';

                    $actions .= FieldList::deleteButton(array(
                        'name' => 'delbutton_x',
                        'text' => $LANG_ADMIN['delete'],
                        'attr' => array(
                            'title' => $LANG01[124],
                            'onclick' => 'return confirm(\'' . $LANG01[125] . '\');'
                        )
                    ));
/*
                    $actions = '<input name="approve" type="image" src="'
                        . $_CONF['layout_url'] . '/images/admin/accept.' . $_IMAGE_TYPE
                        . '" style="vertical-align:bottom;" title="' . $LANG29[44]
                        . '" onclick="return confirm(\'' . $LANG29[45] . '\');"'
                        . '/>&nbsp;' . $LANG29[1];
                    $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                    $actions .= '<input name="delbutton" type="image" src="'
                        . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
                        . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
                        . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
                        . '/>&nbsp;' . $LANG_ADMIN['delete'];
*/
                    $options = array('chkselect' => true,
                                     'chkfield' => 'id',
                                     'chkname' => 'selitem',
                                     'chkminimum' => 0,
                                     'chkall' => true,
                                     'chkactions' => $actions,
                                     );

                    $form_arr['bottom'] = '<input type="hidden" name="type" value="' . $type . '"/>' . LB
                            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>' . LB
                            . '<input type="hidden" name="moderation" value="x"/>' . LB
                            . '<input type="hidden" name="count" value="' . $nrows . '"/>';

                    $retval .= ADMIN_simpleList('MODERATE_getListField', $header_arr,
                                              $text_arr, $data_arr, $options, $form_arr, $token);
                }

                break; // plugin

        } // switch ($type)

    } // !empty($type)

    return $retval;
}

/**
* Generates a series of moderation list for the various object types
*
* This is the primary function called for the Submissions panel
*
*/
function MODERATE_submissions()
{
    global $_CONF, $LANG01, $LANG29, $LANG_ADMIN, $_IMAGE_TYPE;

    $output = outputHandler::getInstance();
    $output->addLinkScript($_CONF['site_url'].'/javascript/addons/modal/jquery.modal.min.js');
    $output->addLinkStyle($_CONF['site_url'].'/javascript/addons/modal/jquery.modal.css');

    $pageContent = '';
    $retval  = COM_startBlock($LANG01[10],'', COM_getBlockTemplate('_admin_block', 'header'));

    $menu_arr = array(
            array('url' => $_CONF['site_admin_url'].'/index.php',
                  'text' => $LANG_ADMIN['admin_home']),
    );

    $retval .= ADMIN_createMenu($menu_arr, $LANG29['info'],
                                $_CONF['layout_url'] . '/images/icons/moderation.'. $_IMAGE_TYPE);
    $token = SEC_createToken();

    // user submissions
    $pageContent .= (MODERATE_ismoderator_user() &&
                (MODERATE_submissioncount_user() > 0) &&
                ($_CONF['usersubmission'] == 1)
                ) ? MODERATE_itemList('user', $token) : '';

    // story & plugin submissions
    $pageContent .= PLG_showModerationList($token);

    // if empty at this point, we have no submissions to moderate
    $pageContent .= (empty($pageContent)) ? '<br /><p>' . $LANG29[39] . '</p>' : '';

    $retval .= $pageContent;

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

// MAIN ========================================================================

$display = '';

$action = '';

$expected = array('approve','delete','delbutton','delbutton_x','approve_x');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
    }
}

$id = (isset($_GET['id'])) ? COM_applyFilter($_GET['id']) : '';

$type = '';
if (isset($_POST['type'])) {
    $type = COM_applyFilter($_POST['type']);
} elseif (isset($_GET['type'])) {
    $type = COM_applyFilter($_GET['type']);
}

$display .= COM_siteHeader ('menu', $LANG01[10]);

switch ($action) {

    case 'delete':
    case 'delbutton_x':
    case 'delbutton':
        if (SEC_checkToken()) {
            $display .= ($action=='delete') ? MODERATE_item($action, $type, $id) : MODERATE_selectedItems('delete', $type);
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to delete submission(s) and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'approve':
    case 'approve_x':
        if (SEC_checkToken()) {
            $display .= ($action == 'approve') ? MODERATE_item($action, $type, $id) : MODERATE_selectedItems('approve', $type);
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to approve submission(s) and failed CSRF checks.');
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;
}

$msg = COM_getMessage();
$plugin = (isset($_GET['plugin'])) ? COM_applyFilter($_GET['plugin']) : '';
$display .= ($msg > 0) ? COM_showMessage($msg, $plugin) : '';

$display .= MODERATE_submissions();

$display .= COM_siteFooter();

echo $display;

?>
