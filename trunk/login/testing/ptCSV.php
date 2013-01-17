<?php
/**
 * Creates a downloadable csv of the pt testing sign-up
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
require('projectFunctions.php');
session_secure_start();
header("Content-type: text/csv");
$now =new DateTime;
header("Content-Disposition: attachment; filename=cpft_test_".$now->format(EVENT_CODE_DATE).".csv");
header("Pragma: no-cache");
header("Expires: 0");
?>
