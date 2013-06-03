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
        <title>add a new member</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
        <?php include("header.php"); 
        include("projectFunctions.php");
        $ident=Connect('Sign-in');
        $otherUnit=false;        //TODO figure out persistance
        $otherContact = false;
        if($_POST["unit"]=="other") {   //if other unit set null to prevent construct errors
            $otherUnit = true;
            $_POST["unit"] = null;
        }                            //creates a member with properties
        $DoB = parse_date_input($_POST,"DoB");
        $DoJ = parse_date_input($_POST, "DoJ");
        $newMember = new member($_POST['CAPID'],-1,$ident,$_POST['Lname'],$_POST["Fname"],$_POST['Gender'],$DoB,$_POST["member"],
                $_POST["achiev"],$_POST["text"],$_POST["unit"],$DoJ);
        $badInput = $newMember->badInput;  //checks if had bad input
        $row =0;
        $numberContacts=0;
        $contact=array();
        while($row<5) {                              //stores to array name
            if($_POST["contName$row"]!=null) {
                $contact[$numberContacts] = cleanInputString($_POST["contName$row"],32,"Contact's Name #$row",false);
            }
            $row++;
        }
        $row =0;                 
        $numberContacts = 0;
        $relation =array();           //force to be array type
        while($row<5) {                 //stores to array relations
            if($_POST["relation$row"]!= null){
                if($_POST["relation$row"]=="other"){        
                    $otherContact = true;                    
                }
                $relation[$numberContacts] =$_POST["relation$row"];
                $numberContacts++;
            }
            $row++;
        }
        $numberContacts=0;
        $row =0;                            //stores phone numbers to array
        $phoneNum = array();                 //forces to be array type
        while($row<5) {
            if($_POST["number$row"]!=null) {
                $phoneNum[$numberContacts] = $_POST["number$row"];
                $numberContacts++;
            }
            $row++;
        }
        $newMember->addEmeregencyContactArray($contact,$relation,$phoneNum);
        if($badInput) {
            newMember($ident,"signin/newMember.php");
        }                                                 //insert member
        if($otherUnit||$otherContact) {
            session_start();
            $_SESSION["member"]=$newMember;           //store to session
            if($otherUnit) {
                newUnit($ident,"addUnit.php");
                echo"</body></html>";
            } if($otherContact) {
                 echo "<form action=\"newContact.php\" method=\"post\"><table border=\"1\" cellspacing=\"1\"><tr>
                    <th>Contact Name</th><th>Contact's Relation</th><th>Contact's Phone Number</th></tr>\n";
                 $row=0;//TODO finish table
                 while($row<5) {                       //searchs for those that had other listed
                     if(($temp=$newMember->emergency_get($row)->getRelation())!=null) {
                         if($temp=="other") {
                             echo"<tr><td>".$newMember->emergency_get($row)->getName()."</td>"; //displays name
                             echo"<td><input type=\"text\" value=\"relat$row\" size=\"5\"/></td></tr>";
                         }
                     }
                 }
                 echo "</table><input type=\"submit\" value=\"Add Relation\"/></form></body></form>";
            }
            exit;
        }                   //insert new member in.   //TODO replace with method call
        $complete=$newMember->insertMember($ident);
         if($complete) {         //else continue to insert data
            echo"<strong>You have successfully been added.</strong><br>";
            $complete=$newMember->insertEmergency($ident);
            if($complete) {
                echo"<strong>Your emergency contact information has been successfully added.</strong>";
            }
            $newMember->sign_in($ident,"<strong>You have been signed-in.</strong>");
         }
        ?>
        <a href="index.php">Go to Sign-in page.</a><br>
        <a href="../index.ph">Go to home page.</a>
        <?php include("footer.php");?>
    </body>
</html>