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
        <title>Finish Visitor Sign-in</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
        <?php
        include("header.php");
        include("projectFunctions.php");
        $ident=Connect('Sign-in');
        session_start();
        $_SESSION["vistor"]->setContName($_POST["ContName"]);  
        $_SESSION["visitor"]->setContPhone($_POST["ContPhone"]);
        if(!$_SESSION["visitor"]->inset($ident,"<strong>You have Successfully been Signed in</strong>")) {
            echo"<strong> You were not signed in</strong><br>\n";
            $_SESSION["visitor"]->getRestofFields("../finishVisit.php");
        } if($_SESSION["visitor"]->badInput) {
            $_SESSION["visitor"]->getRestofFields("../finishVisit.php");
        }
        ?>
        <a href="complete.php">Go Back to Home Page</a>
        <?php
        include("footer.php");
        ?>
    </body>
</html>