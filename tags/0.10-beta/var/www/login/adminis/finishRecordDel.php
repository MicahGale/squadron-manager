<?php
/**
 * Finishes the deleting of a member.
 * 
 * Is passed session information from deleteRecord.php, and also login information
 * once the user has been verified again the deletes will be made.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
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
 */
require("projectFunctions.php");
$ident = connect('login');
session_secure_start();
$passes=  parse_ini_file(PSSWD_INI);
$salt=$passes['salt'];
$staffer=$_SESSION['member'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <script type="text/javascript" src="/java_script/CAPS_LOCK.js"></script>
        <title>Complete Record Deleting</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Finish Deleting Records</h1>
        <?php
        if($staffer->check_password($ident,$_POST['password'],$salt)) {   //check that they logged in correctly then do the stuff
            $delete=  connect("delete");    //get a user who can delete data
            if(isset($_SESSION['request'])) {
                $stmt= prepare_statement($ident,"INSERT INTO DELETE_REQUESTS(REQUESTER, REQUEST_DATE, DELETE_MEMBER)
                    VALUES('".$staffer->getCapid()."',NOW(),?)");
                for($i=0;$i<count($_SESSION['request']);$i++) {  //insert requests
                    bind($stmt,"i",array($_SESSION['request'][$i]));
                    execute($stmt);              //insert the requests
                }
                close_stmt($stmt);
                echo "<p>Successfully entered your ".count($_SESSION['request'])." request(s) to delete records</p>";
            }
            if(isset($_SESSION['delete'])) {
                $delete_tables=array("ATTENDANCE",'CPFT_ENTRANCE','DISCIPLINE_LOG','EMERGENCY_CONTACT',
                    'LOGIN_LOG','PROMOTION_BOARD','PROMOTION_RECORD','PROMOTION_SIGN_UP','REQUIREMENTS_PASSED',
                    'RIBBON_REQUEST','SPECIAL_PERMISSION','STAFF_POSITIONS_HELD','TESTING_SIGN_UP');
                //delete requests
                $tables=array('DISCIPLINE_LOG','PROMOTION_BOARD','PROMOTION_RECORD','REQUIREMENTS_PASSED','RIBBON_REQUEST');   // the tables to check
                $columns=array('GIVEN_BY','BOARD_PRESIDENT','APROVER','TESTER','APROVED_BY');   // the name of the columns to check
                $number_rows=0;
                $fail= array();   //members who have to stay in because they are tied to other members
                for($i=0;$i<count($_SESSION['delete']);$i++) {
                    $disapearer= $_SESSION['delete'][$i];  //get the capid
                    for($j=0;$j<count($delete_tables);$j++) {  //clear each table
                        $query= "DELETE FROM ".$delete_tables[$j]." WHERE CAPID='$disapearer'";
                        Query($query, $delete);
                        $number_rows+=rows_affected($delete);  //gets the number of the rows
                    }
                    $query="DELETE FROM DELETE_REQUESTS WHERE REQUESTER='$disapearer' OR DELETE_MEMBER='$disapearer'";
                    Query($query, $delete);  //delete delete requests,
                    for($j=0;$j<count($tables);$j++) {   //check for other records.
                        $query="SELECT 'HI' FROM ".$tables[$i]." WHERE ".$columns[$i]."='$disapearer'";
                        if(numRows(Query($query, $ident))>0) {  //if found results then log it and break
                            array_push($fail, $disapearer);
                            break;
                        }
                    }
                    if(!in_array($disapearer, $fail)) { //clear member
                        Query("DELETE FROM MEMBER WHERE CAPID='$disapearer'", $delete); //die!!!!!!!!!!!!
                        $number_rows++;
                    }
                    $time=  auditLog($_SERVER['REMOTE_ADDR'], 'DR');
                    auditDump($time, "Deleted Member", $disapearer);
                }
                echo "<p> $number_rows records have been successfully deleted</p>";
                if(count($fail)>0) {  //if members' records stayed in
                    echo "<p>The following members couldn't be deleted because they were involved in other member's records:<br>\n";
                    for($i=0;$i<count($fail);$i++) {
                        $member= new member($fail[$i],1,$ident);
                        echo $member->link_report(true)."<br>\n";
                    }
                    echo "</p>";
                }
            }
            $_SESSION['request']=null;
            $_SESSION['delete']=null;
            unset($_SESSION['request'],$_SESSION['delete']);
            close($delete);                 //close the connection
        } else {  //try to login again
            sleep(BAD_LOGIN_WAIT); //sleep if wrong password
            ?>
            <form method="post">
                <div style="color:red">That was the incorrect Password.</div>
                Capid <input type="text" name="capid" size="5" disabled="disabled" value="<?php echo $staffer->getcapid();?>"/><br>
                Password<input type="password" autocomplete="off" name="password" size="5" onkeypress="check_caps(event);"/><span id="warn" class="F"></span><br>
                <input type="submit" name="login" value="login"/>
            </form>
        <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>
