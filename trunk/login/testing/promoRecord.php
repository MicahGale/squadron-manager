<?php
/**
 * Displays the promotion record for one member, and allows the record to be
 * edited.
 * 
 * INPUTS
 * $_GET
 * capid- the capid of the member to be reported
 * $_POST
 * submit- saves the inputs
 * the input from the promotion record function
 * 
 * SESSION
 * microscope- the member who's report is being edited
 * header- the test types being displayed
 * 
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
 *
 * 
 */
require("projectFunctions.php");
session_secure_start();
$ident=  connect('login');
if(!isset($_GET['capid'])) {  //if the CAPID isn't set to be displayed redirect to the member search
    $url="/login/member/search.php?redirect=/login/testing/promoRecord.php";
    if(isset($_GET['lock']))
        $url.="&lock=".cleanInputString ($_GET['lock'], 1,"member type lock",false);
    header("refresh:0;url=".$url); //refresh
    exit;
}
$capid = cleanInputInt($_GET['capid'],6, 'capid');
if(isset($_SESSION['microscope'])) {                                        // if saved load it up
    $member=$_SESSION['microscope'];
    if($member->getCapid()!=$capid) {            //if not the same member switch it up
        unset($_SESSION['microscope'],$member);
    }
}
if(!isset($_SESSION['microscope'])) {                      //if not saved yet make it
    $member = new member($capid,4,$ident);
    if(isset($_GET['lock'])&&$member->get_member_type()!=$_GET['lock']) {  //if member outside the lock
        header("refresh:0;url=/login/home.php");
        exit;
    }
    $_SESSION['microscope']=$member;
} 
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Promotion Report for:
        <?php echo $member->title();
        ?></title>
    </head>
    <body>
        <?php
        $hide=true;
        require("squadManHeader.php");
        ?>
        <form method="post">
            <table><tr><td style="text-align: center">
            <strong>Color Key</strong><br>
                <p class="P">████=Completed task, and passed</p>
                <p class="I">████=Signed up to test, but hasn't been entered</p>
                <p class="F">████=Hasn't passed, and isn't signed up to test</p>
                <a href="/help/inputPercentages.php" target="_blank">How to Input Percentages</a><br>
            <input type="submit" name="submit" value="save"/>
        <?php
        if(isset($_POST['submit'])) { // if saving do so.
            $member->parseWholeEdit($ident, $_POST);         //parse and save, done!
        }
        $member->promotionReport($ident,true,true,true);
        ?>
            <input type="submit" name="submit" value="save"/>
                    </td></tr></table>
        </form>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>