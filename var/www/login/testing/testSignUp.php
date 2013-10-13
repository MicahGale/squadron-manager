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
$ident=  connect('login');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
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
                        <?php
                        if(!isset($_GET['lock'])) {
                                ?>
                            Filter by test type:
                            <?php
                            dropDownMenu("SELECT TYPE_CODE, TYPE_NAME FROM REQUIREMENT_TYPE
                                WHERE TYPE_CODE NOT IN('AC','CD','ME','SA','SD','PB') ORDER BY TYPE_NAME","filterTypes", $ident,false,null,true);
                            ?>
                            <input type="submit" name="filter" value="filter"/><br><br>
                        <?php } ?>
                        <input type="submit" name="save" value="save"/><br><br>
                        <input type="submit" name="check" value="Check percentages"/><br>
                        Tester:<input type="text" size="2" disabled="disabled" value="<?php echo $_SESSION['member']->getcapid();?>"/>
                    <table class="table">
                        <tr>
                            <th class="table">Member</th><th class="table">Test type</th><th class="table">Test</th><th class="table">Passed</th><th class="table">Percentage<a href="/help/inputPercentages.php" target="_blank">?</a></th><th class="table">On Eservices</th><th class="table">Remove</th>
                        </tr>
                        <?php
                        if(isset($_POST['check'])) {           //if checking percentage
                            $results=$_SESSION['results'];
                            for($i=0;$i<count($results);$i++) {
                                if(isset($_POST["percentage$i"])) {
                                if(is_numeric(parsePercent($i, $_POST, $results[$i]['PASSING_PERCENT'])))
                                        $percent[$i]=true;
                                } else {
                                    $percent[$i]=false;
                                }
                                $input[$i]=$_POST["percentage$i"];
                            }
                        }
                        if(isset($_POST['save'])) {                       //if saved is requested then save it
                            $results=$_SESSION['results'];
                            $query="INSERT INTO REQUIREMENTS_PASSED(CAPID,ACHIEV_CODE,REQUIREMENT_TYPE,TEXT_SET,PASSED_DATE,ON_ESERVICES,PERCENTAGE, TESTER)
                                VALUES(?,?,?,?,CURDATE(),?,?,".$_SESSION['member']->getcapid().")";
                            $stmt=  prepare_statement($ident, $query);        //prepare statement to insert data
                            $success = true;
                            $toRemove = array();               //array to tell what testing sign ups to remove
                            $toInsert =array();
                            for($i=0;$i<count($results);$i++) {     //insert based on specified percentage
                                if(isset($_POST["percentage$i"])&&$_POST["percentage$i"]!="") { //if a percentage is entered then enter it
                                    $percent=  parsePercent($i,$_POST,$_SESSION['results'][$i]["PASSING_PERCENT"]);
                                    if($percent!=null&&$percent!=false) {
                                        array_push($toInsert, $i);             //insert it if the percent isn't null or ridonculous
                                    }
                                }
                            }
                            if(isset($_POST['passed'])) {
                                for($i=0;$i<count($_POST['passed']);$i++) {   //finds things to insert based on things passed
                                    if(!in_array($_POST['passed'][$i],$toInsert)) {  //if hasn't already about to be inserted
                                        array_push($toInsert, $_POST['passed'][$i]);  //insert it then                                    
                                    }
                                }
                            }
                            for($i=0;$i<count($toInsert);$i++) {   //cycles through ones that were passed and save input
                                $capid=$_SESSION['results'][$toInsert[$i]]['CAPID'];
                                $member = new member($capid,2, $ident);           //get text set
                                $text = $member->get_text();
                                $achiev=$_SESSION['results'][$toInsert[$i]]['ACHIEV_CODE'];
                                $type= $_SESSION['results'][$toInsert[$i]]['REQUIRE_TYPE'];
                                $percent = parsePercent($toInsert[$i],$_POST,$_SESSION['results'][$toInsert[$i]]["PASSING_PERCENT"]);
                                if(isset($_POST['passed'])&&isset($_POST['eservice'])&&in_array($toInsert[$i], $_POST['eservice'])) {           //if they also checked the eservices box then say so
                                    $onEservices = "true";
                                } else {
                                    $onEservices = "false";
                                }
                                if($percent!=false) {   //if the percent is within the range then ok to insert
                                    bind($stmt,"isssss", array($capid,$achiev,$type,$text,$onEservices,$percent));
                                    if(!execute($stmt))                  //if failed log it
                                        $success=false;
                                    else {
                                        array_push($toRemove,$toInsert[$i]);  //if was inserted then don't remove
                                    }
                                }
                            }
                            $query="DELETE FROM TESTING_SIGN_UP 
                                WHERE CAPID=? 
                                AND ACHIEV_CODE=? 
                                AND REQUIRE_TYPE=?";
                            $stmt= prepare_statement($ident, $query);
                            if(isset($_POST['remove'])) {
                                for($i=0;$i<count($_POST['remove']);$i++) {      //get request to delete testing sign up
                                    array_push($toRemove, $_POST['remove'][$i]);   //push it onto the array
                                }
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
                        if(isset($_POST['filter'])) {
                            if($_POST['filterTypes']!="null") {
                                $_SESSION['filter']=  cleanInputString($_POST['filterTypes'],2,"test filter",false);
                            } else {
                                unset($_SESSION['filter']);
                            }
                        }
                        $query ='SELECT A.CAPID, C.TYPE_NAME,CONCAT(A.REQUIRE_TYPE," - ",B.NAME) AS TEST_NAME, E.ACHIEV_CODE, A.REQUIRE_TYPE, B.PASSING_PERCENT
                            FROM  REQUIREMENT_TYPE C, TESTING_SIGN_UP A, PROMOTION_REQUIREMENT B, MEMBER D, ACHIEVEMENT E
                            WHERE D.CAPID=A.CAPID AND E.ACHIEV_CODE=D.ACHIEVEMENT
                            AND E.NEXT_ACHIEV=B.ACHIEV_CODE AND A.REQUIRE_TYPE=B.REQUIREMENT_TYPE
                            AND C.TYPE_CODE=A.REQUIRE_TYPE
                            AND A.REQUIRE_TYPE NOT IN(\'AC\',\'CD\',\'ME\',\'SA\',\'SD\',\'PB\')';
                        if(isset($_SESSION['filter'])&&!isset($_GET['lock'])) {
                            $query.=" AND A.REQUIRE_TYPE='".$_SESSION['filter']."'";  //if there's a filter then apply it
                        } else if(isset($_GET['lock'])) {
                            $query.=" AND A.REQUIRE_TYPE='".cleanInputString($_GET['lock'],2,'Lock type' , false)."'";
                        }
                        $results = allResults(Query($query, $ident));
                        $_SESSION['results']=$results;  //stores the results so they may be used later
                        $size=count($results);
                        for($i=0;$i<$size;$i++) {            //display testing requests
                            echo "<tr><td class=\"table\">";
                            $member=new member($results[$i]["CAPID"],1, $ident);
                            echo $member->link_report();
                            echo "</td><td class=\"table\">".$results[$i]['TYPE_NAME'].'</td><td class="table">'.$results[$i]['TEST_NAME']."</td>";
                            echo '<td class="table"><input type="checkbox" name="passed[]" value="'.$i.'" ';
                            if($percent[$i])
                                echo "checked";
                                echo'/></td>';
                            echo '<td class="table"><input type="text" size="1" maxlength="10" name="percentage'.$i.'"';
                            if(isset($input[$i]))
                                echo 'value="'.$input[$i].'"';
                            echo'/></td>';
                            echo '<td class="table"><input type="checkbox" name="eservices[]" value="'.$i.'"/></td>';
                            echo '<td class="table"><input type="checkbox" name="remove[]" value="'.$i."\"/></td></tr>\n";
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
