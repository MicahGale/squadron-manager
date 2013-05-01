<?php
/**
 * Displays the home page for the login section.
 * 
 * Also displays notifications of important things such as test sign ups and security breaches
 * These notifications are based on the tasks they are able to use. This uses a class
 * which content is created from shares outside the document root.
 */
/*  Copyright 2012 Micah Gale
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
include("projectFunctions.php");
session_secure_start();
$ident = Connect($_SESSION["member"]->getCapid(),$_SESSION["password"]);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Squadron Manager</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
         <link rel="stylesheet" type="text/css" href="/main.css">
    </head>
    <body>
        <?php
        include("squadManHeader.php");
        //TODO create a sandboxed notification db user
        /**
         * The class for notifications for a user
         * 
         * Presidence scale: 0-3
         * 0: high priority- security breech
         * 1: System administration needed
         * 2:High-priority membership administration
         * 3:low-priority membership administration
         */
        class notification {
            private $query;  //the query to run that will be displayed
            private $display_text;  //the text that will be displayed, uses special syntax to bring in db results if any.
            private $presidence;   //the priority of the notification 0-3 scale:
            private $display;      //whether or not to display this notification
            private $results;     //the query results
            /*0: high priority- security breech
             * 1: System administration needed
             * 2:High-priority membership administration
             * 3:low-priority membership administration
             */
            /**
             * Creates a new notification object
             * 
             * WARNING:INPUT cannot be cleaned because queries are being parsed. Do not create a notification from user input, use ONLY 
             * input from the included file
             * 
             * @param array $input- the array created for this notification from the parsed share query=>the query to run text=>the text to be displayed with special syntax prior=> the presidence of the notification 0-3 scale
             */
            function __construct(array $input) {
                $this->query = $input['query'];
                $this->display_text=$input['text'];
                $this->presidence =$input['prior'];
            }
            /**
             * Checks if this notification has anything to display. 
             * It also runs the query and preps for the display.
             * 
             * WARNING: Do not use the User's database connection use the sandboxed one 
             * instead.  These will be executing queries that may be tampered with.
             * 
             * @param mysqli $idnet the database connection of the SANDBOXED user
             * @return boolean-Returns true if this will be displayed, false otherwise
             */
            function check_display($idnet) {
                
            }
        }
        include("squadManFooter.php");
        ?>
    </body>
</html>