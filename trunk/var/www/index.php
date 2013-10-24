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
session_start();              //if already loged in go to home page
if(isset($_SESSION['home'])) {
    header("refresh:0;url=/login/home.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Squadron Manager</title>
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css"/>
        <script type="text/javascript" src="/java_script/CAPS_LOCK.js"></script>
    </head>
    <body>
        <?php include("header.php"); 
        include("projectFunctions.php");?>
        <table id="main" style="width: 1100px">  <!-- table for formatting -->
            <tr>                       <!left column>
                <td style="text-align: left;width: 780px">
                    <p><strong>Today is:</strong> 
                        <?php 
                        echo date(PHP_DATE_FORMAT)."</p>\n"; 
                        $connection= Connect('Viewer');
                        $rowLocal=0;
                        $max = 10;
                        if($connection!=false) {
                            $result = Query("SELECT A.SUBEVENT_NAME 
                                FROM EVENT C JOIN SUBEVENT B ON B.PARENT_EVENT_CODE=C.EVENT_CODE
                                JOIN SUBEVENT_TYPE A ON A.SUBEVENT_TYPE=B.SUBEVENT_CODE
                                WHERE C.IS_CURRENT=TRUE",$connection);
                            $max = numRows($result);
                            if($max>=10) {
                                $max=10;
                            }
                            if($max>0) {
                                echo "<p><strong> We have these things planned for today: </strong>";
                            }
                            while($rowLocal<$max) {
                                echo Result($result, $rowLocal,"SUBEVENT_NAME");
                                if($max-$rowLocal>2) {
                                    echo ", ";
                                } else if($max-$rowLocal==2){
                                    echo ", and ";
                                } else {
                                    echo ".\n";
                                }
                                $rowLocal++;
                            }
                            echo "</p>\n";
                        }
                        ?>
                    <br>
                    <br>
                    <strong>Current Members Sign-In Below with your CAPID</strong><br>
                    <form action="signIn" method="get">
                        <input type ="text" name="CAPID" size="5"/>
                        <input type="submit" value ="Sign-In"/>
                    </form>
                    <br>
                </td>
                <td style="text-align: right;width: 120px">                     <!-- right column for holding login info -->
                <form action="/login/" method="post">
                    <strong>Staff Login:</strong> <br>
                    <font size="2">CAPID<br>
                    <input type="text" name="CAPID" size="5"/> <br>
                    Password<br> </font>
                    <span id="warn" class="F"></span><input type="password" name="password" size="5" onkeypress="check_caps(event)"/> <br>
                    <input type="submit" value="login"/>
                </form>
                
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <br><br>
                    <?php
                    if($fields!==false&&isset($fields['site_cal']))
                        echo $fields;
                    ?>
                </td>
            </tr>
        </table>
        <?php include("footer.php");?>
    </body>
</html>