<?php
/**
 * Create a new admin account to add others.
 * 
 * This a temporary file to sets up the new administrator, and the new unit.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2014, Micah Gale
 */
/* Copyright 2014 Micah Gale
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
 */
require("projectFunctions.php");
$ident = connect('login');
if(isset($_GET['stage'])&&isset($_POST)) {  //if it got stuff
    session_start();
    if($_GET['stage']==2) { //if adding the unit
        $newUnit=new unit($_POST["charter"],$ident,$_POST["region"],$_POST["wing"],$_POST['name']);
        $newUnit->insert_unit($ident, "");
        query("UPDATE CAP_UNIT SET DEFAULT_UNIT=1",$ident);
    } else if ($_GET['stage']==3) {  //if setting up user
        $DoB=  parse_date_input($_POST,"DoB");
        $DoJ=  parse_date_input($_POST,"DoJ");
        $newMember = new member($_POST['CAPID'],-1,$ident,$_POST['Lname'],$_POST["Fname"],$_POST['Gender'],$DoB,$_POST["member"],
                $_POST["achiev"],$_POST["text"],$_POST["unit"],$DoJ);
        $newMember->addEmeregencyContactArray($_POST);
        $newMember->insertMember($ident);
        $newMember->insertEmergency($ident);
        $_SESSION['member']=$newMember;  //insert the member and save!
    } else { //otherwise they are saving the password, and staff position
        $passes=  parse_ini_file(PSSWD_INI);
        $salt=$passes['salt'];
        $message=  verify_password($_POST['new'], $_POST['repeat'],"",false);
        if($message[0]) {                 //if the password was good
            $pass_require=true;
            $hash= $_SESSION['member']->hash_password($_POST['new'],$salt);  //hash the password
            if($_SESSION['member']->set_password($ident,$hash)) {             //if password changed properly
                $_SESSION['member']->insert_staff_position(array($_POST['pos']), $ident);
                session_unset();
                session_destroy();
                header("refresh:5;url=/");          //exit
                $failed=false;
            } else {
                $failed=true;                  //display that it failed
            }    
        } else {
            $pass_require=false;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Finish installation</title>
    </head>
    <body>
        <?php
        require("header.php");
        ?>
        <h1>Finish Installation</h1>
        <?php
        if(!isset($_GET['stage'])||$_GET['stage']==1) { //if first stage then get new unit
            newUnit($ident,"/finish.php?stage=2");  //get a new unit
        } else if($_GET['stage']==2) {   //create a new member
            newMember($ident, "/finish.php?stage=3");
        } else {  //set up password and staff positions
             if(isset($failed)&&!$failed)
                echo "<h2>Your password has been changed. We will redirect you now, please wait.</h2>";
            else {
        ?>
        <table id="main"><tr><td>
        <h3>Set your Password</h3>
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
                        <form method="post" action="/finish.php?stage=4">
                            <?php
                            if(isset($good_pass)&&!$good_pass)
                                echo '<div class="warning">Incorrect Password</div>';
                            ?>
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
                            <br>
                            Enter Your staff Position
                            <?php
                            //Create drop down menu to select staff position
                            $member_type=$_SESSION['member']->get_member_type(); //get member type
                            dropDownMenu("SELECT A.STAFF_CODE, A.STAFF_NAME FROM STAFF_POSITIONS A, STAFF_PERMISSIONS B WHERE A.MEMBER_TYPE='$member_type' AND A.STAFF_CODE=B.STAFF_CODE AND B.TASK_CODE='NME'", "pos", $ident);
                            ?>
                            <br><input type="submit" value="Set your Password"/>
                        </form>
                    </td></tr>
            </table>
        <?php
        }
        }
        close($ident);
        require("footer.php");
        ?>
    </body>
</html>
