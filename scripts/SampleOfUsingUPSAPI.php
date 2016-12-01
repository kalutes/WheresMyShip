<?php
	require('Shipment.php');
	$trackingNumber = '1Z20E961YW55079264';
	$ship = new Shipment;
	$ship->setTrackingNumber($trackingNumber);
	$ship->update();
?>
