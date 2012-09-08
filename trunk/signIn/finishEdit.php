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
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Save Personal Information</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
        <?php
        include("header.php");
        include("projectFunctions.php");
        session_start();
        if(!array_key_exists("member", $_SESSION)) {                                //log and redirect user
            auditLog($_SERVER["REMOTE_ADDR"],"DC");
            echo"<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=../signIn/?CAPID=\">";
        } else {
            $ident=Connect('Sign-in','ab332kj2klnnfwdndsfopi320932i45n425l;kfoiewr','localhost');
            $member= $_SESSION["member"];
            if($member->editFields($_POST,$ident)) {             //if contact update succeeded then say so else say so
                echo "<strong>Your Contact Information Updates have been sucessfully saved</strong><br/>\n";
            } else {
                echo "<strong>Your Changes to your Contact Information were not saved</strong><br/>\n";
            }
            if($member->updateFields($ident)) {
                echo "<strong>Your Changes to your Information have been saved</strong><br/>\n";
            } else {
                echo "<strong>Your Changes to your Information could not be saved</strong><br\>\n";
            }
        }
        ?>
        <a href="../index.php">Go Home</a> <br/>
        <a href="promotionReport.php">View Your Promotion Report</a> <br/>
        <a href="logout.php">Logout</a>
        <?php include("footer.php");?>
    </body>
</html>
