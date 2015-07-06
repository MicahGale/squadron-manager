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
include("projectFunctions.php");
$ident=Connect('Sign-in');
session_start();
$quickie=false;
if(isset($_GET['CAPID'])) {
    $member = new member($_GET['CAPID'],2,$ident);
} 
if(isset($member)&&$member->exists()&&!$member->check_terminated($ident)){                   //if a member exists displays member info
    $_SESSION["member"]=$member;
}
if(isset($_GET['quickie'])) { //if doing a quick sign-in get in in and get out
    if($member->exists()&&!$member->check_terminated($ident)) { //some good checks
        $member->sign_in($ident, "");  //test if properly signed in
        $quickie=true;
        header("refresh:0; url=/index.php?quick=on&capid=".$member->getCapid());
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Sign-in</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
    </head>
    <body>
<?php
if($quickie) {
    ?>
    if you haven't been redirected please <a href="/index.php?quick=on&capid=<?php echo $_GET['CAPID']; ?>">click here</a>
    <?php
    exit;
}
include("header.php");
if(isset($member)&&$member->badInput) {
    echo " please search again
        <form action=\"../signIn\" method=\"get\">
                        <input type =\"text\" name=\"CAPID\" size=\"5\"/>
                        <input type=\"submit\" value =\"Search\"/>
                    </form>";
    exit;
}    
    if(isset($member)&&$member->exists()&&!$member->check_terminated($ident)){                   //if a member exists displays member info
        echo"<div styl=\"font-weight:bold\">We found this member from the entered CAPID:</div><br><br>\n";
        echo "<table class=\"table\"><tr class=\"table\"><th class=\"table\">CAPID</th><th class=\"table\">Last Name</th><th class=\"table\">First Name</th><th class=\"table\">Grade</th></tr>\n";
        echo "<tr class=\"table\"><td class=\"table\">".$member->getCapid()."</td><td class=\"table\">".$member->getName_Last()."</td><td class=\"table\">".$member->getName_first()."</td><td>".$member->getGrade($ident,'signin/index.php')."</td></tr></table><br>";  
        $member->testSign_up($ident, "finishSignin.php","signin/index.php");
        $_SESSION["member"]=$member;
    } else if(isset($member)&&$member->check_terminated($ident)) {
        echo '<span class="F">Your membership is terminated, and you cannot log in.</span>';
    }
    else {
        echo "<strong>We didn't find anyone with that CAPID</strong><br>";    //if no member ask to search or make new member
        echo "Search again<br>";
        echo"<form action=\"../signIn\" method=\"get\">
                        <input type =\"text\" name=\"CAPID\" size=\"5\"/>
                        <input type=\"submit\" value =\"Sign-In\"/>
                    </form><br><strong>Or add a new Member</strong>";
        newMember($ident,'newMember.php',$_GET['CAPID']);
    }
    
?>
        
        <br><br><strong>Not You?</strong><br>
Search again.<br>
<form method ="get">
    <input type="text" size="3" name="CAPID"/>
    <input type="submit" value="search again"/>
</form>
<?php include("footer.php");?>
    </body>
</html>