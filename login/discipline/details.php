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
include("projectFunctions.php");
session_secure_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Discipline Action Information</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php
        $ident = Connect('login');
        include("squadManHeader.php");
        if(isset($_GET['ToA'])&&!isset($_GET['month'])) {              //if has ToA field then show exact details
            ?>
            <font size="6"><strong>Details for Selected Discipline Event</strong></font><br><br>
            <?php
            $capid = cleanInputInt($_GET['capid'],6,'Capid', $_SERVER['SCRIPT_NAME']);
            $ToA = cleanInputString($_GET['ToA'],3,"Type of Action", $_SERVER['SCRIPT_NAME'],false);
            $event= cleanInputString($_GET['event'], 32, 'event code', $_SERVER['SCRIPT_NAME'], false);
            $offense = cleanInputString($_GET['O'],3,'Offense Code', $_SERVER['SCRIPT_NAME'],false);
            $given = cleanInputInt($_GET['given'], 6,"Given by capid", $_SERVER['SCRIPT_NAME']);
            $query="SELECT B.DISCIPLINE_NAME, A.EVENT_CODE, D.OFFENSE_NAME, A.SEVERITY, A.GIVEN_BY, A.DETAILS
                FROM DISCIPLINE_LOG A
                LEFT JOIN DISCIPLINE_TYPE B ON A.TYPE_OF_ACTION=B.DISCIPLINE_CODE
                LEFT JOIN EVENT C ON A.EVENT_CODE=C.EVENT_CODE
                LEFT JOIN DISCIPLINE_OFFENSES D ON A.OFFENSE=D.OFFENSE_CODE
                WHERE A.CAPID='$capid'
                AND A.TYPE_OF_ACTION='$ToA'
                AND A.EVENT_CODE='$event'
                AND A.OFFENSE='$offense'
                AND A.GIVEN_BY='$given'";
            $results = Query($query, $ident);        //get all the info
            $member=new member($capid,1, $ident);
            echo "<strong>Offending Member: </strong>".$member->link_report()."<br>\n";
            $member= new member($given,1,$ident);
            echo "<strong>Disciplinary Action Taken: </strong>".  Result($results,0,'B.DISCIPLINE_NAME')."<strong> Given By:</strong>".$member->link_report()."<br>\n";
            echo "<strong>Offense Type: </strong>".  Result($results,0,'D.OFFENSE_NAME')."<strong> Severity</strong> (1-10 scale): ".  Result($results,0,'A.SEVERITY')."<br>\n";
            ?>
            <br><table border="0">
                <tr align="center"><td><strong>Event Information</strong></td></tr>
                <tr><td><table border="1" cellpadding="0">
                            <tr><th>Event Date</th><th>Event Type</th><th>Event Name</th><th>Event Location</th></tr>
                            <tr><td>
                        <?php
                        $query ="SELECT B.EVENT_DATE, C.EVENT_TYPE_NAME, B.EVENT_NAME, D.LOCAT_NAME
                            FROM EVENT B
                            LEFT JOIN EVENT_TYPES C ON B.EVENT_TYPE=C.EVENT_TYPE_CODE
                            LEFT JOIN EVENT_LOCATION D ON B.LOCATION=D.LOCAT_CODE
                            WHERE B.EVENT_CODE='$event'";
                        $result = Query($query, $ident);
                        $date = new DateTime(Result($result,0,'B.EVENT_DATE'));
                        echo $date->format(PHP_DATE_FORMAT)."</td>";
                        echo "<td>".Result($result,0,'C.EVENT_TYPE_NAME')."</td>";
                        echo "<td>".Result($result,0,'B.EVENT_NAME')."</td>";
                        echo "<td>".Result($result,0,"D.LOCAT_NAME").'</td>';
                        ?>
                        </td></tr></table></td></tr></table>
                        <?php
                        echo "<p><strong>Details: </strong>".Result($results,0,'A.DETAILS')."</p>";
                    } else {                   //if not specified allow to search
                        if(!isset($_GET['capid'])&&!isset($_GET['ToA'])&&!isset($_GET['event'])&&!isset($_GET['offense'])) { //if completely unspecified
                            ?>
                            <font size="6"><strong>Search for a discipline Event</strong></font><br>
                            <strong>Search by Offender: </strong>
                            <form action="/login/member/search.php?redirect=/login/discipline/details.php&field=offender" method="post"><input type="text" size="5" name="input"/><input type="submit" value="Search for Member"/></form><br>
                            <strong>Or by Reprimander: </strong>
                            <form action="/login/member/search.php?redirect=/login/discipline/details.php&field=reprimand" method="post"><input type="text" size="5" name="input"/><input type="submit" value="Search for Member"/></form><br>
                            <strong>Or by one or a combination of the Following:</strong>
                            <form method="get"><br>
                            <?php
                            echo "<strong>By disciplinary action taken: </strong>";
                            dropDownMenu('SELECT DISCIPLINE_CODE, DISCIPLINE_NAME FROM DISCIPLINE_TYPE',"ToA", $ident,false,null,true);
                            echo "<br><br><strong>By Type of Offense: </strong>";
                            dropDownMenu("SELECT OFFENSE_CODE, OFFENSE_NAME FROM DISCIPLINE_OFFENSES","O", $ident, false,null,true);
                            echo "<br><br><strong>By Event</strong> (Date and Type): <br>";
                            enterDate(true,null, new DateTime);
                            echo "   ";
                            dropDownMenu('SELECT EVENT_TYPE_CODE, EVENT_TYPE_NAME FROM EVENT_TYPES',"eventType", $ident,false, null,true);
                            ?>
                                <br><br>
                                <input type="submit" value="Search"/></form>
                        <?php   
                        } else  {             //if has specified search criteria list results
                            echo '<font size="6"><strong>Search Results</strong></font>';
                            $isFirst=true;
                            $query="SELECT B.DISCIPLINE_NAME, A.TYPE_OF_ACTION, C.EVENT_DATE, A.EVENT_CODE, D.OFFENSE_NAME, A.OFFENSE, A.SEVERITY, A.GIVEN_BY, A.CAPID
                                FROM DISCIPLINE_LOG A
                                LEFT JOIN DISCIPLINE_TYPE B ON A.TYPE_OF_ACTION=B.DISCIPLINE_CODE
                                LEFT JOIN EVENT C ON A.EVENT_CODE=C.EVENT_CODE
                                LEFT JOIN DISCIPLINE_OFFENSES D ON A.OFFENSE=D.OFFENSE_CODE ";
                            if(isset($_GET['capid'])) {                //if has a capid specified add it to the query
                                if($_GET['field']=='offender') {
                                    $query=$query." WHERE A.CAPID='".cleanInputInt($_GET['capid'],6, 'Offender CAPID')."'";
                                    $isFirst=false;
                                } else {         //else assumes is the discipliner
                                    $query=$query." WHERE A.GIVEN_BY='".cleanInputInt($_GET['capid'],6,'Reprimander CAPID')."'";
                                    $isFirst=false;
                                }
                                    
                            }
                            if(isset($_GET['ToA'])&&($_GET['ToA'])!='null') {           //if type_of_action is specified then add to query
                                if(!$isFirst) {                 //if isn't first add an and in there
                                    $query=$query." AND ";
                                } else {
                                    $query.=" WHERE ";
                                }
                                $query=$query." A.TYPE_OF_ACTION='".cleanInputString($_GET['ToA'],3,"Type of Action",false)."'";
                                $isFirst=false;
                            }
                            if(isset($_GET['eventType'])&&$_GET['eventType']!='null') {  //if event is specified
                                $date=  parse_date_input($_GET);
                                $result = Query("SELECT EVENT_CODE FROM SQUADRON_INFO 
                                    WHERE EVENT_DATE='".$date->format($member->phpToMysqlFormat)."'
                                        AND EVENT_TYPE='".cleanInputString($_GET['eventType'],2,"Event Type",false)."'", $ident);
                               if(numRows($result)>0)  //gets event code from input
                                   $code=  Result ($result,0,'EVENT_CODE');
                               if(!$isFirst)                          //appends an and if isn't first
                                   $query=$query." AND";
                               else
                                   $query.="WHERE ";
                               $query=$query." A.EVENT_CODE='$code'";
                            }
                            if(isset($_GET['O'])&&$_GET['O']!='null') {
                                if(!$isFirst)         // appends an and
                                    $query=$query." AND";
                                else
                                    $query.=" WHERE ";
                                $query=$query." A.OFFENSE='".cleanInputString($_GET['O'],3,"Offense Type",false)."'";
                            }
                            $result = Query($query, $ident);
                            if(numRows($result)>0) {  //if had results show them otherwise so none found
                                ?>
                                <table border="1" cellpadding="0">
                                    <tr><th>Offender</th><th>Disciplinary Action taken</th><th>Date</th><th>Type of Offense</th><th>Severity</th><th>Reported by</th></tr>
                                <?php
                                $size= numRows($result);
                                for($i=0;$i<$size;$i++) {                        //loop through the results
                                    $offender=new member(Result($result,$i,"A.CAPID"),1, $ident);
                                    echo "<tr><td>";
                                    echo $offender->link_report()."</td><td>";
                                    echo '<a href="/login/discipline/details.php?capid='.$offender->getCapid().'&ToA='.Result($result, $i,'TYPE_OF_ACTION').'&event='.Result($result, $i,'A.EVENT_CODE').'&O='.  Result($result, $i, 'A.OFFENSE').'&given='.  Result($result, $i,'A.GIVEN_BY').'">';
                                    echo Result($result, $i,'B.DISCIPLINE_NAME').'</a></td>';         
                                    $date = new DateTime(Result($result, $i,'C.EVENT_DATE'));
                                    echo '<td>'.$date->format(PHP_DATE_FORMAT).'</td>';
                                    echo "<td>".Result($result, $i, 'D.OFFENSE_NAME').'</td>';
                                    echo '<td>'.Result($result, $i,'A.SEVERITY').'</td>';
                                    $given = new member(Result($result, $i,'A.GIVEN_BY'),1,$ident);
                                    echo '<td>'.$given->link_report();
                                    echo"</td></tr>";

                                }
                                ?>
                                </table>
                                <?php
                            } else {
                                echo "<br><br><strong>No Results Found</strong>";
                            }
                        }
                                                       
                    }
                    ?>
    </body>
</html>