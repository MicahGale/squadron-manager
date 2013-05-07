<?php
/**
 * Creates a report of all current members' contact information
 * 
 * @package Squadron-Manager
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
session_secure_start();
$ident = Connect($_SESSION["member"]->getCapid(),$_SESSION["password"]);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/print.css" media="print">
        <title>Squadron Emergency Contact Information</title>
    </head>
    <body>
        <h1>Emergency Contact Information for All Members</h1>
        <table>
        <?php
        $query="SELECT CAPID FROM MEMBER
            WHERE DATE_TERMINATED IS NULL
            AND HOME_UNIT=''
            ORDER BY NAME_LAST, NAME_FIRST";
        $results=  allResults(Query($query, $ident));
        for($i=0;$i<count($results);$i++) {
            $member=new member($results[$i]['CAPID'],4, $ident);
            echo '<tr><td class="header">'.$member->getName_Last().", ".$member->getName_first()."- ".$member->getCapid().'</td></tr>';
            $member->display_Emergency();
        }
        
                //TODO  create Unit defaults
        ?>
        </table>
    </body>
</html>
