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
require("projectFunctions.php");
session_secure_start();
$ident=  connect($_SESSION['member']->getCapid(), $_SESSION['password']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <title>Promotion Sign-Up</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <table border="0" width="800" >
            <tr><td align="center">
                    <strong>View Promotion Sign-up</strong>
                        <form method="post"><strong>Select Member Type:</strong>
                            <?php
                            if(!isset($_SESSION['memberType'])) 
                               $_SESSION['memberType']='C';        //if hasn't chosen member type, then assume cadets
                            if(isset($_POST['filter'])) {  //if set a filter then get it in
                                $_SESSION['memberType']=cleanInputString($_POST['memberType'],1,"member filter",false);
                            }
                            dropDownMenu("SELECT MEMBER_TYPE_CODE, MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES WHERE MEMBER_TYPE_CODE<>'A'",'memberType', $ident,false,$_SESSION['memberType']);
                            ?>
                            <input type="submit" name="filter" value="Filter"/> <br><br>
                            <strong>Color Key</strong><br>
                            <font color="green">████</font>=Completed task, and passed<br>
                            <font color="Orange">████</font>=Signed up to test, but hasn't been entered<br>
                            <font color="red">████</font>=Hasn't passed, and isn't signed up to test<br><br>
                        </form>
                        <form method="post">
                            <input type="submit" name="save" value="Save"/><br>
                            <?php
                            promotionAprove($ident, $_SESSION['memberType']);
                            ?>
                            <input type="submit" name="save" value="Save"/>
                        </form>  
                </td></tr>
        </table>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>
