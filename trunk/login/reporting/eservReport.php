<?php
/**
 * This creates a report for things to be inserted into eservices
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * $_post
 * remove- mark as on eservices
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
 */
require("projectFunctions.php");
$ident = connect('login');
session_secure_start();
if(isset($_POST['remove'])) {
    $query='UPDATE REQUIREMENTS_PASSED SET ON_ESERVICES=TRUE';
    Query($query, $ident);
    $query='UPDATE PROMOTION_RECORD SET ON_ESERVICES=TRUE';
    Query($query, $ident);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/print.css">
        <title>Eservices Report</title>
    </head>
    <body>
        <?php
        $query="SELECT A.CAPID, A.ACHIEV_CODE, A.REQUIREMENT_TYPE, A.PASSED_DATE, A.PERCENTAGE, A.WAIVER, C.NUMBER_QUESTIONS
            FROM REQUIREMENTS_PASSED A, MEMBER B, PROMOTION_REQUIREMENT C WHERE A.ON_ESERVICES=FALSE
            AND A.CAPID=B.CAPID AND C.ACHIEV_CODE=A.ACHIEV_CODE AND A.TEXT_SET=C.TEXT_SET
            AND A.REQUIREMENT_TYPE=C.REQUIREMENT_TYPE AND A.REQUIREMENT_TYPE not IN ('SA','PB','CO','GS','PB')
            ORDER BY CAPID ASC";   //TODO check on what they need to enter
        $require_passed=  allResults(Query($query, $ident));        //get all the requirements not on eservices
        $query="SELECT CAPID, ACHIEVEMENT, DATE_PROMOTED FROM PROMOTION_RECORD
            WHERE ON_ESERVICES=FALSE ORDER BY CAPID ASC";                 //GET PROMOTIONS
        $promotion=  allResults(Query($query, $ident));   //get promotion info
        $results=allResults(Query("SELECT ACHIEV_CODE,ACHIEV_NAME FROM ACHIEVEMENT",$ident));
        for($i=0;$i<count($results);$i++) {   //resets up the array $achiev[code]=name
            $achiev[$results[$i]['ACHIEV_CODE']]=$results[$i]['ACHIEV_NAME'];
        }
        $results=allResults(Query("SELECT TYPE_CODE, TYPE_NAME FROM REQUIREMENT_TYPE",$ident));
        for($i=0;$i<count($results);$i++) {  //reorganizes it $require[code]=name
            $require[$results[$i]['TYPE_CODE']]=$results[$i]['TYPE_NAME'];
        }
        $pt_test=  prepare_statement($ident,"SELECT SCORE, TEST_NAME FROM CPFT_ENTRANCE, CPFT_TEST_TYPES
            WHERE CAPID=? AND ACHIEV_CODE=? AND TEST_TYPE=TEST_CODE ORDER BY TEST_NAME ASC");
        ?>
        <h1>Information to be Entered into Eservices</h1>
        <h2>As of:<?php $date= new DateTime();
                    echo $date->format(PHP_DATE_FORMAT)?></h2>
        <form method="post">
            <input type="submit" name="remove" value="Mark tests as entered on Eservices"/>
        </form>
        <table>
            <?php
            $current=1;
            for($i=0;$i<count($require_passed);$i++) {  //display all the things
                if($require_passed[$i]['CAPID']!=$current) {  //if a different person display the new member
                    if(isset($member)) {                  //if already a member there then check for promotions
                        for($j=0;$j<count($promotion);$j++) {  //display promotions
                            if($promotion[$j]['CAPID']==$current) {  //if for the current member
                                echo '<tr><td>'.$achiev[$promotion[$j]['ACHIEVEMENT']];
                                echo ' - Promotion</td>';
                                $date=new DateTime($promotion[$j]['DATE_PROMOTED']);
                                echo '<td>'.$date->format(PHP_DATE_FORMAT)."</td><td></td></tr>\n";
                            }
                        }
                    }
                    $current=$require_passed[$i]['CAPID'];
                    $member=new member($current,1,$ident);
                    echo '<tr class="header"><td class="header" colspan="3">'.$member->title()."</td></tr>\n";
                }
                $buffer=$require_passed[$i];
                echo '<tr><td>'.$achiev[$buffer['ACHIEV_CODE']]."-".$require[$buffer['REQUIREMENT_TYPE']].'</td>';
                $date=new DateTime($buffer['PASSED_DATE']);
                echo '<td>'.$date->format(PHP_DATE_FORMAT).'</td>';
                if($buffer['REQUIREMENT_TYPE']=="PT") {
                    echo '<td style="width:425px">';
                    bind($pt_test,'is',array($current,$buffer['ACHIEV_CODE']));
                    $results=  allResults(execute($pt_test));
                    for($j=0;$j<count($results);$j++) {  //display tests
                        echo $results[$j]['TEST_NAME'].":".$results[$j]['SCORE']." ";
                    }
                    echo "</td></tr>\n";
                } else {
                    if($buffer['NUMBER_QUESTIONS']==null) {
                        $percent = ($buffer['PERCENTAGE']*100)."%";
                    } else {
                        $percent = round($buffer['NUMBER_QUESTIONS']*$buffer['PERCENTAGE'])."/".$buffer['NUMBER_QUESTIONS'];
                    }
                    echo "<td>$percent</td></tr>\n";
                }
            }
            close_stmt($pt_test);
            ?>
        </table>
        <?php
        require("footer.php");
        ?>
    </body>
</html>
