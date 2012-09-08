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
<!DOCTYPE html>
<html>
    <head>
        <title>redirect for Sign-in</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php
        //TODO include error handling
        include('projectFunctions.php');
        $capid=  cleanInputInt($_POST['user'],6,"CAPID",$_SERVER['SCRIPT_NAME']);
        $password = cleanInputString($_POST['password'],256,password,$_SERVER['SCRIPT_NAME'],false);
        $attempt =  Connect( $capid, $password,'localhost');
        if($attempt!=false) {                           //if successfully signed in allow
            session_start();
            $_SESSION['resign'] = true;                //says safe to sign in
            header("refresh:0;url=".$_SESSION['redirect']);  //redirect back to where they were supposed to be
//            if(isset($_SESSION['post'])) {
//                
//            }
            exit;
        } else {
            $continue = false;
            if(!isset($_SESSION['count'])) {
                $_SESSION['count'] =0;             //start counting attempts
                $continue = true;
            } else if($_SESSION['count']<3) {      //if tolerable attempts
                $_SESSION['count']++;
                $continue = true;
            } else {                          //kill the session if too many attempts
                header("refresh:0;url=/");             //TODO log killing sessions
                $time= auditLog($_SERVER['REMOTE_ADDR'],'KS');
                auditDump($time,"user",$_SESSION['member']->getcapid());
                session_destroy();
                exit;
            } 
            if($continue) {                                        //if allowed enough attempts reattempt sign in
                header("refresh:0;url=/login/reSignIn.php");
                exit;
            }
         }
        ?>
    </head>
</html>