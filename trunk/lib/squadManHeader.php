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
?>
<script type="text/javascript" src="/resize.js"></script>
<table id="head" style="width:1100px">    
    <tr>
        <td style="width:210px"><a href="/login/home.php">
        <img src="/patch.gif"></a></td>
        <td>
            <table style="width:100%">
                <tr><td style="text-align:center; vertical-align: top"><h1>Boise Composite Squadron</h1></td></tr>
                <tr><td style="text-align:right; vertical-align: bottom">
                    <a href="http://boisecap.org" target="_blank">Squadron Web-site</a><br>
                    <a href="http://boisecap.org/calendar/" target="_blank">Squadron Calender</a><br>
                    <a href="http://www.capmembers.com/forms_publications__regulations/" target="_blank">CAP regulations, and forms</a><br>
                    <a href="/login/logout.php">logout</a>
                </td></tr>
            </table>
        </td>
        </tr>
</table>
<table id="main" style="width:1100px">
    <tr>
        <td style="text-align: left; vertical-align: top; width:20%">
            <ul class="tasks">
            <?php
            if(isset($_SESSION['home'])) {                  //if already found pages then display
                $result=$_SESSION['home'];
            } else {
                $query ="SELECT A.TYPE_NAME, B.TASK_NAME,B.TASK_CODE, B.URL, B.NEW_TAB, B.GET_FIELD
                    FROM SQUADRON_INFO.TASK_TYPE A JOIN
                    SQUADRON_INFO.TASKS B ON
                    A.TYPE_CODE=B.TYPE_CODE
                    WHERE (B.TASK_CODE IN (
                        SELECT C.TASK_CODE FROM SQUADRON_INFO.STAFF_PERMISSIONS C
                        LEFT JOIN SQUADRON_INFO.STAFF_POSITIONS_HELD D ON D.STAFF_POSITION=C.STAFF_CODE
                        WHERE  C.STAFF_CODE = 'AL'
                        OR D.CAPID='".$_SESSION["member"]->getCapid()."') OR
                    B.TASK_CODE IN (
                        SELECT TASK_CODE FROM SQUADRON_INFO.SPECIAL_PERMISSION
                        WHERE CAPID='".$_SESSION["member"]->getCapid()."'))
                    AND B.TASK_CODE<>'HOM'
                    ORDER BY A.TYPE_NAME, B.TASK_NAME";
                $permissions =  Query($query, $ident);
                $_SESSION['home']=  allResults($permissions);
                $result = $_SESSION['home'];
            }
            if((isset($permissions)&&$permissions!= false)||!isset($permissions)) {                              //if no errors
                $oldSection = "";
                $size=count($result);
                for($row = 0;$row<$size;$row++) {   //loop trhogh all results
                    if($result[$row]["TYPE_NAME"]!=$oldSection) {   //IF IN NEW section show it
                        echo "<strong>".  $result[$row]['TYPE_NAME']."</strong>\n";
                        $oldSection = $result[$row]['TYPE_NAME'];  //set to new section
                    }           //echo link
                    echo '<li class="tasks"><a href="/login/'.$result[$row]["URL"];
                    if(isset($result[$row]['GET_FIELD']))
                        echo "?lock=".$result[$row]['GET_FIELD'];  //show the get field
                    echo '"';
                    if($result[$row]['NEW_TAB'])              //if should open in a new table do so
                        echo ' target="blank"';
                    echo'>'.$result[$row]["TASK_NAME"]."</a></li>\n";
                }
            }
            ?>
            </ul>
        </td>
        <td style="text-align: left; vertical-align: top">