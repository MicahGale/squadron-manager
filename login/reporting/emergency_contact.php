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
$ident = Connect('login');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/print.css">
        <title>Squadron Emergency Contact Information</title>
    </head>
    <body>
        <h1>Emergency Contact Information for All Members </h1>
        <h2>As Of:<?php $hi=new DateTime(); echo $hi->format(PHP_DATE_FORMAT)?></h2>
        <a href="#" class="hidden" onclick="window.print(); return false;">Click here to Print</a>
        <table>
        <?php
        $query="SELECT CAPID FROM MEMBER
            WHERE DATE_TERMINATED IS NULL
            ORDER BY NAME_LAST, NAME_FIRST";
        $results=  allResults(Query($query, $ident));
        for($i=0;$i<count($results);$i++) {
            $member=new member($results[$i]['CAPID'],4, $ident);
            echo '<tr><td class="header" colspan="3">'.$member->getName_Last().", ".$member->getName_first()."- ".$member->getCapid().'</td></tr>'."\n";
            $member->display_Emergency($ident);
        }
                       //TODO  create Unit defaults
        ?>
        </table>
        <?php include("footer.php"); ?>
    </body>
</html>
