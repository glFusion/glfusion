<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | oauthhelper.class.php                                                    |
// |                                                                          |
// | OAuth Distributed Authentication Module.                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2011-2016 by the following authors:                        |
// |                                                                          |
// | Mark Howard            mark AT usable-web DOT com                        |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2010 by the following authors:                             |
// |                                                                          |
// | Authors: Hiroron          - hiroron AT hiroron DOT com                   |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

require_once $_CONF['path'] . 'lib/http/http.php';
require_once $_CONF['path'] . 'lib/oauth/oauth_client.php';

class OAuthConsumer {
    protected $consumer = NULL;
    protected $client = NULL;
    protected $debug_oauth = false;

    var $error = '';

    public function __construct($service) {
        global $_CONF,$_SYSTEM;

        if (strpos($service, 'oauth.') === 0) {
            $service = str_replace("oauth.", "", $service);
        }

    	$this->client = new oauth_client_class;
    	$this->client->server     = $service;
        $this->client->debug      = $_SYSTEM['debug_oauth'];
        $this->client->debug_http = $_SYSTEM['debug_oauth'];
        $this->debug_oauth        = $_SYSTEM['debug_oauth'];

        // Set key and secret for OAuth service if found in config
        if ($this->client->client_id == '') {
            if (isset($_CONF[$service . '_consumer_key'])) {
                if ($_CONF[$service . '_consumer_key'] != '') {
                    $this->client->client_id = $_CONF[$service . '_consumer_key'];
                }
            }
        }
        if ($this->client->client_secret == '') {
            if (isset($_CONF[$service . '_consumer_secret'])) {
                if ($_CONF[$service . '_consumer_secret'] != '') {
                    $this->client->client_secret = $_CONF[$service . '_consumer_secret'];
                }
            }
        }

        switch ( $this->client->server ) {
            case 'facebook' :
                $api_url = 'https://graph.facebook.com/me?fields=name,email,link,id,first_name,last_name,about';
                $scope   = 'email,public_profile,user_friends';
                $q_api   = array();
                break;
            case 'google' :
                $api_url = 'https://www.googleapis.com/oauth2/v1/userinfo';
                $scope   = 'https://www.googleapis.com/auth/userinfo.email '.'https://www.googleapis.com/auth/userinfo.profile';
                $q_api   = array();
                break;
            case 'microsoft' :
                $api_url = 'https://apis.live.net/v5.0/me';
                $scope   = 'wl.basic wl.emails';
                $q_api   = array();
                break;
            case 'twitter' :
                $api_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
                $scope   = '';
                $q_api   = array('include_entities' => "true", 'skip_status' => "true", 'include_email' => "true");
                break;
            case 'yahoo' :
                $api_url = 'http://query.yahooapis.com/v1/yql';
                $scope   = '';
                $q_api   = array('q'=>'select * from social.profile where guid=me','format'=>'json');
                break;
            case 'linkedin' :
                $api_url = 'http://api.linkedin.com/v1/people/~:(id,first-name,last-name,location,summary,email-address,picture-url,public-profile-url)';
                $scope   = 'r_basicprofile r_emailaddress';
                $q_api   = array('format'=>'json');
                break;
            case 'github' :
                $api_url = 'https://api.github.com/user';
                $scope   = 'user:email';
                $q_api   = array();
                break;

        }

        $this->client->scope = $scope;
        $this->api_url = $api_url;
        $this->q_api   = $q_api;
    }

    public function authenticate_user() {

    	if ( ($success = $this->client->Initialize() ) ) {
    		if ( ($success = $this->client->Process() ) ) {
    			if(strlen($this->client->authorization_error)) {
				    $this->client->error = $this->client->authorization_error;
				    $this->error = $this->client->authorization_error;
				    $success = false;
			    } elseif(strlen($this->client->access_token)) {
                    $user = $this->get_userinfo();
                }
    		}
    		$success = $this->client->Finalize($success);
    	}
        if ($this->debug_oauth) COM_errorLog($this->client->debug_output);
    	if ($this->client->exit) {
    		exit;
    	}
    	if ($success) {
    	    return $user;
    	}
    	return $success;
    }

    public function get_userinfo() {

        if (strlen($this->client->access_token)) {
    	    $success = $this->client->CallAPI(
    		    $this->api_url,
    			'GET', $this->q_api, array('FailOnAccessError'=>true), $user);
    	}
   		$success = $this->client->Finalize($success);
    	if ($this->client->exit) {
    		exit;
    	}
    	if ($success) {
    	    return $user;
    	}
    }

    public function setRedirectURL($url) {
            $this->client->redirect_uri  = $url;
    }

    public function doAction($info) {
        global $_TABLES, $LANG04, $status, $uid, $_CONF, $checkMerge;

        $users      = $this->_getCreateUserInfo($info);
        $userinfo   = $this->_getUpdateUserInfo($info);

        $sql = "SELECT uid,status FROM {$_TABLES['users']} WHERE remoteusername = '".DB_escapeString($users['remoteusername'])."' AND remoteservice = '".DB_escapeString($users['remoteservice'])."'";

        $result = DB_query($sql);
        $tmp = DB_error();
        $nrows = DB_numRows($result);

        if (empty($tmp) && $nrows == 1) { // existing user...
            list($uid, $status) = DB_fetchArray($result);
            $checkMerge = false;

            // Update users
            if (is_array($info)) {
                $columnCount = 0;
                $sql = "UPDATE {$_TABLES['users']} SET ";
                if (!empty($info['fullname'])) {
                    $updatecolumns .= "fullname='".DB_escapeString($info['fullname'])."'";
                    $columnCount++;
                }
                if (!empty($info['email'])) {
                    if (!empty($updatecolumns)) { $updatecolumns .= ", "; }
                    $updatecolumns .= "email='".DB_escapeString($info['email'])."'";
                    $columnCount++;
                }
                if (!empty($info['homepage'])) {
                    if (!empty($updatecolumns)) { $updatecolumns .= ", "; }
                    $updatecolumns .= "homepage='".DB_escapeString($info['homepage'])."'";
                    $columnCount++;
                }

                if ( $columnCount > 0 && $uid > 1 ) {
                    $sql = $sql . $updatecolumns . " WHERE uid=" . (int) $_USER['uid'];
                    DB_query($sql);
                }
                // Update rest of users info
                $this->_DBupdate_users($uid, $info);
            }
        } else {
            if ( $_CONF['disable_new_user_registration'] ) {
                echo COM_siteHeader();
                echo $LANG04[122];
                echo COM_siteFooter();
                exit;
            }
            // initial login - create account
            $loginname = $users['loginname'];

            $checkName = DB_getItem($_TABLES['users'], 'username', "username='".DB_escapeString($loginname)."'");
            if (!empty($checkName)) {
                if (function_exists('CUSTOM_uniqueRemoteUsername')) {
                    $loginname = CUSTOM_uniqueRemoteUsername(loginname, $remoteservice);
                }
                if (strcasecmp($checkName,$loginname) == 0) {
                    $loginname = USER_uniqueUsername($loginname);
                }
            }
            $users['loginname'] = $loginname;
            $uid = USER_createAccount($users['loginname'], $users['email'], '', $users['fullname'], $users['homepage'], $users['remoteusername'], $users['remoteservice']);
            if ( $uid == NULL ) {
                return NULL;
            }
            if (is_array($users)) {
                $this->_DBupdate_users($uid, $users);
            }
            if (is_array($userinfo)) {
                $this->_DBupdate_userinfo($uid, $userinfo);
            }

            $status = DB_getItem($_TABLES['users'],'status','uid='.(int)$uid);
            $remote_grp = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = 'Remote Users'");
            DB_query("INSERT INTO {$_TABLES['group_assignments']} (ug_main_grp_id, ug_uid) VALUES ($remote_grp, $uid)");

            if ( isset($users['socialuser']) ) {
                $social_result = DB_query("SELECT * FROM {$_TABLES['social_follow_services']} WHERE service_name='".DB_escapeString($users['socialservice'])."' AND enabled=1");
                if (DB_numRows($social_result) > 0 ) {
                    $social_row = DB_fetchArray($social_result);
                    $sql  = "REPLACE INTO {$_TABLES['social_follow_user']} (ssid,uid,ss_username) ";
                    $sql .= " VALUES (" . (int) $social_row['ssid'] . ",".$uid.",'".$users['socialuser']."');";
                    DB_query($sql,1);
                }
            }

            if ( isset($users['email']) && $users['email'] != '' ) {
                $sql = "SELECT * FROM {$_TABLES['users']} WHERE account_type = ".LOCAL_USER." AND email='".DB_escapeString($users['email'])."' AND uid > 1";
                $result = DB_query($sql);
                $numRows = DB_numRows($result);
                if ( $numRows == 1 ) {
                    $row = DB_fetchArray($result);
                    $remoteUID = $uid;
                    $localUID  = $row['uid'];
                    USER_mergeAccountScreen($remoteUID, $localUID);
                }
            }
        }
        return true;
    }

    public function doSynch($info) {
        global $_TABLES, $_USER, $status, $uid, $_CONF;

        $users = $this->_getCreateUserInfo($info);
        $userinfo = $this->_getUpdateUserInfo($info);

        $updatecolumns = '';

        // Update users
        if (is_array($users)) {
            $sql = "UPDATE {$_TABLES['users']} SET ";
            if (!empty($users['fullname'])) {
                $updatecolumns .= "fullname='".DB_escapeString($users['fullname'])."'";
            }
            if (!empty($users['email'])) {
                if (!empty($updatecolumns)) { $updatecolumns .= ", "; }
                $updatecolumns .= "email='".DB_escapeString($users['email'])."'";
            }
            if (!empty($users['homepage'])) {
                if (!empty($updatecolumns)) { $updatecolumns .= ", "; }
                $updatecolumns .= "homepage='".DB_escapeString($users['homepage'])."'";
            }
            if (!empty($users['email'])) {
                if (!empty($updatecolumns)) { $updatecolumns .= ", "; }
                $updatecolumns .= "email='".DB_escapeString($users['email'])."'";
            }

            $sql = $sql . $updatecolumns . " WHERE uid=" . (int) $_USER['uid'];

            DB_query($sql);

            // Update rest of users info
            $this->_DBupdate_users($_USER['uid'], $users);
        }

        // Update userinfo
        if (is_array($userinfo)) {
            $this->_DBupdate_userinfo($_USER['uid'], $userinfo);
        }
    }

    protected function _getUpdateUserInfo($info) {
        $userinfo = array();
        switch ( $this->client->server ) {
            case 'facebook' :
                if ( isset($info->about) ) {
                    $userinfo['about'] = $info->about;
                }
                if ( isset($info->location->name) ) {
                    $userinfo['location'] = $info->location->name;
                }
                break;
            case 'google' :
                break;
            case 'microsoft' :
                break;
            case 'twitter' :
                if ( isset($info->email ) ) {
                    $userinfo['email'] = $info->email;
                }
                break;
            case 'yahoo' :
                break;
            case 'linkedin' :
                if ( isset($info->location->name) ) {
                    $userinfo['location'] = $info->location->name;
                }
                break;
            case 'github' :
                break;
        }

        return $userinfo;
    }


    protected function _getCreateUserInfo($info) {

        switch ( $this->client->server ) {
            case 'facebook' :
                $users = array(
                    'loginname'      => (isset($info->first_name) ? $info->first_name : $info->id),
                    'email'          => $info->email,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => $info->link,
                    'remoteusername' => DB_escapeString($info->id),
                    'remoteservice'  => 'oauth.facebook',
                    'remotephoto'    => 'http://graph.facebook.com/'.$info->id.'/picture',
                    'socialservice'  => 'facebook',
                    'socialuser'     => 'app_scoped_user_id/'.$info->id,
                );
                break;
            case 'google' :
                $homepage = $info->link;

                $plusPos = strpos($homepage,"+");
                if ( $plusPos !== false ) {
                    $username = substr($homepage,strlen("https://plug.google.com/+"));
                } else {
                    $username = "";
                }
                $users = array(
                    'loginname'      => (isset($info->given_name) ? $info->given_name : $info->id),
                    'email'          => $info->email,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => $info->link,
                    'remoteusername' => DB_escapeString($info->id),
                    'remoteservice'  => 'oauth.google',
                    'remotephoto'    => $info->picture,
                    'socialservice'  => 'google-plus',
                    'socialuser'     => $username,
                );
                break;
            case 'twitter' :
                $mail = '';
                if ( isset($info->email)) {
                    $mail = $info->email;
                }
                $users = array(
                    'loginname'      => $info->screen_name,
                    'email'          => $mail,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => 'http://twitter.com/'.$info->screen_name,
                    'remoteusername' => DB_escapeString($info->screen_name),
                    'remoteservice'  => 'oauth.twitter',
                    'remotephoto'    => $info->profile_image_url,
                    'socialservice'  => 'twitter',
                    'socialuser'     => $info->screen_name,

                );
                break;
            case 'microsoft' :
                $users = array(
                    'loginname'      => (isset($info->first_name) ? $info->first_name : $info->id),
                    'email'          => $info->emails->preferred,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => '',
                    'remoteusername' => DB_escapeString($info->id),
                    'remoteservice'  => 'oauth.microsoft',
                    'remotephoto'    => 'https://apis.live.net/v5.0/me/picture?access_token='.$this->client->access_token,
                );
                break;
            case 'yahoo' :
                $users = array(
                    'loginname'      => (isset($info->first_name) ? $info->first_name : $info->id),
                    'email'          => $info->emails->preferred,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => '',
                    'remoteusername' => DB_escapeString($info->id),
                    'remoteservice'  => 'oauth.yahoo',
                    'remotephoto'    => 'https://apis.live.net/v5.0/me/picture?access_token='.$this->client->access_token,
                );
                break;
            case 'linkedin' :
                $users = array(
                    'loginname'      => (isset($info->{'firstName'}) ? $info->{'firstName'} : $info->id),
                    'email'          => $info->{'emailAddress'},
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->{'firstName'} . ' ' .  $info->{'lastName'},
                    'homepage'       => $info->{'publicProfileUrl'},
                    'remoteusername' => DB_escapeString($info->id),
                    'remoteservice'  => 'oauth.linkedin',
                    'remotephoto'    => $info->{'pictureUrl'},
                    'socialservice'  => 'linkedin',
                    'socialuser'     => $info->id,

                );
                break;
            case 'github' :
                $users = array(
                    'loginname'      => (isset($info->{'login'}) ? $info->{'login'} : $info->id),
                    'email'          => $info->{'email'},
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->{'name'},
                    'homepage'       => $info->{'html_url'},
                    'remoteusername' => DB_escapeString($info->id),
                    'remoteservice'  => 'oauth.github',
                    'remotephoto'    => $info->{'avatar_url'},
                    'socialservice'  => 'github',
                    'socialuser'     => $info->login,

                );
                break;
        }

        return $users;
    }

    protected function _DBupdate_userinfo($uid, $userinfo) {
        global $_TABLES;
        if (!empty($userinfo['about']) || !empty($userinfo['location']) ) {
            $commaCount = 0;
            $sql = "UPDATE {$_TABLES['userinfo']} SET";
            if ( !empty($userinfo['about'])) {
                $sql .= !empty($userinfo['about']) ? " about = '".DB_escapeString($userinfo['about'])."'" : "";
                $commaCount++;
            }
            if ( !empty($userinfo['location'])) {
                if ( $commaCount > 0 ) $sql .= ",";
                $sql .= !empty($userinfo['location']) ? " location = '".DB_escapeString($userinfo['location'])."'" : "";
                $commaCount++;
            }
            $sql .= " WHERE uid = ".(int) $uid;
            if ( $commaCount > 0 )
                DB_query($sql);
        }
    }

    protected function _DBupdate_users($uid, $users) {
        global $_TABLES, $_CONF;

        $sql = "UPDATE {$_TABLES['users']} SET remoteusername = '".DB_escapeString($users['remoteusername'])."', remoteservice = '".DB_escapeString($users['remoteservice'])."' ";
        if (!empty($users['remotephoto'])) {
            $save_img = $_CONF['path_images'] . 'userphotos/' . $uid ;
            $imgsize = $this->_saveUserPhoto($users['remotephoto'], $save_img);
            if ( $imgsize > 0 ) {
                $ext = $this->_getImageExt($save_img);
                $image = $save_img . $ext;
                if (file_exists($image)) {
                    unlink($image);
                }
                rename($save_img, $image);

                if (($_CONF['max_photo_width'] > 0) && ($_CONF['max_photo_height'] > 0)) {
                    $upWidth  = $_CONF['max_photo_width'];
                    $upHeight = $_CONF['max_photo_height'];
                } else {
                    $upWidth  = $_CONF['max_image_width'];
                    $upHeight = $_CONF['max_image_height'];
                }
                IMG_resizeImage($image, $image, $upHeight, $upWidth);

                $imgname = $uid . $ext;
                $sql .= ", photo = '".DB_escapeString($imgname)."'";
            }
        }
        $sql .= " WHERE uid = ".(int) $uid;
        DB_query($sql);
    }

    protected function _saveUserPhoto($from, $to)
    {
        $ret = 0;
        $img = '';
        $arguments = array();
        $http = new http_class;
        $http->user_agent = 'glFusion/' . GVERSION;
        $error = $http->GetRequestArguments($from,$arguments);
        $error = $http->Open($arguments);
        if ($error=="") {
            $error = $http->SendRequest($arguments);
            if ( $error == "" ) {
                for (;;) {
                    $error = $http->ReadReplyBody($body,10240);
                    if ( $error != "" || strlen($body) == 0 )
                        break;
                    $img = $img . $body;
                }
                $ret = file_put_contents($to, $img);
            }
        }
        $http->Close();
        return $ret;
    }

    protected function _getImageExt($img, $dot = true) {
        $size = @getimagesize($img);
        switch ($size['mime']) {
            case 'image/gif':
                $ext = 'gif';
                break;
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/png':
                $ext = 'png';
                break;
            case 'image/bmp':
                $ext = 'bmp';
                break;
        }
        return ($dot ? '.' : '') . $ext;
    }
}
?>