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
<?php //include("header.php");?>
<table border ="0" width="1000">
    <tr>
        <td><a href="/login/home.php">
        <img src="/patch.gif"></a></td>
        <td><table border="0" width="700">
                <tr>
                    <td style="text-align: center; vertical-align: top"><h1>Boise Composite Squadron</h1></td>
                </tr>
                <tr>
                    <td align="right" valign="bottom">
                        <a href="https://sites.google.com/site/boisesquadron/">Squadron Web-site</a><br>
                        <a href="/regulations/">CAP regulations, and forms</a><br>
                        <a href="/login/logout.php">logout</a>
                    </td>
                </tr>
            </table></td>
        </tr>
</table>
<table border="0" width="1100">
    <tr>
        <td align="left" valign="top" style="width:20%">
            <?php
            if(isset($_SESSION['home'])) {                  //if already found pages then display
                $oldSection = "";
                $result=$_SESSION['home'];
                $size=count($result);
                for($row = 0;$row<$size;$row++) {   //loop trhogh all results
                    if($result[$row]["TYPE_NAME"]!=$oldSection) {   //IF IN NEW section show it
                        echo "<br><strong>".  $result[$row]['TYPE_NAME']."</strong>\n";
                        $oldSection = $result[$row]['TYPE_NAME'];  //set to new section
                    }           //echo link
                    echo "<br><a href=\"/login/".$result[$row]["URL"]."\" ";
                    if($result[$row]['NEW_TAB']==="1")              //if should open in a new table do so
                        echo 'target="blank"';
                    echo">>".$result[$row]["TASK_NAME"]."</a>\n";
                }
            } else {
                $query ="SELECT A.TYPE_NAME, B.TASK_NAME,B.TASK_CODE, B.URL, B.NEW_TAB 
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
                if($permissions!= false) {                              //if no errors
                    $oldSection = "";
                    $size=count($result);
                    for($row = 0;$row<$size;$row++) {   //loop trhogh all results
                        if($result[$row]["TYPE_NAME"]!=$oldSection) {   //IF IN NEW section show it
                            echo "<br><strong>".  $result[$row]['TYPE_NAME']."</strong>\n";
                            $oldSection = $result[$row]['TYPE_NAME'];  //set to new section
                        }           //echo link
                        echo "<br><a href=\"/login/".$result[$row]["URL"]."\" ";
                        if($result[$row]['NEW_TAB']===true)              //if should open in a new table do so
                            echo 'target="blank"';
                        echo">>".$result[$row]["TASK_NAME"]."</a>\n";
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" style="width:80%">