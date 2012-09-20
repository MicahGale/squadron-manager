<?php
/* * Copyright 2012 Micah Gale
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
include("projectFunctions.php");
session_secure_start();
if(in_array("Search", $_POST)) {             //if searched for member save input, then redirect to search
    
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <title>Add Attendance to an event</title>
    </head>
    <body>
        <?php
        if(isset($_GET['ecode'])) {  //if event code is specified then create a bunch of inputs
            $numberOfInserts=50;
            ?>
            <strong>Please enter attendance for this event below</strong><br>
            <br>
            <form method="get">
            <?php
            for($i=0;$i<$numberOfInserts;$i++) {  //loop through to create a ton of inputs
                
            }
            ?>
            </form>
            <?php
        }
        ?>
        
    </body>
</html>
