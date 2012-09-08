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
if(array_key_exists("CAPID", $_POST)&&  array_key_exists("password", $_POST)) {
$banned = array('root',"Useless","Logger",'ViewNext','Sign-in','Viewer');   //list of forbidden users
$capid=  cleanInputInt($_POST['CAPID'],6,"CAPID",false);  //clean inputs
$password = cleanInputString($_POST['password'],256,"password",false);
$ident = false;
if(!in_array($capid, $banned)&&$capid!=null&&$capid!='') {   //logins in if not a banned user, not null, and not empty
    $unlocked = true;                     //says if account is locked
    if(!checkAccountLocks($capid)) {       //if account is locked stop and say it's locked
        $unlocked=false;
    }
    if($unlocked)
        $ident = Connect( $capid,$password,"localhost");   //connects
    if($ident!=false) {        //if the connection didn't fail
        logLogin($capid,true);
        session_secure_start($capid);       //starts session
        $_SESSION["member"]= new member($capid,2,$ident);
        $_SESSION["password"] = $password;
        header("REFRESH:0;url=/login/home.php");  //redirect wa to main page for login
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Staff Login</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php
         
        if(!$unlocked) {
        ?>
            <font color="red">This Account is currently locked. Please wait 30 minutes, or contact you administrator</font>
        <?php
        }
        if($ident==false) {                  //if couldn't log on
            logLogin($capid,false);
            ?>
            <font color="red">We were not able to log you in</font>
            <form method="post">
                CAPID:<input type="text" name="CAPID" value="<?php echo $capid; ?>" size="5"/><br>
                Password:<input type="password" name="password" size="5"/><br>
                <input type="submit" value="Login"/></form>
            <?php
                
        }
    }
    include('header.php');
    if(!array_key_exists("CAPID", $_POST)||  !array_key_exists("password", $_POST)) {
        ?>
        <form method="post">
            Capid: <input type="text" name="CAPID" size="5"/><br>
            Password: <input type="password" name="password" size="5"/> <br>
            <input type="submit" value="Sign-in"/>
        </form>
        <?php
    }
    include("footer.php");
    ?>
    </body>
</html>