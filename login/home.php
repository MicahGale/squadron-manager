<?php
/**
 * Displays the home page for the login section.
 * 
 * Also displays notifications of important things such as test sign ups and security breaches
 * These notifications are based on the tasks they are able to use. This uses a class
 * which content is created from a csv outside the document root
 * 
 * No Inputs
 * @package Squadron-manager
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
$ident = Connect('login');
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
        ?>
        <h1>Notifications</h1>
        <?php
        //TODO create a sandboxed notification db user
        if(($file= fopen(NOTIF_PATH,'r'))!==false) {    //opens the csv file
            fgetcsv($file,10);            //skips the first line
            $hold=array();        //the read notifications
            while(($read=  fgetcsv($file,5000,',','"'))!==false) { //reads each row
                if(count($read)>0&&strlen($read[0])<=3) {
                    $code=$read[0];
                    $text=$read[1];
                    $query=$read[2];
                    $prior=$read[3];
                    $found=false;
                    $home=$_SESSION['home'];
                    for($i=0;$i<count($home);$i++) {  //search if the user has the proper permissions to see this
                        if($home[$i]['TASK_CODE']==$code) {  //if they have the permissions
                            $found=true;
                            break;
                        }
                    }
                    if($found) {            //if it was found create an object
                        array_push($hold,new notification($query,$text,$prior,$code));
                    }
                    
                }
            }
        }
        usort($hold,"compare_notif");  //sort the array
        $notif_ident=connect('notif');
        foreach($hold as $buffer) {
            $buffer->display($notif_ident);
        }
        fclose($file);
        /**
         * Compares notifications to sort by presidence
         * 
         * @param notification $a first one
         * @param notification $b second one
         * @return int standard usort method returns
         */
        function compare_notif(notification $a, notification $b) {
            if($a->get_presid()==$b->get_presid())
                return 0;
            return ($a->get_presid()<$b->get_presid())? -1: 1;
        }
        /**
         * The class for notifications for a user.
         * 
         * The notifications are associated with a staff task from the table TASKS
         * these are the pages the staff allowed to see.
         * If a staff member has permissions for that task he will have that notication.
         * 
         * The display string of the text has a special syntax.  Any normal text will be simply displayed
         * to display a query result enclose the column name (if renamed use the renamed column name) in **
         * Only the first row (0) will be used
         * 
         * if the column name contains DATE it will be parsed as a dateTime, and will follow the display constants for this project
         * 
         * i.e. There are *COUNT* testing requests
         * There was a request by *REQUEST_NAME* to clear the system log on *DATE*.
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
            private $task_code;  //the task code from the database this is for
            /**
             * Creates a new notification object
             * 
             * WARNING:INPUT cannot be cleaned because queries are being parsed. Do not create a notification from 
             * user input, use ONLY input from the csv file
             * 
             * @param String $query -the query to run 
             * @param String text - the text to be displayed with special syntax 
             * * The display string of the text has a special syntax.  Any normal text will be simply displayed
             * to display a query result enclose the column name (if renamed use the renamed column name) in **
             * Only the first row (0) will be used
             * 
             * if the column name contains DATE it will be parsed as a dateTime, and will follow the display constants for this project
             * 
             * i.e. There are *COUNT* testing requests
             * There was a request by *REQUEST_NAME* to clear the system log on *DATE*.
             * @param int $prior - the presidence of the notification 0-3 scale 
             * @param string $code -the task code for this notification from the database
             */
            function __construct($query, $text, $prior, $code) {
                $this->query = $query;
                $this->display_text=$text;
                $this->presidence =$prior;
                $this->task_code=$code;
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
            function check_display($ident) {
                $display=false;
                $results= allResults(Query($this->query, $ident));      //run the query
                if(count($results)>0) {           //if blank results then say it won't show
                    if(array_key_exists('COUNT', $results[0])) {
                        if($results[0]['COUNT']>0)
                            $display=true;
                    } else {
                        $display=true;
                    }
                }
                $this->results=$results;
                $this->display=$display;
                return $display;
            }
            function get_presid() {
                return $this->presidence;
            }
            /**
             * Displays the actual link if it needs to
             * 
             * @param mysqli $ident the database connection
             */
            function display(mysqli $ident) {
                if($this->results===Null) 
                    $this->check_display ($ident);
                if($this->display) {          //if meant to be displayed
                    $display=$this->display_text;
                    $query="SELECT URL FROM TASKS
                        WHERE TASK_CODE='".$this->task_code."'";
                    $results=  allResults(Query($query, $ident));
                    if(count($results)>0) {   //if we have the url proceed
                        echo '<a href="'.$results[0]['URL'].'" class="pres'.$this->presidence.'">';  //display the link first
                        $regex="#\*(\w+)\*#";
                        preg_match_all($regex,$display, $matches);
                        $matched=$matches[1];
                        $results=$this->results;
                        $replace=array();
                        $regex_ar=array();
                        for($i=0;$i<count($matched);$i++) {
                            $buffer=$matched[$i];
                            if(strstr($buffer,'DATE')!==false) {  //if column has DATE in it parse it as a date
                                $date=new DateTime($results[0][$buffer]);  //parse it
                                $replace[$i]=$date->format(PHP_DATE_FORMAT); //display
                            } else {
                                $replace[$i]=$results[0][$buffer];   //get the actual info to sub in
                            }
                            $regex_ar[$i]=$regex;
                        }
                        $display=preg_replace($regex_ar, $replace, $display,1);      //replace with inf
                        echo $display."</a><br>\n";                           //shows it and finishes it up
                    }
                }
            }
        }
        include("squadManFooter.php");
        ?>
    </body>
</html>