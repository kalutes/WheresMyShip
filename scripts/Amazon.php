<?php
	/**
	 * Checks if email is from Amazon and contains link for shipment
	 * Returns True Boolean if it is, otherwise False Boolean
	 */
	function checkAmazonEmail($pathToEmailFile) {
		$text = file_get_contents($pathToEmailFile);
		if (!$text) {
			throw new Exception('Failed to open email file.');
		}
		$target = '/[.]*[Hh]as [Ss]hipped[.]*/i';
		$amazon = '/[.]*[Aa]mazon[.]*/i';
		if (stristr($text, "ship-confirm@amazon.com") || (preg_match($target, $text) && preg_match($amazon, $text))) {
			return true;
		}
		return false;
		// return preg_match($target, $text);
	}

	/**
	 * Returns link to amazon.com contains tracking number
	 * Throws exception if not found
	 */
	function getAmazonLink($pathToEmailFile) {
		$text = file_get_contents($pathToEmailFile);
		if (!$text) {
			throw new Exception('Failed to open email file.');
		}
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