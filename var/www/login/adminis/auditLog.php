<?php
/**
 * Allows staff to view the system audit log.
 * 
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2014, Micah Gale
 * 
 * $_GET
 * page- the page of results to view
 * time- the timestamp of the unique id for the event
 * $_POST
 * filter- filer the data and sort it
 * sortBy- the drop down to sort by
 *      ti- time
 *      int- intrusion type
 *      pa- the page
 *      ip- ip address
 * Order- weather to order hi to low or vise versa
 *      hi-high to low
 *      low- low to high
 * *date*start- the start of the date range
 * *date*end- the end of the date ranger
 * intrusion- the type of the event to filter by
 * ip - the ip address to narrow it by
 * notific- check box to show only new events
 * notif- button to remove from notifications
 * 
 * $_SESSION
 * order_by- the order by clause for the query
 * order- the requested input for the order by cluase hi-lo vise versa
 * sortBy- "" which field to sort by
 * startDate- the start of the date range
 * end- the end of the date range
 * where- the where cluase
 * fitler- the filter criteria by event type
 * ip- the ip address to search for
 * notif- whether or not to show only new 
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
 *
 * 
 */
require("projectFunctions.php");
$ident = connect('login');
session_secure_start();
$where="";
if(isset($_GET['page'])) {
    $page= intval($_GET['page']);
    $start=($page-1)*LOG_PER_PAGE;
} else {
    $start=0;
}
if(isset($_GET['time'],$_POST['notif'])) {   //remove from notifications
    $time=  cleanInputString($_GET['time'],19, 'time',false);
    $query="UPDATE AUDIT_LOG SET NOTIFICATION=FALSE
        WHERE TIME_OF_INTRUSION='$time'";
    Query($query, $ident);              //set to false
}
if(!isset($_GET['time'])) {
    if(isset($_POST['filter'])) {
        $order_by=" ORDER BY ";
        switch($_POST['sortBy']) {
            case "ti":
                $order_by.="TIME_OF_INTRUSION ";
                break;
            case "int":
                $order_by.="INTRUSION_TYPE ";
                break;
            case "pa": 
                $order_by.= "PAGE ";
                BREAK;
            case "ip":
                $order_by.= "IP_ADDRESS ";
                break;
        }
        if($_POST['Datestart']!="") {
            $startDate= parse_date_input($_POST, "start");          //get the date range
            $end= parse_date_input($_POST,'end');
            $where.=" AND TIME_OF_INTRUSION BETWEEN '".$startDate->format(PHP_TO_MYSQL_FORMAT)."' and '".$end->format(PHP_TO_MYSQL_FORMAT)." 23:59:59'";
        } else {
            $startDate=null;
            $end=new DateTime();
        }
        if($_POST['order']=='hi')
            $order_by.=" DESC";
        else 
            $order_by.=" ASC";
        if(isset($_POST['notific'])&&$_POST['notific']=='on') {
            $notif=true;
            $where.=" AND NOTIFICATION=TRUE";
        } else {
            $notif=false;
        }
        if($_POST['intrusion']!="null") {
            $filter=  cleanInputString($_POST['intrusion'],2,'Event type',false);
            $where.=" AND INTRUSION_TYPE='$filter'";
        } else {
            $filter=null;
        }
        if($_POST['ip']!=="") {
            $ip=  cleanInputString($_POST['ip'],15,"IP address",false, false);
            $where.=" AND IP_ADDRESS='$ip'";
        }
        $_SESSION['order_by']=$order_by;
        $_SESSION['order']=$_POST['order'];
        $_SESSION['sortBy']=$_POST['sortBy'];
        $_SESSION['startDate']=$startDate;
        $_SESSION['end']=$end;
        $_SESSION['where']=$where;
        $_SESSION['filter']=$filter;
        $_SESSION['notif']=$notif;
        if(isset($ip)) $_SESSION['ip']=$ip;
    } else if(isset($_SESSION['order_by'])){
        $order_by=$_SESSION['order_by'];
        $startDate=$_SESSION['startDate'];
        $end=$_SESSION['end'];
        $where=$_SESSION['where'];
        $filter=$_SESSION['filter'];
        if(isset($_SESSION['ip']))
            $ip= $_SESSION['ip'];
        $notif=$_SESSION['notif'];
    } else {
        $order_by = " ORDER BY TIME_OF_INTRUSION DESC";
        $startDate=null;
        $end=new DateTime();
        $filter= null;
        $notif=false;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>View Squadron Manager Log</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>View Squadron Manager Logs</h1>
        <?php if(!isset($_GET['time'])) {?>
            <form method="post">
                <table class="center">
                <tr><td colspan="2" style="text-align: center"><input type="submit" name="filter" value="filter"/><br></td></tr>
                <tr>
                    <td>
                        <?php if(isset($_SESSION['sortBy']))
                            $sort= $_SESSION['sortBy']; 
                        else
                            $sort='ti';?>
                        Order by:<select name="sortBy">
                            <option value="ti" <?php if($sort=='ti') echo 'selected="selected"'; ?>>The time of the event</option>
                            <option value="int" <?php if($sort=='int') echo 'selected="selected"'; ?>>The type of event</option>
                            <option value="pa" <?php if($sort=='pa') echo 'selected="selected"'; ?>>The page of the event</option>
                            <option value="ip"<?php if($sort=='ip') echo 'selected="selected"'; ?>>The User's IP address</option>
                        </select>
                    </td><td>
                        <?php if(isset($_SESSION['order']))
                            $order=$_SESSION['order'];
                        else
                            $order="hi";
                        ?>
                        <select name="order">
                            <option value="hi" <?php if($order=='hi') echo 'selected="selected"'; ?>>High to Low</option>
                            <option value="lo"<?php if($order=='lo') echo 'selected="selected"'; ?>>Low to High</option>
                        </select>
                    </td>
                </tr>
                <tr><td colspan="2">Select a Date Range.</td></tr>
                <tr>
                    <td>From:<br> <?php enterDate(true, "start", $startDate) ?></td>
                    <td>To: <br><?php   enterDate(true, "end", $end); ?></td>
                </tr>
                <tr>
                    <td>Select an Event typ to filter by:<br>
                    <?php
                            dropDownMenu('SELECT INTRUSION_CODE, INTRUSION_NAME FROM INTRUSION_TYPE ORDER BY INTRUSION_NAME', "intrusion", $ident,false,$filter, true);
                    ?><br>
                    <input type="checkbox" name="notific" <?php if($notif) echo 'checked="checked"'; ?>/>View only new events</td>
                    <td>
                        Enter an IP-address to narrow the search by:<br>
                        <input type="text" name="ip" size="7" value="<?php if(isset($ip)) echo $ip; ?>"/>
                    </td>
                </tr>
                <tr><td colspan="2"><br><input type="submit" name="filter" value="filter"/></td></tr>
                <tr>
                        <?php
                        $query= "SELECT TIME_OF_INTRUSION AS TIME, MICROSECONDS , INTRUSION_NAME AS NAME, IP_ADDRESS, PAGE
                                FROM AUDIT_LOG, INTRUSION_TYPE
                                WHERE INTRUSION_TYPE=INTRUSION_CODE ".$where.$order_by;
                        $result= allResults(Query($query, $ident));
                        echo '<td style="text-align: left">Total results:'.count($result)."</td>";
                        ?>
                        <td style="text-align: right">
                        <?php
                        if(count($result)>LOG_PER_PAGE) {
                            $total=(int)(count($result)/LOG_PER_PAGE)+1;
                            for($i=1;$i<=$total;$i++) {
                                echo '<a href="/login/adminis/auditLog.php?page='.$i."\">$i</a> ";
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr><td colspan="2">
                        <table class="table">
                            <thead>
                                <tr class="table">
                                    <th class="table">Time of Event</th>
                                    <th class="table">Event Type</th>
                                    <th class="table">IP address</th>
                                    <th class="table">Page of the Event</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            if(count($result)>=$start+LOG_PER_PAGE) {
                                $max =$start+LOG_PER_PAGE;
                            } else
                                $max=count($result);
                            for($i=$start;$i<$max;$i++) {
                                echo '<tr class="table">';
                                $date=new DateTime($result[$i]['TIME']);
                                $time=  floatval($result[$i]['MICROSECONDS']);
                                echo '<td class="table"><a href="/login/adminis/auditLog.php?time='.$date->format('o-m-d%20H:i:s').'&micro='.$time.'">'.$date->format(PHP_DATE_FORMAT." G:i:s").substr(round($time,3),1).'</a></td>';
                                echo '<td class="table">'.$result[$i]['NAME'].'</td>';
                                echo '<td class="table">'.$result[$i]['IP_ADDRESS'].'</td>';
                                echo '<td class="table">'.$result[$i]['PAGE']."</td></tr>\n";
                            }
                            ?>
                            </tbody>
                        </table>
                    </td></tr>
            </table>
            </form>
            <?php
        } else {  //if showing specific details of the event
            ?>
        <a href="/login/adminis/auditLog.php">Return to Search</a>
        <table class="center"><tr><td>
        <h2>Details about a specific event.</h2>
        <h3>Event attributes</h3>
        <form method="post">
            <input type="submit" name="notif" value="Remove From Notifications"/>
        </form>
        <table class="table">
            <tr class="table"><th class="table">Time of Event</th><th class="table">Event Type</th>
                <th class="table">IP Address</th><th class="table">Page of Event</th></tr>
                <?php
                $timeStamp=  cleanInputString($_GET['time'],19,"time",false, false);
                $time=  cleanInputInt($_GET['micro'],8,"microseconds",false);
                $query= "SELECT TIME_OF_INTRUSION AS TIME, INTRUSION_NAME AS NAME, IP_ADDRESS, PAGE
                                FROM AUDIT_LOG, INTRUSION_TYPE
                                WHERE INTRUSION_TYPE=INTRUSION_CODE 
                                AND TIME_OF_INTRUSION='$timeStamp'
                                AND MICROSECONDS='$time'";
                $result=  allResults(Query($query, $ident));
                 $date=new DateTime($result[0]['TIME']);
                echo '<tr class="table"><td class="table">'.$date->format(PHP_DATE_FORMAT." G:i:s").substr(round($time,3),1).'</td>';
                echo '<td class="table">'.$result[0]['NAME'].'</td>';
                echo '<td class="table">'.$result[0]['IP_ADDRESS'].'</td>';
                echo '<td class="table">'.$result[0]['PAGE']."</td></tr>\n";
                ?>
        </table>
        <h3>Specific Details</h3>
        <table class="table">
            <tr class="table"><th class="table">Detail Name</th><th class="table">Detail</th></tr>
            <?php
            $query="SELECT FIELD_NAME, FIELD_VALUE FROM AUDIT_DUMP
                WHERE TIME_OF_INTRUSION='$timeStamp' and MICROSECONDS='$time'";
            $result=  allResults(Query($query, $ident));
            $capids=array("CAPID","user CAPID",'Deleted Member','Requester');  //all the names for capid fields
            for($i=0;$i<count($result);$i++) {
                echo '<tr class="table">';
                echo '<td class="table">'.$result[$i]['FIELD_NAME']."</td>";
                if(in_array($result[$i]['FIELD_NAME'],$capids)) {
                    $capid=$result[$i]['FIELD_VALUE'];
                    echo '<td class="table">';
                    if(strlen($capid)==6) {
                        $member=new member($capid,1,$ident);
                        echo $member->link_report(true);
                    } else
                        echo $capid;
                    echo '</td>';
                } else 
                    echo '<td class="table">'.$result[$i]['FIELD_VALUE']."</td></tr>\n";
            }
            ?>
        </table>
                </td></tr>
        </table>
            <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>