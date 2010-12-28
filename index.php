<?php
/* 
 *   iReview CMS
 *     by IRCReview Staff
 *
 *   See CREDITS for more information on developers
 *
 *   @version      0.1.0.0
 *   @copyright    Copyright © 2011 IRCReview
 *   @description  Index file that deals with the
 *                 query string and includes the 
 *                 required files for the end-user. 
 *   @url          http://ircreview.com/
 *
 */
 
@(include(dirname(__FILE__) . "/config.php")) or die("Couldn't find config.php");
 
// The global.php file is responsible for building the ACTION array that manages which
// pages are displayed depending on the URL query string.
//
// Later on, it will be needed for loading the core modules.
@(include(dirname(__FILE__) . "/global.php")) or die("Couldn't find global.php");

if(empty($actions)) {
	die("There was a problem in global.php, please get a new copy of it.");
}

if(empty($_GET['action']) || !in_array(@$_GET['action'], $actions)) {
	$_GET['action'] = 'home';
}

$Core['MySQL'] = new MySQL($settings);

echo 'To include: <br />' . dirname(__FILE__) . '/templates/' . $actions[$_GET['action']];
// @(include(dirname(__FILE__) . "/templates/header.php")) or die("Couldn't find templates/header.php");
// @(include(dirname(__FILE__) . "/templates/" . $actions[$_GET['action']])) or die("Couldn't find templates/" . $actions[$_GET['action']]);
// @(include(dirname(__FILE__) . "/templates/footer.php")) or die("Couldn't find templates/footer.php");

?>