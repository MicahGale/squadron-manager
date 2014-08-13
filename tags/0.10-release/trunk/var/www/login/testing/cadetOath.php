<?php
/**
 * Allow flight staff to enter cadet oath and grooming standards.
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * $_get
 * capid- the capid from the search
 * $_post
 * capid- the capid of the member 
 * *date*- the date input
 * search- search for the member
 * passed[]- the checkbox for the items to be entered CO- cadet oath GS- grooming standards
 * save- save the input
 * find- find the member
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
 * if not it is available at <http://www.gnu.org/licenses/gpl.txt>
 */
require("projectFunctions.php");
$ident = connect('login');
session_secure_start();
if(isset($_POST['search'])) {
    header('refresh:0; url=/login/member/search.php?redirect=/login/testing/cadetOath.php');
    exit;
}
$oath=false;
$groom=false;
if(isset($_REQUEST['capid'])) {
    $capid= cleanInputInt($_REQUEST['capid'],6, "Capid");
    $member= new member($capid,1,$ident);
    $next_achiev=$member->get_next_achiev($ident);  //get the next achievement
    $text= $member->get_text();
    $query="SELECT REQUIREMENT_TYPE FROM REQUIREMENTS_PASSED
        WHERE CAPID='$capid' AND ACHIEV_CODE='$next_achiev'
            AND REQUIREMENT_TYPE IN ('CO','GS')";
    $results= allResults(Query($query, $ident));
    for($i=0;$i<count($results);$i++) {
        $type=$results[$i]['REQUIREMENT_TYPE'];
        if($type=='CO')
            $oath=true;
        if($type=='GS')
            $groom=true;
    }
} else {
    $capid=null;
    $member=null;
}
if(isset($_POST['save'])) {
     $date = parse_date_input($_POST);       //get the inputs
    $stmt=  prepare_statement($ident, "INSERT INTO REQUIREMENTS_PASSED(CAPID,ACHIEV_CODE, TEXT_SET,PASSED_DATE, TESTER, REQUIREMENT_TYPE)
        VALUES('$capid','$next_achiev','$text','".$date->format(PHP_TO_MYSQL_FORMAT)."','".$_SESSION['member']->getCapid()."',?)");
    for($i=0;$i<count($_POST['passed']);$i++) {
        bind($stmt, 's',array(cleanInputString($_POST['passed'][$i], 5,"Requirement type",false)));
        execute($stmt);    //insert requirements
    }
    close_stmt($stmt);
    header("refresh:0; url=/login/home.php"); 
    exit;
}
$date= new DateTime();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Enter Cadet Oath</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Enter Cadet Oaths and Grooming Standards.</h1>
        <form method="post">
            <p>Enter Capid: <input  type="text" size="4" name="capid" value="<?php echo $capid;?>"/><input type="submit" name="find" value="find a member"/>
                <br>or<input type="submit" name="search" value="Search for a member"/><br>
            <?php if(isset($member)) echo $member->link_report(true); ?></p>
            <p>Approver: <input type="text" size="4" disabled="disabled" value="<?php echo $_SESSION['member']->getCapid();?>"/></p>
            <p>Date Approved: <?php enterDate(true,null, $date)?></p>
            <p>Passed requirements:<br>
                <input type="checkbox" name="passed[]" value="CO" <?php if($oath) echo 'checked="checked" disabled="disabled"'; ?>/>:Cadet Oath<br>
                <input type="checkbox" name="passed[]" value="GS" <?php if($groom) echo 'checked="checked" disabled="disabled"'; ?>/>:Grooming Standards.</p>
            <p><input type="submit" name="save" value="save"/></p>
        </form>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>
