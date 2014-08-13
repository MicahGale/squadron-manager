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
/*inputs
 * $_GET
 * redirect= the url to redirect as the whole url minus domain
 * field = the target field in the redirect so can have multi search
 * lock- the member type lock
 * $_POST
 * input the search input
 * term- whether to search terminated members as well -is a checkbx
 * $_SESSION
 * lock- the member type lock
 */
include("projectFunctions.php");
session_secure_start();
$ident=  Connect('login');
 if(isset($_POST['term'])&&$_POST['term']=="on")
    $term='';
else 
    $term=" AND DATE_TERMINATED IS NULL";
if(isset($_GET['redirect'])) {
    checkPath();
}
if(isset($_GET['lock'])) {
    $_SESSION['lock']=  cleanInputString($_GET['lock'],1,"Member type lock",false);
}
if(isset($_POST['input'])) {
    $start=  microtime(true);
    $results=search();
    $display=prepDisplay($results);
    $results= null;
    unset($results);
}
function checkPath() {                      //keep redirect path and clean it up
    global $ident;
    $redirect=  cleanInputString($_GET['redirect'], 128,'redirect page', false,false);
    $redirect_no_login=  substr($redirect, strpos($redirect,"/", 1));
    $pages= session_predict_path($ident,null, $redirect_no_login);
//    print_r($pages);
//    print_r($redirect);
    if(!in_array($redirect, $pages)) {                    //if wasn't in safe paths
        header("refresh:0;url=/login/home.php");
        die();
    } else {
        $_SESSION['redirect'] = $redirect;
    }
    if(isset($_GET['field'])) {   //if field is specified
        $_SESSION['field'] = cleanInputString($_GET['field'],128,"Member Search get Field",false);
    }
}
function search() {                         //if already tried to search then start searching
    global $ident;
    global $term;
    $input = cleanInputString($_POST['input'],96,"Search Input",true);
    if(isset($_POST['all']))
        $input="";                       //if wanted to show all just do a blank search
    $exploded = preg_split("#[\s,]+#", $input);                         //splits input by spaces and comma
    $input = null;
    unset($input);
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
    $query ="SELECT CAPID FROM MEMBER 
            WHERE CAPID LIKE '$search_capid'
            AND NAME_FIRST LIKE ?
            AND NAME_LAST LIKE ?".$term;
    if(isset($_SESSION['lock']))
        $query.=" AND MEMBER_TYPE='".$_SESSION['lock']."'";  //specifies query locking
    $stmt=  prepare_statement($ident, $query);
    $found_members = array();
    $size = count($exploded);
    for($i=0;$i<=$size;$i++) {                             //cycles through array to search split names by varying 
        $string_1='%';
        $string_2='%';
        if($i>=1) {                         //makes sure has any array elements in it
            for($j=0;$j<=($i-1);$j++) {    //begin appending wildcards and string together from beginning to split
                $string_1 = $string_1.$exploded[$j].'%';
            }
        }
        if($i<=$size-1) {                     //makes sure not out of bounds at top
            for($j=$i;$j<$size;$j++) {       //compile into one string from i and up
                $string_2 = $string_2.$exploded[$j].'%';
            }
        }
        bind($stmt,"ss", array($string_1,$string_2));
        $results = allResults(execute($stmt));   //first searchf assuming first name was first
        $result_size = count($results);
        for($j=0;$j<$result_size;$j++) {                      //now parses user results as an instance of searched_member
            $found_capid =$results[$j]['CAPID'];
            if(!isset($found_members[$found_capid])) { //if not already found
                $found_members[$found_capid] = new searched_member($found_capid, $search_capid, $string_1, $string_2,$ident); //create new object
            } else {
                $found_members[$found_capid]->recalc_match($string_1, $string_2);  //else just see if higher match
            }
        }
        bind($stmt, "ss",array($string_2,$string_1));
        $results = allResults(execute($stmt));  //then assume last name was first
        $result_size = count($results);
        for($j=0;$j<$result_size;$j++) {                      //now parses user results as an instance of searched_member
            $found_capid =$results[$j]['CAPID'];
            if(!isset($found_members[$found_capid])) { //if not already found
                $found_members[$found_capid] = new searched_member($found_capid, $search_capid, $string_2, $string_1,$ident); //create new object
            } else {
                $found_members[$found_capid]->recalc_match($string_2, $string_1);  //else just see if higher match
            }
        }
    }
    $results=null;              //null the results and clear
    unset($results);
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
    $found_members=null;
    $temp=null;
    unset($found_members,$temp);
    return $results_sorted;
}
function prepDisplay($results_sorted) {
    $display="";            //a string to buffer the output so it can be processed in the header
    global $count;                   //global var to tell how many members were found
    $size=count($results_sorted);
    $count=$size;
    if(isset($_SESSION['redirect'])) {  //if wants to be redirected
        $redirectUrl = $_SESSION['redirect'];
        if(isset($_SESSION['field']))
            $redirectUrl=$redirectUrl."?field=".$_SESSION['field']."&";          //make a url with the right get stuff
        else 
            $redirectUrl = $redirectUrl."?";
    } else {                                      // else just go to report page then 
        $redirectUrl="/login/member/report.php?";           
    }
    if(isset($_SESSION['lock']))
        $redirectUrl.="lock=".$_SESSION['lock']."&";
    for($i=0;$i<$size;$i++) {  //start show results
        if($results_sorted[$i]->get_match()==100) {                //if 100% match redirect 
            header("REFRESH:0;url=$redirectUrl"."capid=".$results_sorted[$i]->get_capid());
            exit;
        }
        $display=$display."<tr><td>";
        $name = $results_sorted[$i]->get_capid().", ".$results_sorted[$i]->get_name();
        $display.="<a href=\"$redirectUrl"."capid=".$results_sorted[$i]->get_capid()."\">$name</a>";
        $display.="</td><td>";
        $display.=round($results_sorted[$i]->get_match(),2)."%";
        $display=$display."</td></tr>\n";
    }
    $_SESSION['redirect']=null;
    $_SESSION['field']=null;
    unset($_SESSION['redirect'],$_SESSION['field'],$_SESSION['lock']);   //get rid of useless session info
    return $display;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Search for Member</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
          <?php include('squadManHeader.php');  ?>      
        <p>To search for a member enter part of or all of their CAPID, name, or a combination of these.</p>
        <form method="post">
            <input type="text" name="input" size="8"
                   <?php
                   if(isset($_POST['input']))   //show last input after quick cleaning
                       echo " value=\"".htmlspecialchars($_POST['input'], ENT_QUOTES | 'ENT_HTML5', 'UTF-8')."\"";
                   ?>/><br>
            <input type="checkbox" <?php if(isset($term)&&$term=="") echo 'checked="checked" ';?> name="term"/>:Search terminated members as well.<br>
            <input type="submit" value="Search"/>  <input type="submit" name="all" Value="Show all members"/><br/>
        </form>
        <?php
        if(isset($display)) {
            displayResults($display);
        }
        function displayResults($display) {
        ?>
        <p> We found
            <?php 
            global $start;
            global $count;
            echo $count." members in ".round(microtime(true)-$start,2)."seconds";
            ?>
        </p>
        <table border="1" cellpadding="0">
            <tr><th>Member</th><th>Percent Match</th></tr>
                <?php
                echo $display;
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