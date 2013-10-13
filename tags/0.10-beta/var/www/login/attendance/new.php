<?php
/**
 * Page for creating a new event.
 * 
 * INPUTS
 * $_POST 
 * "start"-date-input- the start date of the event
 * end - date-input - the end date for the event
 * type- drop down menu- the event type meeting, etc
 * location-drop down menu- the location for the event 
 * name- text- the name for the event
 * current- checkbox - whether or not this is the current event that members can sign into
 * attend- checkbox - insert attendance of an event after creation
 * subevent[]-checkbox array- all the subevents for that event
 * 
 * @package Squadron-Manager
 * @copyright (c) 2013, Micah Gale
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 */
/* Copyright 2012 Micah Gale
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
$ident= connect('login');
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
                Please enter any sub-events:<br>
                <?php
                $query='SELECT SUBEVENT_TYPE, SUBEVENT_NAME FROM SUBEVENT_TYPE';     //get all possible subevents
                $result= allResults(Query($query, $ident));
                for($i=0;$i<count($result);$i++) {                 //create checkbox for all possible subevents
                    echo '<input type="checkbox" name="subevent[]" value="'.$result[$i]['SUBEVENT_TYPE'].'"/>: '.$result[$i]['SUBEVENT_NAME']."<br>";
                }
                echo '<input type="checkbox" name="subevent[]" value="other"/>Other<br>';
                ?>
                <br>
                <input type="submit" value="create event"/><!-- input type="submit" name="sub" value="Add More Subevents"/  -->
            </form>
            <?php
        } else if(!isset($_POST['other'])){         //start processing input if not other input specified
            $locat="null";
            $name = "null";
            $endDate = "null";
            $startDate= parse_date_input($_POST,"start");
            $needsOther= false;                                        //says if needs to get input for other, and delays insert
            $subEvents = parse_Sub_events($_POST);
            $otherSubs =array();
            for($i=0;$i<count($subEvents);$i++) {  //cycle trough subevents array and make sure there are no others or nulls
                if($subEvents[$i]=="null") {      //if was null then pull it out
                    array_splice($subEvents,$i);  //pull it out without distorting the indexes
                }
                if($subEvents[$i]=="other") {
                    array_push($otherSubs, $i);       //inserts into the otherSubs array to show we need to get specifity
                    $subEvents[$i]=null;            //null it so no dirty input to the db on accident
                    $needsOther = true;
                }
            }
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
               $_SESSION['subEvents']=$subEvents;
               $_SESSION['otherSubs']=$otherSubs;
               echo '<form method="post">';
               echo '<input type="hidden" name="other" value="hi"/>';
               if($locat) 
                   echo 'Please specify other Location: <input type="text" name="otherLocat" maxlength="50" size="5"/><br>';
               if($type)
                   echo 'Please Specify other event type: <input type="text" name="otherType" maxlength="40" size="5"/><br>';
               if(count($otherSubs)!=0) { //if there were subevents that other needed to specify loop through for input
                   for($i=0;$i<count($otherSubs);$i++) {       //loop through
                       echo "Please specify other sub-event #$i:".' <input type="text" name="sub'.$otherSubs[$i].'" maxlength="40" size="5"/><br>';
                   }
               }
               echo '<input type="submit" value="Finish and Create event"/> </form>';
           } else {
               $event_Code=insert_Event($startDate, $type, $name, $isCurrent, $locat, $endDate);  //just insert as normal 
               if(count($subEvents)!=0) {         //if has subevents insert them
                   insert_Subevents($event_Code, $subEvents);
               }
               ?>
            <strong>The event has been successfully saved</strong><br>
                <?php
               if($gotoAttend) {
                   echo "<meta http-equiv=\"REFRESH\" content=\"5;url=/login/attendance/add.php?eCode=$event_Code\">";
                   echo "You will be redirected in 5 seconds to enter attendence for this event.";
               }
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
            if(count($_SESSION['otherSubs'])) {
                for($i=0;$i<count($_SESSION['otherSubs']);$i++) { //loops trough parsing other subevent input
                    $buffer[$i]=  cleanInputString($_POST['sub'.$_SESSION['otherSubs'][$i]],40,"Sub-event #".$_SESSION['otherSubs'][$i],false);   
                }
                $codes=insert_other_Subevents($buffer);   //inserts codes into db
                for($i=0;$i<count($buffer);$i++) {          //shift codes into subevent array
                    $_SESSION['subEvents'][$_SESSION['otherSubs'][$i]]=$codes[$i]; //pushes onto array
                }
            }
            $event_Code=insert_Event($_SESSION['startDate'], $type, $_SESSION['name'], $_SESSION['isCurrent'], $_SESSION['locat'], $_SESSION['endDate']);
            if(count($_SESSION['subEvents'])>0) {                 //if had subevents then insert them now
                insert_Subevents($event_Code, $_SESSION['subEvents']);  //inserts them
            }
            ?>
            <strong>The event has been saved</strong><br>
            <?php
            if($_SESSION['gotoAttend']) {
               echo "<meta http-equiv=\"REFRESH\" content=\"5;url=/loging/attendance/add.php?eCode=$event_Code\">";
               echo "You will be redirected in 5 seconds to enter attendence for this event.";
            }
            unset($_SESSION['startDate'],$_SESSION['name'],$_SESSION['isCurrent'],$_SESSION['locat'],$_SESSION['endDate'],$_SESSION['subEvents'],$_SESSION['otherSubs']);
        }
        /**
         * Inserts the event. 
         * 
         * @global mysqli $ident the database connection
         * @param DateTime $startDate the start date of the event
         * @param String $type the type of the event
         * @param String $name the name of the event
         * @param Boolean $isCurrent whether this is the current event
         * @param String $locat the location of the event
         * @param type $endDate the End date of the event or set to the string "null"
         * @return String the event code of the event
         */
        function insert_Event(DateTime $startDate,$type,$name,$isCurrent,$locat, $endDate) {
            global $ident;
            $event_code=$startDate->format(EVENT_CODE_DATE).$type;                //make event code, and test it
           $query ="SELECT EVENT_CODE FROM EVENT WHERE EVENT_CODE='$event_code'";
           $num_results= numRows(Query($query, $ident));                       //get the number of results
           while($num_results>=1) {                                     //if had result than create a rng
               $event_code=rand(0,999999);
               $query ="SELECT EVENT_CODE FROM EVENT WHERE EVENT_CODE='$event_code'";
           $num_results= numRows(Query($query, $ident));                                   //keep trying until gets an original one
           }
           if($isCurrent=="true") {                            //if is current unsets old ones
               Query("UPDATE EVENT SET IS_CURRENT=FALSE", $ident);
           }
           $query="INSERT INTO EVENT(EVENT_CODE,EVENT_DATE,EVENT_TYPE,IS_CURRENT,EVENT_NAME,LOCATION,END_DATE)
               VALUES('$event_code','".$startDate->format(PHP_TO_MYSQL_FORMAT)."','$type',$isCurrent,";
           if($name!="null")
               $query.="'$name',";
           else
               $query.="null,";
           if($locat!="null")              //if the locat isn't null then use it
               $query.="'$locat',";
           else                         //else insert a null locat
               $query.="null,";
           if($endDate!="null")
               $query.="'".$endDate->format (PHP_TO_MYSQL_FORMAT)."')";
           else
               $query.="null)";
           Query($query, $ident);
           return $event_code;
        }
        /**
         * Parses the the sube event input and inserts it
         * 
         * @param array $input the Input array, usually $_POST
         * @return array an array of the parsed inputs index is order of the input, the content is the subevent code.
         */
        function parse_Sub_events(array $input) {
            $parsed=array();
            for($i=0;$i<count($input['subevent']);$i++) {
                $buffer=$input["subevent"][$i];
                if(!is_null($buffer)&&$buffer!="null"&&$buffer!==null) {
                    array_push($parsed,cleanInputString($buffer, 3, "subevenet$i",true));
                }
            }
            return $parsed;
         }
         /**
          * Takes the input subevent array, and inserts it into the database
          * 
          * @global type $ident the database connection
          * @param type $event_Code the event code of the event that the subevents are for
          * @param array $subEvents the subevents that are being inserted
          */
         function insert_Subevents($event_Code, array $subEvents) {
             global $ident;
             $stmt=prepare_statement($ident,"INSERT INTO SUBEVENT(PARENT_EVENT_CODE, SUBEVENT_CODE)
                 VALUES('$event_Code',?)");   //prepare a statement to do a mass insert
             $size=count($subEvents);
             for($i=0;$i<$size;$i++) {      //bind and execute all the inputs
                 bind($stmt,"s",array($subEvents[$i]));  //binds info
                 execute($stmt);
             }
             close_stmt($stmt);
         }
         /**
          * Creates a new type of subevent
          * 
          * @global mysqli $ident the database connection
          * @param array $subEvents the array of new subevents to make
          * @return array returns all of the codes of the new created subevents
          */
         function insert_other_Subevents(array $subEvents) {
             global $ident;
             $codes=array();
             $insert=prepare_statement($ident, "INSERT INTO SUBEVENT_TYPE (SUBEVENT_TYPE, SUBEVENT_NAME)
                 VALUES(?,?)");                       //prepares insert statement
             $search = prepare_statement($ident,"SELECT SUBEVENT_TYPE FROM SUBEVENT_TYPE WHERE SUBEVENT_TYPE=?");  //prepare statement to search if it exists
             for($i=0;$i<count($subEvents);$i++) {
                 $name= $subEvents[$i];
                 $code=substr($name,0,3);   //try to create a code
                 bind($search,"S",$code);    //binds for search
                 $results=execute($search);
                 $length= numRows($results);
                 while($length>0) {         //while there are results
                     $code=rand(-99,999);       //randomly generate code
                     bind($search,"S",$code);    //binds for search
                     $results=execute($search);
                     $length= numRows($results);
                 }
                 bind($insert,"SS",$code,$name);      //insert the subevent
                 $codes[$i]= $code;       //pushes onto array of event codes created
             }
             return $codes;
         }
         include("squadManFooter.php");
        ?> 
    </body>
</html>
