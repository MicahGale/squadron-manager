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
require("projectFunctions.php");
session_secure_start();
$ident = connect($_SESSION['member']->getCapid(), $_SESSION['password']);
if(in_array("Search", $_POST)) {             //if searched for member save input, then redirect to search
    $_SESSION['input']= array();
   for($i=0;$i<$_SESSION['number'];$i++) {      //loop through to find which one was searched for and save the input
       if(isset($_POST["search$i"])&&$_POST["search$i"]=="Search") {  //if was the one searched for
           header('REFRESH: 0;url="/login/member/search.php?redirect=/login/attendance/add.php&field='.$i.'"');
       } else if(isset($_POST['cap'][$i])&&$_POST['cap'][$i]!="") {  //if the capid wasn't blank then save it
           $_SESSION['input'][$i] = cleanInputInt($_POST['cap'][$i],6,"CAPID $i");         //stores the input
       }
   }
   exit;
}
if(isset($_GET['capid'])) {            //if has given capid then store it after cleaning
    $field = cleanInputString($_GET['field'],3,"search field",false);
    if(!is_numeric($field)||$field>=$_SESSION['number']) {                        //checks that it's an int or out of bounds
        echo '<font color="red">The field must be an Integer</font>';
        exit;
    } else {              //else just parse the input
        $_SESSION['input'][$field]=  cleanInputInt($_GET['capid'],6,"Searched CAPID");
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <title>Add Attendance to an event</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        if(isset($_POST['insert'])) {             //if is inserting then do so
            $input=array();
            for($i=0;$i<$_SESSION['number'];$i++) {     //loops to parse input
                if(isset($_POST['cap'][$i])&&$_POST['cap'][$i]!="") {          //if isn't blank
                    array_push($input, cleanInputInt($_POST['cap'][$i],6,"CAPID $i"));  //pull it and clean it
                }
            }
            $stmt=  prepare_statement($ident,"INSERT INTO ATTENDANCE(EVENT_CODE,CAPID)
                values('".$_SESSION['eCode']."',?)");                                //prepares statements
            $success=true;
            for($i=0;$i<count($input);$i++) {                             //loop trhough to insert data    
                bind($stmt,'s',$input[$i]);
                if(!execute($stmt))
                    $success=false;
            }
            if($success) {
                ?>
                <strong>The attendance you entered has been saved.</strong>
                <?php
            }
            close_stmt($stmt);
        } else if(isset($_GET['ecode'])||isset($_SESSION['eCode'])) {  //if event code is specified then create a bunch of inputs
            if(!isset($_SESSION['eCode'])) {              //if is the first enterance
                $_SESSION['eCode']=  cleanInputString($_GET['ecode'],32,"Event Code",FALSE);        //store the event code for future use
                $numberOfInserts=50;
                $_SESSION['number']=$numberOfInserts;    //stores to session
                ?>
                <strong>Please enter attendance for this event below</strong><br>
                <br>
                <form method="post">
                <?php
                for($i=0;$i<$numberOfInserts;$i++) {  //loop through to create a ton of inputs
                    echo 'Insert by CAPID: <input type="text" name="cap[]" size="5" maxlength="6"/> or <input type="submit" name="search'.$i.'" value="Search"/>'."<br>\n";
                }
                ?>
                    <input type="submit" name="insert" value="insert"/>
                </form>
                <?php
            } else {                                //if has stored input then display it
                $numberOfInserts=50;
                ?>
                <strong>Please enter attendance for this event below</strong><br>
                <br>
                <form method="post">
                    <input type="hidden" name="number" value="<?php echo $numberOfInserts;?>"/>
                <?php
                for($i=0;$i<$numberOfInserts;$i++) {  //loop through to create a ton of inputs
                    echo 'Insert by CAPID: <input type="text" name="cap[]" size="5" ';
                    if(isset($_SESSION['input'][$i]))          //if has stored data then display it
                        echo ' value="'.$_SESSION['input'][$i].'" ';  //displays stored input
                    echo ' maxlength="6"/> or <input type="submit" name="search'.$i.'" value="Search"/>'."<br>\n";
                }
                ?>
                    <input type="submit" name="insert" value="insert"/>
                </form>
                <?php
            }
        } else if(isset($_POST['type'])) {   //if has searched
            searchEvent($ident,"punchIt",$_SERVER['SCRIPT_NAME']);
        }else {                   //if no input then show searched input
            display_event_search_in($ident);
        }
        function punchIt($code) {         //if has 1 result refresh to have e-code
            echo '<meta http-equiv="REFRESH content="0;url=/login/attendance/add.php?ecode='.$code.'">';
            exit;
        }
        ?>
    </body>
</html>
