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
$ident=  connect($_SESSION['member']->getCapid(), $_SESSION['password']);
if(!isset($_GET['capid'])) {  //if the CAPID isn't set to be displayed redirect to the member search
    header("refresh:0;url=/login/member/search.php?redirect=/login/testing/promoRecord.php"); //refresh
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title></title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        $capid = cleanInputInt($_GET['capid'],6, 'capid');
        $member = new member($capid,4,$ident);
        ?>
        <form method="post">
            <table><tr><td style="text-align: center">
            <strong>Color Key</strong><br>
                <p class="P">████=Completed task, and passed</p>
                <p class="I">████=Signed up to test, but hasn't been entered</p>
                <p class="F">████=Hasn't passed, and isn't signed up to test</p>
                <a href="/help/inputPercentages.php" target="_blank">How to Input Percentages</a>
            <input type="submit" name="submit" value="save"/>
        <?php
        $member->promotionReport($ident,true,true,true);
        //todo parse promotion input!
        ?>
            <input type="submit" name="submit" value="save"/>
                    </td></tr></table>
        </form>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>