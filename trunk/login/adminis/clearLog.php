<?php
/**
 *Request the system logs to be cleared
 * 
 * Doesn't actually do anything that is handed off to another page, this just processes
 * the input, and requires them to resign-in.
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * $_POST
 * clear[]-the logs to clear
 * request- request the deletion
 * $_Session
 * auditT- tentative aprove of clearing audit logs, pending proper sign-in
 * login_clearT- '' except login log
 * authenticated- whether or not the user is authenticated
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
$ident = connect('login');
session_secure_start();
$query="SELECT REQUESTER FROM DELETE_REQUESTS
    WHERE REQUESTER='".$_SESSION['member']->getCapid()."'
    AND (CLEAR_AUDIT=TRUE OR CLEAR_LOGIN=TRUE)";
if(numRows(Query($query, $ident))>0) {  //if they already have made a request
    header("refresh:0;url=/login/home.php");
    exit;
}
$query="SELECT REQUESTER, REQUEST_DATE, CLEAR_AUDIT, CLEAR_LOGIN
    FROM DELETE_REQUESTS WHERE CLEAR_AUDIT=TRUE
    OR CLEAR_LOGIN=TRUE";
$results= allResults(Query($query, $ident));
$failed=false;
if(isset($_POST['request'])) {  //if requested then prepare
    if(in_array("audit", $_POST['clear'])&&((count($results)>0&&$results[0]['CLEAR_AUDIT'])||count($results)==0))  {
        $_SESSION['auditT']=true;
    }
    else 
        $_SESSION['auditT']=false;
    if(in_array("login", $_POST['clear'])&&((count($results)>0&&$results[0]['CLEAR_LOGIN'])||count($results)==0))
        $_SESSION['login_clearT']=true;
    else 
        $_SESSION['login_clearT']=false;
    
} else if(isset($_POST['login'])) {   //if they are logging in
    $member=$_SESSION['member'];
    $passes=  parse_ini_file(PSSWD_INI);
    $salt=$passes['salt'];
    if($member->check_password($ident,$_POST['password'],$salt)) {  //check password
        $_SESSION['audit']=$_SESSION['auditT'];
        $_SESSION['login_clear']=$_SESSION['login_clearT'];  
        unset($_SESSION['auditT'],$_SESSION['login_clearT']);  //changes the name
        $_SESSION['authenticated']=true;
        header('refresh:0;url=/login/adminis/deleteIt.php');
        exit;
    } else {
        $_SESSION['authenticated']=false;  //not authentic
        $failed=true;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <script type="text/javascript" src="/CAPS_LOCK.js"></script>
        <title>Clear System Logs</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Clear the system logs.</h1>
        <p>For security reasons it requires two people to clear the system logs.
        You may request the logs be cleared, but it will require another administrator
        to actually clear the logs.</p>
        <?php if(!isset($_POST['request'])&&!$failed) {
                    if(count($results)>0) {
                        ?>
                        <h3>Current Request</h3>
                        <table class="table">
                            <tr class="table"><th class="table">Requester</th><th class="table">Time requested</th>
                                <th class="table">clear Audit Log</th><th class="table">clear Login Log</th></tr>
                        
                        <?php
                        $member=new member($results[0]['REQUESTER'],1,$ident);
                        echo '<tr class="table"><td class="table">'.$member->link_report(true).'</td>';
                        $date= new DateTime($results[0]['REQUEST_DATE']);
                        echo '<td class="table">'.$date->format(PHP_TIMESTAMP_FORMAT).'</td>';
                        echo '<td class="table">';
                        if($results[0]['CLEAR_AUDIT'])
                            echo "yes";
                        else
                            echo "no";
                        echo '</td><td class="table">';
                        if($results[0]['CLEAR_LOGIN'])
                            echo "yes";
                        else 
                            echo "no";
                        echo "</td></tr>\n";
                        ?>
                            </table>
                    <?php } ?>                        
            <form method="post">
                <h3>Select which Logs you want to clear</h3>
                <?php
                $audit=0;
                $login=0;       //which logs are being displayed 0=no 1=yes 2=yes and checked
                if(count($results)==0) {
                    $audit=1;
                    $login=1;
                } else {
                    if($results[0]['CLEAR_AUDIT'])
                        $audit=2;
                    if($results[0]['CLEAR_LOGIN'])
                        $login=2;
                }
                if($audit>=1) {
                    echo '<input type="checkbox" name=clear[] value="audit" ';
                    if($audit==2)
                        echo 'checked="checked"';
                    echo '/>:Event Logs<br>';
                }
                if ($login>=1) {
                    echo '<input type="checkbox" name="clear[]" value="login" ';
                    if($login==2)
                        echo ' checked="checked"';
                    echo '/>:Login Logs<br>';
                }
                ?>
                <input type="submit" name="request" value="request"/>
            </form>
        <?php
        } else {
            ?>
            <h3>Login again before proceeding for Security.</h3>
            <form method="post">
                <?php
                if($failed)
                    echo '<span class="F">Improper Login.</span><br>';
                ?>
                Capid: <input type="text" size="3" disabled="disabled" value="<?php echo $_SESSION['member']->getCapid(); ?>"/><br>
                Password: <input type="password" name="password" size="5" onkeypress="check_caps(event)"/><span id="warn" class="F"></span><br>
                <input type="submit" name="login" value="login"/>
            </form>
            <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>