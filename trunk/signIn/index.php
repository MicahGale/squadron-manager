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
        <title>Sign-in</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
<?php
include("header.php");
include("projectFunctions.php");
$ident=Connect('Sign-in','ab332kj2klnnfwdndsfopi320932i45n425l;kfoiewr','localhost');
$member = new member($_GET["CAPID"],2,$ident);
if($member->badInput) {
    echo " please search again
        <form action=\"../signIn\" method=\"get\">
                        <input type =\"text\" name=\"CAPID\" size=\"5\"/>
                        <input type=\"submit\" value =\"Search\"/>
                    </form>";
    break;
}    
    if($member->exists()){                   //if a member exists displays member info
        echo"<strong>We found this person from the entered CAPID:</strong><br><br>\n";
        echo "<table border =\"1\" cellspacing=\"1\"><tr><th>CAPID</th><th>Last Name</th><th>First Name</th><th>Grade</th></tr>\n";
        echo "<tr><td>".$member->getCapid()."</td><td>".$member->getName_Last()."</td><td>".$member->getName_first()."</td><td>".$member->getGrade($ident,'signin/index.php')."</td></tr></table><br>";  
        $member->testSign_up($ident, "finishSignin.php","signin/index.php");
        session_start();
        $_SESSION["member"]=$member;
    } else {
        echo "<strong>We didn't find anyone with that CAPID</strong><br>";    //if no member ask to search or make new member
        echo "Search again<br>";
        echo"<form action=\"../signIn\" method=\"get\">
                        <input type =\"text\" name=\"CAPID\" size=\"5\"/>
                        <input type=\"submit\" value =\"Sign-In\"/>
                    </form><br><strong>Or add a new Member</strong>";
        newMember($ident,'newMember.php',$member->getCapid());
    }
    
?>
        
        <br><br><strong>Not You?</strong><br>
Search again.<br>
<form action="../signin" method ="get">
    <input type="text" size="3" name="CAPID"/>
    <input type="submit" value="search again"/>
</form>
<?php include("footer.php");?>
    </body>
</html>