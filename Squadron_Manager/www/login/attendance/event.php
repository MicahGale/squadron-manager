<?php
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
include_once("projectFunctions.php");
session_secure_start();
$ident=  connect('login');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>View Attendance Report</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php if(!isset($_GET['print'])) {?>
            <link rel="stylesheet" type="text/css" href="/main.css">
        <?php } else {?>
            <link rel="stylesheet" type="text/css" href="/print.css">
        <?php }?>
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body> 
        <?php
        if(!isset($_GET['print']))
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
        <h2>Event Report</h2>
            <?php
            global $ident;
            $event=  cleanInputString($eCode, 32,"Event Code",false);
            if(!isset($_GET['print'])) {
                echo '<a href="/login/attendance/event.php?eCode='.$eCode.'&print=p" target="_blank">Print Report</a>';
            }
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
        <table><tr><td style="text-align:center">
            <h3>Attendance</h3></td></tr><tr><td><table style="width:100%" class="table">
            <?php
            $query="SELECT A.CAPID FROM ATTENDANCE A, MEMBER B
                WHERE A.CAPID=B.CAPID AND EVENT_CODE='$event'
                    ORDER BY NAME_LAST, NAME_FIRST";
            $result=  allResults(Query($query, $ident));
            $size=count($result);
            $length=intval($size/3);
            $remainder=$size%3;      //get the remainder
            $max=$length-1;
            if($remainder>=1) {
                $max++;
            }
            $start_2=$max+1;
            $end_2=$start_2+$length-1;
            if($remainder==2) {
                $end_2++;
            }
            $start_3=$end_2+1;
            $end_3=$start_3+$length-1;
            for($i=0;$i<=$max;$i++) {
                $member=new member($result[$i]['CAPID'],1,$ident);
                echo '<tr class="table"><td class="table">'.$member->title().'</td><td>';
                if($i+$start_2<=$end_2&&$i+$start_2<$size) {
                    $member=new member($result[$i+$start_2]['CAPID'],1,$ident);
                    echo $member->title();
                }
                echo '</td><td class="table">';
                if($i+$start_3<=$end_3&&$i+$start_3<$size) {
                    $member=new member($result[$i+$start_3]['CAPID'],1,$ident);
                    echo $member->title();
                }
                echo "</td></tr>\n";
            }
            ?>
            </table></td></tr></table>
            <?php
        }
        if(!isset($_GET['print']))
            include('squadManFooter.php');
        else
            include('footer.php');
        ?>
    </body>
</html>