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
        <title>Promotion Progress Report</title> 
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">        
         <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
        <?php
        include("header.php");
        include("projectFunctions.php");
        $ident=Connect('Sign-in');
        session_start();
        if(!isset($_SESSION["member"])) {           //if no member given redirect out and log
            auditLog($_SERVER["REMOTE_ADDR"],'DC');
            echo"<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=../signIn/?CAPID=\">";
            exit;
        }
        $member=$_SESSION["member"];
        $member->promotionReport($ident,true);
        ?>
        <a href="../index.php">Go Home</a> <br/>
        <a href="edit.php">Edit Your Personal, and Emergency Contact Information</a> <br/>
        <a href="logout.php">Logout</a>
        <?php include("footer.php");?>
    </body>
</html>