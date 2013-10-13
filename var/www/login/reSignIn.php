<?php
/* * Copyright 2013 Micah Gale
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
session_start();
if(!isset($_SESSION['resignin'])) { //if session has died
    header("refresh:0; url=/login/endSession.php");
    exit;
}
if(isset($_SESSION['attempts'])&&$_SESSION['attempts']>=MAX_LOGIN) {
    log_off ();
    header("refresh:0; url=/login/endSession.php");
    exit;
}
if(isset($_POST['password'])) { //if has password given
    $ident=  connect('ViewNext');
     $passes=  parse_ini_file(PSSWD_INI);
     $salt=$passes['salt'];
     $member= $_SESSION['member'];
     $password=$_POST['password'];
     if($member->check_password($ident, $password, $salt)&&!$member->check_terminated($ident)) {  //checks the password
         auditLog($_SERVER['REMOTE_ADDR'],'RS');
         $_SESSION['resignin']=null;
         unset($_SESSION['resignin']);  //say signed in fine
         header("refresh:0; url=/login/home.php");
         exit;
    } else {
        if(!isset($_SESSION['attempts']))  //count number of bad attempts
            $_SESSION['attempts']=0;
        else
            $_SESSION['attempts']++;
        sleep(BAD_LOGIN_WAIT);        //waits if bad login
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>resign-in for security</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico"/>
        <script type="text/javascript" src="/java_script/CAPS_LOCKS.js"></script>
    </head>
    <body>
        <?php
        include("header.php");
        ?>
        <p>
            We have detected that there has been an attempt to hijack your session (someone is trying to imitate you
            to gain access to your login).  Please sign-in again below so we may verify that you are who you say you are, and not the hacker.
            Thank you for your patience.
        </p>
        <form method="post">
            <input type="text" size="5" disabled="disabled" value="<?php echo $_SESSION['member']->getCapid(); ?>"/><br>
            <input type="password" size="5" name="password" onkeypress="check_caps(event)"/><span id="warn" class="warning"></span><br>
            <input type="submit" value="Sign-in"/>
        </form>
    </body>
</html>