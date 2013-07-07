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
 $passes=  parse_ini_file(PSSWD_INI);
 $salt=$passes['salt'];
 $ident=  connect('ViewNext');
if(array_key_exists("CAPID", $_POST)&&  array_key_exists("password", $_POST)) {
    $banned = array('root',"Useless","Logger",'ViewNext','Sign-in','Viewer','login');   //list of forbidden users
    $capid=  cleanInputInt($_POST['CAPID'],6,"CAPID",false);  //clean inputs
    $password = $_POST['password'];                        //don't clean the password, because of the hash
    if(!in_array($capid, $banned)&&$capid!=null&&$capid!='') {   //logins in if not a banned user, not null, and not empty
        $unlocked = true;                     //says if account is locked
        if(!checkAccountLocks($capid)) {       //if account is locked stop and say it's locked
            $unlocked=false;
        }
        if($unlocked) {
            $member=new member($capid, 1, $ident);
            //if the right password and the member isn't terminated
            if($member->check_password($ident, $password, $salt)&&!$member->check_terminated($ident)) {  //checks the password
                logLogin($capid,true);
                session_secure_start($capid);       //starts session
                $_SESSION["member"]= new member($capid,2,$ident);
                $_SESSION['log_time']=time();  //keep track of the start of the login
                if($_SESSION['member']->check_pass_life($ident)!==true) {  //if the password is still valid
                    header("REFRESH:0;url=/login/home.php");  //redirect wa to main page for login
                    exit;
                } else {
                    $reCreate=true;
                }
            }
        }
    }
}
if(isset($_POST['current'])) {  //if they give a password do stuff
    session_secure_start();
    close($ident);
    $ident = connect("login");
    if($_SESSION['member']->check_password($ident,$_POST['current'],$salt)) {  // get the current password and check it
        $good_pass=true;   //record it was a good pass
        $message=  verify_password($_POST['new'], $_POST['repeat'],$_POST['current']);
        if($message[0]) {                 //if the password was good
            $pass_require=true;
            $hash= $_SESSION['member']->hash_password($_POST['new'],$salt);  //hash the password
            if($_SESSION['member']->set_password($ident,$hash)) {             //if password changed properly
                header("refresh:0;url=/login/home.php");          //exit
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
        <title>Staff Login</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <script type="text/javascript" src="/CAPS_LOCK.js"></script>
    </head>
    <body>
        <?php
         include('header.php');
        if(isset($unlocked)&&!$unlocked) {
        ?>
            <font style="color:red">This Account is currently locked. Please wait 30 minutes, or contact you administrator</font>
        <?php
        }
        if(isset($member)&&$member->check_terminated($ident)) {
            echo '<span class="F">Your membership is terminated, and you cannot log in.</span><br>';
        }
        if(!isset($_SESSION['member'])&&isset($capid)) {                  //if couldn't log on
                logLogin($capid,false);
            ?>
            <font color="red">We were not able to log you in</font>
            <form method="post">
                CAPID:<input type="text" name="CAPID" value="<?php echo $capid; ?>" size="5"/><br>
                Password:<input type="password" name="password" size="5" onkeypress="check_caps(event)"/><span id="warn" class="F"></span><br>
                <input type="submit" value="Login"/></form>
            <?php
                
        }
    if((!array_key_exists("CAPID", $_POST)||  !array_key_exists("password", $_POST))&&!isset($_POST['current'])) {
        ?>
        <form method="post">
            Capid: <input type="text" name="CAPID" size="5"/><br>
            Password: <input type="password" name="password" size="5" onkeypress="check_caps(event)"/><span id="warn" class="warning"></span><br>
            <input type="submit" value="Sign-in"/>
        </form>
        <?php
    }
    if(isset($reCreate)||isset($_POST['new'])) {
        ?>
            <table><tr><td>
        <h2 class="warning">Your password is expired, you must change it</h2>
        Your password must meet all of the following requirements:
        <?php
        if(isset($failed)) {
            if($failed)
                echo '<div class="warning">There was an error changing your password</div>';
            else
                echo "<h2>Your password has been changed. We will redirect you now, please wait.</h2>";
        }
        ?>
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
                        <span id="warn" class="F"></span><input type="password" size="10" maxlength="256" name="current" onkeypress="check_caps(event)"/><br><br>
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
                        <input type="submit" value="Change your Password"/>
                    </form>
                </td></tr>
        </table>
        <?php
    }
    include("footer.php");
    ?>
    </body>
</html>