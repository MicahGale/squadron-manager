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
include_once("projectFunctions.php");
session_secure_start();
$ident=  connect('login');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>View Attendance Report</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php
        include('squadManHeader.php');
        if(!isset($_GET['eCode'])&&!isset($_POST['month'])) {
            display_event_search_in($ident);
        } else if((!isset($_POST['day'])||$_POST['type']=="null"||$_POST['location']!="null")&&!isset($_GET['eCode'])) {
            searchEvent($ident,"report");
        } else {
            report($_GET['eCode']);
        } 
        function report($eCode) {
            ?>
        <font size="6">Event Report</font>
            <?php                    
            global $ident;
            $event=  cleanInputString($eCode, 32,"Event Code",false);
            $query="SELECT B.EVENT_DATE, C.EVENT_TYPE_NAME, B.EVENT_NAME, D.LOCAT_NAME, B.END_DATE
                            FROM EVENT B
                            LEFT JOIN EVENT_TYPES C ON B.EVENT_TYPE=C.EVENT_TYPE_CODE
                            LEFT JOIN EVENT_LOCATION D ON B.LOCATION=D.LOCAT_CODE
                            WHERE B.EVENT_CODE='$event'";
            $result=  allResults(Query($query, $ident));
            $date= new DateTime($result[0]['EVENT_DATE']);
            echo "<br><br><strong>Event Date: </strong>".$date->format(PHP_DATE_FORMAT)."<br>\n";
            if(!is_null($result[0]['END_DATE'])) {
                $date = new DateTime($result[0]['END_DATE']);
                echo "<br><strong>End date: </strong>".$date->format(PHP_DATE_FORMAT);
            }
            echo "<strong>Event Type: </strong>".$result[0]['EVENT_TYPE_NAME']."<br>\n";
            echo "<strong>Event Location: </strong>".$result[0]['LOCAT_NAME']."<br>\n";
            echo "<strong>Event Name: </strong>".$result[0]['EVENT_NAME']."<br>\n";
            $query ="SELECT A.SUBEVENT_NAME FROM SUBEVENT_TYPE A
                JOIN SUBEVENT B ON A.SUBEVENT_TYPE=B.SUBEVENT_CODE
                JOIN EVENT C ON B.PARENT_EVENT_CODE=C.EVENT_CODE
                WHERE C.EVENT_CODE='$event'";
            $result=  allResults(Query($query, $ident));
            echo "<strong>Events Done: </strong>";
            if(count($result)>=1) {                                //if more than 2 results than cycle to 2nd to last
                for($i=0;$i<count($result)-1;$i++) {
                    echo $result[$i]['SUBEVENT_NAME'].", ";
                }
                echo $result[$i]['SUBEVENT_NAME'];  //display last one uses the offset jump from the last loop of the for loop
            } else if(count($result)==1)                      //if only 1 then display it 
                echo $result[0]['SUBEVENT_NAME'];
            echo "<br><br>\n";
            ?>
        <table border="0" width="800"><tr><td align="center">
            <strong>Attendance</strong></td></tr><tr><td>
            <?php
            $query="SELECT CAPID FROM ATTENDANCE WHERE EVENT_CODE='$event'";
            $result=  allResults(Query($query, $ident));
            foreach($result as $temp) {
                $member=new member($temp['CAPID'],1, $ident);
                echo $member->link_report();
                echo "<br>\n";
            }
            ?>
            </td></tr></table>
            <?php
        }
        include('squadManFooter.php');
        ?>
    </body>
</html>