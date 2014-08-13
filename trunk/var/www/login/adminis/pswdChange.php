<?php
/**
 * Allows users to change their password
 * $_POST
 * current- the user's current password
 * new- the new password
 * repeat- the password repeated
 * 
 * @package Squadron-Manager
 */
/* Copyright 2013 Micah Gale
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
require("projectFunctions.php");
session_secure_start();
$ident=  connect("login");
$parsed=  parse_ini_file(PSSWD_INI);
$salt=$parsed['salt'];
if(isset($_POST['current'])) {  //if they give a password do stuff
    if($_SESSION['member']->check_password($ident,$_POST['current'],$salt)) {  // get the current password and check it
        $good_pass=true;   //record it was a good pass
        $message=  verify_password($_POST['new'], $_POST['repeat'],$_POST['current']);
        if($message[0]) {                 //if the password was good
            $pass_require=true;
            $hash= $_SESSION['member']->hash_password($_POST['new'],$salt);  //hash the password
            if($_SESSION['member']->set_password($ident,$hash)) {             //if password changed properly
                header("refresh:5;url=/login/home.php");          //exit
                $failed=false;
            } else {
                $failed=true;                  //display that it failed
            }    
        } else {
            $pass_require=false;
        }
    } else {
        $good_pass=false;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <script type="text/javascript" src="/java_script/CAPS_LOCK.js"></script>
        <title>Change your Password</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <table><tr><td>
        <?php
        if(isset($failed)&&!$failed)
                echo "<h2>Your password has been changed. We will redirect you now, please wait.</h2>";
        else {
        ?>
            <h1>Change your Password</h1>
            Your password must meet all of the following requirements:
            <?php
            if(isset($failed)) {
                if($failed)
                    echo '<div class="warning">There was an error changing your password</div>';
            }
            ?>
            <ul>
                <li>Be at least eight(8) characters in length</li>
                <li>Be memorized, it cannot be written down.</li>
                <li>contain at least three of the following</li>
                <ol>
                    <li>Uppercase letter(A-Z)</li>
                    <li>Lowercase letter(a-z)</li>
                    <li>Digit (0-9)</li>
                    <li>Special Character (~`!@#$%^&amp;*()+=_-{}[]\|:;&quot;&#039;?/&lt;&gt;,.) </li>
                </ol>
        </ul>
            Your password cannot contain:
            <ul>
                <li>a common proper name, login ID, e-mail address, initials, first, middle or last name </li>
            </ul>
                    </td>
                    <td style="font-weight: bold">
                        <form method="post">
                            Current Password<br>
                            <?php
                            if(isset($good_pass)&&!$good_pass)
                                echo '<div class="warning">Incorrect Password</div>';
                            ?>
                            <input type="password" size="10" maxlength="256" name="current" onkeypress="check_caps(event)"/><br><br>
                            New Password<br>
                            <?php
                            if(isset($pass_require)&&!$pass_require) {
                                echo '<ul class="warning">';
                                for($i=1;$i<count($message);$i++) {
                                    echo "<li>".$message[$i]."</li>";      //show the errors
                                }
                                echo "</ul>\n";
                            }
                            ?>
                            <input type="password" size="10" maxlength="256" name="new" onkeypress="check_caps(event)"/><br>
                            confirm<br>
                            <input type="password" name="repeat" size="10" maxlength="256" onkeypress="check_caps(event)"/><br>
                            <span class="warning" id="warn"></span>
                            <input type="submit" value="Change your Password"/>
                        </form>
                    </td></tr>
            </table>
        <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>