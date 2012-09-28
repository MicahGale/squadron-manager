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
    $_SESSION['input']= array();
   for($i=0;$i<$_POST['number'];$i++) {      //loop through to find which one was searched for and save the input
       if(isset($_POST["search$i"])&&$_POST["search$i"]=="Search") {  //if was the one searched for
           
       } else if(isset($_POST['cap'][$i])&&$_POST['cap'][$i]!="") {  //if the capid wasn't blank then save it
           
       }
   } 
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
        include("squadManHeader.php");
        if(isset($_GET['ecode'])) {  //if event code is specified then create a bunch of inputs
            $numberOfInserts=50;
            ?>
            <strong>Please enter attendance for this event below</strong><br>
            <br>
            <form method="post">
                <input type="hidden" name="number" value="<?php echo $numberOfInserts;?>"/>
            <?php
            for($i=0;$i<$numberOfInserts;$i++) {  //loop through to create a ton of inputs
                echo 'Insert by CAPID: <input type="text" name="cap[]" size="5" maxlength="6"/> or <input type="submit" name="search'.$i.'" value="Search"/>'."<br>\n";
            }
            ?>
                <input type="submit" name="insert" value="insert"/>
            </form>
            <?php
        }
        ?>
        
    </body>
</html>
