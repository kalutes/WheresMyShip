<?php

	/**
	 * Checks if email is from Amazon and contains link for shipment
	 * Returns True Boolean if it is, otherwise False Boolean
	 */
	function checkAmazonEmail($pathToEmailFile) {
		$text = file_get_contents($pathToEmailFile)
			or die("Failed to open email file.\n");
		$target = '/[.]*[Hh]as [Ss]hipped[.]*/i';
		if (strstr("ship-confirm@amazon.com", $text)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns link to amazon.com contains tracking number
	 * Throws exception if not found
	 */
	function getAmazonLink($pathToEmailFile) {
		$text = file_get_contents($pathToEmailFile)
			or die("Failed to open email file.\n");
		$exploded = explode("\"", $text);
		foreach ($exploded as $potential) {
			$maybe = htmlspecialchars_decode($potential);
			if (!filter_var($maybe, FILTER_VALIDATE_URL) === false) {
				if (strstr($maybe, "shiptrack")) {
					return $maybe;				}
			}
		}
		throw new Exception('Unable to find link to amazon.com with tracking info.');
	}

?>