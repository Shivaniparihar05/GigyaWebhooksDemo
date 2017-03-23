<?php
  require('Pusher.php');

  function IsValid($payload, $hash)
  {
  	$PARTNERSECRET = "PARTNERSECRETHERE";
	$partnerDecoded = base64_decode($PARTNERSECRET);
  
  
// PHP converts the header into all UPPERCASE, converts hyphens("-") into underscores("_") and prepends "HTTP_"
// or "HTTPS_" to the front, based on the protocol used.
// Keep this in mind if converting this code to work in a language other than PHP.
// The actual header passed by Gigya is: "X-Gigya-Sig-Hmac-Sha1".
$msgHash = $hash;
  
  
// Get the JSON payload sent by Gigya.
$messageJSON = $payload;
  
  
// Decode the JSON payload into an associative array.
$jsonDecoded = json_decode($messageJSON, TRUE);
  
  
// Builds and returns expected hash
function createMessageHash($secret, $message){
    return base64_encode(hash_hmac('sha1', $message, $secret, true));
}
  
  
// Compares the two parameters (in this case the hashes) and returns TRUE if they match
// and FALSE if they don't. 
function hashesMatch($expected, $received){
    if ($expected == $received) {
        return TRUE;
    }
    return FALSE;
}
  
  
// Check if the hash matches. If it doesn't, it could mean that the data was tampered
// with in flight. If so, do not send 2XX SUCCESS - let Gigya re-send the notification.
if (hashesMatch(createMessageHash($partnerDecoded, $messageJSON), $msgHash)) {
	return true;
}
return false;
  }

 $payload = file_get_contents('php://input');
 $header = $_SERVER['HTTP_X_GIGYA_SIG_HMAC_SHA1'];

 if(IsValid($payload, $header))
 {

$messageJSON = file_get_contents('php://input') ;
$jsonDecoded = json_decode($messageJSON, TRUE);

//file_put_contents("log.txt", $messageJSON, FILE_APPEND | LOCK_EX);

// Pusher credentials - sign up at https://pusher.com/
 $options = array(
    'encrypted' => true
  );
  $pusher = new Pusher(
    '', //key
    '', // secret
    '', // app_id
    $options
  );

$curEvt = $jsonDecoded['events'][$x]['type'];

$eventString = "";
if(sizeof($jsonDecoded['events']) > 1)
{
	$eventString = "multiple";
}
else
{
	$eventString = $jsonDecoded['events'][0]['type'];
}

  $data['payload'] = $messageJSON;
  $data['header'] = $_SERVER['HTTP_X_GIGYA_SIG_HMAC_SHA1'];
  $data['timestamp'] = time();
  $payload = base64_encode(json_encode($data));
  $pusher->trigger('webhooks', $eventString, $payload);


}
