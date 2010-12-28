<?php
/* 
 *   iReview CMS
 *     by IRCReview Staff
 *
 *   See CREDITS for more information on developers
 *
 *   @version      0.1.0.0
 *   @copyright    Copyright © 2011 IRCReview
 *   @description  Global file that is responsible for
 *                 initialization of the system.
 *   @url          http://ircreview.com/
 *
 */

//               'action' => 'filename.php in templates/',
$actions = array('home' => 'home.php',
				 'staff' => 'staff.php'
				 );

$Core = array();
$coreFiles = glob(dirname(__FILE__) . "/core/Core_*.php");
foreach($coreFiles as $file) {
	preg_match("/Core_(.*)\.php/", $file, $className);
	
	$className = $className[1];	
	
	// Let's load all classes, they will still need to be initiated manually though.
	// Highly impossible to die here, but let's play safe.
	@(include($file)) or die("Couldn't load core/" . $className . ".php.");
	if($className == 'MySQL') {	
		continue; // MySQL will be initiated in index.php
	}
	$Core[$className] = new $className();
}

?>