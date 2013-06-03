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
        <title>Add a new Contact Type</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
        <?php
        include("header.php");
        include("projectFunctions.php");
        $ident=Connect( 'Sign-in');
        $row =0;
        session_start();
        $member = $_SESSION["member"];
        while($row <5) {
            if(array_key_exists("relat$row", $_POST)) {     //searchs for input
                $relat = new Relationship($_POST["relat$row"]);   //insert new relat
                $relat->insertRelat($ident,"<strong>New Contact Type for contact #$row successfully added</strong><br>"); 
                $member->replaceOther($row,$relat->getcode());
            }
            $row++;
        }
        if($member->insertMember($ident)) {
             echo "<strong>New member has been successfully added</strong><br>\n";
             if($member->insertEmergency) {
                 echo"<strong>Your Emergency Contacts have been successfully added</strong>\n";
             }
         }
         $_SESSION["member"]=$member;
        ?>
        <a href="index.php">Go to Sign-in page.</a><br>
        <a href="../index.php">Go to home page.</a>
        <?php include("footer.php");?>
    </body>
</html>
