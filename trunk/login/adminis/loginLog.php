<?php
/**
 * This views login logs and account locks
 * 
 * This allows the user to see the login logs, and he account locks
 * and allows the user to remove account locks.
 * 
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * $_GET
 * lock-view the account lock outs
 * page- the page of the results to see
 * $_POST
 * done- the submit button to remove the input
 * remove[]- the value of the capid of the lock to remove
 * *date*start- the start of the date range
 * *date*end- the end of the date range
 * capid- the capid to focus on
 * ip - th ip address
 * login- the failed logins
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
if(isset($_GET['page'])) {
    $page= intval($_GET['page']);
    $start=($page-1)*LOG_PER_PAGE;
} else {
    $start=0;
}
if(isset($_POST['done'])) {  //remove the 
    $stmt=  prepare_statement($ident,"DELETE FROM ACCOUNT_LOCKS
        WHERE CAPID=?");
    for($i=0;$i<count($_POST['done']);$i++) {
        bind($stmt,'i', array(cleanInputInt($_POST['done'][$i], 6,"capid")));
        execute($stmt);             //delete the lock
    }
    close_stmt($stmt);
}
$where="";
if(isset($_POST['filter'])) {
    if($_POST['Datestart']!=='') {
        $startDate=  parse_date_input($_POST,'start');
        $end= parse_date_input($_POST,'end');
        $where.=" AND TIME_LOGIN BETWEEN '".$startDate->format(PHP_TO_MYSQL_FORMAT)."' and '".$end->format(PHP_TO_MYSQL_FORMAT)." 23:59:59'";
    } else  {
        $startDate= null;
        $end=new DateTime();
    }
    if($_POST['capid']!="") {
        $capid=  cleanInputInt($_POST['capid'],6, "capid");
        $where.=" AND CAPID='$capid'";
    } else
        $capid=null;
    if($_POST['ip']!="") {
        $ip = cleanInputString($_POST['ip'],15, "IP address", false);
        $where.=" and IP_ADDRESS='$ip'";
    } else
        $ip=null;
    if(isset($_POST['login'])) {
        $login=true;
        $where.=" AND SUCEEDED=FALSE";
    } else $login=false;
    $_SESSION['start']=$startDate;
    $_SESSION['end']=$end;
    $_SESSION['searchID']=$capid;
    $_SESSION['ip']=$ip;
    $_SESSION['login']=$login;
    $_SESSION['where']=$where;
} else if(isset($_SESSION['searchID'])) {
    $startDate=$_SESSION['start'];
    $end=$_SESSION['end'];
    $capid=$_SESSION['searchID'];
    $ip=$_SESSION['ip'];
    $login=$_SESSION['login'];
    $where=$_SESSION['where'];
} else {
    $startDate=null;
    $end= new DateTime();
    $capid=null;
    $ip=null;
    $login=false;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>View Login Logs</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h2>View Account Login Logs and Account Locks</h2>
        <a href="/login/adminis/loginLog.php?lock=l">View account Locks</a><br>
        <?php
        if(isset($_GET['lock'])) {
            ?>
        <h3>Account Locks</h3>
        <a href="/login/adminis/loginLog.php">Return to Login Log</a>
        <table style="text-align:center"><tr><td>
        <form method="post">
            <input type="submit" name="done" value="Remove the Locks"/>
            <table class="table"><tr class="table"><th class="table">User</th><th class="table">Lock in effect until:</th><th class="table">remove Lock</th></tr>
                <?php
                $query="SELECT CAPID, VALID_UNTIL FROM ACCOUNT_LOCKS";
                $result=  allResults(Query($query, $ident));
                for($i=0;$i<count($result);$i++) {
                    echo '<tr class="table">';
                    echo '<td class="table">'.$result[$i]['CAPID'].'</td>';
                    $date=new DateTime($result[$i]['VALID_UNTIL']);
                    echo '<td class="table">'.$date->format(PHP_TIMESTAMP_FORMAT).'</td>';
                    echo '<td class="table"><input type="checkbox" name="remove[]" value="'.$result[$i]['CAPID']."\"/></td></tr>\n";
                }
                ?>
                        </table>
            <input type="submit" name="done" value="Remove the Locks"/>
        </form>
                </td></tr></table>
        <?php
        } else {
            ?>
        <form method="post">
            <table style="text-align: center">
                <tr><td colspan="2"><input type="submit" name="filter" value="filter"/>
                        <br>Select a date Range:</td></tr>
                <tr>
                    <td><?php enterDate(true, "start", $startDate)?></td>
                    <td><?php enterDate(true, "end",$end)?></td>
                </tr>
                <tr>
                    <td>Capid: <input type="text" name="capid" size="3" value="<?php echo $capid;?>"/>
                        <br><input type="checkbox" name="login" <?php if($login) echo 'checked="checked"';?>/>:View only failed Logins.</td>
                    <td>IP-address: <input type="text" name="ip" size="5" value="<?php echo $ip;?>"/></td>
                </tr>
                <tr><td colspan="2"><input type="submit" name="filter" value="filter"/></td></tr>
                <tr>
                        <?php
                        $query ="SELECT TIME_LOGIN, LOG_OFF, CAPID, IP_ADDRESS, SUCEEDED
                            FROM LOGIN_LOG WHERE 1=1".$where." ORDER BY TIME_LOGIN DESC";
                        $result=  allResults(Query($query, $ident));
                        echo '<td style="text-align: left">Total results:'.count($result)."</td>";
                        ?>
                        <td style="text-align: right">
                        <?php
                        if(count($result)>LOG_PER_PAGE) {
                            $total=(int)(count($result)/LOG_PER_PAGE)+1;
                            for($i=1;$i<=$total;$i++) {
                                echo '<a href="/login/adminis/loginLog.php?page='.$i."\">$i</a> ";
                            }
                        }
                        ?>
                </tr>
                <tr><td colspan="2">
                        <table class="table">
                            <tr class="table"><th class="table">Time Logged-in</th>
                                <th class="table">Time Logged-off</th><th class="table">User</th>
                                <th class="table">IP-address</th><th class="table">Successful</th></tr>
                            <?php
                            if(count($result)>=$start+LOG_PER_PAGE) {
                                $max =$start+LOG_PER_PAGE;
                            } else
                                $max=count($result);
                            for($i=$start;$i<$max;$i++) {
                                $date=new DateTime($result[$i]['TIME_LOGIN']);
                                echo '<tr class="table"><td class="table">'.$date->format(PHP_TIMESTAMP_FORMAT)."</td>";
                                if($result[$i]['LOG_OFF']!==null) {
                                    $date= new DateTime($result[$i]['LOG_OFF']);
                                    echo '<td class="table">'.$date->format(PHP_TIMESTAMP_FORMAT).'</td>';
                                } else 
                                    echo '<td class="table"></td>';
                                $member=new member($result[$i]['CAPID'],1,$ident);
                                $name = $member->getCapid().", ".$member->getName_first()." ".$member->getName_Last();
                                echo '<td class="table">'.'<a target="_blank" href="/login/member/report.php?capid='.$member->getCapid().'">'.$name.'</a></td>';
                                echo '<td class="table">'.$result[$i]['IP_ADDRESS'].'</td>';
                                echo '<td class="table">';
                                if($result[$i]['SUCEEDED'])
                                    echo "Yes";
                                else
                                    echo '<span class="F">no</span>';
                                echo "</td></tr>\n";
                            }
                            ?>
                        </table>
                    </td></tr>
            </table>
        </form>
            <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>