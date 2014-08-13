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
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Finalize approving new Members</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php
        $ident = Connect('login');
        include('squadManHeader.php');
        $added = true;
        $members=array();
        if($_POST['submit']=='approve selected') {
            $size = count($_POST['approve']);
            $number = count($_SESSION['members']);
            $buffer = null;
            settype($buffer,"array");
            for($i=0;$i<$number;$i++) {   //cycles to restructure index array, organize by capids
                $buffer[$_SESSION['members'][$i]->getcapid()] =$_SESSION['members'][$i];
            }
            for($i=0;$i<$size;$i++) {                   //creates stacked array of members being approved
                array_push($members, $buffer[$_POST['approve'][$i]]);    //stacks 1 at a time by pulling capid from approved array
            }
            
        } else {
            $members = $_SESSION['members'];  //else just use all the members
        }
        $size = count($members);
        for($i=0;$i<$size;$i++) {             //cycle through all approvals
            $member = $members[$i];
            $member->massUpdateFields($_POST);            //updates fields
            if(!$member->saveUpdates($ident)) {
                $added=false;
                echo"<strong>We could not update the information for: ".$member->getcapid()."</strong><br>";
            }
        }
        if($added) {                        //if all were added fine
            ?>
        <p><strong>All changes have been saved.</strong></p>
        <?php
        }
        unset($_SESSION['members']);                       //releaces the allocated resources
        ?>
    </body>
</html>