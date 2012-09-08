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
        <title>resign-in for security</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico"/>
    </head>
    <body>
        <?php
        include("projectFunctions.php");
        session_start();
        include("header.php");
        ?>
        <p>
            We have detected that there has been an attempt to hijack your session (someone is trying to imitate you
            to gain access to your login).  Please sign-in again below so we may verify that you are who you say you are, and not the hacker.
            Thank you for your patience.
        </p>
        <form action="redirect.php" method="post">
            <input type="text" size="5" name="user" value="<?php echo $_SESSION['member']->getCapid(); ?>"/><br>
            <input type="password" size="5" name="password"/><br>
            <input type="submit" value="Sign-in"/>
        </form>
    </body>
</html>