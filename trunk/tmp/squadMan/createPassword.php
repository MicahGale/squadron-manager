<?php
/** Saves the password for the first User
 * This is meant to allow users to create the password for the first user upon install.
 * It will executed from command line, and will have the password, capid, and position
 *  passed to it as arguments. It will hash the salted password, save it and save
 * the staff position.
 * 
 * Usage:
 * 
 * php -f /path/to/file.php -- capid position password
 */
/* Copyright 2014 Micah Gale
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
 */

require('projectFunctions.php');
$ident=connect("login");
$member= new member($argv[1],1,$ident);
$passes=  parse_ini_file(PSSWD_INI);
$salt=$passes['salt'];
$member->set_password($ident, $member->hash_password($argv[3], $salt));  //saved password
echo "Password saved\n";
$member->insert_staff_position(array($argv[2]), $ident);   //insert the staff pos
echo "Staff position saved\n";
close($ident);