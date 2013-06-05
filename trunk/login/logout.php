 <?php
 /**
  * This file logs members out, by destroying their session. It also logs the time that they signed out at 
  */
 /* Copyright 2012 Micah Gale
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
session_start();
$ident=  connect("Logger");  //connect
$time = date(SQL_INSERT_DATE_TIME);
$log_in_time=date(SQL_INSERT_DATE_TIME,$_SESSION['log_time']);
$query="UPDATE LOGIN_LOG SET LOG_OFF='$time'
        WHERE TIME_LOGIN='$log_in_time'
        AND CAPID='".$_SESSION['member']->getCapid()."'
        AND IP_ADDRESS='".$_SERVER['REMOTE_ADDR']."'";
Query($query, $ident);
close($ident);
session_destroy();
    ?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="REFRESH" content="0; url=/">
    </head>
</html>
