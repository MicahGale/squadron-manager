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
        <title>Search for Member</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
                <?php
                $ident= connect($_SESSION['member']->getCapid(), $_SESSION['password'],'localhost');
                include('squadManHeader.php');
                if(isset($_GET['redirect'])) {                      //keep redirect path and clean it up
                    $redirect = cleanInputString($_GET['redirect'],127, "redirect URL", $_SERVER['SCRIPT_NAME'],false);
                     $query = "SELECT B.URL 
                        FROM SQUADRON_INFO.TASK_TYPE A JOIN
                        SQUADRON_INFO.TASKS B ON
                        A.TYPE_CODE=B.TYPE_CODE
                        WHERE (B.TASK_CODE IN (
                        SELECT A.TASK_CODE FROM SQUADRON_INFO.STAFF_PERMISSIONS A,
                        SQUADRON_INFO.STAFF_HOLDING B
                        WHERE (A.STAFF_CODE = B.STAFF_CODE
                        OR A.STAFF_CODE='AL')
                        AND B.CAPID='".$_SESSION['member']->getCapid()."') OR
                            B.TASK_CODE IN (
                            SELECT TASK_CODE FROM SQUADRON_INFO.SPECIAL_PERMISSION
                            WHERE CAPID='".$_SESSION['member']->getCapid()."'))
                        AND B.URL='$redirect'";
                     $result = Query($query, $ident, $_SERVER['SCRIPT_NAME']);
                     if(numRows($result)>=1) {                            //if found redirect url in permissions the allow it to continue
                         $_SESSION['redirect']=$redirect;
                         exit;
                     } else {
                         $query = "SELECT A.NEXT_URL
                                FROM SQUADRON_INFO.NEXT_VISIT A 
                                WHERE A.NEXT_URL ='$redirect'
                                AND A.LAST_URL IN (
                                SELECT B.URL 
                                FROM SQUADRON_INFO.TASK_TYPE A JOIN
                                SQUADRON_INFO.TASKS B ON
                                A.TYPE_CODE=B.TYPE_CODE
                                WHERE B.TASK_CODE IN (
                                SELECT A.TASK_CODE FROM SQUADRON_INFO.STAFF_PERMISSIONS A,
                                SQUADRON_INFO.STAFF_HOLDING B
                                WHERE (A.STAFF_CODE = B.STAFF_CODE
                                OR A.STAFF_CODE='AL')
                                AND B.CAPID='".$_SESSION['member']->getCapid()."') OR
                                    B.TASK_CODE IN (
                                    SELECT TASK_CODE FROM SQUADRON_INFO.SPECIAL_PERMISSION
                                    WHERE CAPID='".$_SESSION['member']->getCapid()."'))";
                         $result=  Query($query, $ident, $_SERVER['SCRIPT_NAME']);
                         if(numRows($result)>=1) {                           //if found it in next permissions
                             $_SESSION['redirect']=$redirect;
                         } else {
                             die("<meta http-equiv=\"REFRESH\" content=\"0;url=/login/home.php");
                         }
                     }
                     if(isset($_GET['field'])) {   //if field is specified
                         $_SESSION['field'] = cleanInputString($_GET['field'],128,"Member Search get Field", $_SERVER['SCRIPT_NAME'],false);
                     }
                }
                ?>
        <p>To search for a member enter part of or all of their CAPID, name, or a combination of these.</p>
        <form method="post">
            <input type="text" name="input" size="8"
                   <?php
                   if(isset($_POST['searched']))
                       echo " value=\"".htmlspecialchars($_POST['input'], ENT_QUOTES | 'ENT_HTML5', 'UTF-8')."\"";
                   ?>/><br>
            <input type="hidden" name="searched" value="1"/>
            <input type="submit" value="Search"/><br/>
        </form>
            <?php 
            if(isset($_POST['searched'])) {                         //if already tried to search then start searching
                $start = microtime(true);
                $input = cleanInputString($_POST['input'],96,"Search Input", $_SERVER['SCRIPT_NAME'],false);
                $exploded = preg_split("#[\s,]+#", $input);                         //splits input by spaces and comma
                $search_capid = '';
                for($i=0;$i<count($exploded);$i++) {                             //rechecks their size and checks for any numbers that are capid
                    if(strlen($exploded[$i])<=32) {                      //first make sure each one is not too long than neccessary
                        if(is_numeric($exploded[$i]))  {                   //if its numeric use as capid
                            $search_capid = $exploded[$i];                //use as capid
                            array_splice($exploded, $i,1);    //pull out of array
                        }
                    } else {
                        array_splice($exploded, $i,1);                       //drops it if it's too long
                    }
                }
                if(strlen($search_capid)<6) {                                //if its not a full capid add wildcards to it
                    $search_capid='%'.$search_capid.'%';
                }
                $stmt = prepare_statement($ident,"SELECT CAPID FROM SQUADRON_INFO.MEMBER 
                        WHERE CAPID LIKE $search_capid
                        AND NAME_FIRST LIKE ?
                        AND NAME_LAST LIKE ?");              //prepare the query
                $found_members = array();
                $size = count($exploded);
                for($i=0;$i<=$size;$i++) {       //cycles through array to search split names by varying 
                    $string_1='%';
                    $string_2='%';
                    if($i>=1) {                         //makes sure has any array elements in it
                        for($j=0;$j<=($i-1);$j++) {    //begin appending wildcards and string together on split array indexes
                            $string_1 = $string_1.$exploded[$j].'%';
                        }
                    }
                    if($i<=$size-1) {                     //makes sure not out of bounds at top
                        for($j=$i;$j<$size;$j++) {       //compile into one string from i and up
                            $string_2 = $string_2.$exploded[$j].'%';
                        }
                    }
                    bind($stmt,"ss",$string_1,$string_2);
                    $results = allResults(execute($stmt));   //first searchf assuming first name was first
                    $result_size = numRows($results);
                    for($j=0;$j<$result_size;$j++) {                      //now parses user results as an instance of searched_member
                        $found_capid = $results[$j]["CAPID"];
                        if(!isset($found_members[$found_capid])) { //if not already found
                            $found_members[$found_capid] = new searched_member($found_capid, $search_capid, $string_1, $string_2,$ident); //create new object
                        } else {
                            $found_members[$found_capid]->recalc_match($string_1, $string_2);  //else just see if higher match
                        }
                    }
                    bind($stmt,"ss",$string_2,$string_1);
                    $results = allResults(execute($stmt));   //then assume last name was first
                    $result_size = numRows($results);
                    for($j=0;$j<$result_size;$j++) {                      //now parses user results as an instance of searched_member
                        $found_capid = $results[$j]['CAPID'];
                        if(!isset($found_members[$found_capid])) { //if not already found
                            $found_members[$found_capid] = new searched_member($found_capid, $search_capid, $string_2, $string_1,$ident); //create new object
                        } else {
                            $found_members[$found_capid]->recalc_match($string_2, $string_1);  //else just see if higher match
                        }
                    }
                }
                $results_sorted = array();
                foreach($found_members as $temp) {               //resort array to allow to be in order by match percent
                    $inserted = false;
                    if(count($results_sorted)>=1) {  //if the sorted one isn't empty then find place
                        for($i=0;$i<count($results_sorted);$i++) {
                            if($temp->get_match()>=$results_sorted[$i]->get_match()) {   //if bigger than one looking at go before
                                array_splice($results_sorted,$i,0,array($temp));         //insert at current index displacing smaller ones\
                                $inserted = true; //tell that it has already been inserted
                                break;                                //stop searching then
                                }
                            }
                            if(!$inserted) {
                                array_push($results_sorted,$temp);
                            }
                    } else {                         //otherwise just fill the array for the first time
                        $results_sorted = array($temp);
                    }
                }
                unset($found_members,$temp);
                ?>
        <p> We found
            <?php 
            echo count($results_sorted)." in ".round(microtime(true)-$start,2)."seconds";
            ?>
        </p>
        <table border="1" cellpadding="0">
            <tr><th>Member</th><th>Percent Match</th></tr>
                <?php
                $size=count($results_sorted);
                if(isset($_SESSION['redirect'])) {  //if wants to be redirected
                    $redirectUrl = $_SESSION['redirect'];
                    if(isset($_SESSION['field']))
                        $redirectUrl=$redirectUrl."?field=".$_SESSION['field'];          //make a url with the right get stuff
                    else 
                        $redirectUrl = $redirectUrl."?";
                } else {                                      // else just go to report page then 
                    $redirectUrl="/login/member/report.php?";           
                }
                for($i=0;$i<$size;$i++) {  //start show results
                    echo "<tr><td>";
                    $name = $results_sorted[$i]->get_capid().", ".$results_sorted[$i]->get_name();
                    echo "<a href=\"$redirectUrl"."capid=".$results_sorted[$i]->get_capid()."\">$name</a>";
                    echo "</td><td>";
                    echo round($results_sorted[$i]->get_match(),2)."%";
                    echo "</td></tr>";
                }
                unset($_SESSION['redirect'],$_SESSION['field']);   //get rid of useless session info
                ?>
        </table>
        <?php
            }
            ?>
            <br/>
            <br/>
            </td>
        </tr>
        <?php include('squadManFooter.php'); ?>
    </body>
</html>