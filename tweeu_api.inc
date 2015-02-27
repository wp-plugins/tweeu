<?php

require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

require_once('tweeu_config.php');

class Tweeupdater
{
	public $buhApiUrl = "http://buh.bz";
	public $debug = false;

	private $buhKey = BUH_KEY;
	private $twitterConsumerKey = CONSUMER_KEY;
	private $twitterConsumerSecret = CONSUMER_SECRET;
	private $twitterConnection;
    
	function __construct($twitterKey, $twitterSecret)
	{
		$this->twitterConnection = new TwitterOAuth($this->twitterConsumerKey, $this->twitterConsumerSecret, $twitterKey, $twitterSecret);
	}

	public function twitterVerifyCredentials()
	{
		$twObject = $this->twitterConnection->get("account/verify_credentials");
		if ($this->twitterConnection->getLastHttpCode() == 200) {
			return true;
		}

		if ($this->debug) {
			error_log("tweeu: Twitter auth failed, HTTP response: " . $this->twitterConnection->getLastHttpCode());
		}

		return false;
	}

	public function twitterScreenName()
	{
		$twObject = $this->twitterConnection->get("account/verify_credentials");
		if ($this->twitterConnection->getLastHttpCode() == 200) {
			return $twObject->name;
		}

		return null;
	}

	public function twitterUpdate($message)
	{
		$twObject = $this->twitterConnection->post('statuses/update', array('status' => $message));
		if ($this->twitterConnection->getLastHttpCode() == 200) {
			return true;
		}

		if ($this->debug) {
			error_log("tweeu: Twitter update failed, HTTP response: " . $this->twitterConnection->getLastHttpCode());
		}

		return false;
	}
    
	public function getBuhUrl($longurl)
	{
		$shortUrl = $longurl;

		$buhResp = $this->curlURL($this->buhApiUrl . "/api.php?url=" . $longurl . "&hash=" . $this->buhKey);
		if ($buhResp != '') {
			return $buhResp;
		} 

		if ($this->debug) {
			error_log("tweeu: buh.bz failed to short: " . $longurl);
		}

		return $shortUrl;
	}

	private function curlURL($url)
	{
		if ($this->debug) {
			error_log("tweeu: curlURL with url: " . $url);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$data = curl_exec($ch);
		if ($this->debug) {
			error_log("tweeu: curl GET response: " . $data);
		}

		curl_close($ch);
		return $data;
	}
}
?>
