<?php
class CarrierInfo {
	private $carrierName;
	private $trackingFormat;
	private $API;

	function UPS() {
		$this->carrierName = "UPS";
		$this->trackingFormat = "1Z"; //TODO: Update according usage
		$this->API = NULL; //TODO: Update according usage
		return $this;
	}
}
?>
