<?php

require_once('config.php');

class TweetlyUpdater {

    public $buhApiUrl = "http://buh.bz";
    public $twitterSource = "tweetlyupdater";
    public $twitterApiUrl = "http://twitter.com";
    public $debug = false;

    private $twitterConsumerKey = CONSUMER_KEY;
    private $twitterConsumerSecret = CONSUMER_SECRET;
    private $twitterConnection;
    
    function __construct($twitterKey, $twitterSecret) {
        $this->twitterConnection = new TwitterOAuth($this->twitterConsumerKey, $this->twitterConsumerSecret, $twitterKey, $twitterSecret);
    }

    public function twitterVerifyCredentials() {
        $twObject = $this->twitterConnection->get("account/verify_credentials");
        if ($twObject->{'name'}) {
                return true;
        } else {
                error_log("Twitter auth failed: " . $twObject->{'error'});
        }
        return false;
    }
    
    public function twitterUpdate($message) {
        //error_log("Sending twitter update");
        $twObject =$this->twitterConnection->post('statuses/update', array('status' => $message));
        if ($twObject->{'id'} != null) {
            return  $twObject->{'id'} ;
        } else {
            error_log("Twitter update failed");
            return null;
        }
    }
    
    public function getBuhUrl($longurl) {
        $shortUrl = $longurl;
            $buhResp = $this->curlURL($this->buhApiUrl . "/api.php?url=" . $longurl);
            if ($buhResp != '') {
                $shortUrl = $buhResp;
            } else {
                error_log("buh.bz shorten request failed");
            }

	return $shortUrl;
    }

    private function curlURL($url) {
        if ($this->debug) {
                error_log("curlGet with url: " . $url);
        }
	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        if ($this->debug) {
                error_log("curl GET response: " . $data);
        }
	curl_close($ch);
        return $data;
    }

    private function curlGet($url, $user, $password) {
        if ($this->debug) {
                error_log("curlGet with url: " . $url);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($user != '' && $password != '') {
            if ($this->debug) {    
                error_log("Using authentication");
            }
            curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        if ($this->debug) {
                error_log("curl GET response: " . $data);
        }
        curl_close($ch);                 
        return $data;
    }
    
    private function curlPost($url, $fields, $user, $password) {
        if ($this->debug) {
                error_log("curlPOST with url: " . $url);
        }
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }  
        rtrim($fields_string,'&');  
        
        $ch = curl_init();  
            
        curl_setopt($ch,CURLOPT_URL,$url);  
        curl_setopt($ch,CURLOPT_POST,count($fields));  
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

        if ($user != '' && $password != '') {
            if ($this->debug) {    
                error_log("Using authentication");
            }
            curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        }
          
        $result = curl_exec($ch);
        if ($this->debug) {
                error_log("curl POST response: " . $result);
        }    
        curl_close($ch);
        return $result;
    }

}
?>
