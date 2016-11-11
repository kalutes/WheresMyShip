<?php


/**
 * Takes in Email object (class)
 * Will move email to appropriate folder.
 */
function selectEmails() {
				if (!file_exists('messages')) {
								throw new Exception('No emails has been fetch!');
				}
				chdir('messages');
				if (!file_exists('confirmed')) {
								mkdir('confirmed');
				}
				if (!file_exists('maybe')) {
								mkdir('maybe');
				}
				if (!file_exists('nonrelated')) {
								mkdir('nonrelated');
				}
				$target = '/[.]*[Hh]as [Ss]hipped[.]*/i';
				$dir = new DirectoryIterator(__DIR__.'/messages');
				foreach ($dir as $filedesc) {
								// print("Checking ".$filedesc->getFilename()."\n");
								if (!$filedesc->isDot() && preg_match('/[.]*.html$/i', $filedesc->getFilename()) == 1) {
												// print("This file might have something!\n\n");
												$text = file_get_contents(__DIR__.'/messages/'.$filedesc->getFilename()) 
																or die("File $filedesc->getFilename() is unreadable.\n");
												if (preg_match($target,$text) == 1 /* Subject contains target phrases */) {
																// Move email to confirmed folder
																rename($filedesc->getFilename(), 'confirmed/'.($filedesc->getFilename()));
												} else if (maybe($test) > 3 /* Has a high score of target words */) {
																// Move email to maybe folder
																rename($filedesc->getFilename(), 'maybe/'.($filedesc->getFilename()));
												} else {
																// Move email to nonrelated folder
																rename($filedesc->getFilename(), 'nonrelated/'.($filedesc->getFilename()));
												}
								} else {
												// print("Bummer. This file is not worth checking.\n\n");
								}
				}
}

function maybe($text) {
				$keywords = array('amazon', 'ebay', 'fedex', 'ups', 'usps');
				$score = 0;
				foreach ($keywords as $target) {
								if (preg_match('/[.]*'.$target.'[.]*$/i', $text) == 1) {
											$score = $score + 1;
								}
				}
				return $score;;
}
?>
