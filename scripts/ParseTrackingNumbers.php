<?php
require('Shipment.php');
require('Amazon.php');
function addTrackingNumbers($userid){

	$servername = "localhost";
	$username = "userfrosting_adm";
	$password = "wheresmyship";
	$dbname = "userfrosting";
	$trackingNumber;
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dir = new DirectoryIterator(__DIR__."/messages/".$userid."/");
	$trackingNums=array();
	// $upsCount = 0;
	// $fedexCount = 0;
	$trackingNums['ups']=array();
	$trackingNums['fedex']=array();
	foreach ($dir as $fileinfo) {
		if (!$fileinfo->isDot() && is_file(__DIR__."/messages/".$userid."/".$fileinfo->getFilename())) {
			$file = __DIR__."/messages/".$userid."/".$fileinfo->getFilename();
			//search each file for UPS tracking numbers
			if (checkAmazonEmail($file)) {
				try {
					echo "Amazon email!\n";
					$file = getAmazonLink($file);
				} catch (Exception $e) {
					echo $e->getMessage() . "\n";
					echo "Crawling email instead...\n";
					$arr = __DIR__."/messages/".$userid."/".$fileinfo->getFilename(); 
					// Just for safety because everyone has trust issues with php
				}
			} else {
				echo "Not amazon email.\n";
			}
			$arr = parseTrackingNumber($file,'ups');
			foreach($arr as $e){
				printf($e."\n");


				$AlreadyInDB = false;
				$query = $conn->query('SELECT * FROM uf_shipments WHERE trackingNumber=' . '"' . $e . '"' . ';');
				foreach ($query as $row){
					if(strcasecmp($row['trackingNumber'], $e) == 0){
						$AlreadyInDB = true;	
					}
				}
				if($AlreadyInDB == false){

					$stmt = $conn->prepare("INSERT INTO uf_shipments (userid, trackingNumber, shipDate, origin, destination, currentLocation, eta, status, history) VALUES (:userid, :trackingNumber, :shipDate, :origin, :destination, :currentLocation, :eta, :status, :history);");
					$shipment = new Shipment($e);
					$shipDate = $shipment->getShipDate();
					$origin = $shipment->getOrigin()['ADDRESSLINE1'] . " " . $shipment->getOrigin()['CITY'] . " " . $shipment->getOrigin()['STATEPROVINCECODE'] . " " . $shipment->getOrigin()['POSTALCODE'] . " " . $shipment->getOrigin()['COUNTRYCODE'];
					$destination = $shipment->getDestination()['CITY'] . " " . $shipment->getDestination()['STATEPROVINCECODE'] . " " . $shipment->getDestination()['POSTALCODE'] . " " . $shipment->getDestination()['COUNTRYCODE'];
					$currentLocation = $shipment->getCurrentLocation()['CITY'] . " " . $shipment->getCurrentLocation()['STATEPROVINCECODE'] . " " . $shipment->getCurrentLocation()['COUNTRYCODE'];
					
					$eta = $shipment->getETA();
					$status = 'ADDED TO DATABASE';
					$history = array();
					$history_data = serialize($history);
					$stmt->bindParam(':userid', $userid);
					$stmt->bindParam(':trackingNumber', $trackingNumber);
					$stmt->bindParam(':shipDate', $shipDate);
					$stmt->bindParam(':origin', $origin);
					$stmt->bindParam(':destination', $destination);
					$stmt->bindParam(':currentLocation', $currentLocation);
					$stmt->bindParam(':eta', $eta);
					$stmt->bindParam(':status', $status);
					$stmt->bindParam(':history', $history_data);
					$trackingNumber = $e;
					$stmt->execute();
				}


				//array_push($trackingNums['ups'],$e);
				//$upsCount++;
			}

			//search for FedEx tracking numbers
			/*$arr = parseTrackingNumber($file,'fedex');
			foreach($arr as $e){
				printf($e."\n");
				array_push($trackingNums['fedex'],$e);
				$fedexCount++;
			}*/
			// We cannot use unlink because usage for URLs are unlink('https://www.amazon.com/blahblahblah') which is dynamically returned by getAmazonLink
			// unlink($file);
		}
	}
	//print_r($trackingNums);
}




function parseTrackingNumber($fileName, $shipper)
{
	$shipperCaps = strtoupper($shipper);
	$trackingNumbers= array();
	$text = file_get_contents($fileName)
	or die("Unable to get contents of HTML File\n");
	$regex=NULL;
	switch($shipperCaps){
		case 'UPS':
			//UPS Case
		$regex = '/[^a-zA-Z0-9](1Z|1z)([a-zA-z0-9]{16})/';
		if(preg_match_all($regex,$text,$matches)){
				//var_dump($matches[0]);
			$trackingNumbers= getValidUPSArray($matches[0]);

				// print_r($trackingNumbers);
		}
		else $trackNumbers=NULL;
		break;
		case 'FEDEX':
			// FedEx case
		$regex = '/[^a-zA-Z0-9][0-9][0-9]{14}(?=[^0-9])/';
		if(preg_match_all($regex,$text,$matches)){
			$trackingNumbers= getValidFedExArray($matches[0]);
				// print_r($trackingNumbers);
		}
		else $trackNumbers=NULL;
		break;
	}
	// printf($shipper);
	print_r($trackingNumbers);
	return $trackingNumbers;
}


function checkUPSDigit($trackNumString){
	//removes 1z
	$nums = substr($trackNumString,2);
	$length = strlen($nums);
	$count = 2;

	for($i=0;$i<$length;$i++){

		if($nums[$i] >= 'A' && $nums[$i] <= 'Z'){
			//if uppercase character found
			//change character to numeric equivalent using chart
			//A=2... H=9, I=0, J=1...
			$nums[$i]=2+(ord($nums[$i])-ord('A'))%10;
		}
	}
	$evenSum=0;
	$oddSum=0;
	for($i=0;$i<$length;$i++)
	{
		if($i%2==0){
			$oddSum+=(int)$nums[$i];
		}
		else{
			$evenSum+=(int)$nums[$i];
		}
	}
	$evenSum*=2;

	$remainder = ($evenSum+$oddSum)%10;
	if($remainder == intval($nums[strlen($nums)-1]))return true;
	return false;

}
function checkFedExDigit($trackNumString){
	return true;
}

function getValidUPSArray($array){
	$uniqueArray=array();
	foreach($array as $element){
		$element = substr($element,1);
		if(!in_array($element,$uniqueArray) && checkUPSDigit($element))array_push($uniqueArray,$element);
	}
	return $uniqueArray;
}
function getValidFedExArray($array){
	$uniqueArray=array();
	foreach($array as $element){
		$element = substr($element,1);
		if(!in_array($element,$uniqueArray) && checkFedExDigit($element))array_push($uniqueArray,$element);
	}
	return $uniqueArray;
}


?>
