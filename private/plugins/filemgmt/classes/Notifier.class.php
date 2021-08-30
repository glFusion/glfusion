<?php
/**
 * Class to send notifications to submitters and administrators.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     filemgmt
 * @version     v1.9.0
 * @since       v1.9.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt;

/**
 * Notification methods.
 * @package filemgmt
 */
class Notifier
{
    /**
     * Send an approval notification to the file submitter.
     *
     * @param   object  $File   Download object
     * @return  boolean     True on success, False on error
     */
    public static function sendApproval(\Filemgmt\Download $File)
    {
        global $_CONF, $_FM_CONF, $_TABLES;

        // Fake successful return if notifications are disabled,
        // or if the submitter is anonymous.
        if ($_FM_CONF['EmailOption'] || $File->getSubmitter() < 2) {
            return true;
        }

        $result = DB_query(
            "SELECT username, email FROM {$_TABLES['users']}
            WHERE a.uid = {$File->getSubmitter()}"
        );
        list ($submitter_name, $emailaddress) = DB_fetchArray($result, false);
        $mailtext  = sprintf(_MD_HELLO,$submitter_name);
        $mailtext .= ",\n\n" ._MD_WEAPPROVED. " " .$title. " \n" ._MD_THANKSSUBMIT. "\n\n";
        $mailtext .= "{$_CONF["site_name"]}\n";
        $mailtext .= "{$_CONF['site_url']}\n";
        COM_emailNotification(array(
            'to' => COM_formatEmailAddress($submitter_name,$emailaddress),
            'textmessage' => $mailtext,
            'subject' => _MD_APPROVED,
        ) );
        return true;
    }


    /**
     * Send a notification to the filemgmt admins when a new file is uploaded.
     *
     * @param   object  $File   Download object
     * @return  boolean     True on success, False on error
     */
    public static function notifyAdmins(\Filemgmt\Download $File)
    {
        global $LANG_DIRECTION, $LANG_CHARSET, $LANG_FM00, $_USER, $_FM_CONF, $_CONF, $_TABLES;

        $altBody = '';
        $to = array();
        $body = '';

        $description = stripslashes($File->getDescription());

        if(empty($LANG_DIRECTION)) {
            // default to left-to-right
            $direction = 'ltr';
        } else {
            $direction = $LANG_DIRECTION;
        }
        if (empty($LANG_CHARSET)) {
            $charset = $_CONF['default_charset'];
            if (empty($charset)) {
                $charset = 'iso-8859-1';
            }
        } else {
            $charset = $LANG_CHARSET;
        }

        COM_clearSpeedlimit(300, 'fmnotify');
        $last = COM_checkSpeedlimit('fmnotify');
        if ($last == 0) {
            return false;
        }

        $subject = $LANG_FM00['new_upload'] . $_CONF['site_name'];

        if ($File->getSubmitter() < 2  ) {
            $uname = 'Anonymous';
        } else {
            $uname = DB_getItem($_TABLES['users'], 'username', 'uid=' . $File->getSubmitter());
        }

        // build the template...
        $T = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file ('email', 'notifyemail.thtml');

        $T->set_var(array(
            'direction'         => $direction,
            'charset'           => $charset,
            'lang_new_upload'   => $LANG_FM00['new_upload_body'],
            'lang_details'      => $LANG_FM00['details'],
            'lang_filename'     => $LANG_FM00['filename'],
            'lang_click_to_mod' => $LANG_FM00['click_to_view'],
            'lang_uploaded_by'  => $LANG_FM00['uploaded_by'],
            'username'          => $uname,
            'filename'          => $filename,
            'description'       => $description,
            'site_name'         => $_CONF['site_name'] . ' - ' . $_CONF['site_slogan'],
        ));
        $T->parse('output','email');
        $body .= $T->finish($T->get_var('output'));

        $groups = Group::withFeature('filemgmt.admin');
        if (count ($groups) == 0) {
            $groupList = '1';   // use Root group if none others fit
        } else {
            $groupList = implode(',', $groups);
        }
    	$sql = "SELECT DISTINCT u.uid, uusername, ufullname, uemail
            FROM {$_TABLES['group_assignments']} ga
            LEFT JOIN {$_TABLES['users']} u
            ON u.uid = ga.uid
	        WHERE u.uid > 1
    	    AND (ga.ug_main_grp_id IN ({$groupList}))";
        $result = DB_query($sql);
        while ($row = DB_fetchArray($result, false)) {
            if ($row['email'] != '') {
                Log::write(
                    'system',
                    Log::ERROR, 
                    'FileMgmt Upload: Sending notification email to: ' . $row['email'] . ' - ' . $row['username']
                );
                $to[] = array('email' => $row['email'],'name' => $row['username']);
            }
        }
        if (count($to) > 0) {
            $msgData = array(
                'to' => $to,
                'from' => array(
                    'email' => $_CONF['site_mail'],
                    'name' => $_CONF['site_name'],
                ),
                'subject' => $subject,
                'htmlmessage' => $body,
            );
            //$msgData['textmessage'] = $altBody;
            COM_emailNotification($msgData);
        } else {
        	Log::write('system',Log::ERROR,'FileMgmt Upload: Error - Did not find any administrators to notify of new upload');
	    }
        COM_updateSpeedlimit('fmnotify');
        return true;
    }

}
