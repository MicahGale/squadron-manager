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
$ident= connect($_SESSION['member']->getCapid(), $_SESSION['password']);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Create a new Event</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php
        include('squadManHeader.php');
        if(!isset($_POST['location'])&&!isset($_POST['other'])) {           //if hasn't recieved input
            ?>
            <font size="6">Create a new Event</font>
            <form method="post">
                <strong>Please enter the date (or starting date) of the Event:</strong>
                <?php
                enterDate(true,"start", new DateTime());
                echo "<br>\n <strong>Enter an end date, if applicaple:</strong>";
                enterDate(true,"end");
                echo "<br>\n <strong>Select an event type:</strong>";
                dropDownMenu("SELECT EVENT_TYPE_CODE, EVENT_TYPE_NAME FROM EVENT_TYPES","type", $ident,true,"M");
                echo "<br>\n <strong>Select an event location (optional):</strong>";
                dropDownMenu("SELECT LOCAT_CODE, LOCAT_NAME FROM EVENT_LOCATION", "location", $ident,true,null,true);
                ?>
                <br><strong>Enter a name for the event (optional):<input type="text" size="10" name="name" maxlength="32"/>
                <br><input type="checkbox" name="current"/>Is this the current event members can sign into?</strong>
                <br>
                <input type="checkbox" name="attend"/>Would you like to enter attendance to this event after creating it? 
                <br>
                <br>
                <input type="submit" value="create event"/>
            </form>
            <?php
        } else if(!isset($_POST['other'])){         //start processing input if not other input specified
            $locat="null";
            $name = "null";
            $endDate = "null";
            $startDate= parse_date_input($_POST,"start");
            $needsOther= false;                                        //says if needs to get input for other, and delays insert
            if(isset($_POST['dayend'])&&$_POST['dayend']!="")                        //if end date is given then parse
                $endDate=  parse_date_input ($_POST,'end');
            $type= cleanInputString($_POST['type'],5,'event Type',false);
            if($type =="other") {                                                //if wants other for the meeting type
                $needsOther = true;
                $type = true;
            }
            if($_POST['location']!="null") {                     //if location is specified clean
                $locat=  cleanInputString ($_POST['location'],5,'Location',false);
                if($locat=="other") {                                              //if wants other get input and delay insert
                    $locat = true;                                                //says this one is needed
                    $needsOther = true;                                            
                }
            }
            if(isset($_POST['name'])&&$_POST['name']!="")                         //gets name if was inputed
                $name= cleanInputString ($_POST['name'],32,"event name",false);
           if(isset($_POST['current'])&&$_POST['current']=="on")                       //if boxes were checked
               $isCurrent = "true";
           else
               $isCurrent = "false";
           if(isset($_POST['attend'])&&$_POST['attend']=="on")
               $gotoAttend=true;
           else
               $gotoAttend=false;
           if($needsOther) {
               $_SESSION['locat']=$locat;
               $_SESSION['name']=$name;
               $_SESSION['endDate']=$endDate;
               $_SESSION['startDate']=$startDate;
               $_SESSION['type']=$type;
               $_SESSION['isCurrent']=$isCurrent;
               $_SESSION['gotoAttend']=$gotoAttend;
               echo '<form method="post">';
               echo '<input type="hidden" name="other" value="hi"/>';
               if($locat) 
                   echo 'Please specify other Location: <input type="text" name="otherLocat" maxlength="50"/><br>';
               if($type)
                   echo 'Please Specify other event type: <input type="text" name="otherType" maxlenght="40"/><br>';
               echo '<input type="submit" value="Finish and Create event"/> </form>';
           } else {
               $_SESSION['gotoAttend']=$gotoAttend;
               insert_Event($startDate, $type, $name, $isCurrent, $locat, $endDate);                  //just insert as normal   
           }
        } else {                                                     //process other fields
            $locatSpec=false;
            $typeSpec=false;
            if(isset($_POST['otherLocat'])) {        //try locat processing first
                $locat= cleanInputString($_POST['otherLocat'],50,"Other Location Name",false);
                $code= substr($locat,0,5);
                $query ="SELECT LOCAT_CODE FROM EVENT_LOCATION WHERE LOCAT_CODE='$code'";
               $num_results= numRows(Query($query, $ident));                       //get the number of results
               while($num_results>=1) {                                     //if had result than create a rng
                   $event_code=rand(0,99999);
                   $query ="SELECT LOCAT_CODE FROM EVENT_LOCATION WHERE LOCAT_CODE='$code'";
                   $num_results= numRows(Query($query, $ident));                                   //keep trying until gets an original one
               }
               $query="INSERT INTO EVENT_LOCATION(LOCAT_CODE, LOCAT_NAME)
                   VALUES('$code','$locat')";
               Query($query, $ident);
               $locatSpec=true;
            }
            if(isset($_POST['otherType'])) {
                $type= cleanInputString($_POST['otherType'],40,"Other Location Name",false);
                $code= substr($type,0,5);
                $query ="SELECT EVENT_TYPE_CODE FROM EVENT_TYPES WHERE EVENT_TYPE_CODE='$code'";
               $num_results= numRows(Query($query, $ident));                       //get the number of results
               while($num_results>=1) {                                     //if had result than create a rng
                   $event_code=rand(0,99999);
                   $query ="SELECT EVENT_TYPE_CODE FROM EVENT_TYPES WHERE EVENT_TYPE_CODE='$code'";
                   $num_results= numRows(Query($query, $ident));                                   //keep trying until gets an original one
               }
               $query="INSERT INTO EVENT_TYPES(EVENT_TYPE_CODE, EVENT_TYPE_NAME)
                   VALUES('$code','$type')";
               Query($query, $ident);
               $typeSpec=true;
            }
            if(!$locatSpec)
                $locat=$_SESSION['locat'];
            if(!$typeSpec)
                $type=$_SESSION['type'];
            insert_Event($_SESSION['startDate'], $type, $_SESSION['name'], $_SESSION['isCurrent'], $_SESSION['locat'], $_SESSION['endDate']);
            unset($_SESSION['startDate'],$_SESSION['name'],$_SESSION['isCurrent'],$_SESSION['locat'],$_SESSION['endDate']);
        }
        function insert_Event(DateTime $startDate,$type,$name,$isCurrent,$locat,$endDate) {
            var_dump(func_get_args());
            global $ident;
            $event_code=$startDate->format(EVENT_CODE_DATE).$type;                //make event code, and test it
           $query ="SELECT EVENT_CODE FROM EVENT WHERE EVENT_CODE='$event_code'";
           $num_results= numRows(Query($query, $ident));                       //get the number of results
           while($num_results>=1) {                                     //if had result than create a rng
               $event_code=rand(0,999999);
               $query ="SELECT EVENT_CODE FROM EVENT WHERE EVENT_CODE='$event_code'";
           $num_results= numRows(Query($query, $ident));                                   //keep trying until gets an original one
           }
           //TODO unset PREVEIOUS CURRENT EVENT 
           //TODO ALLOW FOR SUBEVENTS
           $query="INSERT INTO EVENT(EVENT_CODE,EVENT_DATE,EVENT_TYPE,EVENT_NAME,IS_CURRENT,LOCATION,END_DATE)
               VALUES('$event_code','".$startDate->format(PHP_TO_MYSQL_FORMAT)."','$type','$name',$isCurrent,'$locat','";
           if($endDate!="null")
               $query.=$endDate->format (PHP_TO_MYSQL_FORMAT)."')";
           else
               $query.="null')";
           echo $query;
           Query($query, $ident);
           if($_SESSION['gotoAttend']) {
//               echo "<meta http-equiv=\"REFRESH\" content=\"0;url=/loging/attendance/add.php?eCode=$event_code\">";
           }
        }
        ?>
    </body>
</html>
