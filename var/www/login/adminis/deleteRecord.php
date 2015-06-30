<?php
/**
 * Allows users to delete all the records of a member
 * 
 * This doesn't allow a single member to delete it, but requires two members of
 * the command staff to confirm, also it must be at least five years after member 
 * termination.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * 
 * $_POST
 * delete- submit the delete request
 * *capid* - the confirmation checkbox
 * L*capid*- the legal confirmation checkbox
 * R+ the above- the same thing as above, except for placing requests for deletion of records
 * $_SESSION
 * requests- an array of members to be requested
 * delete- members to be completely deleted.
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
if(isset($_POST['delete'])) { //get the input and prep it
    $query="SELECT DELETE_MEMBER
        FROM DELETE_REQUESTS
        WHERE DELETE_MEMBER IS NOT NULL
        AND REQUESTER<>'".$_SESSION['member']->getcapid()."'";
    $results=  allResults(Query($query, $ident));
    $_SESSION['delete']=array();
    for($i=0;$i<count($results);$i++) {                 //first get the deletes
        $capid=$results[$i]['DELETE_MEMBER'];  //if all the buttons are checked then add to the list
        if(isset($_POST[$capid])&&$_POST[$capid]=="on"&&isset($_POST['L'.$capid])&&$_POST['L'.$capid]=="on") {
            array_push($_SESSION['delete'], $capid);  //add it to the list
        }
    }
    $query="SELECT CAPID
        FROM MEMBER WHERE CAPID NOT IN (SELECT DELETE_MEMBER FROM DELETE_REQUESTS)
        and DATEDIFF(CURDATE(),DATE_ADD(DATE_TERMINATED, INTERVAL 5 YEAR))>=0";
    $results=  allResults(Query($query, $ident));
    $_SESSION['requests']=array();
    for($i=0;$i<count($results);$i++) {  //get requests for deletes
        $capid=$results[$i]['CAPID'];
        if(isset($_POST['R'.$capid])&&$_POST['R'.$capid]=="on"&&isset($_POST['RL'.$capid])&&$_POST['RL'.$capid]=="on") {
            array_push($_SESSION['requests'],$capid);
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
        <script type="text/javascript" src="/java_script/confirm.js"></script>
        <script type="text/javascript" src="/java_script/CAPS_LOCK.js"></script>
        <title>Delete Member Records</title>
    </head>
    <body>
        <pre>
        <?php
        print_r($_SESSION);
        echo "</pre>";
        require("squadManHeader.php");
        ?>
        <h1>Delete Member Records</h1>
        <p style="color:red">NOTE: According to CAPR10-2 Attachment 1 table 11 rule 2, You must wait five (5) years after
        member termination or transfer before destroying these records.</p>
        <?php 
        if(!isset($_POST['delete'])) {
            ?>
            <form id="confirm" method="post">
                        All Requests to delete records, requiring validation.
                <table class="table">
                    <tr class="table"><th class="table">Member</th><th class="table">Date Terminated</th><th class="table">Date eligible for deletion</th><th class="table">Date Delete Requested</th><th class="table">Requester</th><th class="table">Confirm Deletion</th></tr>
                    <?php
                    $query="SELECT DELETE_MEMBER, REQUESTER, REQUEST_DATE, DATE_TERMINATED, DATE_ADD(DATE_TERMINATED, INTERVAL 5 YEAR) AS WAIT
                        FROM DELETE_REQUESTS, MEMBER
                        WHERE DELETE_MEMBER IS NOT NULL
                        AND REQUESTER<>'".$_SESSION['member']->getcapid()."'
                        AND CAPID=DELETE_MEMBER";
                    $results=  allResults(Query($query, $ident));
                    for($i=0;$i<count($results);$i++) {  //displays pending delete requests
                        echo '<tr class="table"><td class="table">';
                        $member= new member($results[$i]['DELETE_MEMBER'],1,$ident);
                        echo $member->link_report(true);
                        echo '</td><td class="table">';
                        $date=new DateTime($results[$i]['DATE_TERMINATED']);
                        echo $date->format(PHP_DATE_FORMAT);
                        $date= new DateTime($results[$i]['WAIT']);
                        echo '</td><td class="table">'.$date->format(PHP_DATE_FORMAT);
                        $date = new DateTime($results[$i]['REQUEST_DATE']);
                        echo'</td><td class="table">'.$date->format(PHP_TIMESTAMP_FORMAT);
                        $deleter = new member($results[$i]['REQUESTER'],1,$ident);
                        echo '</td><td class="table">'.$deleter->link_report(true);
                        echo '</td><td class="table"><input type="checkbox" name="'.$member->getCapid().'"/>Confirm<br>';
                        echo '<input type="checkbox" name="L'.$member->getCapid().'"/>These records aren\'t involved in any legal case';
                    }
                    ?>
                </table>
                <input type="button" name="delete" value="Proceed" onclick="confirm_task('confirm');"/><br><br>
                Records ready to be destroyed.
                <table class="table">
                    <tr class="table"><th class="table">Member</th><th class="table">Date Terminated</th><th class="table">Date Ready for Destruction</th><th class="table">Confirm deletion</th></tr>
                    <?php
                    $query="SELECT CAPID, DATE_TERMINATED, DATE_ADD(DATE_TERMINATED, INTERVAL 5 YEAR) AS WAIT
                        FROM MEMBER WHERE CAPID NOT IN (SELECT DELETE_MEMBER FROM DELETE_REQUESTS)
                        having DATEDIFF(CURDATE(),WAIT)>=0";
                    $results=  allResults(Query($query, $ident));
                    for($i=0;$i<count($results);$i++) {
                        $member=new member($results[$i]['CAPID'],1,$ident);
                        echo '<tr class="table"><td class="table">'.$member->link_report(true).'</td>';
                        $date= new DateTime($results[$i]['DATE_TERMINATED']);
                        echo '<td class="table">'.$date->format(PHP_DATE_FORMAT)."</td>";
                        $date=new DateTime($results[$i]['WAIT']);
                        echo '<td class="table">'.$date->format(PHP_DATE_FORMAT).'</td>';
                        echo '<td class="table"><input type="checkbox" name="R'.$member->getCapid().'"/>Confirm<br>';
                        echo'<input type="checkbox" name="RL'.$member->getCapid().'"/>These records aren\'t involved in any legal case'."</td></tr>\n";
                    }
                    ?>
                </table>
                <input type="hidden" name="delete" value="useless"/>
                <input type="button" name="delete" value="Proceed" onclick="confirm_task('confirm');"/>
            </form>
        <?php
        } else  {
            ?>
            Please login Again to Confirm your Identity.<br>
            <form action="finishRecordDel.php" method="post">
                CAPID: <input type="text" name="capid" value="<?php echo $_SESSION['member']->getCapid();?>" disabled="disabled" size="5"/><br>
                Password:<input type="password" name="password" autocomplete="off" size="5" onkeypress="check_caps(event);"/><span id="warn" class="F"></span><br>
                <input type="submit" name="login" value="login"/>
            </form>
            <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>