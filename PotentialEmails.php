<?php

/**
 * Takes in Email object (class)
 * Will move email to appropriate folder.
 */
function selectEmails($email) {
	if (!file_exists('messages')) {
		throw new Exception('No emails has been fetch!');
	}
	chdir('messages');
	$dir = new DirectoryIterator(__DIR__);
	if (!file_exists('confirmed')) {
		mkdir('confirmed');
	}
	if (!file_exists('maybe')) {
		mkdir('maybe');
	}
	if (!file_exists('nonrelated')) {
		mkdir('nonrelated');
	}
	$target = '*[Hh]as [Ss]hipped*';
	foreach ($dir as $filedesc) {
		if (!$filedesc->isDot() && file_exists($filedesc->getFilename())) {
			if (preg_match($target,$email->Subject) == 1 /* Subject contains target phrases */) {
				// Move email to confirmed folder
				rename($filedesc->getFilename(), 'confirmed/'.($filedesc->getFilename()));
			} else if (/* Has a high score of target words */) {
				// Move email to maybe folder
				rename($filedesc->getFilename(), 'maybe/'.($filedesc->getFilename()));
			} else {
				// Move email to nonrelated folder
				rename($filedesc->getFilename(), 'nonrelated'.($filedesc->getFilename()));
			}
		}
	}
}
?>
