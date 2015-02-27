<?php
@session_start();

require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

require_once('tweeu_config.php');

/* Build TwitterOAuth object with client credentials. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
 
/* Get temporary credentials. */
$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $_GET['callback']));

/* If last connection failed don't display authorization link. */
switch ($connection->getLastHttpCode())
{
	case 200:
		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	
		/* Build authorize URL and redirect user to Twitter. */
		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

		header('Location: ' . $url); 
		break;
	default:
		/* Show notification if something went wrong. */
		echo 'Could not connect to Twitter. Refresh the page or try again later.';
}
