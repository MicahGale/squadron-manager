<?php
/**
 * Creates a downloadable csv of the pt testing sign-up
 * @package Squadron-manager
 * @copyright (c) 2013, Micah Gale
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
require('projectFunctions.php');
session_secure_start();
$ident=  connect($_SESSION['member']->getCapid(), $_SESSION['password']);
header("Content-type: text/csv");
$now =new DateTime();
header("Content-Disposition: attachment; filename=cpft_test_".$now->format(EVENT_CODE_DATE).".csv");
header("Pragma: no-cache");
header("Expires: 0");
$query ="SELECT TEST_CODE, TEST_NAME FROM CPFT_TEST_TYPES ORDER BY TEST_NAME";
$test = allResults(Query($query, $ident));   //gets the pt tests
$fp = fopen('php://output','wt');             //opens the file output stream
$header[0]="CAPID";
$header[1]='Name';
for($i=0;$i<count($test);$i++) {           //changes the array
    $header[$i+2]=$test[$i]['TEST_NAME'];
}
array_push($header,"Waiver");
fputcsv($fp, $header);   //creates the header of the csv
$query="SELECT A.CAPID FROM TESTING_SIGN_UP A, MEMBER B
    WHERE REQUIRE_TYPE='PT' AND A.CAPID=B.CAPID 
    ORDER BY B.NAME_LAST, B.NAME_FIRST";
$testers=  allResults(Query($query, $ident));      //find all the testing requests
for($i=0;$i<count($testers);$i++) {               //displayst the test entrances for each tester
    $buffer= new member($testers[$i]['CAPID'],1,$ident);
    $display[0]=$buffer->getCapid();
    $display[1]=$buffer->getName_Last().", ".$buffer->getName_first();
    for($i=0;$i<=count($header);$i++) {
        $display[$i+2]="";
    }
    fputcsv($fp, $display);
    $display=array();
    $require=$buffer->retrieveCPFTrequire($ident);
    $display[0]="------";
    $display[1]="requirements";
    for($i=0;$i<count($test);$i++) {
        if(isset($require[$test[$i]['TEST_CODE']])) { //if requirement is there then display
            if($test[$i]['TEST_CODE']=="MR")  //if the mile run convert it out of decimal form
                $require['MR']=  minutesFromDecimal ($require['MR']);
            $display[$i+2]="≡>".$require[$test[$i]['TEST_CODE']]."<≡";
        } else {
            $display[$i+2]="-----";
        }
    }
    array_push($display,"--");
    fputcsv($fp, $display);
}
fclose($fp);
?>
