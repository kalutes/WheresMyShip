<?php
	if (!file_exists('messages')) {
		throw new Exception('HTMLEmailGrabber.php has to be run first to fetch emails.');
	}
	chdir('messages');
	$dir = new DirectoryIterator(__DIR__);
	foreach ($dir as $filedesc) {
			if (!$filedesc->isDot() && file_exists($filedesc->getFilename())) {

			}
	}
?>
