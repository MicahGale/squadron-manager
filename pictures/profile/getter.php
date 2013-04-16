<?php
/**
 * A secure way to see pictures on the server
 * 
 * All sensetive pictures (profile pictures) go through here so only people who should see pictures can
 * prevents from wandering through the pictures without a logon
 * 
 * $_GET
 * CAPID-the capid of the member to see the picture
 * @package Squadron-manager
 * @copyright (c) 2013, Micah Gale
 */
/*  Copyright 2013 Micah Gale
 *
 * This file is a part of Squadron Manager
 *
 *Squadron Manager is free software licensed under the GNU General Public License version 3.
 * You may redistribute and/or modify it under the terms of the GNU General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * Squadron Manager comes without a warranty; without even the implied warranty of merchantability
 * or fitness for a particular purpose. See the GNU General Public License version 3 for more
 * details.
 *
 * You should have received the GNU General Public License version 3 with this in GPL.txt
 * if not it is available at <http://www.gnu.org/licenses/gpl.txt>.
 *
 * 
 */
require("projectFunctions.php");
session_secure_start();
header("content-type:image/jpeg");  //say it's a picture
$capid=  cleanInputInt($_GET['capid'], 6, 'Picture CAPID');
header("Content-Disposition: inline ; filename=$capid.jpg");
header("Pragma: no-cache");
header("Expires: 0");
?>
