<?php
/**
 * Allows users to review memberships that are up for renewal, and terminate memberships.
 * 
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * 
 * $_GET
 * mass- saves the changes based on membership renewal
 * *capid*- R to renew for a member T to terminate
 * term- terminate a single member
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
if(isset($_GET['capid'])) {
    $capid=  cleanInputInt($_GET['capid'],6," Get capid");
}
$term=array();
if(isset($_POST['term'])) { // if terminating a single member
    if(isset($capid)) {
        $query="UPDATE MEMBER SET DATE_TERMINATED=CURDATE(), PASS_HASH=NULL
            WHERE CAPID='$capid'";
        Query($query, $ident);   //give them termination
        array_push($term,$capid);
    }
}
if(isset($_POST['mass'])) {
    $query= "SELECT CAPID, DATE_CURRENT FROM MEMBER
        WHERE DATEDIFF(DATE_CURRENT, CURDATE())<=0
        AND DATE_TERMINATED IS NULL";
    $results=  allResults(Query($query, $ident));
    $renew= prepare_statement($ident, "UPDATE MEMBER SET DATE_CURRENT=DATE_ADD(DATE_CURRENT, INTERVAL 1 YEAR) WHERE CAPID=?");
    $terminated= prepare_statement($ident,"UPDATE MEMBER SET DATE_TERMINATED=DATE_CURRENT WHERE CAPID=?");
    for($i=0;$i<count($results);$i++) {
        $id=$results[$i]['CAPID'];
        if($_POST[$id]=="R") {  //renew it 
            bind($renew,'i', array($id));
            execute($renew);
        } else if($_POST[$id]=="T") {
            bind($terminated,'i',array($id));
            execute($terminated);
            array_push($term,$id);
        }
    }
}
if(count($term)>0) { //if members were terminated then delete their permissions
    $list="";
    for($i=0;$i<count($term);$i++) {  //creates list of useless capids
        $list.="'$term[$i]',";
    }
    $list.="''";
    Query("UPDATE MEMBER SET PASS_HASH=NULL WHERE CAPID IN ($list)", $ident);
    $delete=  connect('delete');
    Query("DELETE FROM STAFF_POSITIONS_HELD WHERE CAPID IN ($list)", $delete);
    Query("DELETE FROM SPECIAL_PERMISSION WHERE CAPID IN ($list)", $delete);
    close($delete);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <script type="text/javascript" src="/java_script/confirm.js"></script>
        <title>Review Membership Currency</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Terminate Members</h1>
        <p>The following Members' membership is up for renewal, please enter below if they renewed their membership,
        or terminated it.</p>
        <form method="post" id="confirm">
            <input type="button" name="mass" value="Save Changes" onclick="confirm_task('confirm');"/> 
            <table class="table">
                <tr class="table"><th class="table">Member</th><th class="table">Date Expired</th><th class="table">Renewed?</th></tr>
                <?php
                $query= "SELECT CAPID, DATE_CURRENT FROM MEMBER
                    WHERE DATEDIFF(DATE_CURRENT, CURDATE())<=0
                    AND DATE_TERMINATED IS NULL";
                $results=  allResults(Query($query, $ident));
                for($i=0;$i<count($results);$i++) { //display renewing members
                    echo '<tr class="table"><td class="table">';
                    $member=new member($results[$i]['CAPID'],1,$ident);
                    echo $member->link_report(true);                         //show the member
                    $date= new DateTime($results[$i]['DATE_CURRENT']);
                    echo '</td><td class="table">'.$date->format(PHP_DATE_FORMAT);
                    echo '</td><td class="table">';
                    echo '<input type="radio" name="'.$member->getCapid().'" value="R" />Renewed <input type="radio" name="'.$member->getCapid().'" value="T"/>Terminated'."</td></tr>\n";
                }
                ?>
            </table>
            <input type="hidden" name="mass" value="save Changes"/>
            <button type="submit" name="mass"onclick="confirm_task('confirm');">Save Changes</button> <br><br>
            <a href="/login/member/search.php?redirect=/login/member/termMembership.php">Or search for a member</a><br><br>
            <?php
            if(isset($capid)) {
                $member = new member($capid,1,$ident);
                echo $member->link_report(true);
                echo '<input type="hidden" name="term" value="hi"/>';
                echo '<button type="submit" name="term" onclick="confirm_task(\'confirm\')"/>Do you want to terminate this member?</button>';
            }
            ?>
        </form>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>
