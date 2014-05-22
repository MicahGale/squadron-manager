<?php
/**
 *Enter a cadet Promotion_board
 * 
 * This allows stafff to enter a promotion board, search a cadet's promotion board,
 * and view promotion board sign-ups.
 * 
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * $_GET
 * capid- the person's board
 * date- the date of the promotion board
 * field-"search" finding past ones "enter" enter a promo board "pres" the board president
 * $_POST
 * capid-the boardee
 * approve- the board is approved
 * *date*date- the date of the promotion board
 * *date*next- the next board date
 * pres- the board president
 * save- save it
 * searchM- search for the member
 * searchP- search for the board president
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
$ident=connect("login");
$pres=null;
if(isset($_GET['capid'])&&($_GET['field']=="enter"||$_GET['field']=="search")) {
    $capid=  cleanInputInt($_GET['capid'],6, "capid");
} else {
    $capid=null;
}
if(isset($_GET['date'])) {
    $input=  cleanInputString($_GET['date'],10, "date",false);
    $date=new DateTime($input);
    $next =$date->add(new DateInterval("P56D")); 
} else {
    $date= new DateTime();
    $next =$date->add(new DateInterval("P56D")); 
}
if(isset($_GET['capid'])&&$_GET['field']=='pres') {
    $pres= cleanInputInt($_GET['capid'],6, "President");
} else {
    $pres= $_SESSION['member']->getCapid();
}
if(isset($_GET['field'])&&isset($_POST['save'])) {   //parses the input for a new board and inserts
    $capid=  cleanInputInt($_POST['capid'],6,"Capid");
    if($_POST['approve']=="yes") {
        $approved=1;
    } else
        $approved=0;
    $board_date=  parse_date_input($_POST,'date');
    $next_date=  parse_date_input($_POST, 'next');
    $pres=  cleanInputInt($_POST['pres'],6, "President Capid");
    if(!isset($_GET['date'])) {
        $query="INSERT PROMOTION_BOARD(CAPID,BOARD_DATE,APPROVED,BOARD_PRESIDENT, NEXT_SCHEDULED)
            VALUES('$capid','".$board_date->format(PHP_TO_MYSQL_FORMAT)."','$pres','".$next_date->format(PHP_TO_MYSQL_FORMAT)."'";
        Query($query, $ident);
        $query="DELETE FROM TESTING_SIGN_UP
            WHERE CAPID='$capid' AND REQUIRE_TYPE='PB'";
        Query($query, $ident);
    } else {
        $query="UPDATE PROMOTION_BOARD SET CAPID='$capid', BOARD_DATE='".$board_date->format(PHP_TO_MYSQL_FORMAT)."',
            BOARD_PRESIDENT='$pres', NEXT_SCHEDULED='".$next_date->format(PHP_TO_MYSQL_FORMAT)."'
                WHERE CAPID='".cleanInputInt($_GET['capid'],6,"Get Capid")."'
                AND BOARD_DATE='".$date->format(PHP_TO_MYSQL_FORMAT)."'";
        Query($query, $ident);
    }
}
if(isset($_POST['searchM'])) {
    header("refresh:0;url=/login/member/search.ph?field=search&redirect=/login/testing/promoBoard.php");
    exit;
}
if(isset($_POST['searchP'])) {
    header("refresh:0;url=/login/member/search.php?field=pres&redirect=/login/testing/promoBoard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Enter Promotion Board</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        if(isset($_GET['capid'])&&$_GET['field']=="enter"&&!isset($_GET['date'])) {  //edit a current board
            $query="SELECT APPROVED, NEXT_SCHEDULED, BOARD_PRESIDENT
                FROM PROMOTION_BOARD WHERE CAPID='$capid' AND BOARD_DATE='".$date->format(PHP_TO_MYSQL_FORMAT)."'";
            $results=  allResults(Query($query, $ident));
            $approved=$results[0]['APPROVED'];
            $next= new DateTime($results[0]['NEXT_SCHEDULED']);
            $pres= $results[0]['BOARD_PRESIDENT'];
        }
        if(isset($_GET['capid'])&&$_GET['field']=="enter") {   //if entering a baord
            ?>
            <h2>Enter a Promotion Board</h2>
            <form method="post">
                <p>Enter boarded member: <input type="text" size="5" name="capid" value="<?php echo $capid;?>"/>Or: <input type="submit" name="searchM" value="Search for a Member"/></p>
                <p>Date of the Board:<?php enterDate(true,"date", $date); ?></p>
                <p>Board President <input type="text" size="5" name="pres" value="<?php echo $pres;?>"/>Or: <input type="submit" name="searchP" value="Search for Member"/></p>
                <p>Approved for Promotion: <input type="radio" name="approve" value="yes" <?php if($approved) echo 'selected="selected"'; ?>/>Yes <input type="radio" name="approve" value="no" <?php if(!$approved) echo 'selected="selected"'; ?> />No </p>
                <p>If not approved when is the next board scheduled?: <?php enterDate(true,"next", $next);?><br>
                NOTE: IAW CAPR52-16 5-2(e) The next board must be scheduled within 60 days of the last promotion board.</p>
                <p><input type="submit" name="save" value="save"/></p>
            </form>
        <?php
        } else if(isset($_GET['field'])&&$_GET['field']=="search") {  //if searching then display stuff
            $query="SELECT BOARD_DATE, APPROVED,BOARD_PRESIDENT FROM PROMOTION_BOARD
                WHERE CAPID='$capid'
                ORDER BY BOARD_DATE DESC";
            $results=  allResults(Query($query, $ident));
            $member=new member($capid,1,$ident);
            ?>
            <h2>view Promotion Boards</h2>
            <?php echo $member->link_report();?>
            <table class="table">
                <tr class="table"><th class="table">Board Date</th><th class="table">Approval for promotion</th><th class="table">Board President</th></tr>
            <?php
            for($i=0;$i<count($results);$i++) {
                $date=new DateTime($results[$i]['BOARD_DATE']);
                echo '<tr class="table"><td class="table"><a href="/login/testing/promoBoard.php?field=enter&capid='.$capid.'&date='.$date->format(PHP_TO_MYSQL_FORMAT).'">'.$date->format(PHP_DATE_FORMAT)."</a></td>";
                echo '<td class="table">';
                if($results[$i]['APPROVED'])
                    echo "Yes";
                else 
                    echo "no";
                $president=new member($results[$i]['BOARD_PRESIDENT'],1,$ident);
                echo '</td><td class="table">'.$president->link_report(true)."</td></tr>\n";
            }
            ?>
            </table>
            <?php
        } else {
            ?>
            <h2>Manage Promotion Boards</h2>
            <a href="/login/testing/promoBoard.php?field=enter">Enter a Promotion Board</a><br>
            <form action="/login/member/search.php?redirect=/login/testing/promoBoard.php&field=search" method="post">
                Search for a member: <input type="text" name="input" size="5"/><input type="submit" name="search" value="search"/><br>
            </form>
            <h3>Promotion Board Requests</h3>
            <table class="table">
                <tr class="table"><th class="table">Requesting Member</th><th class="table">Enter Board</th><th class="table">Requested Date</th><th class="table">Scheduled Date</th></tr>
                <?php
                $query="SELECT REQUESTED_DATE, CAPID FROM TESTING_SIGN_UP
                    WHERE REQUIRE_TYPE='PB'";
                $sign_up=  allResults(Query($query, $ident));
                $query="SELECT CAPID, NEXT_SCHEDULED FROM PROMOTION_BOARD
                    WHERE APPROVED=FALSE AND 
                    ABS(DATEDIFF(CURDATE(),NEXT_SCHEDULED))<=14";
                $disproved=  allResults(Query($query, $ident));
                for($i=0;$i<count($disproved);$i++) {  //display the disproved requests
                    $member=new member($disproved[$i]['CAPID']);
                    echo '<tr class="table"><td class="table">'.$member->link_report(true)."</td>";
                    echo '<tr class="table"><a href="/login/testing/promoBoard.php?field=enter&capid='.$member->getCapid().'>Enter Board</a></td><td class="table"></td>';
                    $date= new DateTime($disproved[$i]['NEXT_SCHEDULED']);
                    echo '<td class="table">'.$date->format(PHP_DATE_FORMAT)."</td></tr>\n";
                }
                for($i=0;$i<count($sign_up);$i++) {
                    $member=new member($sign_up[$i]['CAPID']);
                    echo '<tr class="table"><td class="table">'.$member->link_report(true)."</td>";
                    echo '<tr class="table"><a href="/login/testing/promoBoard.php?field=enter&capid='.$member->getCapid().'>Enter Board</a></td>';
                    $date= new DateTime($sign_up[$i]['NEXT_SCHEDULED']);
                    echo '<td class="table">'.$date->format(PHP_DATE_FORMAT)."</td><td class=\"table\"></td></tr>\n";
                } 
                ?>
            </table>
            <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>
