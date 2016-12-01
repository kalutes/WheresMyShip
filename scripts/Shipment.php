<?php
require(__DIR__.'/UPS_API/UPSTrack.php');

class Shipment {
	private $trackingNumber;
	private $shipDate;
	private $origin;
	private $destination;
	private $currentStatus;
	private $carrier;
	private $sender;
	private $currentLocation;
	private $ETA;
	private $status;

	/**
	 * Constructor function
	 */
	function __construct($trackingNumber) {
		$this->trackingNumber = $trackingNumber;
		$this->update();
	}

	/**
	 * Add email address of sender
	 */
	function setSender($sender) {
		$this->sender = $sender;
	}

	/**
	 * Accessors function
	 * @return shipment properties
	 */
	function getTrackingNumber() {
		return $this->trackingNumber;
	}

	function getShipDate() {
		return $this->shipDate;
	}

	function getOrigin() {
		return $this->origin;
	}

	function getDestination() {
		return $this->destination;
	}

	function getStatus() {
		return $this->status;
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

	/*
	 * Update function
	 */
	function update() {
		$returned = upsTrack($this->trackingNumber);
		// print_r($returned);
		$shipmentInfo = $returned['TRACKRESPONSE']['SHIPMENT'];
		// print_r($shipmentInfo);
		$this->carrier = 'UPS';
		$this->shipDate = $shipmentInfo['PICKUPDATE'];
		$this->origin = $shipmentInfo['SHIPPER']['ADDRESS'];
		$this->destination = $shipmentInfo['SHIPTO']['ADDRESS'];
		$this->status = $shipmentInfo['PACKAGE']['ACTIVITY']['STATUS']['STATUSTYPE']['DESCRIPTION'];
		$this->carrier = 'UPS';
		$this->sender = $shipmentInfo['SHIPPER'];
		$this->currentLocation = $shipmentInfo['PACKAGE']['ACTIVITY']['ACTIVITYLOCATION']['ADDRESS'];
		$this->ETA = $shipmentInfo['SCHEDULEDDELIVERYDATE'];
		if ($this->ETA == 0) {
			$this->ETA = $shipmentInfo['PACKAGE']['RESCHEDULEDDELIVERYDATE'];
		}
		print_r($this);
	}
}
?>
