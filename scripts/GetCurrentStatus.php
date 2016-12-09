<?php
require('Shipment.php');

$servername = "localhost";
$username = "userfrosting_adm";
$password = "wheresmyship";
$dbname = "userfrosting";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
while(true){
	$query = $conn->query("SELECT * FROM uf_shipments WHERE status<>'DELIVERED';");
	$packages = array();
	$historys = array();
	foreach ($query as $row){
		$currstatus = $row['status'];
		if(strcasecmp($currstatus, "DELIVERED") != 0){
			array_push($packages, $row['trackingNumber']);
			array_push($historys, $row['history']);
		}

	}
	$counter = 0;
	foreach ($packages as $package){
			$stmt = $conn->prepare("UPDATE uf_shipments SET currentLocation=:currentLocation, status=:status, history=:history WHERE trackingNumber=:trackingNumber");

			$trackingNumber = $package; 
			$history = unserialize($historys[$counter]);
			$counter++;
			$shipment = new Shipment($trackingNumber);
			$currentLocation = $shipment->getCurrentLocation()['CITY'] . " " . $shipment->getCurrentLocation()['STATEPROVINCECODE'] . " " . $shipment->getCurrentLocation()['COUNTRYCODE'];
			$status = $shipment->getStatus();
			array_push($history, ($status . " " . $currentLocation));
			print_r($history);
			$history = serialize($history);
			print_r(unserialize($history));
			$stmt->bindParam(':trackingNumber', $trackingNumber);
			$stmt->bindParam(':currentLocation', $currentLocation);
			$stmt->bindParam(':status', $status);
			$stmt->bindParam(':history', $history);
			$stmt->execute(); 


	}
	$counter = 0;
}

?>
