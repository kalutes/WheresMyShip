<?php
require(__DIR__.'/CarrierInfo.php');
require(__DIR__.'/UPS_API/UPSTrack.php');

class Shipment {
	private $emailSource;
	private $trackingNumber;
	private $origin;
	private $destination;
	private $waypoints;
	private $carrier;
	private $sender;
	private $currentLocation;
	private $ETA;
	private $status;


	/*
	 * Setter function
	 */
	function setTrackingNumber($num) {
		$this->trackingNumber = $num;
	}

	/*
	 * Accessors function
	 * @return shipment properties
	 */
	function getEmailSource() {
		return $this->emailSource;
	}

	function getTrackingNumber() {
		return $this->trackingNumber;
	}

	function getOrigin() {
		return $this->origin;
	}

	function getDestination() {
		return $this->destination;
	}

	function getWaypoints() {
		return $this->waypoints;
	}

	function getCarrier() {
		return $this->carrier;
	}

	function getSender() {
		return $this->sender;
	}

	function getCurrentLocation() {
		return $this->currentLocation;
	}

	function getETA() {
		return $this->ETA;
	}

	function getStatus() {
		return $this->status;
	}

	/*
	 * Update function
	 * @return error code: (-1) for API issues, (0) for other errors, (0) for success
	 * TODO: complete this function.
	 */
	function update() {
		$returned = upsTrack($this->trackingNumber);
		$this->origin = $returned[TRACKRESPONSE][SHIPMENT][SHIPPER];
		$this->destination = $returned[TRACKRESPONSE][SHIPMENT][SHIPTO];
		$carrier = new CarrierInfo;
		$this->carrier = $carrier->UPS();
		print_r($this);
	}
}
?>
