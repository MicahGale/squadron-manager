<?php
/**Handles all pt testing tasks, except for handling uploaded csv pt test files
 * 
 * This will handle viewing PT test sign-ups, creating PT test csv files, and entering individual PT tests
 * @package Squadron-manager
 */
/*  Copyright 2013 Micah Gale
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
/*Input
 * $_GET
 * capid the individual who the test is for
 * achiev the code they are testing for
 * multi 0 is for individual 1 is for testing signup
 * 
 * $_POST
 * capid the person 
 * *TEST_CODE* the input for the CPFT test
 * search clicking on search for jumping to member search
 * save saves the input
 * 
 */
require("projectFunctions.php");
session_secure_start();
$ident=  connect($_SESSION['member']->getCapid(), $_SESSION['password']);
if(isset($_POST['search'])) {  //if searched then save it
    $_SESSION['date']=  parse_date_input($_POST);
    $_SESSION['achiev']=$_REQUEST['achiev'];
    for($i=0;$i<count($_SESSION['header']);$i++) {
        $_SESSION['CPFT'][$_SESSION['header'][$i]['TEST_CODE']]=$_POST[$_SESSION['header'][$i]['TEST_CODE']];
    }
    header("refresh:0;url=/login/member/search.php?redirect=/login/testing/PTtest.php");  //refresh
    exit;
} if(isset($_POST['save'])) {
    $query="INSERT INTO CPFT_ENTRANCE(CAPID,ACHIEV_CODE, TEST_TYPE,SCORE)
        VALUES('".$_REQUEST['capid']."','".$_SESSION['achiev']."',?,?)";
    $insert= prepare_statement($ident, $query);
    $header=$_SESSION['header'];
    for($i=0;$i<count($header);$i++) {
        bind($insert,"sd", array($header[$i]['TEST_CODE'],$_POST[$header[$i]['TEST_CODE']]));
        execute($insert);
    }
    unset($_SESSION['date'],$_SESSION['CPFT'],$_SESSION['header'],$_SESSION['achiev']);
    header("refresh:0;url=/login/home.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Manage CPFT tests</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Manage CPFT Tests</h1>
        <form method="post">
        <?php
        if(!isset($_GET['capid'])&&!isset($_GET['multi'])) {
            ?>
        <p><a href="/login/testing/PTtest.php?multi=0">Enter individual CPFT test</a></p>
        <p><a href="/login/testing/PTtest.php?multi=1">View CPFT testing sign-up</a></p>
        <p><a href="/login/testing/ptCSV.php">Download CSV file of CPFT testing sign-up</a></p>
        <?php
        } else if(isset($_GET['capid'])||(isset($_GET['multi'])&&$_GET['multi']==0)) {
            if(isset($_GET['capid']))
                $capid=  cleanInputInt($_GET['capid'],6, 'capid');
            echo 'Enter member CAPID:<input type="text" size="5" maxlength="100" name="capid" ';
            if(isset($capid))
                echo 'value="'.$capid.'"';
            echo '/>or<input type="submit" name="search" value="search for a member"/><br><br>'."\n";
            echo "Select achievement test is for:";
            if(isset($_REQUEST['achiev']))
                $_SESSION['achiev']=  cleanInputString ($_REQUEST['achiev'],5,'achievement',true);
            dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "achiev", $ident, false,$_SESSION['achiev']);
            echo "<br><br>";
            $date=null;
            if(isset($_SESSION['date']))
                $date=$_SESSION['date'];
            enterDate(true,null,$date);
            $query="SELECT TEST_CODE, TEST_NAME FROM CPFT_TEST_TYPES ORDER BY TEST_NAME";
            $header=  allResults(Query($query, $ident));
            $_SESSION['header']=$header;
            ?>
        <br> <br><table border="1">
            <?php
            echo "<tr>";
            for($i=0;$i<count($header);$i++) {
                echo "<th>".$header[$i]['TEST_NAME'].'</th>';
            }
            echo "</tr>\n<tr>";
            for($i=0;$i<count($header);$i++) {     //display input
                $code=$header[$i]['TEST_CODE'];
                echo '<td><input type="text" size="1" maxlength="10" name="'.$code.'" ';
                if(isset($_SESSION['CPFT'][$code]))
                    echo 'value="'.$_SESSION['CPFT'][$code].'"';
                echo '/></td>';
            }
            echo "</tr>";
            ?>  
            </table>
        <br><br><input type="submit" name="save" value="save"/>
        <?php
        } else if($_GET['mulit']==1) {
            
        }
        require("squadManFooter.php");
        ?>
        </form>
    </body>
</html>
