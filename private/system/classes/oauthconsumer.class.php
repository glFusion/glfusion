<?php
/**
* glFusion CMS
*
* OAuth Distributed Authentication Handler
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2011-2019 by the following authors:
*   Mark Howard     mark AT usable-web DOT com
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2010 by the following authors:
*   Hiroron          hiroron AT hiroron DOT com
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

class OAuthConsumer
{
    public $service;

    protected $consumer = NULL;

    protected $client = NULL;

    protected $debug_oauth = false;

    protected $serviceMap = array(
                'facebook'  => 'Facebook',
                'github'    => 'github',
                'google'    => 'Google',
                'linkedin'  => 'LinkedIn',
                'microsoft' => 'Microsoft',
                'twitter'   => 'Twitter',
              );

    var $error = '';

    /**
     * OAuthConsumer constructor
     *
     * @param  string $service
     */
    public function __construct($service)
    {
        global $_CONF,$_SYSTEM;

        $service = strtolower($service);

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

        switch (strtolower($this->client->server)) {
            case 'facebook' :
                $api_url = 'https://graph.facebook.com/me?fields=name,email,id,first_name,last_name';
                $scope   = 'email,public_profile';
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
            default :
                // we should throw an error here
                break;
        }

        $this->client->scope = $scope;
        $this->api_url = $api_url;
        $this->q_api   = $q_api;
    }

    /**
     * @return bool|object|array
     */
    public function authenticateUser()
    {
        $userObject = null;

    	if (($success = $this->client->Initialize())) {
    		if (($success = $this->client->Process())) {
    			if(strlen($this->client->authorization_error)) {
				    $this->client->error = $this->client->authorization_error;
				    $this->error = $this->client->authorization_error;
				    $success = false;
			    } elseif(strlen($this->client->access_token)) {
                    $userObject = $this->getOauthUserinfo();
                }
    		} else {
    		    $this->error = $this->client->error;
    		}
    		$success = $this->client->Finalize($success);
    	} else {
    	    $this->error = $this->client->error;
    	}

        if ($this->debug_oauth) {
            Log::write('system',Log::DEBUG,$this->client->debug_output);
        }

    	if ($this->client->exit) {
    		exit;
    	}

    	if ($success) {
    	    return $userObject;
    	}
    	return $success;
    }


    /*
     * returns user Object from remote site
     */
    public function getOauthUserinfo()
    {
        $userObject = null;

        if (strlen($this->client->access_token)) {
    	    $success = $this->client->CallAPI(
    		    $this->api_url,
    			'GET', $this->q_api, array('FailOnAccessError'=>true), $userObject);
    	}
   		$success = $this->client->Finalize($success);
    	if ($this->client->exit) {
    		exit;
    	}

        return $userObject;
    }

    public function setRedirectURL($url)
    {
        $this->client->redirect_uri  = $url;
    }

    /*
     * @param $info User Object returned via oauth
     * @return true or null - also populated $status and $uid
     */
    public function doFinalLogin($info,&$status,&$uid)
    {
        global $_TABLES, $LANG04, $_CONF;

        $retval = null;

        $db = Database::getInstance();

        // fields for users table
        $users      = $this->getUserData($info);
        // fields for userinfo table
        $userinfo   = $this->getUserInfoData($info);

        $userRow = $db->conn->fetchAssoc(
                        "SELECT uid,status FROM `{$_TABLES['users']}`
                         WHERE remoteusername = ?
                               AND remoteservice = ?",
                        array(
                            $users['remoteusername'],
                            $users['remoteservice']
                        ),
                        array(
                            Database::STRING,
                            Database::STRING
                        )
        );

        if ($userRow !== false && $userRow !== null) { // existing user...
            $uid    = $userRow['uid'];
            $status = $userRow['status'];
            $retval = true;
            $this->_DBupdate_users($uid, $users);
            $this->_DBupdate_userinfo($uid, $userinfo);
        } else {
            // new user
            $loginname = $users['loginname'];
            $checkName = $db->getItem($_TABLES['users'], 'username', array('username' => $loginname));
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
                'oauth_provider'    => strtolower($users['remoteservice']),
                'oauth_username'    => $users['remoteusername'],
                'oauth_email'       => $users['email'],
                'oauth_photo'       => $users['remotephoto'],
                'oauth_homepage'    => $users['homepage'],
                'oauth_service'     => $this->service,
            );
            $page = USER_registrationForm($userData);
            echo COM_siteHeader('menu') . $page . COM_siteFooter();
            exit;
        }
        return $retval;
    }

    /*
     * @param   Oauth Object    $info
     */
    public function resyncUserData($info)
    {
        global $_TABLES, $_USER, $_CONF;

        $db = Database::getInstance();

        $users = $this->getUserData($info);
        $userinfo = $this->getUserInfoData($info);

        // Update users
        if (is_array($users) && count($users) > 0) {
            $this->_DBupdate_users($_USER['uid'], $users);
        }
        // Update userinfo
        if (is_array($userinfo) && count($userinfo) > 0) {
            $this->_DBupdate_userinfo($_USER['uid'], $userinfo);
        }
    }

    /*
     * populates user table data returned in $info oauth object
     */
    protected function getUserInfoData($info)
    {
        $userinfo = array();

        switch (strtolower($this->client->server)) {
            case 'facebook' :
                if ( isset($info->location->name) ) {
                    $userinfo['location'] = $info->location->name;
                }
                break;
            case 'google' :
                break;
            case 'microsoft' :
                break;
            case 'twitter' :
                break;
            case 'linkedIn' :
                if (isset($info->location->name)) {
                    $userinfo['location'] = $info->location->name;
                }
                break;
            case 'github' :
                break;
        }

        return $userinfo;
    }


    /*
     * Take a user object returned via Oauth and maps to a user array
     */
    protected function getUserData($info)
    {
        switch (strtolower($this->client->server)) {
            case 'facebook' :
                $users = array(
                    'loginname'      => (isset($info->first_name) ? $info->first_name : $info->id),
                    'email'          => $info->email,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => '',
                    'remoteusername' => $info->id,
                    'remoteservice'  => 'oauth.facebook',
                    'remotephoto'    => 'http://graph.facebook.com/'.$info->id.'/picture',
                    'socialservice'  => 'facebook',
                );
                break;
            case 'google' :
                $homepage = $info->link;

                $plusPos = strpos($homepage,"+");
                if ( $plusPos !== false ) {
                    $username = substr($homepage,strlen("https://plus.google.com/+"));
                } else {
                    $username = "";
                }
                $users = array(
                    'loginname'      => (isset($info->given_name) ? $info->given_name : $info->id),
                    'email'          => $info->email,
                    'passwd'         => '',
                    'passwd2'        => '',
                    'fullname'       => $info->name,
                    'homepage'       => '',
                    'remoteusername' => $info->id,
                    'remoteservice'  => 'oauth.google',
                    'remotephoto'    => $info->picture,
                    'socialservice'  => '',
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
                    'remoteusername' => $info->screen_name,
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
                    'remoteusername' => $info->id,
                    'remoteservice'  => 'oauth.microsoft',
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
                    'remoteusername' => $info->id,
                    'remoteservice'  => 'oauth.linkedin',
                    'remotephoto'    => (isset($info->{'pictureUrl'}) ? $info->{'pictureUrl'} : ''),
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
                    'remoteusername' => $info->id,
                    'remoteservice'  => 'oauth.github',
                    'remotephoto'    => $info->{'avatar_url'},
                    'socialservice'  => 'github',
                    'socialuser'     => $info->{'login'},
                );
                break;
        }

        return $users;
    }

    public function _DBupdate_userinfo($uid, $userinfo)
    {
        global $_TABLES;

        $db = Database::getInstance();

        $params = array();
        $types  = array();

        if (!empty($userinfo['about'])) {
            $params['about'] = $userinfo['about'];
            $types[] = Database::STRING;
        }
        if (!empty($userinfo['location'])) {
            $params['location'] = $userinfo['location'];
            $types[] = Database::STRING;
        }
        if (count($params) > 0 && $uid > 1) {
            $types[] = Database::INTEGER; // where
            $db->conn->update(
                $_TABLES['userinfo'],
                $params,
                array('uid' => $uid),
                $types
            );
        }
    }

    public function _DBupdate_users($uid, $users)
    {
        global $_TABLES, $_CONF;

        $db = Database::getInstance();

        $params = array();
        $types  = array();

        $params['remoteusername'] = $users['remoteusername'];
        $types[] = Database::STRING;
        $params['remoteservice']  = $users['remoteservice'];
        $types[] = Database::STRING;

        if (!empty($users['fullname'])) {
            $params['fullname'] = $users['fullname'];
            $types[] = Database::STRING;
        }
        if (!empty($userinfo['email'])) {
            $params['email'] = $users['email'];
            $types[] = Database::STRING;
        }
        if (!empty($userinfo['homepage'])) {
            $params['homepage'] = $users['homepage'];
            $types[] = Database::STRING;
        }
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

                $params['photo'] = $imgname;
                $types[] = Database::STRING;
            }
        }
        if (count($params) > 0 && $uid > 1) {
            $types[] = Database::INTEGER;
            $db->conn->update(
                    $_TABLES['users'],
                    $params,
                    array('uid' => $uid),
                    $types
            );
        }
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

    protected function _getImageExt($img, $dot = true)
    {
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