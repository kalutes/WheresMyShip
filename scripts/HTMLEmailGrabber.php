
<?php
require_once __DIR__ . '/vendor/autoload.php';
require(__DIR__.'/ParseTrackingNumbers.php');

date_default_timezone_set('America/Indiana/Indianapolis');

define('APPLICATION_NAME', 'Gmail API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/gmail-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/gmail-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Gmail::GMAIL_READONLY)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

if (!file_exists('messages')) {
  mkdir('messages');
}
chdir('messages');
$newestDate = 0;

class Email {
	public $From;
	public $To;
	public $Subject;
	public $Date;
	public $HTMLBody;
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient($googleAuth) {
  global $currentID;
  global $conn;
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');
  
  //$authUrl = $client->createAuthUrl();
  //printf("%s\n", $authUrl);
  //$authCode = trim(fgets(STDIN)); 
  //$accessToken = $client->fetchAccessTokenWithAuthCode($googleAuth);
  //$conn->prepare("UPDATE uf_user SET googleauth = ? WHERE id = ?")->execute([json_encode($accessToken), $currentID]);
  $client->setAccessToken(json_decode($googleAuth, true));
  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    $conn->prepare("UPDATE uf_user SET googleauth = ? WHERE id = ?")->execute([json_encode($client->getAccessToken(), true), $currentID]);
  }
  return $client;
}

function listMessages($service, $userId) {
  $pageToken = NULL;
  $messages = array();
  $opt_param = array();
  do {
    try {
      if ($pageToken) {
        $opt_param['pageToken'] = $pageToken;
      }
      $messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);
      if ($messagesResponse->getMessages()) {
        $messages = array_merge($messages, $messagesResponse->getMessages());
        $pageToken = $messagesResponse->getNextPageToken();
      }
    } catch (Exception $e) {
      print 'An error occurred: ' . $e->getMessage();
    }
  } while (false);
  file_put_contents('messages', "START\r\n");
  foreach ($messages as $message) {
    //print 'Message with ID: ' . $message->getHistoryID() . "\r\n"
	$message3 = $service->users_messages->get('me', $message->getID());
	print 'Message Contents ' . (String)($message3->getSizeEstimate()) . "\r\n";
	file_put_contents('messages', serialize($message3) . "\r\n", FILE_APPEND);
  }

  return $messages;
}




/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.


//$messages = listMessages($service, 'me');

// Print the labels in the user's account.


function decodeBody($body) {
    $rawData = $body;
    $sanitizedData = strtr($rawData,'-_', '+/');
    $decodedMessage = base64_decode($sanitizedData);
    if(!$decodedMessage){
        $decodedMessage = FALSE;
    }
    return $decodedMessage;
}

/*Returns an array of headers: To, From, Subject, and Date for a message. Access member vars by typing $headers->Subject for example*/
function getOurHeaders($fullEmail){
    $headers = (object)array('From' => NULL, 'To' => NULL, 'Subject' => NULL, 'Date' => NULL);
    $headersArray = $fullEmail->getPayload()->getHeaders();
    foreach($headersArray as $entry){
        if($entry->name == "From"){
            $headers->From = $entry->value;
        }else if($entry->name == "To"){
            $headers->To = $entry->value;
        }else if($entry->name == "Subject"){
            $headers->Subject = $entry->value;
        }else if($entry->name == "Date"){
            $headers->Date = $entry->value;

        }
    }
    return $headers;
}




function getNewEmails($initialGrab, $lastEmailDate, $googleAuth)
{
	$client = getClient($googleAuth);
	$gmail = new Google_Service_Gmail($client);
	$list = $gmail->users_messages->listUsersMessages('me', ['maxResults' => 1000]);
	$i = 0;
	$date;
	$continueGrabbingEmails = true;
	$currentDate = time();
	global $conn;
	global $currentID;
	try{
		while ($list->getMessages() != null && $continueGrabbingEmails) {

			foreach ($list->getMessages() as $mlist) {

				$message_id = $mlist->id;
				$optParamsGet2['format'] = 'full';
				$single_message = $gmail->users_messages->get('me', $message_id, $optParamsGet2);
				$date = $single_message->getInternalDate() / 1000;
				if($initialGrab && $currentDate - $date > (3*24*3600))
				{
					$continueGrabbingEmails = false;
					$conn->prepare("UPDATE uf_user SET firstgrab = 0 WHERE id = ?")->execute([$currentID]);
					$conn->prepare("UPDATE uf_user SET lastemaildate = ? WHERE id = ?")->execute([$date, $currentID]);
					break;
				}
				if(!$initialGrab && $lastEmailDate >= $date)
				{
					$continueGrabbingEmails = false;
					$conn->prepare("UPDATE uf_user SET lastemaildate = ? WHERE id = ?")->execute([$date, $currentID]);
					break;
				}
				if($date > $lastEmailDate)
				{
					$lastEmailDate = $date;
				}
				$payload = $single_message->getPayload();
				$parts = $payload->getParts();
				// With no attachment, the payload might be directly in the body, encoded.
				$body = $payload->getBody();
				$FOUND_BODY = FALSE;
				// If we didn't find a body, let's look for the parts
				if(!$FOUND_BODY) {
					foreach ($parts  as $part) {
						if($part['parts'] && !$FOUND_BODY) {
							foreach ($part['parts'] as $p) {
								if($p['parts'] && count($p['parts']) > 0){
									foreach ($p['parts'] as $y) {
										if(($y['mimeType'] === 'text/html') && $y['body']) {
											$FOUND_BODY = decodeBody($y['body']->data);
											break;
										}
									}
								} else if(($p['mimeType'] === 'text/html') && $p['body']) {
									$FOUND_BODY = decodeBody($p['body']->data);
									break;
								}
							}
						}
						if($FOUND_BODY) {
							break;
						}
					}
				}
				// let's save all the images linked to the mail's body:
				if($FOUND_BODY && count($parts) > 1){
					$images_linked = array();
					foreach ($parts  as $part) {
						if($part['filename']){
							array_push($images_linked, $part);
						} else{
							if($part['parts']) {
								foreach ($part['parts'] as $p) {
									if($p['parts'] && count($p['parts']) > 0){
										foreach ($p['parts'] as $y) {
											if(($y['mimeType'] === 'text/html') && $y['body']) {
												array_push($images_linked, $y);
											}
										}
									} else if(($p['mimeType'] !== 'text/html') && $p['body']) {
										array_push($images_linked, $p);
									}
								}
							}
						}
					}
					// special case for the wdcid...
					preg_match_all('/wdcid(.*)"/Uims', $FOUND_BODY, $wdmatches);
					if(count($wdmatches)) {
						$z = 0;
						foreach($wdmatches[0] as $match) {
							$z++;
							if($z > 9){
								$FOUND_BODY = str_replace($match, 'image0' . $z . '@', $FOUND_BODY);
							} else {
								$FOUND_BODY = str_replace($match, 'image00' . $z . '@', $FOUND_BODY);
							}
						}
					}
					preg_match_all('/src="cid:(.*)"/Uims', $FOUND_BODY, $matches);
					if(count($matches)) {
						$search = array();
						$replace = array();

						// let's trasnform the CIDs as base64 attachements
						foreach($matches[1] as $match) {
							foreach($images_linked as $img_linked) {
								foreach($img_linked['headers'] as $img_lnk) {
									if( $img_lnk['name'] === 'Content-ID' || $img_lnk['name'] === 'Content-Id' || $img_lnk['name'] === 'X-Attachment-Id'){

										if ($match === str_replace('>', '', str_replace('<', '', $img_lnk->value))
												|| explode("@", $match)[0] === explode(".", $img_linked->filename)[0]
												|| explode("@", $match)[0] === $img_linked->filename){
											$search = "src=\"cid:$match\"";
											$mimetype = $img_linked->mimeType;
											$attachment = $gmail->users_messages_attachments->get('me', $mlist->id, $img_linked['body']->attachmentId);
											$data64 = strtr($attachment->getData(), array('-' => '+', '_' => '/'));
											$replace = "src=\"data:" . $mimetype . ";base64," . $data64 . "\"";
											$FOUND_BODY = str_replace($search, $replace, $FOUND_BODY);
										}
									}
								}
							}
						}
					}
				}

				// If we didn't find the body in the last parts,
				// let's loop for the first parts (text-html only)
				if(!$FOUND_BODY) {
					foreach ($parts  as $part) {
						if($part['body'] && $part['mimeType'] === 'text/html') {
							$FOUND_BODY = decodeBody($part['body']->data);
							break;
						}
					}
				}
				// With no attachment, the payload might be directly in the body, encoded.
				if(!$FOUND_BODY) {
					$FOUND_BODY = decodeBody($body['data']);
				}

				// Last try: if we didn't find the body in the last parts,
				// let's loop for the first parts (text-plain only)
				if(!$FOUND_BODY) {
					foreach ($parts  as $part) {
						if($part['body']) {
							$FOUND_BODY = decodeBody($part['body']->data);
							break;
						}
					}
				}
				if(!$FOUND_BODY) {
					$FOUND_BODY = '(No message)';
				}
				// Finally, print the message ID and the body
				print_r($message_id . "\r\n");
				if($mlist != null)
				{
					$headers = getOurHeaders($single_message);
					$email = new Email;
					$email->To = $headers->To;
					$email->From = $headers->From;
					$email->Subject = $headers->Subject;
					$email->Date = $headers->Date;
					$email->HTMLBody = $FOUND_BODY;
					//ADD CODE HERE TO SAVE TO DATABASE ONCE ITS CONSTRUCTED
					file_put_contents($date . " " . $i . '.html', $FOUND_BODY);
					$i++;
				}
			}

			if ($list->getNextPageToken() != null) {
				$pageToken = $list->getNextPageToken();
				$list = $gmail->users_messages->listUsersMessages('me', ['pageToken' => $pageToken, 'maxResults' => 1000]);
			} else {
				break;
			}
		}
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}

$servername = "localhost";
$username = "userfrosting_adm";
$password = "wheresmyship";
$dbname = "userfrosting";
$id = 1; //must be changed to generic user id
$trackingNumber;
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$currentID = 0;
while(true)
{
	$query = $conn->query('SELECT * FROM uf_user');
	foreach ($query as $row)
	{	
		$currentID = $row['id'];
		if (!file_exists($row['id'])) {
  			mkdir($row['id']);
		}
		chdir($row['id']);
		if(!$row['googleauth'])
		{
			//user hasn't added an email account, ignore
		}
		else
		{	
			$timerDate = time();
			print_r("Grabbing Emails for user " . $row['id'] . "\n");
			getNewEmails($row['firstgrab'], $row['lastemaildate'], $row['googleauth']);
			chdir("../..");
			addTrackingNumbers($row['id']);
			chdir("messages/".$row['id']);
			
		}
		chdir("..");
	}
}	
