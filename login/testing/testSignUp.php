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
/*
 *forms: is recursive and is post
 *  filterTypes: dropDown menu- gives possible filters
 * filter:       submit: applies the requested filter
 * save:         submit: saves 
 * passed[]:     value=result row checkbox:  weather or not to save the input, weather or not to insert data
 * percentage(result row): text: the percentage the test passed with
 * eservice[]    value=result row : checkbox: if the test is entered onto eservices yet
 * remove[]     value=result row:  checkbox: to remove sign-ups that weren't passed.
 */
require("projectFunctions.php");
session_secure_start();
$ident=  connect($_SESSION['member']->getCapid(), $_SESSION['password']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <title>View testing Sign-up and enter Scores</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <table border="0" width="800">
            <tr>
                <td align="center">
                    <strong>View Testing and Promotion Board Sign-up</strong>
                    <form method="post">
                        Filter by test type:
                        <?php
                        dropDownMenu("SELECT TYPE_CODE, TYPE_NAME FROM REQUIREMENT_TYPE
                            WHERE TYPE_CODE NOT IN('AC','CD','ME','SA','SD','PB') ORDER BY TYPE_NAME","filterTypes", $ident,false,null,true);
                        ?>
                        <input type="submit" name="filter" value="filter"/><br><br>
                        <input type="submit" name="save" value="save"/>
                    <table border="1" cellpadding="0">
                        <tr>
                            <th>Member</th><th>Test type</th><th>Test</th><th>Passed</th><th>Percentage (optional)</th><th>On Eservices</th><th>Remove</th>
                        </tr>
                        <?php
                        if(isset($_POST['filter'])) {
                            if($_POST['filterTypes']!="null") {
                                $_SESSION['filter']=  cleanInputString($_POST['filterTypes'],2,"test filter",false);
                            } else {
                                unset($_SESSION['filter']);
                            }
                        }
                        $query ='SELECT A.CAPID, C.TYPE_NAME,CONCAT(A.ACHIEV_CODE," - ",B.NAME) AS TEST_NAME, A.ACHIEV_CODE, A.REQUIRE_TYPE
                            FROM TESTING_SIGN_UP A, PROMOTION_REQUIREMENT B, REQUIREMENT_TYPE C
                            WHERE A.ACHIEV_CODE=B.ACHIEV_CODE
                            AND A.REQUIRE_TYPE=B.REQUIREMENT_TYPE
                            AND C.TYPE_CODE=A.REQUIRE_TYPE
                            AND A.REQUIRE_TYPE NOT IN(\'AC\',\'CD\',\'ME\',\'SA\',\'SD\',\'PB\')';
                        if(isset($_SESSION['filter'])) {
                            $query.=" AND A.REQUIRE_TYPE='".$_SESSION['filter']."'";  //if there's a filter then apply it
                        }
                        echo $query;
                        $results = allResults(Query($query, $ident));
                        var_dump($results);
                        $_SESSION['results']=$results;  //stores the results so they may be used later
                        $size=count($results);
                        for($i=0;$i<$size;$i++) {            //display testing requests
                            echo "<tr><td>";
                            $member=new member($results[$i]["CAPID"],1, $ident);
                            echo $member->link_report();
                            echo "</td><td>".$results[$i]['TYPE_NAME']."</td><td>".$results[$i]['TEST_NAME']."</td>";
                            echo '<td><input type="checkbox" name="passed[]" value="'.$i.'"/></td>';
                            echo '<td><input type="text" size="1" maxlength="3" name="percentage'.$i.'"/></td>';
                            echo '<td><input type="checkbox" name="eservices[]" value="'.$i.'"/></td>';
                            echo '<td><input type="checkbox" name="remove[]" value="'.$i."\"/></td></tr>\n";
                        } 
                        if(isset($_POST['save'])) {                       //if saved is requested then save it
                            $query="INSERT INTO REQUIREMENTS_PASSED(CAPID,ACHIEV_CODE,REQUIREMENT_TYPE,TEXT_SET,PASSED_DATE,ON_ESERVICES)
                                VALUES(?,?,?,?,CURDATE(),?)";
                            $stmt=  prepare_statement($ident, $query);        //prepare statement to insert data
                            $success = true;
                            $toRemove = array();               //array to tell what testing sign ups to remove
                            for($i=0;$i<count($_POST['passed']);$i++) {   //cycles through ones that were passed and save input
                                array_push($toRemove, $_POST['passed'][$i]); //remove the ones that were passed
                                $capid=$_SESSION['results'][$_POST['passed'][$i]]['CAPID'];
                                $member = new member($capid,2, $ident);           //get text set
                                $text = $member->get_text();
                                $achiev=$_SESSION['results'][$_POST['passed'][$i]]['ACHIEV_CODE'];
                                $type= $_SESSION['results'][$_POST['passed'][$i]]['REQUIRE_TYPE'];
                                if(in_array($_POST['passed'][$i], $_POST['eservices'])) {           //if they also checked the eservices box then say so
                                    $onEservices = "true";
                                } else {
                                    $onEservices = "false";
                                }
                                bind($stmt,"issss", array($capid,$achiev,$type,$text,$onEservices));
                                if(!execute($stmt))                  //if failed log it
                                    $success=false;
                            }
                            $query="DELETE FROM TESTING_SIGN_UP 
                                WHERE CAPID=? 
                                AND ACHIEV_CODE=? 
                                AND REQUIRE_TYPE=?";
                            $stmt= prepare_statement($ident, $query);
                            for($i=0;$i<count($_POST['remove']);$i++) {      //get request to delete testing sign up
                                array_push($toRemove, $_POST['remove'][$i]);   //push it onto the array
                            }
                            foreach ($toRemove as $buffer) {
                                $capid=$results[$buffer]['CAPID'];
                                $achiev = $results[$buffer]['ACHIEV_CODE'];
                                $type = $results[$buffer]['REQUIRE_TYPE'];
                                bind($stmt,"iss", array($capid,$achiev,$type));   //bind the field so it can be deleted
                                if(!execute($stmt))
                                    $success=false;               //if failed then document it
                            }
                            close_stmt($stmt);
                        }
                        ?>
                    </table>
                        <input type="submit" name="save" value="save"/>
                    </form>
                </td>
            </tr>
        </table>
        <?php
        include("squadManFooter.php");
        ?>
    </body>
</html>
