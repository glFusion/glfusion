<?php
/*
 * login_with_garmin.php
 *
 * @(#) $Id: login_with_garmin.php,v 1.4 2021/08/17 13:34:19 mlemos Exp $
 *
 */

	/*
	 *  Get the http.php file from http://www.phpclasses.org/httpclient
	 */
	require('http.php');
	require('oauth_client.php');

	$client = new oauth_client_class;
	$client->debug = true;
	$client->debug_http = true;
	$client->server = 'Garmin';
	$client->redirect_uri = 'https://'.$_SERVER['HTTP_HOST'].
		dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/login_with_garmin.php';

	$client->client_id = ''; $application_line = __LINE__;
	$client->client_secret = '';

	if(strlen($client->client_id) == 0
	|| strlen($client->client_secret) == 0)
		die('Please go to Garmin Apps page at '.
			'https://developerportal.garmin.com/user/me/apps?program=829 '.
			'create an application, and in the line '.$application_line.
			' set the client_id to Consumer key and client_secret with Consumer secret. ');

	if(($success = $client->Initialize()))
	{
		if(($success = $client->Process()))
		{
			if(strlen($client->access_token))
			{
				$success = $client->CallAPI(
					'https://apis.garmin.com/wellness-api/rest/user/id',
					'GET', array(), array(
						'FailOnAccessError' => true, 
					), $user);
				
				$success = $client->CallAPI(
					'https://apis.garmin.com/wellness-api/rest/activities?uploadStartTimeInSeconds=0&uploadEndTimeInSeconds=86400',
					'GET', array(), array(
						'FailOnAccessError' => true, 
					), $activity);
			}
		}
		$success = $client->Finalize($success);
	}
	if($client->exit)
		exit;
	if($success)
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Garmin OAuth client results</title>
</head>
<body>
<?php
		echo '<h1>You have logged in successfully with Garmin!</h1>';
		echo '<pre>User:', "\n\n", HtmlSpecialChars(print_r($user, 1)), '</pre>';
		echo '<pre>Activity:', "\n\n", HtmlSpecialChars(print_r($activity, 1)), '</pre>';
?>
</body>
</html>
<?php
	}
	else
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>OAuth client error</title>
</head>
<body>
<h1>OAuth client error</h1>
<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
</body>
</html>
<?php
	}

?>