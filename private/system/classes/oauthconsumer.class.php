<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | oauthhelper.class.php                                                    |
// |                                                                          |
// | OAuth Distributed Authentication Module.                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2011-2018 by the following authors:                        |
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

class OAuthConsumer {
    public $service;
    protected $consumer = NULL;
    protected $client = NULL;
    protected $debug_oauth = false;
    protected $serviceMap = array('facebook' => 'Facebook',
                                  'github' => 'github',
                                  'google' => 'Google',
                                  'linkedin' => 'LinkedIn',
                                  'microsoft' => 'Microsoft',
                                  'twitter' => 'Twitter',
                                  );
    var $error = '';

    public function __construct($service) {
        global $_CONF,$_SYSTEM;

        if (strpos($service, 'oauth.') === 0) {
            $service = str_replace("oauth.", "", $service);
        }
        $this->service = $service;
    	$this->client = new oauth_client_class;
    	$this->client->configuration_file = $_CONF['path'].'vendor/phpclasses/oauth-api/oauth_configuration.json';
    	$this->client->server     = $this->serviceMap[$service];
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
            case 'Facebook' :
                $api_url = 'https://graph.facebook.com/me?fields=name,email,link,id,first_name,last_name,about';
                $scope   = 'email,public_profile,user_friends';
                $q_api   = array();
                break;
            case 'Google' :
                $api_url = 'https://www.googleapis.com/oauth2/v1/userinfo';
                $scope   = 'https://www.googleapis.com/auth/userinfo.email '.'https://www.googleapis.com/auth/userinfo.profile';
                $q_api   = array();
                break;
            case 'Microsoft' :
                $api_url = 'https://apis.live.net/v5.0/me';
                $scope   = 'wl.basic wl.emails';
                $q_api   = array();
                break;
            case 'Twitter' :
                $api_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
                $scope   = '';
                $q_api   = array('include_entities' => "true", 'skip_status' => "true", 'include_email' => "true");
                break;
            case 'LinkedIn' :
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
    		} else {
    		    $this->error = $this->client->error;
    		}
    		$success = $this->client->Finalize($success);
    	} else {
    	    $this->error = $this->client->error;
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

    public function doFinalLogin($info) {
        global $_TABLES, $LANG04, $status, $uid, $_CONF, $checkMerge, $REMOTE_ADDR;

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
            // new user

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

            SESS_setVar('users',$users);
            SESS_setVar('userinfo',$userinfo);

            $userData = array(
                'regtype'           => 'oauth',
                'username'          => $loginname,
                'email'             => $users['email'],
                'fullname'          => $users['fullname'],
                'oauth_provider'    => $users['remoteservice'],
                'oauth_username'    => $users['remoteusername'],
                'oauth_email'       => $users['email'],
                'oauth_photo'       => $users['remotephoto'],
                'oauth_homepage'    => $users['homepage'],
                'oauth_service'     => $this->service,
            );
            $page = USER_registrationForm($userData);
            $display = COM_siteHeader('menu');
            $display .= $page;
            $display .= COM_siteFooter();
            echo $display;
            exit;
        }
        return true;
    }

    public function resyncUserData($info) {
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
            case 'Facebook' :
                if ( isset($info->about) ) {
                    $userinfo['about'] = $info->about;
                }
                if ( isset($info->location->name) ) {
                    $userinfo['location'] = $info->location->name;
                }
                break;
            case 'Google' :
                break;
            case 'Microsoft' :
                break;
            case 'Twitter' :
                if ( isset($info->email ) ) {
                    $userinfo['email'] = $info->email;
                }
                break;
            case 'LinkedIn' :
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
            case 'Facebook' :
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
            case 'Google' :
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
            case 'Twitter' :
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
            case 'Microsoft' :
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
            case 'LinkedIn' :
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
                    'email'          => (isset($info->{'email'}) ? $info->{'email'} : ''),
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

    public function _DBupdate_userinfo($uid, $userinfo) {
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

    public function _DBupdate_users($uid, $users) {
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