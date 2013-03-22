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
$query="SELECT TEST_CODE, TEST_NAME FROM CPFT_TEST_TYPES ORDER BY TEST_NAME";
$header=  allResults(Query($query, $ident));
$_SESSION['header']=$header;

if(isset($_POST['search'])) {  //if searched then save it
    $_SESSION['date']=  parse_date_input($_POST);
    $_SESSION['achiev']=$_REQUEST['achiev'];
    for($i=0;$i<count($_SESSION['header']);$i++) {
        $_SESSION['CPFT'][$_SESSION['header'][$i]['TEST_CODE']]=$_POST[$_SESSION['header'][$i]['TEST_CODE']];
    }
    header("refresh:0;url=/login/member/search.php?redirect=/login/testing/PTtest.php");  //refresh
    exit;
} if(isset($_POST['save'])) {
    $capid = cleanInputInt($_REQUEST['capid'],6,"Capid");
    $buffer=new member($capid,1,$ident);
    $requirements=$buffer->retrieveCPFTrequire($ident);  //get the requirements from
    $actual=array();  //parse the input as an array
    for($i=0;$i<count($header);$i++) {  //parse it together
        $buffer=$header[$i]['TEST_CODE'];   //buffer the results
        $actual[$buffer]= cleanInputInt($_POST[$buffer],strlen($_POST[$buffer]),"CPFT entrance".$buffer);
    }
    if(verifyCPFT($ident, $requirements, $actual)||(isset($_POST['waiver']))) {
        $query="INSERT INTO CPFT_ENTRANCE(CAPID,ACHIEV_CODE, TEST_TYPE,SCORE)
            VALUES('$capid','".$_SESSION['achiev']."',?,?)";
        $insert= prepare_statement($ident, $query);
        $header=$_SESSION['header'];
        for($i=0;$i<count($header);$i++) {
            bind($insert,"sd", array($header[$i]['TEST_CODE'],$actual[$i] ));
            execute($insert);
        }
        $_SESSION['achiev']=  cleanInputString($_SESSION['achiev'], 5,'achievement', false);
        $buffer->init(2, $ident); //get the text set
        if(isset($_POST['waiver'])&&$_POST['waiver']=='waive')
            $waive="TRUE";
        else
            $waive="FALSE";
        $query = "INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIRE_TYPE, TEXT_SET, PASSED_DATE, ON_ESERVICE, WAIVER)
            VALUES('$capid','".$_SESSION['achiev']."','PT','".$buffer->get_text()."',false,'$waive')";
        Query($query, $ident);
        unset($_SESSION['date'],$_SESSION['CPFT'],$_SESSION['header'],$_SESSION['achiev']);
        header("refresh:0;url=/login/testing/PTtest.php");
        exit;
    }
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
        <?php
        if(!isset($_GET['capid'])&&!isset($_GET['multi'])) {
            ?>
        <p><a href="/login/testing/PTtest.php?multi=0">Enter individual CPFT test</a></p>
        <p><a href="/login/testing/PTtest.php?multi=1">View CPFT testing sign-up</a></p>
        <p><a href="/login/testing/ptCSV.php">Download CSV file of CPFT testing sign-up</a></p>
        <p><a href="/login/testing/PTtest.php?multi=1&upload=0">Upload a CSV file of Testing_results</a></p>
        <?php
        } else if(isset($_GET['capid'])||(isset($_GET['multi'])&&$_GET['multi']==0)) {
            echo '<form method="post">';
            if(isset($_GET['capid']))
                $capid=  cleanInputInt($_GET['capid'],6, 'capid');
            echo 'Enter member CAPID:<input type="text" size="5" maxlength="100" name="capid" ';
            if(isset($capid))
                echo 'value="'.$capid.'"';
            echo '/>or<input type="submit" name="search" value="search for a member"/><br><br>'."\n";
            echo "Select achievement test is for:";
            if(isset($_REQUEST['achiev']))
                $_SESSION['achiev']=  cleanInputString ($_REQUEST['achiev'],5,'achievement',true);
            if(isset($_SESSION['achiev']))
                dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV where A.ACHIEV_CODE<>'0' ORDER BY A.ACHIEV_NUM", "achiev", $ident, false,$_SESSION['achiev']);
            else 
                 dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV where A.ACHIEV_CODE<>'0' ORDER BY A.ACHIEV_NUM ", "achiev", $ident);
            echo "<br><br>";
            $date=new DateTime();
            if(isset($_SESSION['date']))
                $date=$_SESSION['date'];
            enterDate(true,null,$date);
            ?>
        <br> <br><input type="checkbox" name="waiver" value="waive"/> Category II,III,IV CPFT waiver<br><br><table border="1">
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
        } else if(isset($_GET['multi'])&&$_GET['multi']==1&&!isset($_GET['upload'])) {
            echo '<form method="post">';
            $query ="SELECT A.CAPID, FLOOR(DATEDIFF(CURDATE(),A.DATE_OF_BIRTH)/365.25) AS AGE, A.GENDER, CONCAT(D.GRADE_NAME,' - ',E.ACHIEV_NAME) AS GRADE
                FROM MEMBER A, TESTING_SIGN_UP B, ACHIEVEMENT C, GRADE D, ACHIEVEMENT E
                WHERE A.ACHIEVEMENT=C.ACHIEV_CODE
                AND E.ACHIEV_CODE=C.NEXT_ACHIEV
                AND D.GRADE_ABREV=E.GRADE
                AND B.CAPID=A.CAPID
                AND B.REQUIRE_TYPE='PT'
                ORDER BY C.ACHIEV_NUM, A.NAME_LAST, A.NAME_FIRST";
            $results=  allResults(Query($query, $ident));
            ?>
        <table border="1"><tr><th>Name</th><th>Gender</th><th>Age</th>
            <?php
            for($i=0;$i<count($_SESSION['header']);$i++) { //crate headers
                echo "<th>".$_SESSION['header'][$i]['TEST_NAME'].'</th>';
            }
            echo "<th>CPFT Waiver</th>";
            ?>
            </tr></table>
        <?
        } else if(isset($_GET['multi'],$_GET['upload'])&&$_GET['multi']==1&&$_GET['upload']==0) {
            ?>
        <form action="/login/testing/PTtest.php?multi=1&upload=1" method="post" enctype="multipart/form-data">
            <p>Upload the CSV file that has the PT test score saved on it. The data will be saved to the database. NOTE: only .csv files are allowed.</p>
            <label for="file">Upload CSV:</label>
            <input type="file" name="file" id="file" accept="text/csv"/>
            <input type="submit" value="upload"/>
            <?php
        } else if(isset($_FILES['file'])) {
            $locat = cleanUploadFile('file',5*1024,'/var/upload/csv', 'text/csv');
            if($locat!==false&&($handle=fopen($locat,'r'))!==false) {              //opens the file
                $parse=array(array());                            //the array to hold it
                while(($row=  fgetcsv($handle, 1000))!=false) {   //parses one row at a time
                    if(!isset($columns)) {  //if the columns aren't defined
                        if($row[0]=='CAPID') {  //if the first cell is capid, then parse the columns 
                            for($i=2;$i<count($row);$i++) {       //cycle through the headers
                                $buffer=$row[$i];   
                                for($j=0;$j<count($header);$j++) {
                                    if(str_replace(' ','', $buffer)==str_replace(" ", '', $header[$j]['TEST_NAME'])) {  //compares it to the test name without space
                                        $columns[$i-2]=$header[$j]['TEST_CODE'];
                                    }
                                }
                            }
                        } else {
                            if(strpos($row[0],"--"==false)) {   //if the row doesn't start with --- then parse the input
                                $capid=  cleanInputInt($row[0], 6,'CAPID from CSV');
                                $parse[$capid]['member']=new member($capid,1,$ident);
                                $buffer=$parse[$capid]['member'];
                                if($row[1]==(getName_Last().", ".$buffer->getName_first())) {  //check if the proper name in the row
                                    $parse[$capid]['name']="C";
                                } else {
                                    $parse[$capid]['name']='I';
                                }
                                for($i=0;i<count($columns);$i++) {   //parse the test results
                                    $parse[$capid]['actual'][$columns[$i]]=  cleanInputInt($row[$i+2], strlen($row[$i+2]),"Test input for:".$capid." for: ".$columns[$i]);  //parses the info
                                }
                                $standard=$buffer->retrieveCPFTrequire($ident);  //get the standards they need
                            }
                        }
                    }
                }
            }
        }
        require("squadManFooter.php");
        ?>
        </form>
    </body>
</html>
