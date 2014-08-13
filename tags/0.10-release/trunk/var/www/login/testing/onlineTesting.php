<?php
/**
 * Allows Users to enter the online testing from eservices.
 * 
 * First allows the user to find the member, then displays the possibilities for 
 * entry and allows the user to enter them
 * 
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * 
 * $_GET
 * capid
 * 
 * $_POST
 * passed[]- all the passed tests the value is the test code
 * *date**type_code*- the date passed
 * percentage*type_code* - the percentage
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
if(isset($_POST['submit'],$_POST['passed'])) { //if they are saving get the inputs
    $capid= cleanInputInt($_GET['capid'],6,'capid');
    $member=new member($capid,1,$ident);
    $next=$member->get_next_achiev($ident);
    $insert= prepare_statement($ident, "INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, ON_ESERVICES, TESTER, PASSED_DATE, PERCENTAGE, REQUIREMENT_TYPE)
        VALUES('$capid','$next',true,'".$_SESSION['member']->getCapid()."',?,?,?)");
    $check=  prepare_statement($ident, "SELECT PASSING_PERCENT FROM PROMOTION_REQUIREMENT
        WHERE ACHIEV_CODE='$next' AND REQUIREMENT_TYPE=?");
    $bad_percent=array();
    for($i=0;$i<count($_POST['passed']);$i++) {   //gets the input
        $code=  cleanInputString($_POST['passed'][$i],2,"requirement type",false);  //parse the checkbox input
        bind($check,"s",array($code));            //get the passing percentage
        $result=  allResults(execute($check));   //acutally get it
        $in_percent= parsePercent($code, $_POST,$result[0]['PASSING_PERCENT']);
        $date= parse_date_input($_POST, $code);           //get the input date
        if($date==null)
            $date= new DateTime();          //else assume it was today
        if($in_percent!=false) {                //input into the database if passed.
            bind($insert,"sds",array($date->format(PHP_TO_MYSQL_FORMAT),$in_percent,$code));
            execute($insert);               //insert entrance into database
        } else {       //get a list of errors
            array_push($bad_percent, $code);  
        }
   }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Enter Online Testing</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Enter Online Testing</h1>
        <form method="get">
            Enter Capid:<input type="text" name="capid" size="3"/><input type="submit" value="Check"/>
            Or <a href="/login/member/search.php?redirect=/login/testing/onlineTesting.php">Search for A member</a><br>
        </form>
        <?php
        if(isset($_GET['capid'])) {
            $capid= cleanInputInt($_GET['capid'],6,"Capid");
            $member= new member($capid,1,$ident);
            echo $member->link_report(true);
            $results= allResults(query("SELECT B.ACHIEV_CODE, B.ACHIEV_NAME FROM ACHIEVEMENT A, ACHIEVEMENT B
                WHERE A.NEXT_ACHIEV=B.ACHIEV_CODE AND A.ACHIEV_CODE='".$member->get_achievement()."'",$ident));
            $next=$results[0]['ACHIEV_CODE'];
            echo '<br>Enter Testing for:<input type="text" disabled="disabled" size="6" value="'.$results[0]['ACHIEV_NAME'].'"/>';
            $query="SELECT CONCAT(IFNULL(NAME,''),'-',TYPE_NAME) AS NAME, TYPE_CODE 
                FROM PROMOTION_REQUIREMENT, REQUIREMENT_TYPE
                WHERE TYPE_CODE=REQUIREMENT_TYPE AND IS_ONLINE=TRUE
                AND ACHIEV_CODE='$next'
                AND REQUIREMENT_TYPE NOT IN (SELECT REQUIREMENT_TYPE FROM REQUIREMENTS_PASSED
                    WHERE CAPID='$capid' AND ACHIEV_CODE='$next')";
            $results = allResults(Query($query, $ident));  //gets all the tests that can be entered
            ?>
        <br>
        <br>
        <form method="post">
            <a href="/help/inputPercentages.php" target="_blank">Inputting Percentages</a>
            <table class="table">
                <tr class="table"><th class="table">Passed</th><th class="table">Test</th><th class="table">Passed Date</th><th class="table">Percentage</th></tr>
                <?php
                for($i=0;$i<count($results);$i++) {   //cycles through the results
                    echo '<tr class="table"><td class="table"><input type="checkbox" name="passed[]" value="'.$results[$i]['TYPE_CODE'].'" /></td>';
                    echo '<td class="table">'.$results[$i]['NAME']."</td><td class=\"table\">";
                    enterDate(true, $results[$i]['TYPE_CODE']);
                    echo '</td><td class="table">';
                    if(isset($bad_percent)&&  in_array($results[$i]['TYPE_CODE'], $bad_percent))
                            echo "This is not a passing percentage";
                    echo '<input type="text" size="5" name="percentage'.$results[$i]['TYPE_CODE']."\"/></td></tr>\n";
                }
                ?>
            </table>
            <input type="submit" name="submit" value="submit"/>
        </form>
            <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>