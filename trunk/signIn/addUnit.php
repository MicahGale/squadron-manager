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
        <title>Add A CAP Unit</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../patch.icon">
    </head>
    <body>
        <?php
        include("header.php");
        include("projectFunctions.php");
         $ident=Connect('Sign-in','ab332kj2klnnfwdndsfopi320932i45n425l;kfoiewr','localhost');
         session_start();       //connect to db and continue session
         $newUnit = new unit($_POST["charter"],$_POST["region"],$_POST["wing"]);
         $newUnit->insert_unit($ident,"<strong>New Unit Successfully added</strong><br>\n");
         $newMember=$_SESSION["member"];
         $newMember->unit_set($newUnit);
         if($newMember->insertMember($ident)) {
             echo "<strong>New member has been successfully added</strong><br>\n";
             if($newMember->insertEmergency) {
                 echo"<strong>Your Emergency Contacts have been successfully added</strong>\n";
             }
         }
         $_SESSION["member"]=$newMember;
        ?>
        <a href="index.php">Go to Sign-in page.</a><br>
        <a href="../index.ph">Go to home page.</a>
        <?php include("footer.php")?>
    </body>
</html>