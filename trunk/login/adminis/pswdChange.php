<?php
require("projectFunctions.php");
session_secure_start();
if(isset($_POST['current'])) {  //if they give a password do stuff
    if(cleanInputString($_POST['current'], 256, "Current Password",false)==$_SESSION['password']) {  // get the current password and check it
        $good_pass=true;   //record it was a good pass
        $message=  checkPassword($_POST['new'], $_POST['retype']);
        if($message[0]) {                 //if the password was good
            $pass_require=true;
            $hash=  passthru($message[1]);  //hashes the password
            $query="SET PASSWORD ='$hash'";  //changes the password for the user
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
        <title>Change your Password</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <table><tr><td>
        <h1>Change your Password</h1>
        Your password must meet all of the following requirements:
        <ul>
            <li>Be at least eight(8) characters in length</li>
            <li>Be memorized, it cannot be written down.</li>
            <li>contain at least three of the following</li>
            <ol>
                <li>Uppercase letter</li>
                <li>Lowercase letter</li>
                <li>Digit (0-9)</li>
                <li>Special Character (~`!@#$%^&amp;*()+=_-{}[]\|:;&quot;&#039;?/&lt;&gt;,.) </li>
            </ol>
    </ul>
        Your password cannot contain:
        <ul>
            <li>contain a common proper name, login ID, e-mail address, initials, first, middle or last name </li>
        </ul>
                </td>
                <td style="font-weight: bold">
                    <form method="post">
                        Current Password<br>
                        <?php
                        if(isset($good_pass)&&!$good_pass)
                            echo '<div class="warning">Incorrect Password</div>';
                        ?>
                        <input type="password" size="10" maxlength="256" name="current"/><br><br>
                        New Password<br>
                        <?php
                        
                        ?>
                        <input type="password" size="10" maxlength="256" name="new"/><br>
                        confirm<br>
                        <input type="password" name="repeat" size="10" maxlength="256"/><br>
                        <input type="submit" value="Change your Password"/>
                    </form>
                </td></tr>
        </table>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>
