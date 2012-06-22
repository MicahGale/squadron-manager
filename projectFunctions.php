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
/*
 * TODO redo ors in sql query for member search
 * TODO debug session hijacking resign-in also do post keep thingy with foreach and allow to go back to where logoute
 * TODO consider promo boards
 * TODO error handling for mysql connect? upgrade to mysqli
 * TODO create a member search
 * TODO just kill views
 * TODO add edit member and add picture
 * TODO membership termination and deletion
 * TODO allow to change password
 * TODO create event pages and auto create meetings for sign-in
 * TODO promotion and testing pages and ribbon stuff
 * TODO psuedo roles using php file
 * TODO Admin pages
 * TODO notifications
 * TODO regulations page and update regsupdater
 * TODO finish populating db
 * TODO populate pictures
 */
$phpDateFormat = "m-d-Y";
$phpToMysqlFormat = "Y-m-d";
$sqlDateFormat = "%d-%m-%Y";
function auditLog($page, $ip, $type) {
    $time = date('o-m-d H:i:s');
    mysql_connect('localhost', 'Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf', true);
    mysql_query("USE SQUADRON_INFO");
    mysql_query("INSERT INTO AUDIT_LOG(TIME_OF_INTRUSION, INTRUSION_TYPE, PAGE,IP_ADDRESS)
        VALUES('$time','$type','$page','$ip');");
    return $time;
}
function auditDump($time, $fieldName, $fieldValue) {
    mysql_connect('localhost', 'Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf', true);
    mysql_query("USE SQUADRON_INFO");
    mysql_query("INSERT INTO AUDIT_DUMP(TIME_OF_INTRUSION, FIELD_NAME, FIELD_VALUE)
        VALUES('$time','$fieldName','$fieldValue');");
}
function newMember($identifier, $page,$capid) {                                              //if no members allow to create new member
    //displays table for input
    echo "<form action=\"$page\" method=\"post\">";
    //displays input fields
    echo "CAPID:<input type=\"text\" name=\"CAPID\" value=\"$capid\" size=\"1\"/><br>   
        Last Name:<input type=\"text\" name=\"Lname\" size=\"4\"/><br>
        First Name:<input type=\"text\" name=\"Fname\" size=\"4\"/><br>
        Gender:<select name=\"Gender\"><option value=\"M\">Male</option><option value=\"F\">Female</option></select><br>
        Date of Birth:\n";
    enterDate(true,'DoB');
    mysql_selectdb("SQUADRON_INFO", $identifier);
    echo "<br>CAP Grade"; //SELECT A.ACHIEV_CODE, CONCAT(A.ACHIEV_CODE,'-',B.GRADE_NAME) FROM SQUADRON_INFO.ACHIEVEMENT A JOIN SQAUDRON_INFO.GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY B.GRADE_NUM
    dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) FROM SQUADRON_INFO.ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "achiev", $identifier, false);
    echo "<br>Member Type";
    dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM SQUADRON_INFO.MEMBERSHIP_TYPES", "member", $identifier, false);  //creates drop down menu for membership types
    echo "<br>Textbook Set";
    dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM SQUADRON_INFO.TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL'", 'text', $identifier, false);  //creates drop down menu for text sets
    echo "<br>Unit Charter Number:";
    dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM SQUADRON_INFO.CAP_UNIT", 'unit', $identifier, true,'RMR-ID-073');  //creates drop down menu for text sets
    echo "<br>Date Joined CAP:";
    enterDate(true,'DoJ');
    echo "<br><br><strong>Also add at least One emergency Contact</strong>";
    newContact(FALSE, $identifier);
}
function newContact($submit, $identifier, $page = null) {
    if ($submit) {
        echo"<form action=\"$page\" method=\"post\">";
    }
    echo "<table border=\"1\" cellspacing=\"1\"><tr>
            <th>Contact Name</th><th>Contact's Relation</th><th>Contact's Phone Number</th></tr>\n";
    $row = 0;
    while ($row < 5) {
        echo "<tr><td><input type=\"text\" name=\"ContName$row\" size=\"7\"/></td><td>";
        dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM SQUADRON_INFO.CONTACT_RELATIONS", "relation$row", $identifier, true);
        echo "</td><td><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
        $row++;
    }
    echo "</tr></table><br>";
    if ($submit) {
        echo"<input type=\"submit\" value=\"Add Emergency Contacts\"/></form>";
    }
}
function newUnit($identifier, $page) {
    echo "<br>Please enter the new unit's information below:<br>\n";
    echo"<form action=\"$page\" method=\"post\">\n";
    echo"Charter Number(i.e. RMR-ID-073)<input type=\"text\" name=\"charter\" size=\"5\"/>";
    echo "<br>\nRegion:";
    dropDownMenu("SELECT REGION_CODE,REGION_NAME FROM SQUADRON_INFO.REGION", "region", $identifier, false);
    echo"<br>\nWing:";
    dropDownMenu("SELECT WING, WING_NAME FROM SQUADRON_INFO.WING", "wing", $identifier, false);
    echo "<br>\n<input type=\"submit\" value=\"Add new Unit\"/></form>";
}
function newContactType($page) {
    echo"<br>Please enter the new type of Contact below\n";
    echo "<form action=\"$page\" method=\"post\">";
    echo "Contact Type: <input type=\"text\" name=\"contact\"/><br>\n";
    echo "<input type=\"submit\" value=\"add contact type\"/><br></form>";
}
function newVisitor($page, $defaultFname = null, $defaultLname = null) {
    echo "<form action=\"$page\" method=\"post\">\n";
    echo "First Name:<input type=\"text\" name=\"Fname\" size=\"5\" default=\"$defaultFname\"/><br>\n";
    echo "Last Name:<input type=\"text\" name=\"Lname\" size=\"5\" default=\"$defaultLname\"/><br>\n";
    echo "<strong>Please Provide an Emergency Contact</strong><br>\n";
    echo "Emergency Contact Name: <input type=\"text\" name=\"ContName\" size=\"5\"/><br>\n";
    echo "Emergency Contact Phone Number:<input type=\"text\" name=\"ContPhone\" size=\"5\"/><br>\n";
    echo "<input type=\"submit\" value=\"Finish\"/></form>\n";
}
function dropDownMenu($query, $name, $identifier, $hasotherfield, $default = null, $page = null) {      //drop down menu 1st field is code 2nd is name
    $results = Query($query, $identifier, $page);                     //TODO include error handlin
    $row = 0;
    echo "<select name=\"$name\">";
    while ($row < mysql_num_rows($results)) {
        $code = mysql_result($results, $row, 0);
        $names = mysql_result($results, $row, 1);
        if ($default != null && $code == $default) {
            echo"<option selected=\"selected\" value=\"$code\">$names</option>";
        } else {
            echo"<option value=\"$code\">$names</option>";
        }
        $row++;
    }
    if ($hasotherfield) {
        echo "<option value=\"other\">other</option>";
    }
    echo "</select>";
}
function reportDbError($ident, $page) {
    $time = date('o-m-d H:i:s');
    auditLog($time, 'signin/newMember.php', $_SERVER['REMOTE_ADDR'], 'ER');
    auditDump($time, 'Error Code', mysql_errno($ident));
    auditDump($time, 'Error Message', mysql_error($ident));
    echo"<br><strong>there was an error with processing the request</strong><br>
        Please give the following information to you Squadron's IT Officer(s)<br>
        <strong>error:</strong>\n";
    echo mysql_errno($ident) . " " . mysql_error($ident);
    echo "<br><strong>Time:</strong>$time\n";
    echo"<br><strong>Page:</strong>$page<br>\n";
    echo"<strong>IP:</strong>" . $_SERVER['REMOTE_ADDR'] . "<br>";
}
function Query($query, $ident, $page, $message = null) {
    $results = mysql_query($query, $ident);
    if ($results == false) {
        reportDbError($ident, $page);
    } else if ($results == true) {
        echo $message;
    }
    return $results;
}
function cleanInputInt($input, $length, $fieldName, $page) {
    $clean = htmlspecialchars(mysql_real_escape_string($input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
    if (strlen($clean) != $length || !is_numeric($clean) || $clean != $input) {
        $badInput = true;
        $time = auditLog($page, $_SERVER['REMOTE_ADDR'], "SI");
        auditDump($time, $fieldName, $clean);
        echo "<font color=\"red\">$fieldName is not a valid number it must be $length digits long.</font><br>";
        if (strlen($clean) != $length || !is_numeric($clean)) {          //nulls if wrong type
            $clean = null;
        }
    }
    return $clean;
}
function cleanInputString($input, $length, $fieldName, $page, $empty) {                      //clean and log numbers
    $clean = htmlspecialchars(mysql_real_escape_string($input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
    if (strlen($clean) > $length || $clean != $input || $clean == "" || $clean == null) {
        if (strlen($clean) == 0 && $empty == false) {
            echo "<font color=\"red\"> $fieldName can not be empty</font><br>";
        } else if ($empty == false) {
            echo "<font color=\"red\"> $fieldName is not valid Maximum is: $length</font><br>";
        }
         $time = auditLog($page, $_SERVER['REMOTE_ADDR'], 'SI');
        auditDump($time, "$fieldName", $clean);
        $badInput = true;
        if (strlen($clean) > $length) {
            $clean = null;
        }
    }
    return $clean;
}
function cleanInputDate($input, $regex, $length, $fieldName, $page) {                      //clean and log numbers
    $clean = htmlspecialchars(mysql_real_escape_string($input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
    if (strlen($clean) > $length || $clean != $input || (preg_match($regex, $clean) != 1) || strtotime($clean) == false) {
        echo "<font color=\"red\"> $fieldName is not a valid date.</font><br>";
        $time = auditLog($page, $_SERVER['REMOTE_ADDR'], 'SI');
        auditDump($time, $fieldName, $input);
        $badInput = true;
        if (strlen($clean) > $length || (preg_match($regex, $clean)) || strtotime($clean) == false) {
            $clean = null;
        }
    }
    return $clean;
}
function create_AES_256_key($password, $td) {
    $ks = mcrypt_enc_get_key_size($td);                    //gets key size
    $key = substr(hash("sha512", $password), 0, $ks);          //creates the key from SHA512
    return $key;
}
function session_secure_start($capid=null) {
    session_start();                     //starts the session
    if (!isset($_SESSION['started'])) {       //if starting the session
        if ($_SERVER['SCRIPT_NAME'] == '/login/index.php') {        //if at the login page
            $_SESSION['started'] = true;                    //say that it started
            $_SESSION['ip_addr'] = $_SERVER['REMOTE_ADDR'];    //store the ip address to prevent hijacking from other "ips"
            $_SESSION['last_page'] = $_SERVER['SCRIPT_NAME'];  //store what the current page is
            $_SESSION['predicted'] = null;
            $_SESSION['intruded'] = false;                           //assumes no intrusion yet
            $_SESSION['resignin'] = true;                             //assume good no need to kill session
            settype($_SESSION['predicted'], 'array');
            $ident = mysql_connect('localhost', 'ViewNext', 'oiu34wioejnkfvlkmse39ijfdokfdyuhjf');
            if($capid==null) {
                    session_predict_path($ident);
            } else {
                session_predict_path($ident,$capid);
            }
            mysql_close($ident);
        } else {                             //force redirect to login page
            header("refresh:0;url=/login");       //force the user to redirect out 
            exit;                                                              //ends current script
        }
    } else {                 //if session is already started check for malicious intent
        $hijacked = false;
        if (($_SESSION['ip_addr'] == $_SERVER['REMOTE_ADDR'])) { //checks if from the correct ip and not spoofing the http refere
            $size = count($_SESSION['predicted']);
            $found = false;                           //tells if ever was found
            for ($i = 0; $i < $size; $i++) {                            //search predicted array if is on a permissable page
                if ($_SESSION['predicted'][$i] == $_SERVER['SCRIPT_NAME']) {  //if found it in the list
                    $found = true;                 //says it was found
                    break;
                }
            }
            if (!$found) {  //if not where it was supposed to go
                $hijacked = true;
            }
        } else {                        //dies if broken in 
            $hijacked = true;
        }
        if (!$_SESSION['resignin']) {   //if didn't resign in then kill the session
            $time = auditLog($_SERVER['SCRIPT_NAME'], $_SERVER['REMOTE_ADDR'], 'KS');
            auditDump($time, "user", $_SESSION['member']->getcapid());
            session_destroy();
            header("refresh:0;url=/");       //destroy the session and then redirect
            exit;
        }
        if ($hijacked) {                     //redirect to reprompt for user info
            $_SESSION['intruded'] = true;
            $_SESSION['resign'] = false;
            unset($_SESSION['password']);  //clear password so can't connect to database at all until reverified
            $time = auditLog($_SERVER['SCRIPT_NAME'], $_SERVER['REMOTE_ADDR'], 'SH');  //log it
            auditDump($time, "USER", $_SESSION['member']->getcapid());                //dump username
            session_resign_in(false);            //makes resign in
        } else {              //if no foul play set up info for next request
            $_SESSION['last_page'] = $_SERVER['SCRIPT_NAME'];       //allocate last page
            $ident = mysql_connect('localhost', 'ViewNext', 'oiu34wioejnkfvlkmse39ijfdokfdyuhjf');
            if($capid!=null) {
                session_predict_path($ident,$capid);
            }else {
                session_predict_path($ident);
            }
            mysql_close($ident);
            session_regenerate_id();                                //if all good regenerate id lengthen session
            if ($_SESSION['intruded']) {                       //if someone has tried to intrude make resign in
                session_resign_in(true);               //has them resign in and keep the post stuff
            }
        }
    }
}
function session_predict_path($ident,$capid=null) {                                //creates an array of pages that the user may visit next    
    $_SESSION['predicted'] = null;
    settype($_SESSION['predicted'], 'array');
    $path = $_SERVER['SCRIPT_NAME'];
    $path = substr($path, strpos($path, "/", 1) + 1);            //cuts off leading login or just first ..../ todo redo as regex
    $query = "SELECT NEXT_URL FROM SQUADRON_INFO.NEXT_VISIT
        WHERE LAST_URL='" . $path . "'";                           //query to find next 
    $result = Query($query, $ident, $_SERVER['SCRIPT_NAME']);
    $size = mysql_num_rows($result);
    for ($i = 0; $i < $size; $i++) {
        array_push($_SESSION['predicted'], "/login/" . mysql_result($result, $i, 'NEXT_URL'));
    }
    print_r($capid);
    if($capid!=null) {
        $id = $capid;
    } else {
        $id = $_SESSION['member']->getcapid();
    }
    $query = "SELECT B.URL 
                FROM SQUADRON_INFO.TASK_TYPE A JOIN
                SQUADRON_INFO.TASKS B ON
                A.TYPE_CODE=B.TYPE_CODE
                WHERE B.TASK_CODE IN (
                SELECT A.TASK_CODE FROM SQUADRON_INFO.STAFF_PERMISSIONS A,
                SQUADRON_INFO.STAFF_HOLDING B
                WHERE (A.STAFF_CODE = B.STAFF_CODE
                OR A.STAFF_CODE='AL')
                AND B.CAPID='$id') OR
                    B.TASK_CODE IN (
                    SELECT TASK_CODE FROM SQUADRON_INFO.SPECIAL_PERMISSION
                    WHERE CAPID='$id')";                           //repeats except now looking for urls that are permanently allowed
    $result = Query($query, $ident, $_SERVER['SCRIPT_NAME']);
    $size = mysql_num_rows($result);
    for ($i = 0; $i < $size; $i++) {
        array_push($_SESSION['predicted'], "/login/" . mysql_result($result, $i, 'B.URL'));
    }
}
function session_resign_in($keepPost) {                        //requires person to resign in
    if ($keepPost) {                 //if want to keep post store post info to session 
        $_SESSION['GET'] = $_GET;
        $_SESSION['POST'] = $_POST;
    }
    $_SESSION['redirect'] = $_SERVER['SCRIPT_NAME'];
//    header("refresh:0;url=/login/reSignIn.php"); //redirect to resign in and exit
//    exit;
}
/* Functions urlsafe_b64encode and urlsafe_b64decode posted on
 * <https://www.php.net/manual/en/function.mcrypt-generic.php#71135>
 * by  tmacedo@linux.ime.usp.br (tmacedo at linux dot ime dot usp dot br)
 * 
 */
function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', '.'), $data);
    return $data;
}
function aes_Encrypt_Encode($password, $input) {
    $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, "", "ecb", "");
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
    $key = create_AES_256_key($password, $td);
    mcrypt_generic_init($td, $key, $iv);
    $encrypted = mcrypt_generic($td, $input);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return urlsafe_b64encode($encrypted);
}
function aes_Decrypt_Encode($password, $input) {
    $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, "", "ecb", "");
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
    $key = create_AES_256_key($password, $td);
    mcrypt_generic_init($td, $key, $iv);
    $decrypted = mdecrypt_generic($td, urlsafe_b64decode($input));
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $decrypted;
}
function urlsafe_b64decode($string) {
    $data = str_replace(array('-', '_', '.'), array('+', '/', '='), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}
function enterDate($sameLine = true, $append = null, DateTime $default = null) {
    $months = monthArray();
    if ($append == null) {
        echo "<select name=\"month\">";
    } else {
        echo "<select name=\"month$append\">";
    }
    if ($default != null) {
        $month = $default->format("n");
    }
    for ($i = 1; $i <= count($months); $i++) {
        echo "<option value=\"$i\"";
        if ($default != null) {
            if ($i == $month)
                echo " selected=\"selected\"";
        }
        echo ">" . $months[$i] . "</option>";
    }
    echo "</select>";
    if (!$sameLine)
        echo "<br>";
    echo "Day:<input type=\"text\" size=\"1\" name=\"Date";
    if ($append != null)
        echo $append;
    echo "\"";
    if ($default != null) {
        echo " value=\"" . $default->format('d') . "\"";
    }
    echo "/>";
    if (!$sameLine)
        echo "<br>";
    echo "Year:<input type=\"text\" size=\"1\" name=\"Year";
    if ($append != null)
        echo $append;
    echo"\"";
    if ($default != null)
        echo " value=\"" . $default->format('Y') . "\"";
    echo"/>";
}
function parse_date_input(array $input, $append = null) {
    if ($append == null) {
        $month = cleanInputString($input["month"], 2, "month", $_SERVER['SCRIPT_NAME'], false);
        $day = cleanInputString($input["Date"], 2, "day", $_SERVER['SCRIPT_NAME'], false);
        $year = cleanInputInt($input["Year"], 4, "year", $_SERVER['SCRIPT_NAME']);
    } else {
        $month = cleanInputString($input["month" . $append], 2, "month", $_SERVER['SCRIPT_NAME'], false);
        $day = cleanInputString($input["Date" . $append], 2, "day", $_SERVER['SCRIPT_NAME'], false);
        $year = cleanInputInt($input["Year" . $append], 4, "year", $_SERVER['SCRIPT_NAME']);
    }
    try {
        $buffer = new DateTime($day . "-" . $month . "-" . $year);
    } catch (exception $e) {
        $time = auditLog($_SERVER['SCRIPT_NAME'], $_SERVER['REMOTE_ADDR'], "EX");
        auditDump($time, "error message", $e->getMessage());
        die("Could not parse date");
    }
    return $buffer;
}
function monthArray() {
    return array(1 => "January",
        2 => "February",
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => "June",
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December');
}
class member {
    private $phpDateFormat = "m-d-Y";
    private $phpToMysqlFormat = "Y-m-d";
    private $sqlDateFormat = "%d-%m-%Y";
    private $capid;
    private $name_last;
    private $name_first;
    private $gender;
    private $DoB;
    private $memberType;
    private $achievement;
    private $text_set;
    private $unit;
    private $Date_of_Join;
    private $emergencyContacts = array();
    public $badInput;
    private $isEmpty = false;     //member doesn't exist    
    private $initLevel;
    public function __construct($capid, $level, $page, $ident, $name_last = null, $name_first = null, $gender = null, dateTime $DoB = null, $memberType = null, $achievement = null, $text_set = null, $unit = null, DateTime $Date_of_Join = null) {
        $this->capid = cleanInputInt($capid, 6, "CAPID", $page);
        if ($level == -1) {             //levels -1= all from input 0=capid 1=capid+name+gender 2=1+achievement+text+member_type 3=2+dates 4=3+emergency+unit         
            $this->badInput = false;
            $this->capid = $capid;
            $this->name_last = $name_last;
            $this->name_first = $name_first;
            $this->gender = $gender;
            $this->DoB = $DoB;
            $this->memberType = new memberType(cleanInputString($memberType, 1, "Member Type", $_SERVER['SCRIPT_NAME']));
            $this->achievement = $achievement;
            $this->text_set = $text_set;
            $this->unit = new unit(cleanInputString($unit, 10, "unit", $_SERVER['SCRIPT_NAME']));
            $this->Date_of_Join = $Date_of_Join;
            $this->cleanFields();
            $this->initLevel();
        } else {
            $this->init($level, $ident, $page);
        }
    }
    public function cleanFields() {
        $this->capid = cleanInputInt($this->capid, 6, 'CAPID', 'signin/newMember.php');
        $this->name_first = cleanInputString($this->name_first, 32, "First Name", "signin/newMember.php", false);
        $this->name_last = cleanInputString($this->name_last, 32, "Last Name", "signin/newMember.php", false);
        $this->gender = cleanInputString($this->gender, 1, "Gender", "signin/newMember.php", false);
        $this->achievement = cleanInputString($this->achievement, 5, "achievement", "signin/newMember.php", false);
        $this->text_set = cleanInputString($this->text_set, 5, "Textbook set", "signin/newMember.php", false);
    }
    public function cleanFieldsComplete() {
        $this->cleanFields();
        $this->DoB = new DateTime(cleanInputDate(date("o-m-d", $this->DoB), 10, 'Date of Birth', "#^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$#"));
        $this->memberType = new memberType($this->memberType->getCode());
        $this->unit = new unit($this->unit->getCharter());
        $this->Date_of_Join = new DateTime(cleanInputDate(date("o-m-d", $this->Date_of_Join), 10, 'Date Joined', "#^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$#"));
    }
    public function addEmergencyContact($Name, $relation, $number) {
        array_push($this->emergencyContacts, new Contact($Name, $relation, $number, true));
    }
    public function addEmeregencyContactArray(array $Name, array $Relation, array $phone) {
        $index = 0;
        while ($index < count($Name)) {
            if ($Name != null) {
                array_push($this->emergencyContacts, new contact($Name[$index], $Relation[$index], $phone[$index], true));
            }
            $index++;
        }
    }
    public function insertMember($ident) {
        $query = "INSERT INTO SQUADRON_INFO.MEMBER (CAPID,NAME_LAST,NAME_FIRST,GENDER,DATE_OF_BIRTH,ACHIEVEMENT,MEMBER_TYPE,TEXTBOOK_SET,HOME_UNIT,DATE_JOINED)
            VALUES('$this->capid','$this->name_last','$this->name_first','$this->gender',STR_TO_DATE('" . date($phpDateFormat, $this->DoB) . "','" . $sqlDateFormat . "'),'$this->achievement','" . $this->memberType->getCode . "','$this->textset','" . $this->unit->getCharter() . "',STR_TO_DATE('" . date($phpDateFormat, $this->DoJ) . "','" . $this->sqlDateFormat . "'))";
        return Query($query, $ident, "member->insertMember()");
    }
    public function insertEmergency($ident) {
        $query = "INSERT INTO SQUADRON_INFO.EMERGENCY_CONTACT (CAPID,RELATION,CONTACT_NAME,CONTACT_NUMBER) VALUES";
        $row = 0;
        while ($row < (count($this->emergencyContacts) - 1)) {
            $con = $this->emergencyContacts[$row]->getName;
            $relat = $this->emergencyContacts[$row]->getRelation;
            $num = $this->emergencyContacts[$row]->getPhone;
            $query = $query . "('$capid','$relat','$con','$num'), ";
        }
        $con = $this->emergencyContacts[$row]->getName;
        $relat = $this->emergencyContacts[$row]->getRelation;
        $num = $this->emergencyContacts[$row]->getPhone;
        $query = $query . "('$capid','$relat','$con','$num')";
        return Query($query, $ident, "member->insertEmergency()");
    }
    public function sign_in($ident, $page, $message, $event_code = null) {
        if ($event_code == null) {                         //assume current event
            return Query("INSERT INTO SQUADRON_INFO.INSERT_CURRENT (CAPID, EVENT_CODE)
                SELECT '" . $this->capid . "',EVENT_CODE FROM SQUADRON_INFO.CURRENT_EVENT", $ident, $page, $message);
        } else {              //else use provided event
            return Query("INSERT INTO SQUADRON_INFO.ATTENDANCE(CAPID,EVENT_CODE)
                VALUES('" . $this->capid . "','$event_code')", $ident, $page, $message);
        }
    }
    public function emergency_get($index) {
        if ($index < count($this->emergencyContacts)) {
            return $this->emergencyContacts[$index];
        } else {
            return null;
        }
    }
    public function unit_set(unit $unit) {
        $this->unit = $unit;
    }
    public function replaceOther($index, $relatCode, $page) {
        if ($index < count($this->emergencyContacts)) {
            $contact = $this->emergencyContacts[$index];
            $contact->setRelation($relatCode, $page);
            $this->emergencyContacts[$index] = $contact;
        }
    }
    public function exists() {
        return !$this->isEmpty;
    }
    public function getCapid() {
        return $this->capid;
    }
    public function getName_first() {
        return $this->name_first;
    }
    public function getName_Last() {
        return $this->name_last;
    }
    public function getGrade($ident, $page) {
        $results = Query("SELECT A.GRADE_NAME FROM SQUADRON_INFO.GRADE A
            JOIN SQUADRON_INFO.ACHIEVEMENT B ON B.GRADE=A.GRADE_ABREV
            WHERE B.ACHIEV_CODE='" . $this->achievement . "'", $ident, $page, "");
        if (mysql_num_rows($results)) {
            return mysql_result($results, 0, "GRADE_NAME");
        }
    }
    public function testSign_up($ident, $target, $caller) {
        echo "<strong>Testing and Promotion Sign-up</strong>";
        echo "<form action=\"$target\" method=\"post\">
            <table border=\"1\" cellspacing=\"1\">
                <tr><th>Sign-Up</th><th>Test Type</th><th>Test Name</th><th>Achievement</th></tr>\n";
        $results = Query("SELECT ACHIEV_NAME FROM SQUADRON_INFO.ACHIEVEMENT
            WHERE ACHIEV_CODE='" . $this->achievement . "'", $ident, $caller, "");
        if (mysql_num_rows($results)) {
            $achievName = mysql_result($results, 0, "ACHIEV_NAME");
        }
        $results = Query("SELECT A.REQUIREMENT_TYPE, B.TYPE_NAME, A.NAME FROM SQUADRON_INFO.PROMOTION_REQUIREMENT A
            JOIN SQUADRON_INFO.REQUIREMENT_TYPE B ON A.REQUIREMENT_TYPE=B.TYPE_CODE
            JOIN SQUADRON_INFO.ACHIEVEMENT C ON A.ACHIEV_CODE=C.ACHIEV_CODE
            JOIN SQUADRON_INFO.ACHIEVEMENT D ON D.NEXT_ACHIEV=C.ACHIEV_CODE
            WHERE D.ACHIEV_CODE='" . $this->achievement . "' AND
                A.TEXT_SET='" . $this->text_set . "'AND
                    A.REQUIREMENT_TYPE IN ('LT','DT','AE','PT','PB')
                    AND A.REQUIREMENT_TYPE NOT IN (
                    SELECT E.REQUIREMENT_TYPE FROM SQUADRON_INFO.REQUIREMENTS_PASSED E
                    JOIN SQUADRON_INFO.ACHIEVEMENT F ON E.ACHIEV_CODE =F.ACHIEV_CODE
                    JOIN SQUADRON_INFO.ACHIEVEMENT G ON G.NEXT_ACHIEV=F.ACHIEV_CODE
                    WHERE G.ACHIEV_CODE='" . $this->achievement . "'AND
                    E.CAPID='" . $this->capid . "')", $ident, $caller, "");
        if (mysql_num_rows($results) > 0) {                                   //if can sign up for testing
            for ($row = 0; $row < mysql_num_rows($results); $row++) {
                echo "<tr><td><input type=\"checkbox\" name=\"signup[]\" value=\"" . mysql_result($results, $row, "REQUIREMENT_TYPE") . "\"/></td>"; //create checkbox
                echo"<td>" . mysql_result($results, $row, "TYPE_NAME") . "</td>";         //show test type
                if (is_null(mysql_result($results, $row, "NAME"))) {                   //if the name is null for the test just echo n/a
                    echo "<td>n/a</td>";
                } else {
                    echo "<td>" . mysql_result($results, $row, "NAME") . "</td>";    //display test name
                }
                echo "<td>$achievName</td></tr>";                      //displays achievement name
            }
        } else {               //display promotion sign up if available
            echo "<tr><td><input type=\"checkbox\" name=\"signup[]\" value=\"PR\"/></td>";
            echo "<td>Promotion</td><td>n/a</td><td>$achievName</td></tr>";
        }
        echo "</table>\n<input type=\"submit\" name=\"finish\" value=\"Sign-in only\"/>
            <input type=\"submit\" name=\"finish\" value=\"Sign-in and Sign-up for testing\"/></form>";
    }
    public function signUp(array $input, $page, $ident, $message = null) {
        for ($row = 0; $row < count($input); $row++) {
            $code = cleanInputString($input[$row], 2, "signup#$row", $page, false);
            if ($code == "PR") {              //insert promotion requirement if requested
                $query = "INSERT INTO SQUADRON_INFO.PROMOTION_SIGN_UP (CAPID, ACHIEV_CODE,DATE_REQUESTED)
                    SELECT '" . $this->capid . "',A.ACHIEV_CODE,CURDATE() FROM SQUADRON_INFO.ACHIEVEMENT A
                        JOIN SQUADRON_INFO.ACHIEVEMENT B ON B.NEXT_ACHIEV=A.ACHIEV_CODE
                        WHERE B.ACHIEV_CODE='" . $this->achievement . "'";
            } else {
                $query = "INSERT INTO SQUADRON_INFO.TESTING_SIGN_UP(CAPID,ACHIEV_CODE,REQUIRE_TYPE,REQUESTED_DATE)
                    SELECT '" . $this->capid . "',A.ACHIEV_CODE,'$code', CURDATE() FROM SQUADRON_INFO.ACHIEVEMENT A
                        JOIN SQUADRON_INFO.ACHIEVEMENT B ON B.NEXT_ACHIEV=A.ACHIEV_CODE
                        WHERE B.ACHIEV_CODE='" . $this->achievement . "'";
            }
            return Query($query, $ident, $page, $message);
        }
    }
    public function cadetpromotionReport($ident, $page) {
        ?>
        <table border="0" width="900"><tr><td valign="top" align="center">
                    <strong>Cadet Promotion Report for:</strong>
                    <?php

                    echo $this->capid . "- " . $this->name_first . " " . $this->name_last . "\n";
                    echo"</td></tr>";               //center header
                    $header = Query("SELECT TYPE_NAME, TYPE_CODE FROM SQUADRON_INFO.REQUIREMENT_TYPE
            ORDER BY TYPE_CODE", $ident, $page);    //QUERY TO get requirement types
                    echo "<tr><td align=\"center\">
            <table border =\"1\" cellspacing=\"1\" width=\"900\">
            <tr><th>Achievement</th>";
                    for ($row = 0; $row < mysql_num_rows($header); $row++) {
                        echo "<th>" . mysql_result($header, $row, "TYPE_NAME") . "</th>";  //make them into headers
                    }
                    echo "</tr>\n";
                    $query = "SELECT B.ACHIEV_CODE AS ACHIEV, A.REQUIREMENT_TYPE AS TYPE, A.PASSED_DATE AS DATE
            FROM SQUADRON_INFO.PROTECTED_REQUIRE_PASSED A JOIN SQUADRON_INFO.ACHIEVEMENT B
            ON A.ACHIEV_CODE=B.ACHIEV_CODE
            JOIN SQUADRON_INFO.GRADE C ON B.GRADE=C.GRADE_ABREV
            WHERE A.CAPID='" . $this->capid . "'
            ORDER BY C.GRADE_NUM, B.PHASE,A.REQUIREMENT_TYPE";
                    $passed = Query($query, $ident, $page);             //get all the requirements the cadet passed
                    $query = "SELECT A.ACHIEV_NAME, A.ACHIEV_CODE FROM SQUADRON_INFO.ACHIEVEMENT A
            JOIN SQUADRON_INFO.GRADE B ON A.GRADE=B.GRADE_ABREV
            WHERE A.MEMBER_TYPE='C'                                  
            ORDER BY B.GRADE_NUM, A.PHASE, A.ACHIEV_CODE";
                    $achievements = Query($query, $ident, $page);    //get all the achievements 
                    $query = "SELECT ACHIEV_CODE, REQUIREMENT_TYPE FROM SQUADRON_INFO.PROMOTION_REQUIREMENT
            WHERE TEXT_SET IN('" . $this->text_set . "','ALL')
            ORDER BY ACHIEV_CODE, REQUIREMENT_TYPE";
                    $requirements = Query($query, $ident, $page);          //get all the requirements
                    $max = Query("SELECT A.ACHIEV_CODE FROM SQUADRON_INFO.ACHIEVEMENT A
            JOIN SQUADRON_INFO.ACHIEVEMENT B ON A.ACHIEV_CODE=B.NEXT_ACHIEV
            WHERE B.ACHIEV_CODE='" . $this->achievement . "'", $ident, $page);
                    $maxAchiev = mysql_result($max, 0, "ACHIEV_CODE");             //get the next achievement so don't list to spaatz
                    for ($achievRow = 0; $achievRow < mysql_num_rows($achievements); $achievRow++) {  //loop to create rows
                        echo "<tr><td>" . mysql_result($achievements, $achievRow, "ACHIEV_NAME") . "</td>"; //shows name of achievemnt
                        $achievCode = mysql_result($achievements, $achievRow, "ACHIEV_CODE");
                        $passedRequire = null;
                        settype($passedRequire, "array");             //gets all the passed requirements for this row's achievemtn
                        for ($passedRow = 0; $passedRow < mysql_num_rows($passed); $passedRow++) {
                            if (mysql_result($passed, $passedRow, "ACHIEV") == $achievCode) {  //if it is for this achievement
                                array_push($passedRequire, array("Code" => mysql_result($passed, $passedRow, "TYPE"),
                                    "Date" => mysql_result($passed, $passedRow, "DATE"))); //PUSH THE TYPE CODE AND DATE ONTO THE ARRAYfr
                            }
                        }
                        $require = null;             //gets all requirements for this achievemnets promo
                        settype($require, "array");
                        for ($passedRow = 0; $passedRow < mysql_num_rows($requirements); $passedRow++) {
                            if (mysql_result($requirements, $passedRow, "ACHIEV_CODE") == $achievCode) {  //if it is for this achievement
                                array_push($require, mysql_result($requirements, $passedRow, "REQUIREMENT_TYPE")); //PUSH THE TYPE CODE on to the array
                            }
                        }
                        $passedRow = 0;
                        $requireRow = 0;
                        for ($row = 0; $row < mysql_num_rows($header); $row++) {    //cylces through requirements to display them 
                            $testCode = mysql_result($header, $row, "TYPE_CODE");
                            if (array_key_exists($passedRow, $passedRequire)) {     //if have record for that passed just show it
                                if ($passedRequire[$passedRow]["Code"] == $testCode) {        //checks if null first
                                    echo"<td>" . $passedRequire[$passedRow]["Date"] . "</td>";
                                    $passedRow++;
                                    $requireRow++;           //increment other counters up
                                } elseif (array_key_exists($requireRow, $require)) {                       //else sees if there are any requirements for that
                                    if ($require[$requireRow] == $testCode) {
                                        echo "<td></td>";             //leaves cell blank than if no entry, but required
                                        $requireRow++;
                                    } else {
                                        echo "<td>n/a</td>";
                                    }
                                } else {
                                    echo "<td>n/a</td>";           //assumes not required
                                }
                            } elseif (array_key_exists($requireRow, $require)) {                       //else sees if there are any requirements for that
                                if ($require[$requireRow] == $testCode) {
                                    echo "<td><font color=\"red\">incomplete</font></td>";             //leaves cell blank than if no entry, but required
                                    $requireRow++;
                                } else {
                                    echo "<td>n/a</td>";
                                }
                            } else {
                                echo "<td>n/a</td>";           //assumes not required
                            }
                        }
                        echo "</tr>\n";          //ends this row of the table
                        if ($achievCode == $maxAchiev) {                       //break if hit next achievement
                            break;
                        }
                    }
                    echo "</table></table>\n";
                }
    public function editInformation($page, $identifier) {
                    //displays table for input
                    echo "<form action=\"$page\" method=\"post\"><table border =\"1\" cellspacing=\"1\"><tr><th>Last Name</th><th>First Name</th>
            <th>Date of Birth</th></tr>";
                    //displays input fields
                    echo "<tr><td><input type=\"text\" name=\"Lname\" value=\"" . $this->name_last . "\" size=\"4\"/></td>
        <td><input type=\"text\" name=\"Fname\" value=\"" . $this->name_first . "\" size=\"4\"/></td>
        <td>";
                    enterDate(false,null,$this->DoB);
                    echo "</td>\n</tr></table><br><strong>Also add at least One emergency Contact</strong>";
                    $this->editContact(false, $identifier);
                    echo "<input type=\"submit\" value=\"Save Changes\"/> </form>";
                }
    public function editContact($submit, $identifier, $page = null) {
        if ($submit) {
            echo"<form action=\"$page\" method=\"post\">";
        }
        echo "<table border=\"1\" cellspacing=\"1\"><tr>
    <th>Contact Name</th><th>Contact's Relation</th><th>Contact's Phone Number</th></tr>\n";
        $row = 0;
        while ($row < 5) {
            if (array_key_exists($row, $this->emergencyContacts)) {
                echo "<tr><td><input type=\"text\" name=\"ContName$row\" value=\"" . $this->emergencyContacts[$row]->getName . "\" size=\"7\"/></td><td>";
                dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM SQUADRON_INFO.CONTACT_RELATIONS", "relation$row", $identifier, true, $this->emergencyContacts[$row]->getRelation());
                echo "</td><td><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
                $row++;
            } else {
                echo "<tr><td><input type=\"text\" name=\"ContName$row\" size=\"7\"/></td><td>";
                dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM SQUADRON_INFO.CONTACT_RELATIONS", "relation$row", $identifier, true);
                echo "</td><td><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
                $row++;
            }
        }
        echo "</tr></table><br>";
        if ($submit) {
            echo"<input type=\"submit\" value=\"Add Emergency Contacts\"/></form>";
        }
    }
    public function init($level, $ident, $page) {
        if (is_numeric($level)) {              //makes sure its a number
            if ($level == $this->initLevel) {   //if already inited to level return
                return true;
            }
            if ($level > $this->initLevel) {    //if going up in levels
                if($level>=1) {
                    $results = Query("SELECT NAME_LAST,NAME_FIRST,GENDER FROM SQUADRON_INFO.MEMBER
            WHERE CAPID='" . $this->capid . "'", $ident, $page, "");
                    if (mysql_num_rows($results)) {
                        $this->name_last = mysql_result($results, 0, "NAME_LAST");
                        $this->name_first = mysql_result($results, 0, "NAME_FIRST");
                        $this->gender = mysql_result($results,0,"GENDER");
                    } else {
                        $this->isEmpty = true;
                    }
                }
                if($level>=2) {
                    $results = Query("SELECT ACHIEVEMENT, TEXTBOOK_SET,MEMBER_TYPE FROM SQUADRON_INFO.MEMBER
            WHERE CAPID='" . $this->capid . "'", $ident, $page, "");
                    if (mysql_num_rows($results) > 0) {
                        $this->achievement = mysql_result($results, 0, "ACHIEVEMENT");
                        $this->text_set = mysql_result($results, 0, "TEXTBOOK_SET");
                        $this->memberType = mysql_result($results, 0, "MEMBER_TYPE");
                    } 
                    $this->initLevel = 2;
                }
                if($level>=3) {
                    $results = Query("SELECT DATE_OF_BIRTH AS DOB, DATE_JOINED AS DOJ
        FROM SQUADRON_INFO.MEMBER
        WHERE CAPID='" . $this->capid . "'", $ident, $page, "");
                    if (mysql_num_rows($results) > 0) {
                        $this->DoB = new DateTime(mysql_result($results, 0, "DOB"));
                        $this->Date_of_Join = new DateTime(mysql_result($results, 0, "DOJ"));
                    }
                }
                if($level>=4) {
                    $results = Query("SELECT HOME_UNIT FROM SQUADRON_INFO.MEMBER
        WHERE CAPID='" . $this->capid . "'", $ident, $page, "");
                    if (mysql_num_rows($results) > 0) {
                        $this->unit = new unit(mysql_result($results, 0, "HOME_UNIT"), $page, $ident);
                    } 
                    $results = Query("SELECT RELATION, CONTACT_NAME, CONTACT_NUMBER
        FROM SQUADRON_INFO.EMERGENCY_CONTACT 
        WHERE CAPID='" . $this->capid . "'", $ident, $page);
                    for ($row = 0; $row < mysql_num_rows($results); $row++) {
                        settype($this->emergencyContacts, "array");
                        $buffer = new contact(mysql_result($results, $row, "CONTACT_NAME"),
                                        mysql_result($results, $row, "RELATION"),
                                        mysql_result($results, $row, "CONTACT_NUMBER"));
                        array_push($this->emergencyContacts, $buffer);
                    }
                }
                $this->initLevel = $level;              //stores level
            }
        }
    }
    public function editFields(array $input, $page, $ident) {
        $contactSuccess = true;
        if (array_key_exists("Fname", $input)) {
            $this->name_first = cleanInputString($input["Fname"], 32, "First Name", $page, false);
        } if (array_key_exists("Lname", $input)) {
            $this->name_last = cleanInputString($input["Lname"], 32, "Last Name", $page, false);
        } if (array_key_exists("month", $input)) {
            $this->DoB =  parse_date_input($input);
        }
        for ($row = 0; $row < 5; $row++) {                                        //edit contact information
            if (array_key_exists("ContName" . $row, $input)) {
                if ($input["ContName" . $row] != "") {
                    if (array_key_exists($row, $this->emergencyContacts)) {                      //if not a new one edit it
                        $oldRelat = $this->emergencyContacts[$row]->getRelation();
                        $this->emergencyContacts[$row]->setName($input["ContName" . $row], $page);
                        $this->emergencyContacts[$row]->setRelation($input["relation" . $row], $page);
                        $this->emergencyContacts[$row]->setPhone($input["number" . $row], $page);
                        if (!$this->updateContact($row, $oldRelat, $ident, $page)) {
                            $contactSuccess = false;
                        }
                    } else {
                        array_push($this->emergencyContacts, new contact($input["contName" . $row], $input["relation" . $row], $input["number" . $row]));
                        if (!$this->insertSingleContact($row, $ident, $page)) {
                            $contactSuccess = false;
                        }
                    }                       //TODO update all info and contact info.
                }
            }
        }
        return $contactSuccess;
    }
    public function updateContact($row, $oldRelat, $ident, $page) {
        $query = "UPDATE SQUADRON_INFO.EMERGENCY_CONTACT
    SET RELATION='" . $this->emergencyContacts[$row]->getRelation() . "'
    CONTACT_NAME='" . $this->emergencyContacts[$row]->getName() . "'
        CONTACT_NUMBER='" . $this->emergencyContacts[$row]->getPhone() . "'
            WHERE CAPID='" . $this->capid . "'
                AND RELATION='" . $oldRelat . "'";
        return Query($query, $ident, $page);
    }
    public function insertSingleContact($row, $ident, $page) {
        $query = "INSERT INTO SQUADRON_INFO.EMERGENCY_CONTACT (CAPID,RELATION,CONTACT_NAME,CONTACT_NUMBER) VALUES";
        $con = $this->emergencyContacts[$row]->getName;
        $relat = $this->emergencyContacts[$row]->getRelation;
        $num = $this->emergencyContacts[$row]->getPhone;
        $query = $query . "('$capid','$relat','$con','$num')";
        return Query($query, $ident, $page);
    }
    public function updateFields($ident, $page) {
        $query = "UPDATE SQUADRON_INFO.MEMBER 
    SET NAME_LAST='" . $this->name_last . "',
    NAME_FIRST='" . $this->name_first . "',
    DATE_OF_BIRTH=STR_TO_DATE('" . $this->DoB->format($phpDateFormat) . "','" . $sqlDateFormat . "'),
    DATE_JOINED=STR_TO_DATE('" . $this->Date_of_Join->format($phpDateFormat) . "'," . $sqlDateFormat . "),
    ACHIEVEMENT='" . $this->achievement . "',
    MEMBER_TYPE='" . $this->memberType . "',
    TEXTBOOK_SET='" . $this->text_set . "'
    WHERE CAPID='" . $this->capid . "'";
        return Query($query, $ident, $page);
    }
    public function approveFields($ident, $page) {
        echo "<tr><td><input type=\"checkbox\" name=\"approve[]\" value=\"" . $this->capid . "\"/></td>";
        echo "<td><input type=\"text\" size=\"1\" name=\"capid" . $this->capid . "\" value=\"" . $this->capid . "\"/></td>";
        echo "<td><input type=\"text\" size=\"1\" name=\"Lname" . $this->capid . "\" value=\"" . $this->name_last . "\"/></td>";
        echo "<td><input type=\"text\" size=\"1\" name=\"Fname" . $this->capid . "\" value=\"" . $this->name_first . "\"/></td>";
        echo "<td><select name=\"gender" . $this->capid . "\">";
        echo "<option value=\"M\" ";                   //drop down menu for gender
        if ($this->gender == "M") {                          //sets default to male if so
            echo "selected=\"yes\"";
        }
        echo ">male</option><option value=\"F\" ";
        if ($this->gender == "F")
            echo "selected=\"yes\"";
        echo ">female</option>";
        echo "</select></td><td>";                                  //end of drop down
        enterDate(false, "DoB" . $this->capid, $this->DoB);
        echo "</td><td>";
        mysql_select_db("SQUADRON_INFO");
        dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) FROM SQUADRON_INFO.ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "grade" . $this->capid, $ident, false, $this->achievement, $page);
        echo "</td><td>";
        dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM SQUADRON_INFO.MEMBERSHIP_TYPES", "member" . $this->capid, $ident, false, $this->memberType, $page);
        echo "</td><td>";
        dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM SQUADRON_INFO.TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL'", "text" . $this->capid, $ident, false, $this->text_set, $page);
        echo "</td><td>";
        dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM SQUADRON_INFO.CAP_UNIT", 'unit' . $this->capid, $ident, false, $this->unit->getCharter(), $page);
        echo "</td><td>";
        enterDate(false, "DoJ" . $this->capid, $this->Date_of_Join);
        echo "</td></tr>\n";
    }
    public function massUpdateFields(array $input) {
        if (array_key_exists("Fname" . $this->capid, $input)) {
            $this->name_first = cleanInputString($input["Fname" . $this->capid], 32, "First Name", $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists("Lname" . $this->capid, $input)) {
            $this->name_last = cleanInputString($input["Lname" . $this->capid], 32, "Last Name", $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists("monthDoB" . $this->capid, $input)) {
            $this->DoB = parse_date_input($_POST, "DoB" . $this->capid);
        } if (array_key_exists('capid' . $this->capid, $input)) {
            $this->capid = cleanInputInt($input['capid' . $this->capid], 6, 'capid', $_SERVER['SCRIPT_NAME']);
        } if (array_key_exists('gender' . $this->capid, $input)) {
            $this->gender = cleanInputString($input['gender' . $this->capid], 1, 'gender', $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists('grade' . $this->capid, $input)) {
            $this->achievement = cleanInputString($input['grade' . $this->capid], 5, "Achievement", $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists('member' . $this->capid, $input)) {
            $this->memberType = cleanInputString($input['member' . $this->capid], 1, 'Member type', $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists('text' . $this->capid, $input)) {
            $this->text_set = cleanInputString($input['text' . $this->capid], 5, 'Textbook set', $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists('unit' . $this->capid, $input)) {
            $this->unit = cleanInputString($input['unit' . $this->capid], 10, 'unit charter number', $_SERVER['SCRIPT_NAME'], false);
        } if (array_key_exists('monthDoJ' . $this->capid, $input)) {
            $this->Date_of_Join = parse_date_input($_POST, "DoJ" . $this->capid);
        }
    }
    public function saveUpdates($ident) {
    $query = "UPDATE SQUADRON_INFO.MEMBER
SET CAPID='" . $this->capid . "',
NAME_LAST='" . $this->name_last . "',
NAME_FIRST='" . $this->name_first . "',
GENDER='" . $this->gender . "',
DATE_OF_BIRTH='" . $this->DoB->format($this->phpToMysqlFormat) . "',
ACHIEVEMENT='" . $this->achievement . "',
MEMBER_TYPE='" . $this->memberType . "',
TEXTBOOK_SET='" . $this->text_set . "',
HOME_UNIT='" . $this->unit . "',
DATE_JOINED='" . $this->Date_of_Join->format($this->phpToMysqlFormat) . "',
APPROVED=TRUE";
    return Query($query, $ident, $_SERVER['SCRIPT_NAME']);
}
}
class unit {
    private $charter_num;
    private $region;
    private $wing;
    function __construct($charter_num, $page, $ident, $region = null, $wing = null, $page = null) {
        if ($region == null || $wing == null) {
            $this->charter_num = cleanInputString($charter_num, 10, "Unit charter Number", $page, false);
            $results = Query("SELECT REGION, WING FROM SQUADRON_INFO.CAP_UNIT WHERE CHARTER_NUM='$this->charter_num'", $ident, $page);
            $this->region = mysql_result($results, 0, "REGION");
            $this->wing = mysql_result($results, 0, "WING");
        } else {
            $this->charter_num = cleanInputString($charter_num, 10, "charter Number", $page, false);
            $this->region = cleanInputString($region, 3, "Region code", $page, false);
            $this->wing = cleanInputString($wing, 2, "Wing code", $page, false);
        }
    }
    function getCharter() {
        return $this->charter_num;
    }
    function insert_unit($ident, $page, $message) {
        return Query("INSERT INTO SQUADRON_INFO.CAP_UNIT (CHARTER_NUM,REGION,WING)
VALUES('" . $this->charter_num . "','" . $his->region . "','" . $this->wing . ")", $ident, $page, $message);
    }
}
class memberType {
    private $code;
    private $name;
    function __construct($code) {
        $this->code = cleanInputString($code, 1, "Member Type");
        $results = Query("SELECT MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES WHERE MEMBER_TYPE_CODE='$this->code'");
        $this->name = mysql_result($results, 0, "MEMBER_TYPE_NAME");
    }
    function getCode() {
        return $this->code;
    }
}
class contact {
    private $name;
    private $relation;
    private $phone;
    function __construct($name, $relation, $phone) {
        $this->name = cleanInputString($name, 32, "Contact Name");
        $this->relation = cleanInputString($relation, 2, "Contact's Relation");
        $this->phone = cleanInputString($phone, 12, "Contact phone num");
    }
    function getName() {
        return $this->name;
    }
    function getRelation() {
        return $this->relation;
    }
    function getPhone() {
        return $this->phone;
    }
    public function setName($name, $page) {
        $this->name = cleanInputString($name, 32, "Contact Name", $page, false);
    }
    public function setPhone($phone, $page) {
        $this->phone = cleanInputString($phone, 12, "Contact Phone Number", $page, false);
    }
    function setRelation($relation, $page) {
        $this->relation = cleanInputString($relation, 2, "relation", $page, false);
    }
}
class relationShip {
    private $name;
    private $code;
    function __construct($name, $page) {
        $this->name = cleanInputString($name, 20, "relationship name", $page, false);
        $this->code = substr($this->name, 0, 2);
    }
    function insertRelat($ident, $page, $message) {
        return Query("INSERT INTO SQUADRON_INFO.CONTACT_RELATIONS(RELATION_CODE, RELATION_NAME)
VALUES('" . $this->code . "','" . $this->name . "')", $ident, $page, $message);
    }
    function code_get() {
        return $this->code;
    }
}
class visitor {
    private $Fname;
    private $Lname;
    private $contName;
    private $contPhone;
    public $badInput;
    function __construct($Fname, $Lname, $page) {
        $this->Fname = cleanInputString($Fname, 50, "First Name", $page, false);
        $this->Lname = cleanInputString($Lname, 50, "Last Name", $page, false);
    }
    function getRestofFields($page) {
        newVisitor($page, $this->Fname, $this->Lname);
    }
    function setContName($contName, $page) {
        $this->contName = cleanInputString($contName, 50, "Emergency Contact Name", $page, false);
    }
    function setContPhone($contPhone, $page) {
        $this->contPhone = cleanInputString($contPhone, 12, "Emergency Contact Phone Number", $page, false);
    }
    function insert($ident, $page, $message = null) {
        return Query("INSERT INTO SQUADRON_INFO.NEW_MEMBER(NAME_LAST,NAME_FIRST,DATE_CAME,EMERGENCY_CONTACT_NAME,EMERGENCY_CONTACT_NUMBER)
VALUES(" . $this->Lname . "," . $this->Fname . ",CURDATE()," . $this->contName . "," . $this->contPhone . ")", $ident, $page, $message);
    }
}
class searched_member {
    private $member;
    private $percent_match;
    private $capid_match;
    public function __construct($found_capid,$search_capid,$search_name_first, $search_name_last,$ident) {
        $this->member = new member($found_capid,1, $_SERVER['SCRIPT_NAME'], $ident);
        $capid_match = $this->match_capid($search_capid);
        if($capid_match==100) {
            $this->capid_match = $capid_match;
            return;
        } else {
            $this->capid_match = $capid_match;
            $this->percent_match = $capid_match+$this->match_name_first($search_name_first)+$this->match_name_last($search_name_last);
        }
    }
    public function get_member() {
        return $this->member;
    }
    public function recalc_match($search_name_first, $search_name_last) {
        $old = $this->percent_match - $this->capid_match;
        $new = $this->match_name_first($search_name_first) +$this->match_name_last($search_name_last);
        if($new>$old) {
            $this->percent_match = $new + $this->capid_match;
        }
    }
    private function match_name_last($search) {
        $plain = preg_replace('#(^%|%$)#','',$search);  //strip off % at beginning and end
        $plain =preg_replace("#%#",' ',$plain);        //replace % with a space to mimmick spaces
        return ((strlen($plain))/(strlen($this->member->getName_Last())))*(100/3);
    }
    private function match_name_first($search) {
        $plain = preg_replace('#(^%|%$)#','', $search);  //strip off % at beginning and end
        $plain =preg_replace("#%#",' ',$plain);        //replace % with a space to mimmick spaces
        return ((strlen($plain))/(strlen($this->member->getName_first())))*(100/3);
    }
    private function match_capid($search) {
        $plain_capid = preg_replace("#%#",'',$search);
        $length=strlen($plain_capid);
        if($length==6) {                     //if it was a full capid say its 100% match
            $this->percent_match = 100;
            $relavence=100;
        } else {
            $relavence= ($length/6) * (100/3);  //else add as a function of length and weight as 1/3 of the percent
        }
        return $relavence;
    }
    public function get_match() {
        return $this->percent_match;
    }
    public function get_capid() {
        return $this->member->getCapid();
    }
    public function get_name() {
        return $this->member->getName_first()." ".$this->member->getName_Last();
    }
}
?>