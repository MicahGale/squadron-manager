<?php
/**
 * Page for creating new staff members
 * 
 * Creates a new member or promotes a member to being a staff member
 * Creates their password, and gives them staff duties
 * 
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * 
 * $_GET
 * capid- from the member search
 * phase- the phase it's in 1- is set up member 2- set password, and staff positions
 * $_POST
 * CAPID- the CAPID of the new member
 * Lname-the Member's Last Name
 * Fname-member's First name
 * Gender- the member's Gener
 * *date*DoB- member's Date of Birth
 * achiev- the member's current achievement for grade
 * member - membership type
 * text- the textbook set they use
 * unit- the member's home unit
 * *date*DoJ- the member's date of joining.
 * 
 * phase2
 * new- the new password
 * repeat- the password repeated
 * pos[]- the staff positions selected
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
 */
require("projectFunctions.php");
$ident = connect('login');
session_secure_start();
$parsed=  parse_ini_file(PSSWD_INI);
$salt=$parsed['salt'];
if(!isset($_GET['phase'])||$_GET['phase']==1) {
    if(isset($_GET['capid'])) {             //if searched mmember
        $success=true;
        $capid=  cleanInputInt($_GET['capid'],6,'Capid');
        $staffer=new member($capid,2,$ident);
        if(!$staffer->exists())
            $success=false;
    } else if(isset($_POST['CAPID'])){                   //else create a new member
        $success=true;
        $capid=  cleanInputInt($_POST['CAPID'], 6, "capid");
        $Lname= cleanInputString($_POST['Lname'], 32, "Last Name",false);
        $Fname=  cleanInputString($_POST['Fname'],32, "First Name",false);
        $Gender=  cleanInputString($_POST['Gender'],1,"Gender",false);
        $DoB=  parse_date_input($_POST,"DoB");
        $achiev= cleanInputString($_POST['achiev'],5,"achievement",false);
        $type=cleanInputString($_POST['member'],1,'member type',false);
        $text=cleanInputString($_POST['text'],5,'Textbook set',false);
        $unit=  cleanInputString($_POST['unit'], 10, 'Home Unit', false);
        $DoJ=  parse_date_input($_POST,'DoJ');
        $staffer=new member($capid,-1, $ident, $Lname, $Fname, $Gender, $DoB, $type, $achiev, $text,$unit,$DoJ);
        if(!$staffer->insertMember($ident))  //insert into the database
            $success=false;
        $staffer->addEmeregencyContactArray($_POST);
        if(!$staffer->insertEmergency($ident))     //insert emergency contact info
            $success=false;
    }
    if(isset($staffer))
        $_SESSION['staffer']=$staffer;
    if(isset($success)&&$success) {
        header("REFRESH:0;url=/login/adminis/newMember.php?phase=2");
        exit;
    }
    $success=null;
    unset($success);
}
if(isset($_POST['pos'])&&isset($_POST['new']))
    $success=true;
if(isset($_POST['new'])) {  //if they give a password do stuff
        $message=  verify_password($_POST['new'], $_POST['repeat'],null,false);
        if($message[0]) {                 //if the password was good
            $hash= $_SESSION['staffer']->hash_password($_POST['new'],$salt);  //hash the password
            if(!$_SESSION['staffer']->set_password($ident,$hash)) {             //if password changed properly
                $success=false;
            }    
        } else {
            $success=false;
        }
}
if(isset($_POST['pos'])) {   //parses the staff positions they are given
    $_SESSION['staffer']->insert_staff_position($_POST['pos'], $ident);
    if(count($_POST['pos'])==0)
        $success=false;
}
if(isset($success)&&$success) {
    unset($_SESSION['staffer']);
    header("REFRESH:0;url=/login/home.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Create a staff Member</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Create a new staff member</h1>
        <?php
        if(!isset($_GET['phase'])||$_GET['phase']==1) {
        ?>
            <form action="/login/member/search.php?redirect=%2Flogin%2Fadminis%2FnewMember.php" method="post">
                <input type="text" name="input" size="8"/>
                <input type="submit" value="Search for a current Member"/> <br>           
            </form><br>
            Or create a new member:<br>
            <?php
            if(isset($_GET['capid']))
                $capid=  cleanInputInt($_GET['capid'],6, 'capid');
            else
                $capid=null;
            newMember($ident,"/login/adminis/newMember.php",$capid);
            echo '<input type="submit" value="Continue"/></form>';
        } else if($_GET['phase']==2) {
            ?>
            <table><tr><td>
        <h2>Set member's Password</h2>
        The password must meet all of the following requirements:
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
                <li>Uppercase letter(A-Z)</li>
                <li>Lowercase letter(a-z)</li>
                <li>Digit (0-9)</li>
                <li>Special Character (~`!@#$%^&amp;*()+=_-{}[]\|:;&quot;&#039;?/&lt;&gt;,.) </li>
            </ol>
    </ul>
        The password cannot contain:
        <ul>
            <li>a common proper name, login ID, e-mail address, initials, first, middle or last name </li>
        </ul>
                </td>
                <td style="font-weight: bold">
                    <form method="post">
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
                        <input type="password" size="10" maxlength="256" name="new"/><br>
                        confirm<br>
                        <input type="password" name="repeat" size="10" maxlength="256"/><br>
                </td></tr>
                <tr><td colspan="2">
                        <h2>Select Staff Positions</h2>
                        <table>
                        <?php
                        $query="SELECT STAFF_NAME, STAFF_CODE FROM STAFF_POSITIONS WHERE STAFF_CODE<>'AL' AND MEMBER_TYPE='".$_SESSION['staffer']->get_member_type()."' ORDER BY STAFF_NAME";
                        $results=allResults(Query($query, $ident));
                        echo "<tr>";
                        for($i=0;$i<count($results);$i++) {
                            if(($i+1)%3==1)  //if 1 over a multiple of 3 
                                echo "<tr>";
                            echo '<td><input type="checkbox" name="pos[]" value="'.$results[$i]['STAFF_CODE'].'"/>'.$results[$i]['STAFF_NAME']."</td>";
                           if(($i+1)%3==0) //if mutliple of 3
                                echo "</tr>\n";
                        }
                        if($i%3!=0)
                            echo "</tr>";
                        ?>
                        </table>
                    </td></tr>
                <tr><td style="text-align: center">
                        <br><input type="submit" value="Create Member"/>
                    </td></tr>
            </table>
        <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>