<?php
require('Shipment.php');

$servername = "localhost";
$username = "userfrosting_adm";
$password = "wheresmyship";
$dbname = "userfrosting";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
while(true){
	$query = $conn->query('SELECT * FROM uf_shipments;');
	foreach ($query as $row){
		$currstatus = $row['status'];
		if(strcasecmp($currstatus, "DELIVERED") != 0){
			$stmt = $conn->prepare("UPDATE uf_shipments SET currentLocation=':currentLocation, status=':status' WHERE trackingNumber=':trackingNumber');");

			$trackingNumber = $row['trackingNumber'];
			$currentLocation = $shipment->getCurrentLocation()['CITY'] . " " . $shipment->getCurrentLocation()['STATEPROVINCECODE'] . " " . $shipment->getCurrentLocation()['COUNTRYCODE'];
			$status = $shipment->getStatus();
			$stmt->bindParam(':trackingNumber', $trackingNumber);
			$stmt->bindParam(':currentLocation', $currentLocation);
			$stmt->bindParam(':status', $status);
			$stmt->execute(); 

		}

	}
}

?>
