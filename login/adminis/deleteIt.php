<?php
/**
 *Request the system logs to be cleared finalizes
 * 
 * This is the page that the requests are handed off to, and the actual 
 * DB queries.
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * $_Session
 * audit- aprove of clearing audit logs, pending proper sign-in
 * login_clear- '' except login log
 * authenticated- whether or not the user is authenticated
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
$ident = connect('login');
session_secure_start();
if(!$_SESSION['authenticated']) {                 //if not authenticated leave immediately
    header("refresh:0;url/login/home.php");
    exit;
} else if($_SESSION['audit']||$_SESSION['login_clear']) {      //otherwise let's go for it
    $query="SELECT REQUESTER FROM DELETE_REQUESTS
        WHERE CLEAR_AUDIT=TRUE OR CLEAR_LOGIN=TRUE";  //see if there is a standing request
    $result=  allResults(Query($query, $ident));
    $success=true;
    if(count($result)==0) {            //if there's no request create a request
        $stmt=  prepare_statement($ident, "INSERT INTO DELETE_REQUESTS(REQUESTER, REQUEST_DATE, CLEAR_AUDIT, CLEAR_LOGIN)
            VALUES('".$_SESSION['member']->getCapid()."',CURRENT_TIMESTAMP(),?,?)");
        if($_SESSION['audit'])
            $audit="true";
        else
            $audit="false";
        if($_SESSION['login_clear'])
            $login="true";
        else
            $login="false";
        bind($stmt,"ss",array($audit, $login));
        if(!execute($stmt))
            $success=false;
        close_stmt($stmt);
    } else  {  //if not first then actually clear the log
        if($_SESSION['audit']) {
            $query1="DELETE FROM AUDIT_DUMP"; //delete the audit dump
            $query2="DELETE FROM AUDIT_LOG";  //clear the log
            if(!Query($query1, $ident))
                    $success=false;
            if(Query($query2, $ident))    //actually clear them
                    $success=false;
            $query="DELETE FROM DELETE_REQUESTS
                WHERE CLEAR_AUDIT=TRUE";
            if(Query($query, $ident))  //delete the request
                    $success=false;
        } 
        if($_SESSION['login_clear']) {
            $query='DELETE FROM LOGIN_LOG';
            if(!Query($query, $ident))         //delete the records
                    $success=false;
            $query= 'DELETE FROM LOGIN_LOG WHERE 
                CLEAR_LOGIN=TRUE';
            if(!Query($query, $ident))         //delete the request
                    $success=false;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <meta http-equiv="REFRESH" content="5; url=/login/home.php">
        <title>Finish the Delete</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        if($success) {
            ?>
        <h2>This deletion was successful. You will be redirected in 5 seconds.</h2>
        <?php
        }
        require("squadManFooter.php");
        ?>
    </body>
</html>
