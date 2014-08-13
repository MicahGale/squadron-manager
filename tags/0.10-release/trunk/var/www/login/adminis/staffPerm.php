<?php
/**
 * Alter a staff member's permissions
 * 
 * This pages allow system admins to change the staff positions held by a staff member
 * and give them special permissions on single taks
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
 * 
 * $_SESSION
 * staffer- the staff member being altered
 * 
 * $_GET
 * capid- from the member search
 * 
 * $_POST
 * find-finds a member by the capid
 * search-go to member search
 * save- save the changes
 * pos[]- the positions they hold
 * perm[]-the special permissions
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
if(isset($_GET['capid'])) {
    $capid=  cleanInputInt($_GET['capid'],6, "Capid");
    if($capid!=$_SESSION['member']->getCapid()) {
        $staffer=new member($capid,2,$ident);
        $_SESSION['staffer']=$staffer;
    }
} else
    $capid=null;
if(isset($_POST['find'])) {
    $capid=  cleanInputInt($_POST['capid'], 6, 'capid');
    $staffer=new member($capid,2, $ident);
    $_SESSION['staffer']=$staffer;
}
if(isset($_POST['search'])) {
    header("refresh:0;url=/login/member/search.php?redirect=/login/adminis/staffPerm.php");
    exit;
}
if(isset($_POST['save'])) {  //save the permissions
    $query="DELETE FROM STAFF_POSITIONS_HELD 
        WHERE CAPID='".$_SESSION['staffer']->getCapid()."'";
    $deleter=  connect("delete");
    Query($query, $deleter);              //deletes the staff psoitions
    $_SESSION['staffer']->insert_staff_position($_POST['pos'], $ident);  //insert the staff positions
    $query="DELETE FROM SPECIAL_PERMISSION
        WHERE CAPID='".$_SESSION['staffer']->getCapid()."'";  //delete special permissions
    Query($query, $deleter);
    close($deleter);
    if(isset($_POST['perm'])) {
            $query= 'SELECT TASK_CODE FROM TASKS
                WHERE UNGRANTABLE=1';            //checks that these permissions can be be granted
            $result=  allResults(Query($query, $ident));
            for($i=0;$i<count($result);$i++) {
                $result[$i]=$result[$i]['TASK_CODE'];
            }
            $stmt=  prepare_statement($ident, "INSERT INTO SPECIAL_PERMISSION(CAPID, TASK_CODE)
                VALUES ('".$_SESSION['staffer']->getCapid()."',?)");
            for($i=0;$i<  count($_POST['perm']);$i++) {   //give the special permissions
                if(!in_array($_POST['perm'][$i],$result)) {          //makes sure they can be granted
                    bind($stmt,'s',array(cleanInputString($_POST['perm'][$i],3,"Task code", false)));
                    execute($stmt);
                }
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
        <title>Change Staff Permissions</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <h1>Alter Staff Member's Permissions</h1>
        <p class="F">Note: Staff member will need to log-out and log back in for changes to take effect.</p>
        <form method="post">
            Enter Capid:<input type="text" name="capid" value="<?php echo $capid;?>" size="3"/> <input type="submit" name="find" value="find"/>
            <br> or:<input type="submit" name="search" value="search for a member"/><br>
            <?php
            if(isset($staffer))
                echo $staffer->link_report(true);
            ?>
            <h2>Staff Positions Held:</h2>
            <input type="submit" name="save" value="save"/>
             <table>
                    <?php
                    if(isset($_SESSION['staffer'])) {   //get current permissions
                        $query="SELECT STAFF_POSITION AS CODE FROM STAFF_POSITIONS_HELD WHERE CAPID='".$_SESSION['staffer']->getCapid()."'";
                        $buffer=  allResults(Query($query, $ident));
                        $staff_pos=array();
                        for($i=0;$i<count($buffer);$i++) {              //strip out the layers of the onion
                            array_push($staff_pos, $buffer[$i]['CODE']);
                        }
                        $member_type=$_SESSION['staffer']->get_member_type();
                        $query="SELECT STAFF_NAME, STAFF_CODE FROM STAFF_POSITIONS WHERE STAFF_CODE<>'AL' AND MEMBER_TYPE='$member_type' ORDER BY STAFF_NAME";
                        $results=allResults(Query($query, $ident));
                        echo "<tr>";
                        for($i=0;$i<count($results);$i++) {
                            if(($i+1)%3==1)  //if 1 over a multiple of 3 
                                echo "<tr>";
                            echo '<td><input type="checkbox"';
                            if(isset($staff_pos)&&  in_array($results[$i]['STAFF_CODE'], $staff_pos)) 
                                    echo ' checked="checked"';
                            echo ' name="pos[]" value="'.$results[$i]['STAFF_CODE'].'"/>'.$results[$i]['STAFF_NAME']."</td>";
                           if(($i+1)%3==0) //if mutliple of 3
                                echo "</tr>\n";
                        }
                        if($i%3!=0)
                            echo "</tr>";
                    }
                    ?>
                </table>
            <h2>Specific Permissions:</h2>
            <table>
                <?php
                $query="SELECT TASK_CODE, TASK_NAME, UNGRANTABLE FROM TASKS WHERE TASK_CODE <>'HOM' ORDER BY TASK_NAME";
                $results=allResults(Query($query, $ident));
                if(isset($_SESSION['staffer'])) {
                    $query= "SELECT TASK_CODE FROM STAFF_PERMISSIONS A, STAFF_POSITIONS_HELD B
                        WHERE A.STAFF_CODE=B.STAFF_POSITION
                        AND B.CAPID='".$_SESSION['staffer']->getCapid()."'
                        OR A.STAFF_CODE='AL'";
                    $staff_pos_perm=  allResults(Query($query, $ident));
                    for($i=0;$i<count($staff_pos_perm);$i++) {
                        $staff_pos_perm[$i]=$staff_pos_perm[$i]['TASK_CODE'];
                    }
                    $query="SELECT TASK_CODE FROM SPECIAL_PERMISSION
                        WHERE CAPID='".$_SESSION['staffer']->getCapid()."'";
                    $special=  allResults(Query($query, $ident));
                    for($i=0;$i<count($special);$i++) {
                        $special[$i]=$special[$i]['TASK_CODE'];
                    }
                
                    echo "<tr>";
                    for($i=0;$i<count($results);$i++) {
                        $disabled=false;
                        if(($i+1)%3==1)  //if 1 over a multiple of 3 
                            echo "<tr>";
                        echo '<td><input type="checkbox"';
                        if(isset($staff_pos_perm)&&in_array($results[$i]['TASK_CODE'], $special))
                                echo ' checked="checked"';
                        if(isset($staff_pos)&&in_array($results[$i]['TASK_CODE'], $staff_pos_perm)) { 
                                echo ' checked="checked"';
                                $disabled=true;
                        }
                        if($results[$i]['UNGRANTABLE'])
                            $disabled=true;
                        if($disabled)
                            echo ' disabled="disabled"';
                        echo ' name="perm[]" value="'.$results[$i]['TASK_CODE'].'"/>'.$results[$i]['TASK_NAME']."</td>";
                       if(($i+1)%3==0) //if mutliple of 3
                            echo "</tr>\n";
                    }
                    if($i%3!=0)
                        echo "</tr>\n";
                }
                ?>
            </table>
            <input type="submit" name="save" value="save"/>
        </form>
        <?php
        require("squadManFooter.php");
        ?>
    </body>
</html>
