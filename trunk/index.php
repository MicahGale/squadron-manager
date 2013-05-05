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
        <title>Boise Composite Squadron: RMR-ID-073</title>
        <link rel="shortcut icon" href="/patch.ico">
    </head>
    <body>
        <?php include("header.php"); 
        include("projectFunctions.php");?>
        <table border="0" width ="900">  <!table for formatting>
            <tr>                       <!left column>
                <td style="text-align: left;width: 780px">
                    <p><strong>Today is:</strong> 
                        <?php 
                        echo date(PHP_DATE_FORMAT)."</p>\n"; 
                        $connection= Connect('Viewer',"2438iuoewjkld--[p0xfdkuu,zcxmeeeeem4e8m, cxpondsvlkc m,ryfsdhPOJLKNUHKJN<",'localhost');
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
                <br><strong>Or if you are new to CAP please Sign-in Below</strong><br>
                <form action="/visitor/visitor.php" method="post">
                    First Name:<input type="text" name="Fname" size="5"/><br>
                    Last Name: <input type="text" name="Lname" size="5"/><br>
                    <input type="submit" value="Sign-in"/>
                </form>
                </td>
                <td style="text-align: right;width: 120px">                     <!right column for holding login info>
                <form action="/login/" method="post">
                    <strong>Staff Login:</strong> <br>
                    <font size="2">CAPID<br>
                    <input type="text" name="CAPID" size="5"/> <br>
                    Password<br> </font>
                    <input type="password" name="password" size="5"/> <br>
                    <input type="submit" value="login"/>
                </form>
                
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <br><br>
                    <iframe src="https://www.google.com/calendar/embed?height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=rmdltfq8r1lvh7brb7s1ib23k8%40group.calendar.google.com&amp;color=%23182C57&amp;src=ufhvf2qfb8ccsum50kvig52m9k%40group.calendar.google.com&amp;color=%23182C57&amp;src=idahowing%40gmail.com&amp;color=%232F6309&amp;src=en.usa%23holiday%40group.v.calendar.google.com&amp;color=%23691426&amp;ctz=America%2FDenver" style=" border-width:0 " width="800" height="600" frameborder="0" scrolling="no"></iframe>
                </td>
            </tr>
        </table> <!google calendar>
        <?php include("footer.php");?>
    </body>
</html>