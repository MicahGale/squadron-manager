<?php
require("projectFunctions.php");
session_secure_start();
$ident=  connect($_SESSION['member']->getCapid(), $_SESSION['password']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <title>View testing Sign-up and enter Scores</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        ?>
        <table border="0" width="800">
            <tr>
                <td align="center">
                    <strong>View Testing and Promotion Board Sign-up</strong>
                    <form method="post">
                        Filter by test type:
                        <?php
                        dropDownMenu("SELECT TYPE_CODE, TYPE_NAME FROM REQUIREMENT_TYPE
                            WHERE TYPE_CODE NOT IN('AC','CD','ME','SA','SD') ORDER BY TYPE_NAME","filterTypes", $ident,false,null,true);
                        ?>
                        <input type="submit" name="filter" value="filter"/><br><br>
                        <input type="submit" name="save" value="save"/>
                    <table border="1" cellpadding="0">
                        <tr>
                            <th>Member</th><th>Test type</th><th>Test</th><th>Passed</th><th>Percentage</th><th>On Eservices</th><th>Remove</th>
                        </tr>
                        <?php
                        if(isset($_POST['filter'])) {
                            if($_POST['filterTypes']!="null") {
                                $_SESSION['filter']=  cleanInputString($_POST['filterTypes'],2,"test filter",false);
                            } else {
                                unset($_SESSION['filter']);
                            }
                        }
                        $query ='SELECT A.CAPID, C.TYPE_NAME,CONCAT(A.ACHIEV_CODE," - ",B.NAME) AS TEST_NAME
                            FROM TESTING_SIGN_UP A, PROMOTION_REQUIREMENT B, REQUIREMENT_TYPE C
                            WHERE A.ACHIEV_CODE=B.ACHIEV_CODE
                            AND A.REQUIRE_TYPE=B.REQUIREMENT_TYPE
                            AND C.TYPE_CODE=A.REQUIRE_TYPE
                            AND A.REQUIRE_TYPE NOT IN(\'AC\',\'CD\',\'ME\',\'SA\',\'SD\')';
                        if(isset($_SESSION['filter'])) {
                            $query.=" AND A.REQUIRE_TYPE='".$_SESSION['filter']."'";  //if there's a filter then apply it
                        }
                        $results = allResults(Query($query, $ident));
                        $size=count($results);
                        for($i=0;$i<$size;$i++) {            //display testing requests
                            echo "<tr><td>";
                            $capid=$results[$i]['CAPID'];
                            $member=new member($capid,1, $ident);
                            echo $member->link_report();
                            echo "</td><td>".$results[$i]['TYPE_NAME']."</td><td>".$results[$i]['TEST_NAME']."</td>";
                            echo '<td><input type="checkbox" name="passed[]" value="'.$capid.'"/></td>';
                            echo '<td><input type="text" size="1" maxlength="3" name="percentage'.$capid.'"/></td>';
                            echo '<td><input type="checkbox" name="eservices[]" value="'.$capid.'"/></td>';
                            echo '<td><input type="checkbox" name="remove[]" value="'.$capid."\"/></td></tr>\n";
                        } if(isset($_POST['save'])) {                       //if saved is requested then save it
                            
                        }
                        ?>
                    </table>
                        <input type="submit" name="save" value="save"/>
                    </form>
                </td>
            </tr>
        </table>
        <?php
        include("squadManFooter.php");
        ?>
    </body>
</html>
