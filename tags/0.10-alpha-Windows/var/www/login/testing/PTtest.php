<?php
/**
 * Handles all pt testing tasks, except for handling uploaded csv pt test files
 * 
 * This will handle viewing PT test sign-ups, creating PT test csv files, and entering individual PT tests
 * 
 * Input
 * $_GET
 * capid the individual who the test is for
 * achiev the code they are testing for
 * multi 0 is for individual 1 is for testing signup
 * upload 0 if uploading files 1 when the file has been uploaded
 * field the field the capid is for- tester the tester testee the testee
 * 
 * $_POST
 * capid the person (text)
 * tester the capid of the tester
 * *TEST_CODE* the input for the CPFT test (text)
 * search clicking on search for jumping to member search (submit)
 * save saves the input (submit)
 * *capid* the pass/fail of the member pass=passed fail=failed (radio)
 * waive[$i]=*capid* waiving the cpft for the capid (checkbox)
 * 
 * $_SESSION
 * date- the date of the test
 * achiev- the achievement an individual test is for
 * capid- the capid of the testee 
 * tester- the capid of the tester
 * 
 * @package Squadron-manager
 * @copyright (c) 2013, Micah Gale
 */
/*  Copyright 2014 Micah Gale
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
$ident=  connect('login');
$query="SELECT TEST_CODE, TEST_NAME FROM CPFT_TEST_TYPES ORDER BY TEST_NAME";
$header=  allResults(Query($query, $ident));
$_SESSION['header']=$header;
if(isset($_POST['search'])||isset($_POST['searchT'])) {  //if searched then save it
    $_SESSION['date']=  parse_date_input($_POST);
    $_SESSION['achiev']=  cleanInputString($_REQUEST['achiev'],2,'Achievement',false);
    $_SESSION['capid']=  cleanInputInt($_POST['capid'],6, 'capid');
    $_SESSION['tester']=  cleanInputInt($_POST['tester'], 6,'tester');
    for($i=0;$i<count($_SESSION['header']);$i++) {
        $_SESSION['CPFT'][$_SESSION['header'][$i]['TEST_CODE']]=$_POST[$_SESSION['header'][$i]['TEST_CODE']];
    }
    if(isset($_POST['searchT']))
        $field='tester';
    else
        $field='testee';
    header("refresh:0;url=/login/member/search.php?redirect=/login/testing/PTtest.php&field=$field");  //refresh
    exit;
} if(isset($_POST['save'])&&(!isset($_GET['multi'])||$_GET['multi']==0)) { //S(!M!I+MI) S!(M xor I)
    $capid = cleanInputInt($_REQUEST['capid'],6,"Capid");
    $tester= cleanInputInt($_POST['tester'], 6, 'Tester Capid',true);
    $buffer=new member($capid,1,$ident);
    $_SESSION['achiev']=  cleanInputString($_REQUEST['achiev'],2,'Achievement',false);
    $requirements=$buffer->retrieveCPFTrequire($ident);  //get the requirements from
    $_SESSION['date']=  parse_date_input($_POST);
    $actual=array();  //parse the input as an array
    for($i=0;$i<count($header);$i++) {  //parse it together
        $buff=$header[$i]['TEST_CODE'];   //buffer the results
        if($_POST[$buff]!==0&&$_POST[$buff]!=="") {
            if($buff!='MR')
                $actual[$buff]= cleanInputInt($_POST[$buff],strlen($_POST[$buff]),"CPFT entrance".$buff);
            else 
                $actual[$buff]=  parseMinutes (cleanInputString ($_POST[$buff],5,"Mile run",true));
        }
    }
    if(verifyCPFT($ident, $requirements, $actual)||(isset($_POST['waiver']))) {
        $query="REPLACE CPFT_ENTRANCE(CAPID,ACHIEV_CODE, TEST_TYPE,SCORE)
            VALUES('$capid','".$_SESSION['achiev']."',?,?)";
        $insert= prepare_statement($ident, $query);
        $header=$_SESSION['header'];
        for($i=0;$i<count($header);$i++) {
            $buff=$header[$i]['TEST_CODE'];
            if(isset($actual[$buff])&&$actual[$buff]!==0&&$actual[$buff]!=="") {
                bind($insert,"sd", array($buff,$actual[$buff] ));
                execute($insert);
            }
        }
        $buffer->init(2, $ident); //get the text set
        if(isset($_POST['waiver'])&&$_POST['waiver']=='waive')
            $waive="TRUE";
        else
            $waive="FALSE";
        $query = "REPLACE INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET, PASSED_DATE, ON_ESERVICES, WAIVER,TESTER)
            VALUES('$capid','".$_SESSION['achiev']."','PT','".$buffer->get_text()."','".$_SESSION['date']->format(PHP_TO_MYSQL_FORMAT)."',false,'$waive','$tester')";
        Query($query, $ident);
        unset($_SESSION['date'],$_SESSION['CPFT'],$_SESSION['header'],$_SESSION['achiev'],$_SESSION['capid'],$_SESSION['tester']);
        header("refresh:0;url=/login/testing/PTtest.php");
        exit;
    }
    unset($_SESSION['csv']);   //clear the csv parsed data
}
if(isset($_POST['save'])&&isset($_GET['multi'])&&$_GET['multi']==1) {
    $tester=  cleanInputInt($_POST['tester'], 6, 'Tester capid');
    $query="INSERT INTO CPFT_ENTRANCE(CAPID, ACHIEV_CODE,TEST_TYPE, SCORE)
            VALUES(?,?,?,?)";
    $score_insert=  prepare_statement($ident, $query);
    $query="INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET, PASSED_DATE, WAIVER,TESTER)
        VALUES(?,?,'PT',?,?,?,'$tester')";
    $log= prepare_statement($ident, $query);
    $query="DELETE FROM TESTING_SIGN_UP WHERE REQUIRE_TYPE='PT' AND CAPID=?";
    $delete = prepare_statement($ident, $query);
    $header=$_SESSION['header'];
    foreach($_SESSION['csv'] as $key=>$buffer) {    //cycle trhough results
        if($key!='date') {
            $capid=$buffer['member']->getCapid();
            if($_POST[$capid]=='pass') {               //if they passed enter it
                $passed=false;
                for($i=0;$i<count($header);$i++) {   //parse all the input
                    $buffer=$header[$i]['TEST_CODE'];
                    if($buffer!='MR') {    //if it isn't the mile run parse it as a number
                        $actual[$buffer]=  cleanInputInt($_POST[$buffer.$capid],  strlen($_POST[$buffer.$capid]), "Test input for ".$buffer);
                    } else {
                        $parsed=  cleanInputString($_POST[$buffer.$capid],5,"Test input for ".$buffer,false);
                        if(is_numeric($parsed))
                            $actual[$buffer]=$parsed;
                        else
                            $actual[$buffer]=  parseMinutes ($parsed);
                    }
                }
                if(isset($_POST['waive'])&&!in_array($capid,$_POST['waive'])) {  //if wasn't waived then don't worry
                    $waived=false;
                    if(isset($_SESSION['csv'][$capid]['waive']))
                        $date=$_SESSION['csv']['date'];
                    else
                        $date=null;
                    $standards=$buffer['member']->retrieveCPFTrequire($ident, $date);
                    if(verifyCPFT($ident, $standards, $actual)) {
                        $passed=true;
                    } else {
                        echo '<p style="color:red">'.$_SESSION['csv'][$capid][$member]->link_report()." did not pass</p>";
                    }
                } else {
                    $waived=true;
                    $passed=true;
                }
                if($passed) {        //if they actually passed, then store it
                    if(!isset($date)||$date===null)
                        $date=new DateTime;
                    $member=$_SESSION['csv'][$capid]['member'];
                    bind($log, 'issss', array($capid,$member->get_next_achiev($ident),'ALL',$date->format(PHP_TO_MYSQL_FORMAT),$waived));
                    execute($log);
                    for($i=0;$i<count($header);$i++) {  //log all the scores they got
                        $buffer=$header[$i]['TEST_CODE'];
                        $score=$actual[$buffer];
                        if(!(!isset($score)||$score===null||$score==""||$score==0)) {
                            bind($score_insert,"issd",array($capid,$member->get_next_achiev($ident),$buffer,$score));
                            execute($score_insert);                          //log all the scores
                        }
                    }
                    bind($delete, 's',array($capid));
                    execute($delete);                    //delete testing request
                }
            }
        }
    }
    close_stmt($log);
    close_stmt($delete);
    close_stmt($score_insert);
    unset($_SESSION['date'],$_SESSION['CPFT'],$_SESSION['header'],$_SESSION['achiev'],$_SESSION['csv']);
    header("refresh:0;url=/login/testing/PTtest.php");
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
        <?php
        if(!isset($_GET['capid'])&&!isset($_GET['multi'])) {
            ?>
        <p><a href="/login/testing/PTtest.php?multi=0">Enter individual CPFT test</a></p>
        <p><a href="/login/testing/PTtest.php?multi=1">View CPFT testing sign-up</a></p>
        <p><a href="/login/testing/ptCSV.php" target="_blank">Download CSV file of CPFT testing sign-up</a> <a href="/help/CPFTcsv.php" target="_blank">?</a></p>
        <p><a href="/login/testing/PTtest.php?multi=1&upload=0">Upload a CSV file of Testing_results</a></p>
        <?php
        } else if(isset($_GET['capid'])||(isset($_GET['multi'])&&$_GET['multi']==0)) {
            echo '<form method="post">';
            if(isset($_SESSION['capid']))
                $capid=$_SESSION['capid'];
            if(isset($_SESSION['tester']))
                $tester=$_SESSION['tester'];
            if(isset($_GET['capid'])) {
                if(!isset($_GET['field'])||$_GET['field']=='testee')
                    $capid=  cleanInputInt($_GET['capid'],6, 'capid');
                else 
                    $tester=  cleanInputInt ($_GET['capid'], 6, 'capid');
            }
            echo 'Enter member CAPID:<input type="text" size="5" maxlength="10" name="capid" ';
            if(isset($capid))
                echo 'value="'.$capid.'"';
            echo '/>or<input type="submit" name="search" value="search for a member"/><br>'."\n";
            if(!isset($tester))
                $tester=$_SESSION['member']->getCapid ();
            echo 'Enter Tester CAPID:<input type="text" size="5" maxlength="10" name="tester" value="'.$tester.'"/>';
            echo 'or<input type="submit" name="searchT" value="search for a member"/><br><br>';
            echo "Select achievement test is for:";
            if(isset($_REQUEST['achiev']))
                $_SESSION['achiev']=  cleanInputString ($_REQUEST['achiev'],5,'achievement',true);
            if(isset($_SESSION['achiev']))
                dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV where A.ACHIEV_CODE<>'0'AND A.MEMBER_TYPE='C' ORDER BY A.ACHIEV_NUM", "achiev", $ident, false,$_SESSION['achiev']);
            else 
                 dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV where A.ACHIEV_CODE<>'0' AND A.MEMBER_TYPE='C' ORDER BY A.ACHIEV_NUM ", "achiev", $ident);
            echo "<br><br>";
            $date=new DateTime();
            if(isset($_SESSION['date']))
                $date=$_SESSION['date'];
            enterDate(true,null,$date);
            ?>
        <br> <br><input type="checkbox" name="waiver" value="waive"/> Category II,III,IV CPFT waiver<br><br><table class="table">
            <?php
            echo "<tr class=\"table\">";
            for($i=0;$i<count($header);$i++) {
                echo "<th class=\"table\">".$header[$i]['TEST_NAME'].'</th>';
            }
            echo "</tr>\n<tr class=\"table\">";
            for($i=0;$i<count($header);$i++) {     //display input
                $code=$header[$i]['TEST_CODE'];
                echo '<td class="table"><input type="text" size="1" maxlength="10" name="'.$code.'" ';
                if(isset($_SESSION['CPFT'][$code]))
                    echo 'value="'.$_SESSION['CPFT'][$code].'"';
                echo '/></td>';
            }
            echo "</tr>";
            ?>  
            </table>
        <br><br><input type="submit" name="save" value="save"/>
        <?php
        } else if(isset($_GET['multi'],$_GET['upload'])&&$_GET['multi']==1&&$_GET['upload']==0) {
            ?>
        <form action="/login/testing/PTtest.php?multi=1&upload=1" method="post" enctype="multipart/form-data">
            <p>Upload the CSV file that has the PT test score saved on it. The data will be saved to the database. 
                NOTE: only .csv files are allowed.</p>
            Enter the date:
            <?php
            enterDate(true,null,new DateTime());
            ?>
            <br><br><label for="file">Upload CSV:</label>
            <input type="file" name="file" id="file" accept="text/csv"/><br>
            <input type="submit" value="upload"/>
            <?php
        } else if(isset($_FILES['file'])) {
            $locat = cleanUploadFile('file',5*1024,CSV_SAVE_PATH, 'text/csv');
            if($locat!==false&&($handle=fopen($locat,'r'))!==false) {              //opens the file
                $date=  parse_date_input($_POST);               //get the test date
                $parse=array();
                $columns=array();
                while(($row=  fgetcsv($handle, 1000))!=false) {   //parses one row at a time
                    if(!isset($columns[1])) {  //if the columns aren't defined
                        if($row[0]=='CAPID') {  //if the first cell is capid, then parse the columns 
                            for($i=2;$i<count($row);$i++) {       //cycle through the headers
                                $buffer=$row[$i];   
                                for($j=0;$j<count($header);$j++) {
                                    if(str_replace(' ','', $buffer)===str_replace(" ", '', $header[$j]['TEST_NAME'])) {  //compares it to the test name without space
                                        $columns[$i-2]=$header[$j]['TEST_CODE'];
                                    }
                                }
                            }
                        }
                    }else {
                        if(strpos($row[0],"--")===false) {   //if the row doesn't start with --- then parse the input
                            $capid=  cleanInputInt($row[0], 6,'CAPID from CSV');
                            $parse[$capid]['member']=new member($capid,1,$ident);
                            $buffer=$parse[$capid]['member'];
                            if($row[1]==($buffer->getName_Last().", ".$buffer->getName_first())) {  //check if the proper name in the row
                                $parse[$capid]['name']="C";
                            } else {
                                $parse[$capid]['name']='I';
                            }
                            $parse[$capid]['actual']=array();
                            for($i=0;$i<count($columns);$i++) {   //parse the test results
                                $parse[$capid]['actual'][$columns[$i]]=  cleanInputInt($row[$i+2], strlen($row[$i+2]),"Test input for:".$capid." for: ".$columns[$i]);  //parses the info
                            }
                            if(isset($row[$i+2])&&str_replace(" ","",$row[$i+2])!=="") {  //if the waiver was checked then 
                                $parse[$capid]['waive']=true;
                            } else {
                                $parse[$capid]['waive']=false;
                            }
                            $standards=$buffer->retrieveCPFTrequire($ident,$date);  //get the standards they 
                            $parse[$capid]['age']=$standards['age'];                //get the age
                            $parse[$capid]['pass']=  verifyCPFT($ident, $standards, $parse[$capid]['actual']);  //check if they passed
                            $parse[$capid]['standards']=$standards;
                            $parse['date']=$date;
                        }
                    }
                }
                unset($parse[0]);
                $_SESSION['csv']=$parse;    //save it to the session
            }
        }
        if(isset($_GET['multi'])&&$_GET['multi']==1&&((isset($_GET['upload'])&&$_GET['upload']==1)||!isset($_GET['upload']))) {
            
            echo '<form method="post">';
            $query ="SELECT A.CAPID, FLOOR(DATEDIFF(CURDATE(),A.DATE_OF_BIRTH)/365.25) AS AGE
                FROM MEMBER A, TESTING_SIGN_UP B
               Where B.CAPID=A.CAPID
                AND B.REQUIRE_TYPE='PT'
                ORDER BY A.NAME_LAST, A.NAME_FIRST";
            $results=  allResults(Query($query, $ident));
            $tester=$_SESSION['member']->getCapid ();
            for($i=0;$i<count($results);$i++) {  //loop through the results
                $capid=$results[$i]['CAPID'];
                if(!isset($_SESSION['csv'][$capid])) {  //if isn't set already make an array
                    $_SESSION['csv'][$capid]['member']=new member($capid,1,$ident);
                    $_SESSION['csv'][$capid]['age']=$results[$i]['AGE'];
                }
            }
            ?>
            <table><tr><td style="text-align: center">
            <input type="submit" name="save" value="save"/><br>
            Tester:<input type="text" name="tester" value="<?php echo $tester;?>" size="3" maxlength="6"/><br><br>
        <table class="table"><tr><th class="table">Name</th><th class="table">Gender</th><th class="table">Age</th>
            <?php
            for($i=0;$i<count($_SESSION['header']);$i++) { //crate headers
                echo '<th class="table">'.$_SESSION['header'][$i]['TEST_NAME'].'</th>';
            }
            echo "<th class=\"table\">CPFT Waiver</th><th class=\"table\">Passing</th>";
            ?>
            </tr>
            <?
            $capid=array();
            if(isset($_SESSION['csv'])){
                foreach($_SESSION['csv'] as $key=>$buffer) {    //gets the info for sorting
                    if($key!="date") {                           //if it isn't the date then go on
                        $last_name[$key]=$buffer['member']->getName_Last();
                        $first_name[$key]=$buffer['member']->getName_first();
                        $capid[$key]=$buffer['member']->getCapid();
                    }
                }
                array_multisort($last_name, SORT_STRING,$first_name, SORT_STRING, $capid, SORT_NUMERIC);  //sort it
                foreach($capid as $id) {
                    echo "<tr><td class=\"table\">";
                    $buffer=$_SESSION['csv'][$id];
                    echo $buffer['member']->link_report().'</td><td class="table">';
                    echo $buffer['member']->get_gender().'</td><td class="table">';
                    echo $buffer['age']."</td>";
                    if(isset($buffer['standards']))
                        $requirements=$buffer['standards'];
                    else
                        $requirements=$buffer['member']->retrieveCPFTrequire ($ident);
                    for($i=0;$i<count($_SESSION['header']);$i++) {
                        $code=$header[$i]['TEST_CODE'];
                        echo '<td class="table"><input type="text" size="1" maxlength="5" name="'.$code.$id.'"';
                        if (isset($buffer['actual'][$code])) {
                            if($code=='MR')
                                echo ' value="'.minutesFromDecimal ($buffer['actual'][$code]).'"';
                            else 
                                echo ' value="'.$buffer['actual'][$code].'"';
                        }
                        echo " />";
                        if(isset($requirements[$code])) {
                            if($code!='MR')
                                echo $requirements[$code]."</td>";
                            else 
                                echo minutesFromDecimal ($requirements[$code])."</td>";
                        }
                    }
                    echo '<td class="table"><input type="checkbox" name="waive[]" value="'.$id.'"';
                    if(isset($buffer['waive'])&&$buffer['waive']===true)
                        echo " checked ";
                    echo ' /></td><td class="table"><label for="pass">Pass</label><input type="radio" name="'.$id.'" value="pass" id="pass"';
                    if(isset($buffer['waive'])&& $buffer['pass']===true)
                        echo ' checked ';
                    echo ' /><br><label for="fail">Fail</label><input type="radio" name="'.$id.'" value="fail" id="fail"';
                    if(!isset($buffer['waive'])||$buffer['pass']===false)
                        echo ' checked ';
                    echo " /></td></tr>\n";
                }
            }
            ?>
            </table><br>
            <input type="submit" name="save" value="save"/>
                    </td></tr></table>
            <?php
        }
        ?>
        </form>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>