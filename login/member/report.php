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
include('projectFunctions.php');
session_secure_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Member Report</title>
         <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php
        $ident = Connect($_SESSION['member']->getCapid(), $_SESSION['password'],'localhost');
        include('squadManHeader.php');
        ?>
        <table border="0" width="880">
            <tr>
                <td align="center">
                    <?php
                    if(isset($_GET['capid'])) {          //if has capid parameter
                        $member = new member($_GET['capid'],4,$ident);  //init member
                        echo "<strong>Membership Report for:</strong> ";            //shows header
                        echo $member->getCapid()."- ".$member->getName_Last().", ".$member->getName_first()."<br><br>";
                        echo "<a href=\"".$member->getPicture()."\"><img src=\"".$member->getPicture()."\" width=\"200\" height=\"275\" style=\"border:1px solid black\"/></a><br><br>";
                        ?>
                        <table border="1" cellpadding="0">
                            <tr><th>Membership Type</th><th>Grade</th><th>Home Unit</th><th>Textbook Set</th><th>Gender</th><th>Date Joined CAP</th><th>Date Terminated</th><th>Date of Birth</th></tr>
                        <?php 
                        $member->general_info($ident);
                        ?>
                        </table>
                    <br>
                    <?php
                        $member->display_Contact($ident, false);
                        echo "<br><br>";
                        $member->promotionReport($ident,false);
                        echo "<br><br>";
                        $member->attendance_report($ident,false);
                        echo "<br><br>";
                        $member->promo_board_report($ident,false);
                        echo "<br><br>";
                        $member->discipline_report($ident, false);
                        echo "<br><br>";
                        $member->staff_position($ident, false);
                        new chain_of_command($ident,$member->getCapid());
                    }
                    ?>
                </td>
            </tr>
        </table>
    </td>
    </tr>
    <?php include('squadManFooter.php'); ?>
    </body>
</html>