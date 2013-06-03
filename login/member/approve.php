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
session_secure_start();
?>
<!BODY html>
<html>
    <head>
        <title>Approve Newly added members</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../../patch.ico">
    </head>
    <body>
        <?php
        $ident = Connect('login');
        include("squadManHeader.php");
        $result = Query("SELECT CAPID FROM SQUADRON_INFO.MEMBER WHERE APPROVED=FALSE", $ident);
        $size=  numRows($result);
        $members = array();
        settype($members,"array");
        $_SESSION['members'] = null;
        settype($_SESSION['members'],'array');
        for($i=0;$i<$size;$i++) {                                 //get array of people waiting approval
            $capid = Result($result,$i,"CAPID");
            array_push($members,new member($capid,4, $ident));
            $_SESSION["members"][$members[$i]->getcapid()]=$members[$i];
        }
        $_SESSION["members"]=$members;                               //load to session
        $size = count($members);
        ?>
        <p>Listed Below are all added members that weren't entered by a staff member, and has not been approved.
        You may edit the fields below for each member. To save these changes you must approve the member.  To approve members
        either check all the added members you approve and click "approve selected", or to approve all added members click approve all.</p>
        <form action="finalApprove.php" method="post">
            <table border="1" cellspacing="1" width="800">
                <tr><th>Approve</th><th>CAPID</th><th>Last Name</th><th>First Name</th><th>Gender</th><th>Date of Birth</th><th>CAP Grade</th><th>Member Type</th><th>Textbook Set</th><th>Home Unit</th><th>Date Joined CAP</th></tr>
        <?php
        for($i=0;$i<$size;$i++) {                                   //displays the edit field and approval
            $members[$i]->approveFields($ident);
        }
        ?>
            </table>
            <input type="submit" name="submit" value="approve selected"/><input type="submit" name="submit" value="approve all"/>
        </form>
    </body>
</html>