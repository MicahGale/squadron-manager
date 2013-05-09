<?php
/**
 * The main API file for the project
 * 
 * All the APIs for the project.  This is never directly displayed, nor can,
 * but is included in the all the pages, and its functions are invoked by the 
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2013, Micah Gale
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
/*
 * **********************FOR v. .10*****************************
 * TODO enforce CAPR110-1 password policy
 * TODO ban terminated members
 * TODO debug promotion report and check accuracy, including time
 * TODO consider cadet oath and grooming standards
 * TODO add admin to add other users and grant privelidges
 * TODO create reports: emergency contact info, and eservices
 * TODO check promoboard halts on sign-up and promo report
 * TODO membership termination and deletion and edit members
 * TODO allow to change password
 * TODO finish populating db
 * TODO check old TODO tags
 * TODO edit member information
 * ***************************Debug/fix*******************************************
 * TODO fix member-side queries
 * TODO debug session hijacking resign-in keep post input
 * 
 * *******************FOR LATER******************************
 *TODO populate pictures
 *TODO create warning system
 *TODO have javascript resize function
 *TODO create page for units
 * TODO debug commanders and add chain of command
 * TODO  add scheduling
 * TODO add edit member and add picture
 * TODO regulations page and update regsupdater
 * TODO add statistics esp. for attendance
 * TODO use css  
 * TODO make colorblind safe options
 */
/*Unix specific functions
 * cleanUploadFile-path delimeter /
 * 
 */
/*
 *Function to change to port to different DBMS
 * CleanInputInt-sql escape function
 * CleanInputString -''
 * cleanInputDate   -''
 * cleanUploadFile -''
 * Connect
 * Query
 * Result
 * allResults
 * numRows
 * close
 * prepare_statement
 * bind
 * execute
 * close_stmt
 * auditLog - query
 * auditDump- query
 */
/**
 * @constant PHP_DATE_FORMAT 
 * 
 * The date format to universally display the date
 */
 define("PHP_DATE_FORMAT","d M y");     //Date formats to use, default php display date
 /**
  * how to format to insert into mysql
  */
 define("PHP_TO_MYSQL_FORMAT","Y-m-d");   
 define( "SQL_DATE_FORMAT", "%d-%m-%Y");  //php display except at mysql level
 /**
  * The format for inserting a complete date time into SQL
  */
 define("SQL_INSERT_DATE_TIME","o-m-d H:i:s");
 define("EVENT_CODE_DATE",'dMy');         //date for creating event codes
 define("CPFT_RUNNING_REQ",1);            //the amount of running events that must be passed
 define("CPFT_OTHER_REQ",2);             //the amount of non-running events that must be passed
 define('CSV_SAVE_PATH',"/var/upload/csv");  //the constant for where csv files go
 define('PROFILE_PATH',"/usr/share/www/profile");  //the path to the profile pictures stored outside document root
 define('NOTIF_PATH','/usr/share/www/notifications.csv');  //the csv that holds the notification information
 /**
  * The constant for how long to have an account in SQL time format
  */
 define("LOCK_TIME","00:30:00");
 /**
  * The maximum number of bad login attempts in account lockout time before the account is locked
  */
 define("MAX_LOGIN",8);
 /**
  * Stores and auditable event to the AUDIT_LOG table.
  * If we have the user's CAPID that will be stored along with the log
  * 
  * @param String $ip the IP address of the client
  * @param String $type - the type of event from the table INTRUSION_TYPE
  * @return String the date and time of the Event formatted for SQL
  */
function auditLog($ip, $type) {
    $time = date(SQL_INSERT_DATE_TIME);
    $ident= Connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf','localhost');
    mysqli_query($ident,"INSERT INTO AUDIT_LOG(TIME_OF_INTRUSION, INTRUSION_TYPE, PAGE,IP_ADDRESS)
        VALUES('$time','$type','".$_SERVER['SCRIPT_NAME']."','$ip')");
    close($ident);
    if(isset($_SESSION['member'])) {               //if this is an user session, attribute the user's capid to it
        auditDump($time,"user CAPID", $_SESSION['member']->getCapid());
    }
    return $time;
}
/**
 * Inserts pertinant information for auditable event into AUDIT_DUMP
 * 
 * @param String $time the time of the event returned from auditLog
 * @param String $fieldName the name of the value being stored, i.e. CAPID, input
 * @param String $fieldValue the actual value of the field. i.e. 123456
 */
function auditDump($time, $fieldName, $fieldValue) {
    $ident=connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf',"localhost");
    mysqli_query($ident,"INSERT INTO AUDIT_DUMP(TIME_OF_INTRUSION, FIELD_NAME, FIELD_VALUE)
        VALUES('$time','$fieldName','$fieldValue')");
    close($ident);
}
/**
 * Logs login attempts, successful or not to LOGIN_LOG.
 * @param Int $capid the USERs CAPID that the login was for
 * @param boolean $success True if they were able to login false if they're login failed
 */
function logLogin($capid, $success) {
    $capid= cleanInputInt($capid,6,'capid');
    $time = date(SQL_INSERT_DATE_TIME);
    $ident=connect( 'Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf','localhost');
    $ip = $_SERVER['REMOTE_ADDR'];
    Query("INSERT INTO LOGIN_LOG(TIME_LOGIN, CAPID, IP_ADDRESS, SUCEEDED)
                 VALUES('$time','$capid','$ip','$success')", $ident);
    close($ident);
}
/**
 * Checks if an account is locked from too many bad logins
 * Checks ACCOUNT_LOCKS and LOGIN_LOG
 * 
 * This will first if their is ACCOUNT_LOCK in place already from said table, and verifies the lock
 * is still in effect.  If there isn't one it checks the number of bad logins,and will place a block
 * Uses the constant MAX_LOGIN for the maximum number of logins in the time from LOCK_TIME
 * 
 * @param Int $capid the user's CAPID you are checking
 * @return boolean true if there is no lock false if there is a lock
 */
function checkAccountLocks($capid) {
    $capid=  cleanInputInt($capid,6,'CAPID');
    $ident = connect('ViewNext', 'oiu34wioejnkfvlkmse39ijfdokfdyuhjf','localhost');   //logon to view account locks
    $results = Query("SELECT VALID_UNTIL FROM ACCOUNT_LOCKS
                        WHERE CAPID='$capid'", $ident);  //get account locks
    if(numRows($results)>0) {                             //if account lock found
        $time = new dateTime(Result($results,0,"VALID_UNTIL"));
        if($time->diff(new DateTime)->format("%R")=="-") {         //compares current time to lock time if difference is - then not time
            return false;               //return that the account is locked
        } else {                  //if no longer valid just remove lock and allow
            $ident =connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf', 'localhost');
            Query("DELETE FROM ACCOUNT_LOCKS WHERE CAPID='$capid'", $ident);
            return true;              //unlocked and good to go
        }
    } else {              //check if enough attempts to lock account
        $query = "SELECT COUNT(*) FROM LOGIN_LOG
                    WHERE SUCEEDED=FALSE
                    AND FACTORED=TRUE
                    AND CAPID='$capid'
                    AND TIME_LOGIN >=(SUBTIME(NOW(),'".LOCK_TIME."'))";
        $results=Query($query,$ident);
        if(Result($results,0, "COUNT(*)")>=MAX_LOGIN) {             //if tried too many times then lock it
            $ident =connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf','localhost');
            Query("INSERT INTO ACCOUNT_LOCKS(CAPID, VALID_UNTIL)
                VALUES('$capid',ADDTIME(NOW(),'".LOCK_TIME."'))", $ident);
            return false;
        } else {                                //else says it's fine
            return true;
        }
    }
}
/**
 * Creates an input form for creating a new member
 * 
 * @param mysqli $identifier the Datebase connection
 * @param String $page the target page for the form
 * @param Int $capid the CAPID if specified to display as the defualt CAPID
 */
function newMember($identifier, $page,$capid=null) {                                              //if no members allow to create new member
    //displays table for input
    echo "<form action=\"$page\" method=\"post\">";
    //displays input fields
    echo "CAPID:<input type=\"text\" name=\"CAPID\" value=\"$capid\" size=\"1\"/><br>   
        Last Name:<input type=\"text\" name=\"Lname\" size=\"4\"/><br>
        First Name:<input type=\"text\" name=\"Fname\" size=\"4\"/><br>
        Gender:<select name=\"Gender\"><option value=\"M\">Male</option><option value=\"F\">Female</option></select><br>
        Date of Birth:\n";
    enterDate(true,'DoB');
    echo "<br>CAP Grade"; //SELECT A.ACHIEV_CODE, CONCAT(A.ACHIEV_CODE,'-',B.GRADE_NAME) FROM ACHIEVEMENT A JOIN SQAUDRON_INFO.GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY B.GRADE_NUM
    dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "achiev", $identifier, false);
    echo "<br>Member Type";
    dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES WHERE MEMBER_TYPE_CODE<>'A'", "member", $identifier, false);  //creates drop down menu for membership types
    echo "<br>Textbook Set";
    dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL'", 'text', $identifier, false);  //creates drop down menu for text sets
    echo "<br>Unit Charter Number:";
    dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM CAP_UNIT", 'unit', $identifier, true,'RMR-ID-073');  //creates drop down menu for text sets
    echo "<br>Date Joined CAP:";
    enterDate(true,'DoJ');
    echo "<br><br><strong>Also add at least One emergency Contact</strong>";
    newContact(FALSE, $identifier);
}
/**
 * Creates a form to input emergency contact information
 * 
 * @param Boolean $submit whether or not to have it's own form 
 * @param mysqli $identifier the database connection
 * @param String $page the target page if $submit is true
 */
function newContact($submit, $identifier, $page = null) {
    if ($submit) {
        echo"<form action=\"$page\" method=\"post\">";
    }
    echo "<table border=\"1\" cellspacing=\"1\"><tr>
            <th>Contact Name</th><th>Contact's Relation</th><th>Contact's Phone Number</th></tr>\n";
    $row = 0;
    while ($row < 5) {
        echo "<tr><td><input type=\"text\" name=\"ContName$row\" size=\"7\"/></td><td>";
        dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true);
        echo "</td><td><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
        $row++;
    }
    echo "</tr></table><br>";
    if ($submit) {
        echo"<input type=\"submit\" value=\"Add Emergency Contacts\"/></form>";
    }
}
/**
 *Creates a form to create a new Unit
 *  
 * @param mysqli $identifier the database connection
 * @param String $page the target page for the form
 */
function newUnit($identifier, $page) {
    echo "<br>Please enter the new unit's information below:<br>\n";
    echo"<form action=\"$page\" method=\"post\">\n";
    echo"Charter Number(i.e. RMR-ID-073)<input type=\"text\" name=\"charter\" size=\"5\"/>";
    echo "<br>\nRegion:";
    dropDownMenu("SELECT REGION_CODE,REGION_NAME FROM REGION", "region", $identifier, false);
    echo"<br>\nWing:";
    dropDownMenu("SELECT WING, WING_NAME FROM WING", "wing", $identifier, false);
    echo "<br>\n<input type=\"submit\" value=\"Add new Unit\"/></form>";
}
/**
 * Form to create a new Emergency Contact relationship
 * 
 * @param String $page the target page
 */
function newContactType($page) {
    echo"<br>Please enter the new type of Contact below\n";
    echo "<form action=\"$page\" method=\"post\">";
    echo "Contact Type: <input type=\"text\" name=\"contact\"/><br>\n";
    echo "<input type=\"submit\" value=\"add contact type\"/><br></form>";
}
/**
 * Creates a form for inputing a new visitor to our unit
 * 
 * @param String $page the target page
 * @param String $defaultFname the first name from the input to display by defualt
 * @param String $defaultLname the last name from input to display by default
 */
function newVisitor($page, $defaultFname = null, $defaultLname = null) {
    echo "<form action=\"$page\" method=\"post\">\n";
    echo "First Name:<input type=\"text\" name=\"Fname\" size=\"5\" default=\"$defaultFname\"/><br>\n";
    echo "Last Name:<input type=\"text\" name=\"Lname\" size=\"5\" default=\"$defaultLname\"/><br>\n";
    echo "<strong>Please Provide an Emergency Contact</strong><br>\n";
    echo "Emergency Contact Name: <input type=\"text\" name=\"ContName\" size=\"5\"/><br>\n";
    echo "Emergency Contact Phone Number:<input type=\"text\" name=\"ContPhone\" size=\"5\"/><br>\n";
    echo "<input type=\"submit\" value=\"Finish\"/></form>\n";
}
/**
 * Creates a drop down <select> menu from a database query
 * 
 * The first column needs to be the code for the variable to input to the server, column 2 is the text to display in the drop down
 * @param String $query the Query to run
 * @param String $name the name of the input for the form
 * @param mysqli $identifier the database connection
 * @param boolean $hasotherfield true to allow users to select an other value
 * @param String $default the code of the option to show by default
 * @param boolean $hasNoSelect allows the user to not put input
 */
function dropDownMenu($query, $name, $identifier, $hasotherfield=false, $default = null, $hasNoSelect=false) {      //drop down menu 1st field is code 2nd is name
    $results = Query($query, $identifier);                     //TODO include error handlin
    $row = 0;
    echo "<select name=\"$name\">";
    if($hasNoSelect==true) {                          //if has no select show empty drop down
        echo '<option selected="selected" value="null">-This input is optional-</option>';
    }
    while ($row < numRows($results)) {
        $code = Result($results, $row, 0);
        $names = Result($results, $row, 1);
        if ($default != null&&$code == $default) {
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
/**
 * Logs a database error, and displays an error message to the user
 * 
 * WARNING: for development displays the error code, and more information, this needs
 * to be removed for production servers
 * 
 * @param String $errorno the Mysql error 
 * @param String $error the error text from Mysql
 */
function reportDbError($errorno,$error) {
    $time = auditLog( $_SERVER['REMOTE_ADDR'], 'ER');
    auditDump($time, 'Error Code', $errorno);
    auditDump($time, 'Error Message', $error);
    echo"<br><strong>there was an error with processing the request</strong><br>
        Please give the following information to you Squadron's IT Officer(s)<br>
        <strong>error:</strong>\n";
    echo $errorno . " " .$error;
    echo "<br><strong>Time:</strong>$time\n";
    echo"<br><strong>Page:</strong>".$_SERVER['SCRIPT_NAME']."<br>\n";
    echo"<strong>IP:</strong>" . $_SERVER['REMOTE_ADDR'] . "<br>";
}
/**
 * Runs a SQL query, and handles the database errors
 * 
 * Use this function for all your queries, and not mysqli_query. This allows easy 
 * portability to other DBMS's
 * 
 * @param String $query the query to run
 * @param mysqli $ident the database connection
 * @param String $message a message to display on success
 * @return type for Select returns the mysqli_result, for UPdate, etc returns true on success false for failures
 */
function Query($query, mysqli $ident, $message = null) {         //kill $page sig on all queries
    $results = mysqli_query($ident, $query);
    if ($results == false) {
        reportDbError(mysqli_errno($ident),  mysqli_error($ident));
    } else if ($results == true) {
        echo $message;
    }
    return $results;
}
/**
 * Connects to a database.
 * 
 * Use this function allow easy portability to other DBMSs
 * 
 * @param String $username the username
 * @param String $password the password
 * @param String $server the server you are connecting to 
 * @param String $db the default database to use
 * @return mixed the mysqli connection on success false on failure
 */
function connect($username,$password,$server="localhost",$db="SQUADRON_INFO") {
    $connection=  mysqli_connect($server, $username, $password, $db);
    if(!$connection) {                         //if had error
        reportDbError(mysqli_connect_errno(), mysqli_connect_error());
        die;
    } else{
        return $connection;                    //else just give them the resource
    }
}
function Result(mysqli_result $result,$row,$field) {
    if(is_int($field)) {                          //if number was given number then get field name
        $temp=  mysqli_fetch_field_direct($result,$field);
        $field = $temp->name;  
    }
    $pos = strpos($field,".");                         //try to find . to get only column name
    if(is_int($pos)) {                     //if actually found it then take it off
        $field =substr($field,$pos+1);       //cut off preceeding . and text
    }
    if(mysqli_num_rows($result)==0) {
        return "unknown";
    } else {
        $success= mysqli_data_seek($result, $row);      //shift iterator
        if($success) {                               //if moved then get dat
            $row=mysqli_fetch_assoc($result);           //fetch row
            return $row[$field];
        } else {                                     //if failed say unknown
            return "unknown";
        }
    }
}
function allResults(mysqli_result $result) {
    $array=array();
    for($row=0;$row<mysqli_num_rows($result);$row++) {       //get all the rows and gett array
        $array[$row]=  mysqli_fetch_assoc($result);
    }
    return $array;
}
function numRows(mysqli_result $result) {
    if(!is_bool($result))                  //if is actually a result
        return mysqli_num_rows($result);
    else
        return 0;                //else return 0
}
function close(mysqli $ident) {
    return mysqli_close($ident);
}
function prepare_statement(mysqli $ident,$query) {
    $stmt= mysqli_stmt_init($ident);
    if(!mysqli_stmt_prepare($stmt, $query))
        reportDbError (mysqli_errno ($ident), mysqli_error ($ident));
    return $stmt;
}
function bind(mysqli_stmt $ident,$types, array $bind) {
    for($i=0;$i<count($bind);$i++) {
        $buffer[$i]=&$bind[$i];
    }
    $pass = array_merge(array($ident,$types), $buffer);
    call_user_func_array("mysqli_stmt_bind_param", $pass);
}
function execute(mysqli_stmt $ident) { 
    if(!($success=mysqli_stmt_execute($ident))) {                       //if there was an error with the execution
        reportDbError (mysqli_stmt_errno ($ident), mysqli_stmt_error($ident));
    } else {
        if(($result=mysqli_stmt_get_result($ident))!=false) {
            return $result;
        }
    }
    return $success;       //if no results then return the success
}
function close_stmt(mysqli_stmt $stmt) {
    mysqli_stmt_close($stmt);                 //closes the prepared statement
}
/**
 * CleanInputInt-cleans input number
 * 
 * This cleans input numbers against SQL injection, XSS, and remote Execution and file traversing.
 * It uses the mysqli_real_escape_string htmlspecialchars, and escapshellcmd to do this.
 * It also checks lenght, and parses it as a number to prevent other issues. Any issues and the event will be 
 * logged along with the sanatized form of the input
 * 
 * This 
 * @param String $input the raw Input
 * @param Int $length the absolute length the number must be 
 * @param String $fieldName the name of the input field used for logging
 * @return float The Input Number parsed and cleaned as a floating point 
 */
function cleanInputInt($input, $length, $fieldName) {
    $link = mysqli_connect();
    $clean = escapeshellcmd(htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8'));
    if (strlen($clean) > $length || !is_numeric($clean) || $clean != $input) {
        $time = auditLog( $_SERVER['REMOTE_ADDR'], "SI");
        auditDump($time, $fieldName, $clean);
        echo "<font color=\"red\">$fieldName is not a valid number it must be $length digits long.</font><br>";
        if (strlen($clean) >= $length || !is_numeric($clean)) {          //nulls if wrong type
            $clean = null;
        }
    }
    $clean = floatval($clean);                                            //cast it to int
    return $clean;
}
function cleanInputString($input, $length, $fieldName, $empty) {                      //clean and log numbers
    $link= mysqli_connect();
    $clean = escapeshellcmd(htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8'));
    if (strlen($clean) > $length || $clean != $input || $clean == "" || $clean == null) {
        if (strlen($clean) == 0&& $empty == false) {
            echo "<font color=\"red\"> $fieldName can not be empty</font><br>";
        } else if ($empty == false) {
            echo "<font color=\"red\"> $fieldName is not valid Maximum is: $length</font><br>";
        }
         $time = auditLog( $_SERVER['REMOTE_ADDR'], 'SI');
        auditDump($time, "$fieldName", $clean);
        $badInput = true;
        if (strlen($clean) > $length) {
            $clean = null;
        }
    }
    return $clean;
}
function cleanInputDate($input, $regex, $length, $fieldName) {                      //clean and log numbers
    $link = mysqli_connect();
    $clean = escapeshellarg(htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8'));
    if (strlen($clean) > $length || $clean != $input || (preg_match($regex, $clean) != 1)) {
        echo "<font color=\"red\"> $fieldName is not a valid date.</font><br>";
        $time = auditLog($_SERVER['SCRIPT_NAME'], $_SERVER['REMOTE_ADDR'], 'SI');
        auditDump($time, $fieldName, $input);
        $badInput = true;
        if (strlen($clean) > $length || (preg_match($regex, $clean)) || strtotime($clean) == false) {
            $clean = null;
        }
    }
    return $clean;
}
/**
 * A function to verify uploaded files. 
 * 
 * This verifies the proper data type, and that file was uploaded.
 *   Checks for malicious code. Saves the file in the specified 
 * directory with a new name, a md5hash and the timestamp. It also will handle
 * any upload errors 
 * 
 * @param String $index the index for the file in the $_FILES array
 * @param int $maxSize the maximum size of the file in bytes
 * @param String $saveDir the directory to save the file in
 * @param String $MIME_TYPE the mime type of the accepted file type
 * @return String the URL to the saved file or false if there was an error or attack
 */
function cleanUploadFile($index, $maxSize, $saveDir,$MIME_TYPE) {
    $file = $_FILES[$index];         //get a buffer var
    $time=  auditLog($_SERVER['REMOTE_ADDR'], 'UF');    //log the file upload
    $buffer=explode(".",$file['name']);
    $ext=  end($buffer);   //get the extension
    $buffer=explode('/',$MIME_TYPE);
    $allowed_ext=end($buffer);  //get the allowed type
    $hash= md5_file($file['tmp_name']);
    $now=new DateTime();
    $locat=$saveDir.'/'.$hash.'_'.$now->format(EVENT_CODE_DATE).".".$ext;
    if(!move_uploaded_file($file['tmp_name'],$locat)) { //try to move the file to the location                      // if wasn't uploade
        $error=  auditLog($_SERVER['REMOTE_ADDR'],'FA');
        auditDump($error, 'reffer to', $time);
        auditDump($error,'type','not uploaded');
        auditDump($error,'File path', $file['tmp_name']);
        echo '<p style="color:red">File must be uploaded.</p>';
        return false;
    }
    $link=  mysqli_connect();          //store the location of the file 
    auditDump($time,"location", mysqli_escape_string($link,$locat));
    if($file['error']!=0) {        //if there was an error with the upload
        $error=  auditLog($_SERVER['REMOTE_ADDR'],'FR');
        auditDump($error, 'reffer to', $time);
        auditDump($error, 'error code', $file['error']);  
        echo '<p style="color:red">There was an error uploading your file</p>';
        return false;
    }
    if($file['size']>$maxSize) {
        $error=  auditLog($_SERVER['REMOTE_ADDR'],'FM');
        auditDump($error, "Reffer to:", $time);
        auditDump($error, "actual Size", $file['size']);
        auditDump($error, "field", $index);
        auditDump($error, 'name', $file['name']);
        echo '<p style="color:red">Maximum upload Size is:'.($maxSize/1024)."kb. Upload was: ".($file['size']/1024)."kb</p>";
        return false;
    }
    $finfo=  finfo_open(FILEINFO_MIME_TYPE);   //gets the file type by header bits
    $header=  finfo_file($finfo, $locat);
    finfo_close($finfo);
    if($header==="text/plain"&& strpos($MIME_TYPE,'text/')!==false) {  //if the upload was text, and it was supposed to be a text derivative
        $header=$MIME_TYPE;
    }
    if($file['type']!=$MIME_TYPE||($allowed_ext!='*'&&$ext!=$allowed_ext)||$header!=$MIME_TYPE) {  //checks that the extension is the proper type and that the actual data is the right type
        $error=  auditLog($_SERVER['REMOTE_ADDR'],'FT');
        auditDump($error,'reffer to',$time);
        auditDump($error,'extension', $ext);
        echo '<p style="color:red">Upload must be: '.$MIME_TYPE." file type</p>";
        return false;
    }
    return $locat;
}
function session_secure_start($capid=null) {
    session_start();                     //starts the session
    if (!isset($_SESSION['ip_addr'])) {       //if starting the session
        if ($_SERVER['SCRIPT_NAME'] == '/login/index.php') {        //if at the login page
            $_SESSION['ip_addr'] = $_SERVER['REMOTE_ADDR'];    //store the ip address to prevent hijacking from other "ips"
            $_SESSION['last_page'] = $_SERVER['SCRIPT_NAME'];  //store what the current page is
            $_SESSION['predicted'] = array();
            $_SESSION['resignin'] = true;                             //assume good no need to kill session
            $_SESSION['intruded']=false;
            $ident =connect('ViewNext', 'oiu34wioejnkfvlkmse39ijfdokfdyuhjf','localhost');
            if($capid==null) {
                    session_predict_path($ident);
            } else {
                session_predict_path($ident,$capid);
            }
            close($ident);
        } else {                             //force redirect to login page if not at index
            header("refresh:0;url=/login");       //force the user to redirect out 
            exit;                                                              //ends current script
        }
    } else {                 //if session is already started check for malicious intent
        $hijacked = false;
        if (($_SESSION['ip_addr'] == $_SERVER['REMOTE_ADDR'])) { //checks if from the correct ip and not spoofing the http refere
            if (!in_array($_SERVER['SCRIPT_NAME'], $_SESSION['predicted'])) {  //if not where it was supposed to go
                $hijacked = true;
            }
        } else {                        // if not the right ip is hijacked
            $hijacked = true;
        }
        if (!$_SESSION['resignin']) {   //if didn't resign in then kill the session 
            $time = auditLog( $_SERVER['REMOTE_ADDR'], 'KS');
            auditDump($time, "user", $_SESSION['member']->getcapid());
            session_destroy();
            header("refresh:0;url=/");       //destroy the session and then redirect
            exit;
        }
        if ($hijacked) {                     //redirect to reprompt for user info
            $_SESSION['resign'] = false;     //says needs to resignin if they don't will kill it next time
            $_SESSION['intruded'] = true;        //says someone has intruded so all need to be reverified
            unset($_SESSION['password']);  //clear password so can't connect to database at all until reverified
            $time = auditLog( $_SERVER['REMOTE_ADDR'], 'SH');  //log it
            if(isset($_SESSION['member']))
                auditDump($time, "USER", $_SESSION['member']->getcapid());                //dump username
            else
                auditDump ($time, "USER",$capid);
            session_resign_in(false);            //makes resign in
        } else {              //if no foul play set up info for next request
            $_SESSION['last_page'] = $_SERVER['SCRIPT_NAME'];       //allocate last page
            $ident = connect( 'ViewNext', 'oiu34wioejnkfvlkmse39ijfdokfdyuhjf','localhost');
            if($capid!=null) {
                session_predict_path($ident,$capid);
            }else {
                session_predict_path($ident);
            }
            close($ident);
            session_regenerate_id();                                //if all good regenerate id lengthen session
            if ($_SESSION['intruded']) {                       //if someone has tried to intrude make resign in
                session_resign_in(true);               //has them resign in and keep the post stuff
            }
        }
    }
}
function session_predict_path($ident,$capid=null,$page=null) {     //creates an array of pages that the user may visit next    
    $results=array();
    if($page==null)                          //if page isn't specified use current page
        $path = $_SERVER['SCRIPT_NAME'];
    else 
        $path=$page;
    $path = substr($path, strpos($path, "/", 1) + 1);            //cuts off leading /login/ offset by 1 to ignore first /
    $query = "SELECT NEXT_URL FROM NEXT_VISIT
        WHERE LAST_URL='" . $path . "'";                           //query to find next 
    $result = allResults(Query($query, $ident));
    $size = count($result);
    for ($i = 0; $i < $size; $i++) {
        array_push($results, "/login/" .$result[$i]['NEXT_URL']);
    }
    if(!isset($_SESSION['home'])) {
        if($capid!=null) {
            $id = $capid;
        } else {
            $id = $_SESSION['member']->getcapid();
        }
        $query = "SELECT B.URL 
                    FROM TASK_TYPE A JOIN
                    TASKS B ON
                    A.TYPE_CODE=B.TYPE_CODE
                    WHERE B.TASK_CODE IN (
                    SELECT A.TASK_CODE FROM STAFF_PERMISSIONS A
                        LEFT JOIN CHAIN_OF_COMMAND B ON B.STAFF_CODE=A.STAFF_CODE
                        LEFT JOIN STAFF_POSITIONS_HELD C ON B.POS_CODE=C.STAFF_POSITION
                        WHERE  A.STAFF_CODE = 'AL'
                        OR C.CAPID='$id')
                        OR B.TASK_CODE IN (
                        SELECT TASK_CODE FROM SPECIAL_PERMISSION
                        WHERE CAPID='$id')";                           //repeats except now looking for urls that are permanently allowed
        $pure = Query($query, $ident);
        $size = numRows($pure);
        $result=  allResults($pure);
        for ($i = 0; $i < $size; $i++) {
            array_push($results, "/login/".$result[$i]["URL"]);
        }
    } else {
        for($i=0;$i<count($_SESSION['home']);$i++) {           //reparse array
            array_push($results,"/login/".$_SESSION["home"][$i]["URL"]);
        }
    }
    array_push($results,"/login/home.php");                 //add the home page on
    if($page!=null) {
        return $results;                                      //if page is given give results now
    } else {
        $_SESSION['predicted']=$results;                   //else put in session array 
    }
    array_push($_SESSION['predicted'], $_SERVER['SCRIPT_NAME']);  //push current page onto predicted
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
    if(!isset($input['Date'.$append]))
            return null;
    if($input['Date'.$append]==""||$input['Year'.$append]=="")
            return null;
    $month = cleanInputString($input["month" . $append], 2, "month", false);
    $day = cleanInputString($input["Date" . $append], 2, "Date", false);
    $year = cleanInputInt($input["Year" . $append], 4, "Year");
    try {
        $buffer = new DateTime($day . "-" . $month . "-" . $year);
    } catch (exception $e) {
        $time = auditLog( $_SERVER['REMOTE_ADDR'], "EX");
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
function display_event_search_in($ident){   //if doesn't have an event selected then allow them to search for it
    ?>
<font size="6">Search for An event</font><br><br>
<form method="post"> Select a Date:
    <?php
    enterDate(true);
    echo "<br>\n Select an Event Type:";
    dropDownMenu("SELECT EVENT_TYPE_CODE, EVENT_TYPE_NAME FROM EVENT_TYPES", "type", $ident,false,null,true);
    echo "<br>\nOr select an Event Location: ";
    dropDownMenu("SELECT LOCAT_CODE, LOCAT_NAME FROM EVENT_LOCATION", "location", $ident,false,null,true);
    ?>
    <br><input type="submit" value="search"/>
</form>
<?php
}
function searchEvent($ident,$callable,$link="/login/attendance/event.php"){      //if didn't provide complete then search
    ?>
<table border="1" cellpadding="0"><tr><th>Event Date</th><th>Event Type</th><th>Event Location</th></tr>
    <?php
    $query="SELECT A.EVENT_CODE, A.EVENT_DATE, B.EVENT_TYPE_NAME, C.LOCAT_NAME
        FROM EVENT A JOIN EVENT_TYPES B ON A.EVENT_TYPE=B.EVENT_TYPE_CODE
        LEFT JOIN EVENT_LOCATION C ON A.LOCATION=C.LOCAT_CODE";
    $isFirst = true;
    if(isset($_POST['type'])&&$_POST['type']!="null") {                                           //if type provided tak onto query
        $type=  cleanInputString($_POST['type'],2, "Event Type",false);
        $query.=" WHERE A.EVENT_TYPE='$type'";
        $isFirst=false;
    }
    if(isset($_POST['day'])) {                              //if date given add it to 
        $date=  parse_date_input($_POST);
        if($isFirst)
            $query.=" WHERE "; 
        else
            $query.=" AND ";
        $query.=" A.EVENT_DATE='".$date->format(PHP_TO_MYSQL_FORMAT)."'";
        $isFirst=false;
    }
    if(isset($_POST['location'])&&$_POST['location']!=null) {
        if($isFirst)
            $query.=" WHERE ";
        else
            $query.=" AND ";
        $query.=" A.LOCATION='".cleanInputString($_POST['location'],5,"location",false)."'";
    }
    $result = allResults(Query($query, $ident));
    $size=count($result);
    if($size==1) {
        call_user_func($callable,$result[0]["EVENT_CODE"]);
    } else {
        for($i=0;$i<$size;$i++) {
            echo "<tr><td>";
            echo '<a href="'.$link.'?eCode='.$result[$i]['EVENT_CODE'].'">';
            $date=new DateTime($result[$i]['EVENT_DATE']);
            echo $date->format(PHP_DATE_FORMAT)."</a></td><td>";
            echo $result[$i]['EVENT_TYPE_NAME']."</td>";
            echo "<td>".$result[$i]['LOCAT_NAME']."</td></tr>\n";
        }
    }
    ?>
     </table>
    <?php
}
function getEventPromo($ident,$capid) {
    $query ="SELECT A.ACHIEVEMENT, A.DATE_PROMOTED
            FROM PROMOTION_RECORD A JOIN ACHIEVEMENT B
            ON A.ACHIEVEMENT=B.ACHIEV_CODE
            WHERE CAPID='$capid'
            ORDER BY B.ACHIEV_NUM";
    $promotions= allResults(Query($query, $ident));
    $query="SELECT B.EVENT_DATE FROM ATTENDANCE A
            JOIN EVENT B ON A.EVENT_CODE=B.EVENT_DATE
            WHERE A.CAPID='$capid'
            AND B.EVENT_TYPE<>'M'
            AND B.EVENT_DATE BETWEEN ? AND ?";
    $activ=  prepare_statement($ident, $query);
    $query ="SELECT B.EVENT_DATE, C.SUBEVENT_CODE 
        FROM SUBEVENT C, ATTENDANCE A
        JOIN EVENT B ON A.EVENT_CODE=B.EVENT_CODE
        WHERE C.PARENT_EVENT_CODE=A.EVENT_CODE
        AND CAPID='$capid'
        AND C.SUBEVENT_CODE IN (
        SELECT TYPE_CODE FROM REQUIREMENT_TYPE
        WHERE IS_SUBEVENT=TRUE)
        AND B.EVENT_DATE BETWEEN ? AND ?
        ORDER BY C.SUBEVENT_CODE";
    $subevent= prepare_statement($ident, $query);
    $results=array();
    if(count($promotions)>0) {
        for($i=0;$i<count($promotions)+1;$i++) {
            if($i==0) {                            //if at 0 so the first one try dec 1,1941-first promo
                bind($activ,"ss", array("1941-12-1",$promotions[$i]['DATE_PROMOTED']));
                bind($subevent,"ss",array("1941-12-1",$promotions[$i]['DATE_PROMOTED']));
                $promoFor=$promotions[$i]['ACHIEVEMENT'];
            } else if($i<count($promotions)) {          //if is less then the count so in bounds then bind by 2 promos
                bind($activ,"ss",array($promotions[$i-1]['DATE_PROMOTED'],$promotions[$i]['DATE_PROMOTED']));
                bind($subevent,"ss",array($promotions[$i-1]['DATE_PROMOTED'],$promotions[$i]['DATE_PROMOTED']));
                $promoFor=$promotions[$i]['ACHIEVEMENT'];
            } else {                                                 //if hit top then try between last promo and now
                bind($activ,'ss',array($promotions[$i-1]['DATE_PROMOTED'],'curdate()'));
                bind($subevent,'ss',array($promotions[$i-1]['DATE_PROMOTED'],'curdate()'));
                $query='SELECT NEXT_ACHIEV FROM ACHIEVEMENT 
                    WHERE ACHIEV_CODE=\''.$promotions[$i-1]['ACHIEVEMENT']."'";
                $promoFor=Result(Query($query, $ident),0,'NEXT_ACHIEV');
            }
            $activity = allResults(execute($activ));                  //execute the prepared statements and get results
            $subs = allResults(execute($subevent));
            $results[$promoFor]=array();            //create an array for the current promotion
            if(count($activity)>0) //if had activity for promo then show it
                $results[$promoFor]['AC']=new DateTime($activity[0]['EVENT_DATE']);  //shown
            for($j=0;$j<count($subs);$j++) {         //parse subevent results
                $results[$promoFor][$subs[$j]['SUBEVENT_CODE']]=new DateTime($subs[$j]['EVENT_DATE']);//ORGANIZE INTO ARRAY BY SUB_CODE AND STORE DATE
            }
        }
    }
    close_stmt($activ);
    close_stmt($subevent);
    return $results;
}
function checkEventPromo(array $results, $achiev,$code) {        //checks if the event exists for such promo
    if(isset($results[$achiev][$code]))        //if isset then return the date
        return $results[$achiev][$code];
    else 
        return false;                        //otherwise assume not and return false
}
function specialPromoRequire($ident) {
    $results=array('AC');
    $query='SELECT TYPE_CODE FROM REQUIREMENT_TYPE
        WHERE IS_SUBEVENT=TRUE';
    $result =  allResults(Query($query, $ident));
    for($i=0;$i<count($result);$i++) {            //get all the results
        array_push($results,$result[$i]['TYPE_CODE']);
    }
    return $results;
}
function promotionAprove($ident,$memberType) {
    $query="SELECT A.CAPID, A.ACHIEV_CODE, A.APPROVED, CONCAT(D.GRADE_NAME,' - ',C.ACHIEV_NAME) AS NAME
        FROM ACHIEVEMENT C, GRADE D, PROMOTION_SIGN_UP A
        JOIN MEMBER B ON A.CAPID=B.CAPID
        WHERE B.MEMBER_TYPE='$memberType'
        AND A.ACHIEV_CODE=C.ACHIEV_CODE
        AND C.GRADE=D.GRADE_ABREV";  //get everyone who wants to promote that is the selected member type
    $results = allResults(Query($query, $ident));  //get this stuff
    $signUps=array();
    for($i=0;$i<count($results);$i++) {         //parse it, and objectify
        //0=>member requesting 1=>requesting for
        $signUps[$i]= array(new member($results[$i]['CAPID'],3, $ident),$results[$i]['ACHIEV_CODE']);
        $signUps[$i][2]=$signUps[$i][0]->getPromotionInfo($signUps[$i][1], $ident,$results[$i]['NAME']);       //get the promo info and number incomplete
        $signUps[$i][3]=$results[$i]['APPROVED'];                   //store if they have been approved
    }
    usort($signUps,'compareSignUp');  //reorder array based on who is most incomplete list them first
    $results=null;  //delete useless array that results, reparsed, is now useless
    unset($results); //''
    $query ="SELECT TYPE_CODE, TYPE_NAME FROM
        REQUIREMENT_TYPE WHERE (MEMBER_TYPE='$memberType' OR MEMBER_TYPE IS NULL)
        AND TYPE_CODE IN ( SELECT REQUIREMENT_TYPE FROM PROMOTION_REQUIREMENT 
        WHERE ACHIEV_CODE IN(SELECT ACHIEV_CODE FROM PROMOTION_SIGN_UP))ORDER BY TYPE_NAME"; //get the requirements for the header
    $header=  allResults(Query($query, $ident));    //get headers
    ?>
    <table class="promotion">
        <tr><th class="promotion">Member</th><th class="promotion">Promotion to:</th>
    <?php
    for($i=0;$i<count($header);$i++) {  //displays the headers
        echo "<th class=\"promotion\">".$header[$i]['TYPE_NAME']."</th>";  //show header for each thinger
    }
    echo "<th class=\"promotion\">Approved</th></tr>\n";   //display approval header
    for($i=0;$i<count($signUps);$i++) {  //cycle trhough member sign-up
        echo "<tr>";
        $signUps[$i][0]->displayPromoRequest($header,true,true,$signUps[$i][3]);
    }
    $_SESSION['signUps']=$signUps;
    $_SESSION['header']=$header;
    ?>
    </table>
    <?php
}
function compareSignUp($a,$b) {    //compares the signups based on the number of incompletes and inprogress
    $aCount=$a[2][0]+$a[2][1]*0.5;
    $bCount=$b[2][0]+$b[2][1]*0.5;
    if($aCount==$bCount)
        return 0;
    return ($aCount < $bCount) ? 1 : -1;
}
/**
 * Creates an input field to input promotion score, and date
 * 
 * Displays input tags to get the input for entering a promotion requirement
 * passage.  This only creates the input, and does not parse or handle the input
 * 
 * @param type $capid The capid of who it is
 * @param type $type the requirement type this is for
 * @param DateTime $date The date passed if any
 * @param type $percentage the percentage stored if already passed
 * @param type $achiev  The achievement this is for
 * @return returns whether or not more needs to be displayed.
 */
function promoRequireInput($capid, $type, DateTime $date= null,$percentage=null, $achiev=null) {
    $append = $capid.$type.$achiev;
    if(in_array($type, array('LT','AE','DT'))) {
        if(is_numeric($percentage)) {   //if percent is a decimal change to percent
            $display =  round($percentage*100,2)."%";
        } else{
            $display = $percentage;
        }
        echo '%:<input type="text" size="1" maxlength="10" name="percentage'.$append.'" value="'.$display.'"/>';
    } if(!in_array($type,array('PB','PT'))) {
        enterDate(false, $append, $date);
        return false;
    } else {
        if($type=="PT") {      //if was pt test link to page for pt test
            echo '<a href="/login/testing/PTtest.php?capid='.$capid.'&achiev='.$achiev.'" target="_blank">enter PT test</a>';
        } else {        //if was promo board give link
        ?>
            <a href="/login/testing/promoBoard.php" target="_blank">enter Promotion Board</a>
        <?php
        }
        return true;
    } 
}
/**
 * Parse the input from the promotion sign-up and insert it
 * 
 * Inserts the promotion information into                                                                           qqqqq the SQL database.
 * @param mysqli $ident The resource for a SQL connection
 * @param array $input the input array to be inserted 
 * @return Void
 */
function parsePromoInput(mysqli $ident,array $input) {
    $approve=  prepare_statement($ident, "UPDATE PROMOTION_SIGN_UP SET APPROVED=?
        WHERE CAPID=? AND ACHIEV_CODE=?");  //create a prepared statement to approve one 
    $insert =  prepare_statement($ident,"INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET,PASSED_DATE,PERCENTAGE)
        VALUES(?,?,?,?,?,?)");         //create prepared statement to insert requirements
    $update = prepare_statement($ident,"UPDATE REQUIREMENTS_PASSED
        SET PASSED_DATE=?, PERCENTAGE=?
        WHERE CAPID=? AND ACHIEV_CODE=? AND REQUIREMENT_TYPE=?");
    $deleteTest =  prepare_statement($ident,"DELETE FROM TESTING_SIGN_UP
        WHERE CAPID=? AND REQUIRE_TYPE=?");
    $signUps=$_SESSION['signUps'];
    $header=$_SESSION['header'];
    for($i=0;$i<count($signUps);$i++) {       //cycle through old sign-ups
        $current=$signUps[$i];
        $capid=$current[0]->getCapid();
        $current[0]->parsePromoEdit($insert,$update,$deleteTest,$header,$input);   //parse each member's thing
        if(isset($input[$capid])) {   //aprove it
            $execute=true;
            if($input[$capid]=="yes") {          //if approved bind the vars
                if($current[0]->checkPassing()) {
                    bind($approve,"iis", array(1,$capid,$current[1]));  //aprove it
                } else {
                    echo '<p class="F">'.$current[0]->title()." Must pass all requirements to pass</p>";
                    $execute=false;
                }
            } else {
                bind($approve,"iis",array(0,$capid,$current[1])); //disprove it
            }
            if($execute)
                execute($approve);       //aprove it or disprove it!
        }
    }
    $_SESSION['signUps']=null;
    $_SESSION['header']=null;
    unset($_SESSION['signUps'],$_SESSION['header']);
    close_stmt($approve);
    close_stmt($insert);
    close_stmt($update);
    close_stmt($deleteTest);
}
/**
 * Parses percentage input
 * 
 * @param type $append the appended stuff to post array
 * @param array $inputs the post array
 * @param type $passing the passing percentage as a decimal
 * @return null|boolean|float returns null if no input, false if incorrect, and the percentage as a float
 */
function parsePercent($append, array $inputs, $passing) {
    if(isset($inputs['percentage'.$append])&&$inputs['percentage'.$append])
        $input = $inputs['percentage'.$append];
    else 
        return null;
    if($input=="") 
        return null;
    $input= str_replace("%","", $input);      //strips out percent signs
    if(strpos($input,"/")==false) {          //if there is no / assume decimal or percent
        $percent=  cleanInputInt($input,5,"percentage".$append); //clean and parse as num
        if($percent>1) {         //if was a percent i.e. >1 and a big num
            $percent = $percent/100;
        }  //else assume is decimal and is all good
    } else {
       $input =  cleanInputDate($input,"#^[0-9]+/[0-9]+$#",strlen($input),"percentage$append");
       $input = explode("/", $input);   //split into numerator and denominator
       $numerator = cleanInputInt($input[0],3,"numerator$append");    //take the numerator from first thing
       $denominator = cleanInputInt($input[1],3, 'denominator'.$append);  //take the denom from second position
       $percent=$numerator/$denominator;
    }
    if($percent>1||$percent<$passing) { //check if the percent is legit
        if($percent>1) {  //if over 100% yell at the user
            echo '<font color="red">Cannot be over 100%</font>';
        } else {
            echo '<font color="red">Did not pass, passing score is:';
            echo round($passing*100,2)."%</font>";
        }
        return false;
    } else {
        return round($percent,2);
    }
}
/**
 *  Checks if the tester passed the CPFT
 * 
 * @param array $requirements an array from retrieveCPFTrequire 
 * @param array $actual an array of the actual scores
 * @param ident  the database connection
 * @return true iff they passed, false if otherwise
 */
function verifyCPFT($ident, array $requirements, array $actual) {
    $query ="SELECT TEST_CODE FROM CPFT_TEST_TYPES WHERE IS_RUNNING=TRUE";  //gets the tests that are running
    $running =  allResults(Query($query, $ident));
    $query = "SELECT TEST_CODE FROM CPFT_TEST_TYPES WHERE IS_RUNNING=FALSE"; //gets non-running events
    $non_running=  allResults(Query($query, $ident));
    $counter=0;
    $running_passed=false;
    for($i=0;$i<count($running);$i++) {          //check if passed running element
        $buffer=$running[$i]['TEST_CODE'];
        if($buffer=='MR'&&!is_numeric($actual[$buffer])) {
            $actual[$buffer]=  parseMinutes($actual[$buffer]);   //if it's a mile rule then convert it to decimal
        }
        if(isset($actual[$buffer],$requirements[$buffer])&&$actual[$buffer]>=$requirements[$buffer])
            $counter++;
        if($buffer=='RS'&&isset($actual[$buffer],$requirements[$buffer])&&$actual[$buffer]<=$requirements[$buffer])
            $counter++;
        if($counter>=CPFT_RUNNING_REQ) {          //if passed the running req say so then break
            $running_passed=true;
            break;
        }
    }
    $counter=0;
    for($i=0;$i<count($non_running);$i++) {         //check non-running events
        $buffer=$non_running[$i]['TEST_CODE'];
        if(isset($actual[$buffer],$requirements[$buffer])&&$actual[$buffer]>=$requirements[$buffer]) {
            $counter++;
        }
        if($counter>=CPFT_OTHER_REQ) {
            return true;
        }
    }
    return false;
}
/**
 * Converts decimal form of minutes to mm:ss
 * 
 * @param Float $input the decimal form of the minutes
 * @return String the minutes in mm:ss
 */
function minutesFromDecimal($input) {
    $minutes=(int)($input);
    $seconds=round(($input-$minutes)*60);  //create the seconds
    return $minutes.":".$seconds;
}
/**
 * Parses mm:ss as a decimal of minutes
 * 
 * @param type $input
 * @return float the $input as a decimal for of the time
 */
function parseMinutes($input) {
    $exploded=  explode(":", $input);
    $minute=$exploded[0];
    $seconds=$exploded[1];
    return $minute+$seconds/60;
}
class member {
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
    private $date_terminated;
    public $badInput;
    private $picture_link;
    private $isEmpty = false;     //member doesn't exist    
    private $initLevel;
    private $promoRecord=array();
    public function __construct($capid, $level, $ident, $name_last = null, $name_first = null, $gender = null, dateTime $DoB = null, $memberType = null, $achievement = null, $text_set = null, $unit = null, DateTime $Date_of_Join = null) {
        $this->capid = cleanInputInt($capid, 6, "CAPID");
        if ($level == -1) {             //levels -1= all from input 0=capid 1=capid+name+gender+achievement 2=1+text+member_type+picture 3=2+dates 4=3+emergency+unit         
            $this->badInput = false;
            $this->capid = $capid;
            $this->name_last = $name_last;
            $this->name_first = $name_first;
            $this->gender = $gender;
            $this->DoB = $DoB;
            $this->memberType = new memberType(cleanInputString($memberType, 1, "Member Type"));
            $this->achievement = $achievement;
            $this->text_set = $text_set;
            $this->unit = new unit(cleanInputString($unit, 10, "unit"));
            $this->Date_of_Join = $Date_of_Join;
            $this->cleanFields();
            $this->initLevel();
        } else {
            $this->init($level, $ident);
        }
    }
    public function cleanFields() {
        $this->capid = cleanInputInt($this->capid, 6, 'CAPID');
        $this->name_first = cleanInputString($this->name_first, 32, "First Name", false);
        $this->name_last = cleanInputString($this->name_last, 32, "Last Name", false);
        $this->gender = cleanInputString($this->gender, 1, "Gender", false);
        $this->achievement = cleanInputString($this->achievement, 5, "achievement", false);
        $this->text_set = cleanInputString($this->text_set, 5, "Textbook set", false);
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
        $query = "INSERT INTO MEMBER (CAPID,NAME_LAST,NAME_FIRST,GENDER,DATE_OF_BIRTH,ACHIEVEMENT,MEMBER_TYPE,TEXTBOOK_SET,HOME_UNIT,DATE_JOINED)
            VALUES('$this->capid','$this->name_last','$this->name_first','$this->gender',STR_TO_DATE('" . date(PHP_DATE_FORMAT, $this->DoB) . "','" . $sqlDateFormat . "'),'$this->achievement','" . $this->memberType->getCode . "','$this->textset','" . $this->unit->getCharter() . "',STR_TO_DATE('" . date(PHP_DATE_FORMAT, $this->DoJ) . "','" . SQL_DATE_FORMAT . "'))";
        return Query($query, $ident);
    }
    public function insertEmergency($ident) {
        $stmt = prepare_statement($ident, "INSERT INTO EMERGENCY_CONTACT (CAPID,RELATION,CONTACT_NAME,CONTACT_NUMBER) 
            VALUES('".$this->capid."',?,?)");
        for ($row=0;$row < count($this->emergencyContacts);$row++) {
            $con = $this->emergencyContacts[$row]->getName;
            $relat = $this->emergencyContacts[$row]->getRelation;
            $num = $this->emergencyContacts[$row]->getPhone;
            bind($stmt,"sss",$relat,$con,$num);
            execute($stmt);
        }
    }
    public function sign_in($ident, $message, $event_code = null) {
        if ($event_code == null) {                         //assume current event
            return Query("INSERT INTO ATTENDANCE (CAPID, EVENT_CODE)
                SELECT '" . $this->capid . "',EVENT_CODE FROM EVENT WHER IS_CURRENT=TRUE", $ident, $message);
        } else {              //else use provided event
            return Query("INSERT INTO ATTENDANCE(CAPID,EVENT_CODE)
                VALUES('" . $this->capid . "','$event_code')", $ident, $message);
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
    public function replaceOther($index, $relatCode) {
        if ($index < count($this->emergencyContacts)) {
            $contact = $this->emergencyContacts[$index];
            $contact->setRelation($relatCode);
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
    public function getGrade($ident,$abreviated=false) {
        if(!$abreviated) {
            $field= "A.GRADE_NAME";
        } else {
            $field='A.GRADE_ABREV';
        }
        $query =  "SELECT $field FROM GRADE A
            JOIN ACHIEVEMENT B ON B.GRADE=A.GRADE_ABREV
            WHERE B.ACHIEV_CODE='" . $this->achievement . "'";
        $results = Query($query, $ident);
        if (numRows($results)) {
            return result($results, 0, $field);
        }
    }
    public function testSign_up($ident, $target) {
        echo "<strong>Testing and Promotion Sign-up</strong>";   //shows header and starts a form and show table header
        echo "<form action=\"$target\" method=\"post\">
            <table border=\"1\" cellspacing=\"1\">
                <tr><th>Sign-Up</th><th>Test Type</th><th>Test Name</th><th>Achievement</th></tr>\n";
        $results = Query("SELECT ACHIEV_NAME FROM ACHIEVEMENT
            WHERE ACHIEV_CODE='" . $this->achievement . "'", $ident);  //gets name of achievement 
        if (numRows($results)) {
            $achievName = Result($results, 0, "ACHIEV_NAME");
        }
        $results = Query("SELECT A.REQUIREMENT_TYPE /*for sign_up value*/, B.TYPE_NAME /*the requirement type*/, A.NAME /*test name*/ FROM PROMOTION_REQUIREMENT A
            JOIN REQUIREMENT_TYPE B ON A.REQUIREMENT_TYPE=B.TYPE_CODE  #for full requirement type name
            JOIN ACHIEVEMENT C ON A.ACHIEV_CODE=C.ACHIEV_CODE          #get the right achievement
            JOIN ACHIEVEMENT D ON D.NEXT_ACHIEV=C.ACHIEV_CODE          #from next achievement
            WHERE D.ACHIEV_CODE='" . $this->achievement . "' AND                     #your achievement
                A.TEXT_SET='" . $this->text_set . "'AND                           #using your text
                    A.REQUIREMENT_TYPE IN ('LT','DT','AE','PT','PB')              #only sign up for these
                    AND A.REQUIREMENT_TYPE NOT IN (                                 #makes sure not already passed the requirement
                    SELECT E.REQUIREMENT_TYPE FROM REQUIREMENTS_PASSED E
                    JOIN ACHIEVEMENT F ON E.ACHIEV_CODE =F.ACHIEV_CODE
                    JOIN ACHIEVEMENT G ON G.NEXT_ACHIEV=F.ACHIEV_CODE
                    WHERE G.ACHIEV_CODE='" . $this->achievement . "'AND
                    E.CAPID='" . $this->capid . "')", $ident);  //get all available requirement sign_up
        if (numRows($results) > 0) {                                   //if can sign up for testing
            for ($row = 0; $row < numRows($results); $row++) {
                echo "<tr><td><input type=\"checkbox\" name=\"signup[]\" value=\"" . Result($results, $row, "REQUIREMENT_TYPE") . "\"/></td>"; //create checkbox
                echo"<td>" .Result($results, $row, "TYPE_NAME") . "</td>";         //show test type
                if (is_null(Result($results, $row, "NAME"))) {                   //if the name is null for the test just echo n/a
                    echo "<td>n/a</td>";
                } else {
                    echo "<td>" . Result($results, $row, "NAME") . "</td>";    //display test name
                }
                echo "<td>$achievName</td></tr>";                      //displays achievement name
            }
        } else {               //display promotion sign up if available
            echo "<tr><td><input type=\"checkbox\" name=\"signup[]\" value=\"PR\"/></td>";
            echo "<td>Promotion</td><td>n/a</td><td>$achievName</td></tr>";
        }
        unset($results);
        echo "</table>\n<input type=\"submit\" name=\"finish\" value=\"Sign-in only\"/>
            <input type=\"submit\" name=\"finish\" value=\"Sign-in and Sign-up for testing\"/></form>";
    }
    public function signUp(array $input, $ident, $message = null) {
        for ($row = 0; $row < count($input); $row++) {
            $code = cleanInputString($input[$row], 2, "signup#$row", false);
            if ($code == "PR") {              //insert promotion requirement if requested
                $query = "INSERT INTO PROMOTION_SIGN_UP (CAPID, ACHIEV_CODE,DATE_REQUESTED)
                    SELECT '" . $this->capid . "',A.ACHIEV_CODE,CURDATE() FROM ACHIEVEMENT A
                        JOIN ACHIEVEMENT B ON B.NEXT_ACHIEV=A.ACHIEV_CODE
                        WHERE B.ACHIEV_CODE='" . $this->achievement . "'";
            } else {
                $query = "INSERT INTO TESTING_SIGN_UP(CAPID,REQUIRE_TYPE,REQUESTED_DATE)
                    VALUESS('" . $this->capid ."','$code', CURDATE())";
            }
            return Query($query, $ident, $message);
        }
    }
    public function promotionReport($ident, $header=true, $date=false,$edit=false) {
        ?>
        <table style="text-align: center"><tr><td>
                    <strong>Promotion Report
                    <?php
                    if($header) {                  //show who its for if it's wanted 
                        echo "for:</strong>".$this->title()."\n";
                    } else   {                //else just close the tag for saying its a promo report
                       ?>
                    </strong>
                    <?php
                    }
                    echo"</td></tr>";               //center header
                    $query= "SELECT NEXT_ACHIEV FROM ACHIEVEMENT
                        WHERE ACHIEV_CODE='".$this->achievement."'";
                    $result=  allResults(Query($query, $ident));
                    if(count($result)>=1)
                        $achiev=$result[0]['NEXT_ACHIEV'];
                    else
                        $achiev=$this->achievement;
                   $query ="SELECT TYPE_CODE, TYPE_NAME FROM
                        REQUIREMENT_TYPE WHERE (MEMBER_TYPE='".$this->memberType."' OR MEMBER_TYPE IS NULL)
                        AND TYPE_CODE IN ( SELECT REQUIREMENT_TYPE FROM PROMOTION_REQUIREMENT 
                        WHERE ACHIEV_CODE IN(SELECT A.ACHIEV_CODE FROM ACHIEVEMENT A, ACHIEVEMENT B
                        WHERE B.ACHIEV_CODE='$achiev'
                        AND A.ACHIEV_NUM <= B.ACHIEV_NUM))ORDER BY TYPE_NAME"; //get the requirements for the header
                    $header=  allResults(Query($query, $ident));    //get headers
                    $_SESSION['header']=$header;   //store it for use later.                                                  
                    array_push($header,array("TYPE_NAME"=>'Promotion','TYPE_CODE' =>'PRO'));
                    echo "<tr><td>    
                        <table class=\"promotion\">
                        <tr><th class=\"promotion\">Achievement</th>";                     //creates headers of rows based on requirement types
                    for ($row = 0; $row < count($header); $row++) { 
                        echo "<th class=\"promotion\">" .$header[$row]["TYPE_NAME"] . "</th>";  //make them into headers
                    }
                    echo "</tr>\n";
                    $query = "SELECT A.ACHIEV_NAME, A.ACHIEV_CODE FROM ACHIEVEMENT A
                        WHERE A.MEMBER_TYPE='".$this->memberType."'   
                        AND A.ACHIEV_CODE <> '0'
                        AND A.ACHIEV_NUM <= ( SELECT 
                        D.ACHIEV_NUM FROM ACHIEVEMENT D, ACHIEVEMENT B
                        JOIN MEMBER C ON C.ACHIEVEMENT=B.ACHIEV_CODE
                        WHERE C.CAPID='".$this->capid."'
                        AND B.NEXT_ACHIEV=D.ACHIEV_CODE)
                        ORDER BY ACHIEV_NUM";
                    $achievements = allResults(Query($query, $ident));    //get all the achievements ^
                    for($i=0;$i<count($achievements);$i++) {      //loop through rows
                        $this->getPromotionInfo($achievements[$i]['ACHIEV_CODE'], $ident);
                        echo '<tr><td class="promotion">'.$achievements[$i]['ACHIEV_NAME'].'</td>';
                        $this->displayPromoRequest($header, $date, $edit,null,true);
                    }
                    ?>
        </table>
    </table>
    <?php
    }
    public function editInformation($page, $identifier) {
                    //displays table for input
                    echo "<form action=\"$page\" method=\"post\"><table border =\"1\" cellspacing=\"1\"><tr><th>Last Name</th><th>First Name</th>
            </tr>";
                    //displays input fields
                    echo "<tr><td><input type=\"text\" name=\"Lname\" value=\"" . $this->name_last . "\" size=\"4\"/></td>
        <td><input type=\"text\" name=\"Fname\" value=\"" . $this->name_first . "\" size=\"4\"/></td>
        ";
//                    enterDate(false,null,$this->DoB);
                    echo "\n</tr></table><br><strong>Also add at least One emergency Contact</strong>";
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
                dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true, $this->emergencyContacts[$row]->getRelation());
                echo "</td><td><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
                $row++;
            } else {
                echo "<tr><td><input type=\"text\" name=\"ContName$row\" size=\"7\"/></td><td>";
                dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true);
                echo "</td><td><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
                $row++;
            }
        }
        echo "</tr></table><br>";
        if ($submit) {
            echo"<input type=\"submit\" value=\"Add Emergency Contacts\"/></form>";
        }
    }
    public function init($level, $ident) {
        if (is_numeric($level)) {              //makes sure its a number
            if ($level == $this->initLevel) {   //if already inited to level return
                return true;
            }
            if ($level > $this->initLevel) {    //if going up in levels
                if($level>=1) {
                    $results = Query("SELECT NAME_LAST,NAME_FIRST,GENDER, ACHIEVEMENT FROM MEMBER
            WHERE CAPID='" . $this->capid . "'", $ident);
                    if (numRows($results)) {
                        $this->name_last = Result($results, 0, "NAME_LAST");
                        $this->name_first = Result($results, 0, "NAME_FIRST");
                        $this->gender = Result($results,0,"GENDER");
                        $this->achievement = Result($results, 0, "ACHIEVEMENT");
                    } else {
                        $this->isEmpty = true;
                    }
                }
                if($level>=2) {
                    $results = Query("SELECT TEXTBOOK_SET,MEMBER_TYPE, PROFILE_PICTURE FROM MEMBER
            WHERE CAPID='" . $this->capid . "'", $ident);
                    if (numRows($results) > 0) {
                        $this->text_set = Result($results, 0, "TEXTBOOK_SET");
                        $this->memberType = Result($results, 0, "MEMBER_TYPE");
                        $this->picture_link = Result($results,0,'PROFILE_PICTURE');
                        if(is_null($this->picture_link))
                            $this->picture_link="/pictures/profile/unavailable.jpg";
                        else 
                            $this->picture_link="/pictures/profile/".$this->picture_link;
                    } 
                    $this->initLevel = 2;
                }
                if($level>=3) {
                    $results = Query("SELECT DATE_OF_BIRTH AS DOB, DATE_JOINED AS DOJ, DATE_TERMINATED AS DOT
        FROM MEMBER
        WHERE CAPID='" . $this->capid . "'", $ident);
                    if (numRows($results) > 0) {
                        $this->DoB = new DateTime(Result($results, 0, "DOB"));
                        $this->Date_of_Join = new DateTime(Result($results, 0, "DOJ"));
                        $this->date_terminated = new DateTime(Result($results,0,"DOT"));
                    }
                }
                if($level>=4) {
                    $results = Query("SELECT HOME_UNIT FROM MEMBER
        WHERE CAPID='" . $this->capid . "'", $ident);
                    if (numRows($results) > 0) {
                        $this->unit = new unit(Result($results, 0, "HOME_UNIT"), $ident);
                    } 
                    $results = Query("SELECT RELATION, CONTACT_NAME, CONTACT_NUMBER
        FROM EMERGENCY_CONTACT 
        WHERE CAPID='" . $this->capid . "'", $ident);
                    for ($row = 0; $row < numRows($results); $row++) {
                        settype($this->emergencyContacts, "array");
                        $buffer = new contact(Result($results, $row, "CONTACT_NAME"),
                                        Result($results, $row, "RELATION"),
                                        Result($results, $row, "CONTACT_NUMBER"));
                        array_push($this->emergencyContacts, $buffer);
                    }
                }
                $this->initLevel = $level;              //stores level
            }
        }
    }
    public function editFields(array $input, $ident) {
        $contactSuccess = true;
        if (array_key_exists("Fname", $input)) {
            $this->name_first = cleanInputString($input["Fname"], 32, "First Name", false);
        } if (array_key_exists("Lname", $input)) {
            $this->name_last = cleanInputString($input["Lname"], 32, "Last Name", false);
        } if (array_key_exists("month", $input)) {
            $this->DoB =  parse_date_input($input);
        }
        for ($row = 0; $row < 5; $row++) {                                        //edit contact information
            if (array_key_exists("ContName" . $row, $input)) {
                if ($input["ContName" . $row] != "") {
                    if (array_key_exists($row, $this->emergencyContacts)) {                      //if not a new one edit it
                        $oldRelat = $this->emergencyContacts[$row]->getRelation();
                        $this->emergencyContacts[$row]->setName($input["ContName" . $row]);
                        $this->emergencyContacts[$row]->setRelation($input["relation" . $row]);
                        $this->emergencyContacts[$row]->setPhone($input["number" . $row]);
                        if (!$this->updateContact($row, $oldRelat, $ident)) {
                            $contactSuccess = false;
                        }
                    } else {
                        array_push($this->emergencyContacts, new contact($input["contName" . $row], $input["relation" . $row], $input["number" . $row]));
                        if (!$this->insertSingleContact($row, $ident)) {
                            $contactSuccess = false;
                        }
                    }                       //TODO update all info and contact info.
                }
            }
        }
        return $contactSuccess;
    }
    public function updateContact($row, $oldRelat, $ident) {
        $query = "UPDATE EMERGENCY_CONTACT
    SET RELATION='" . $this->emergencyContacts[$row]->getRelation() . "'
    CONTACT_NAME='" . $this->emergencyContacts[$row]->getName() . "'
        CONTACT_NUMBER='" . $this->emergencyContacts[$row]->getPhone() . "'
            WHERE CAPID='" . $this->capid . "'
                AND RELATION='" . $oldRelat . "'";
        return Query($query, $ident);
    }
    public function insertSingleContact($row, $ident) {
        $query = "INSERT INTO EMERGENCY_CONTACT (CAPID,RELATION,CONTACT_NAME,CONTACT_NUMBER) VALUES";
        $con = $this->emergencyContacts[$row]->getName;
        $relat = $this->emergencyContacts[$row]->getRelation;
        $num = $this->emergencyContacts[$row]->getPhone;
        $query = $query . "('".$this->capid."','$relat','$con','$num')";
        return Query($query, $ident);
    }
    public function updateFields($ident) {
        $query = "UPDATE MEMBER 
    SET NAME_LAST='" . $this->name_last . "',
    NAME_FIRST='" . $this->name_first . "',
    DATE_OF_BIRTH=STR_TO_DATE('" . $this->DoB->format(PHP_DATE_FORMAT) . "','" . SQL_DATE_FORMAT . "'),
    DATE_JOINED=STR_TO_DATE('" . $this->Date_of_Join->format(PHP_DATE_FORMAT) . "'," . SQL_DATE_FORMAT . "),
    ACHIEVEMENT='" . $this->achievement . "',
    MEMBER_TYPE='" . $this->memberType . "',
    TEXTBOOK_SET='" . $this->text_set . "'
    WHERE CAPID='" . $this->capid . "'";
        return Query($query, $ident);
    }
    public function approveFields($ident) {
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
        dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "grade" . $this->capid, $ident, false, $this->achievement);
        echo "</td><td>";
        dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES", "member" . $this->capid, $ident, false, $this->memberType);
        echo "</td><td>";
        dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL'", "text" . $this->capid, $ident, false, $this->text_set);
        echo "</td><td>";
        dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM CAP_UNIT", 'unit' . $this->capid, $ident, false, $this->unit->getCharter());
        echo "</td><td>";
        enterDate(false, "DoJ" . $this->capid, $this->Date_of_Join);
        echo "</td></tr>\n";
    }
    public function massUpdateFields(array $input) {
        if (array_key_exists("Fname" . $this->capid, $input)) {
            $this->name_first = cleanInputString($input["Fname" . $this->capid], 32, "First Name", false);
        } if (array_key_exists("Lname" . $this->capid, $input)) {
            $this->name_last = cleanInputString($input["Lname" . $this->capid], 32, "Last Name", false);
        } if (array_key_exists("monthDoB" . $this->capid, $input)) {
            $this->DoB = parse_date_input($_POST, "DoB" . $this->capid);
        } if (array_key_exists('capid' . $this->capid, $input)) {
            $this->capid = cleanInputInt($input['capid' . $this->capid], 6, 'capid');
        } if (array_key_exists('gender' . $this->capid, $input)) {
            $this->gender = cleanInputString($input['gender' . $this->capid], 1, 'gender', false);
        } if (array_key_exists('grade' . $this->capid, $input)) {
            $this->achievement = cleanInputString($input['grade' . $this->capid], 5, "Achievement", false);
        } if (array_key_exists('member' . $this->capid, $input)) {
            $this->memberType = cleanInputString($input['member' . $this->capid], 1, 'Member type', false);
        } if (array_key_exists('text' . $this->capid, $input)) {
            $this->text_set = cleanInputString($input['text' . $this->capid], 5, 'Textbook set', false);
        } if (array_key_exists('unit' . $this->capid, $input)) {
            $this->unit = cleanInputString($input['unit' . $this->capid], 10, 'unit charter number', false);
        } if (array_key_exists('monthDoJ' . $this->capid, $input)) {
            $this->Date_of_Join = parse_date_input($_POST, "DoJ" . $this->capid);
        }
    }
    public function saveUpdates($ident) {
        $query = "UPDATE MEMBER
            SET CAPID='" . $this->capid . "',
            NAME_LAST='" . $this->name_last . "',
            NAME_FIRST='" . $this->name_first . "',
            GENDER='" . $this->gender . "',
            DATE_OF_BIRTH='" . $this->DoB->format(PHP_To_MYSQL_FORMAT) . "',
            ACHIEVEMENT='" . $this->achievement . "',
            MEMBER_TYPE='" . $this->memberType . "',
            TEXTBOOK_SET='" . $this->text_set . "',
            HOME_UNIT='" . $this->unit . "',
            DATE_JOINED='" . $this->Date_of_Join->format(PHP_To_MYSQL_FORMAT) . "',
            APPROVED=TRUE";
        return Query($query, $ident);
    }
    public function getPicture() {
        return $this->picture_link;
    }
    public function get_full_memberType($ident) {
        $query = 'SELECT MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES
            WHERE MEMBER_TYPE_CODE=\''.$this->memberType.'\'';
        $result = Query($query, $ident);
        if(numRows($result)>0) {
            return Result($result,0,'MEMBER_TYPE_NAME');
        }
    }
    public function get_leader_text($ident) {
        $query = 'SELECT TEXT_SET_NAME FROM TEXT_SETS
            WHERE TEXT_SET_CODE=\''.$this->text_set.'\'';
        $result = Query($query, $ident);
        if(numRows($result)>0) {
            return Result($result,0,0);
        }
    }
    public function get_gender() {
        switch ($this->gender) {
            case "M" :
                return "male";
            case "F" :
                return "female";
        }
    }
    public function general_info($ident) {
        echo "<tr><td>".$this->get_full_memberType($ident)."</td>";
        echo "<td>".$this->getGrade($ident)."</td>";
        echo '<td><a href="/login/misc/unit.php?charter='.$this->unit->getCharter().'">'.$this->unit->getCharter().'</a></td>';
        echo "<td>".$this->get_leader_text($ident)."</td>";
        echo "<td>".$this->get_gender()."</td>";
        echo "<td>".$this->Date_of_Join->format(PHP_DATE_FORMAT)."</td>";
        echo "<td>".$this->date_terminated->format(PHP_DATE_FORMAT)."</td>";
        echo "<td>".$this->DoB->format(PHP_DATE_FORMAT)."</td>";
        echo "</tr>";
    }
    public function display_Contact($ident,$head_name=true) {
        echo "<strong>Emergency Contact Information";
        if($head_name) {                             //show who its for
             echo "for: </strong>";
             echo $this->title();
        } else {
            echo "</strong>";
        }
        ?>
        <br>
        <table border="1" cellpadding="0">
            <tr><th>Contact Name</th><th>Contact Relation</th><th>Contact's Number</th></tr>
        <?php
        for($i=0;$i<count($this->emergencyContacts);$i++) {              //cycles through contact
            echo "<tr><td>";
            echo $this->emergencyContacts[$i]->getName()."</td><td>";
            echo $this->emergencyContacts[$i]->getRelation_full($ident)."</td><td>";
            echo $this->emergencyContacts[$i]->getPhone()."</td></tr>";
        } 
        ?>
        </table>
        <?php
    }
    public function attendance_report($ident,$header=true,$event=null) {
        echo "<strong>Attendance Report";
        if($header) {
            echo "for:</strong>".$this->title();
        } else 
            echo "</strong>";
        ?>
            <table border="1" cellpadding="0">
                <tr><th>Event Date</th><th>Event Type</th><th>Event Name</th><th>Event Location</th></tr>
        <?php
        $query ="SELECT B.EVENT_CODE, B.EVENT_DATE, C.EVENT_TYPE_NAME, B.EVENT_NAME, D.LOCAT_NAME
                FROM EVENT B
                LEFT JOIN EVENT_TYPES C ON B.EVENT_TYPE=C.EVENT_TYPE_CODE
                LEFT JOIN EVENT_LOCATION D ON B.LOCATION=D.LOCAT_CODE
                LEFT JOIN ATTENDANCE A ON A.EVENT_CODE=B.EVENT_CODE
                WHERE CAPID='".$this->capid."'";
        if($event!=null)
            $query =$query."AND B.EVENT_CODE='$event'";
        $query= $query."ORDER BY B.EVENT_DATE DESC";              //get all the event info for the events attended by them
        $attendance = Query($query, $ident);
        $size = numRows($attendance);
        for($i=0;$i<$size;$i++) {          //show all the events attended
            echo "<tr><td>";                            //start row
            $date = new DateTime(Result($attendance, $i, "B.EVENT_DATE"));  //parse date and then format it 
            echo '<a href="/login/attendance/event.php?eCode='.  Result($attendance, $i,'B.EVENT_CODE').'">'.$date->format(PHP_DATE_FORMAT);
            echo '</a></td><td>'.Result($attendance, $i,"C.EVENT_TYPE_NAME")."</td>"; //show event typ
            echo "<td>".Result($attendance, $i,'B.EVENT_NAME')."</td>";          //event name
            echo "<td>".Result($attendance, $i, 'D.LOCAT_NAME')."</td></tr>";  //show location and end the row
        }
        ?>
            </table>
        <?php
    }
    public function promo_board_report($ident,$header=true) {
        echo"<strong>Promotion Boards Report ";
        if($header) {
            echo "for: </strong>".$this->title();
        } else
            echo "</strong>";
        $query="SELECT PHASE, BOARD_DATE, APPROVED, NEXT_SCHEDULED FROM PROMOTION_BOARD
            WHERE CAPID='".$this->capid."'
                ORDER BY BOARD_DATE DESC";
        $boards = Query($query, $ident);
        $size = numRows($boards);
        ?>
        <table border="1" cellpadding="0">
            <tr><th>Board Date</th><th>Phase</th><th>Approval</th><th>Next Board Date</th></tr>
        <?php
        for($i=0;$i<$size;$i++) {
            echo "<tr><td>";
            $date = new DateTime(Result($boards, $i,'BOARD_DATE'));
            echo $date->format(PHP_DATE_FORMAT)."</td>";
            echo "<td>".  Result($boards, $i,"PHASE")."</td>";
            if(Result($boards, $i, "APPROVED")==1) {           //if approved
                echo"<td>Approved</td>";
            } else {
                echo "<td><font color=\"red\">Retained</font></td>";
            }
            if(Result($boards, $i, 'NEXT_SCHEDULED')!=null) {  //if isn't null
                $date=new DateTime(Result($boards,$i,'NEXT_SCHEDULED'));
                echo "<td>".$date->format(PHP_DATE_FORMAT)."</td>";
            } else 
                echo "<td>n/a</td>";
        }
        ?>
        </table>
        <?php
    }
    public function discipline_report($ident,$header=true) {
        $query="SELECT B.DISCIPLINE_NAME, A.TYPE_OF_ACTION, C.EVENT_DATE, A.EVENT_CODE, D.OFFENSE_NAME, A.OFFENSE, A.SEVERITY, A.GIVEN_BY
            FROM DISCIPLINE_LOG A
            LEFT JOIN DISCIPLINE_TYPE B ON A.TYPE_OF_ACTION=B.DISCIPLINE_CODE
            LEFT JOIN EVENT C ON A.EVENT_CODE=C.EVENT_CODE
            LEFT JOIN DISCIPLINE_OFFENSES D ON A.OFFENSE=D.OFFENSE_CODE
            WHERE CAPID='".$this->capid."'";
        $result = Query($query, $ident);
        echo "<strong>Discipline Report ";
        if($header)
            echo "for: </strong>".$this->title();
        else
            echo"</strong>";
        ?>
        <table border="1" cellpadding="0">
            <tr><th>Disciplinary Action taken</th><th>Date</th><th>Type of Offense</th><th>Severity</th><th>Reported by</th></tr>
        <?php
        $results=  allResults($result);        //get all results
        $size= count($results);
        for($i=0;$i<$size;$i++) {                        //loop through the result
            echo "<tr><td>";
            echo '<a href="/login/discipline/details.php?capid='.$this->capid.'&ToA='.$results[$i]['TYPE_OF_ACTION'].'&event='.$results[$i]['EVENT_CODE'].'&O='.$results[$i]['OFFENSE'].'&given='.$results[$i]['GIVEN_BY'].'">';
            echo $results[$i]['DISCIPLINE_NAME'].'</a></td>';         
            $date = new DateTime($results[$i]['EVENT_DATE']);
            echo '<td>'.$date->format(PHP_DATE_FORMAT).'</td>';
            echo "<td>".$results[$i]['OFFENSE_NAME'].'</td>';
            echo '<td>'.$results[$i]['SEVERITY'].'</td>';
            $given = new member($results[$i]['GIVEN_BY'],1,$ident);
            $given->link_report();
            echo"</td></tr>";
        }
        ?>
        </table>
        <?php
    }
    function title() {
       $ident=  connect("Sign-in", "ab332kj2klnnfwdndsfopi320932i45n425l;kfoiewr");
       $buffer= $this->getGrade($ident, true)." ".$this->name_first." ".$this->name_last;
       close($ident);
       return $buffer;
    }
    public function staff_position($ident, $header=true) {
        echo "<strong>Staff Positions ";
        if($header)
            echo "for:</strong>".$this->title ();
        else
            echo "</strong>";
        ?>
        <table border="1" cellpadding="0">
            <tr><th>Staff Position</th></tr>
        <?php
        $query ='SELECT B.FLIGHT_NAME, A.ELEMENT, C.STAFF_NAME
            FROM CHAIN_OF_COMMAND A
            JOIN STAFF_POSITIONS_HELD D ON D.STAFF_POSITION=A.POS_CODE
            LEFT JOIN FLIGHTS B ON A.FLIGHT=B.FLIGHT
            LEFT JOIN STAFF_POSITIONS C ON A.STAFF_CODE=C.STAFF_CODE
            WHERE D.CAPID=\''.$this->capid."'";
        $result = Query($query, $ident);
        $size = numRows($result);
        for($i=0;$i<$size;$i++) {
            echo '<tr><td>';
            if(Result($result, $i,'B.FLIGHT_NAME')!=null) {
                    echo Result ($result, $i, 'B.FLIGHT_NAME')." flight ";
                    if(Result($result, $i,'A.ELEMENT')!=null)
                            echo Result ($result, $i, 'A.ELEMENT').' element ';
            }
            echo Result($result, $i,'C.STAFF_NAME')."</td></tr>\n";
        }
        ?>
        </table>
        <br>
        <strong>Commanders</strong>
        <?php
        $chain = new chain_of_command($ident,$this->capid);
        $chain->display($ident);
    }
    public function link_report() {
        return '<a href="/login/member/report.php?capid='.$this->capid.'">'.$this->title().'</a>';
    }
    public function get_text() {
        return $this->text_set;
    }
    public function getPromotionInfo($promoFor, mysqli $ident,$name=null) {
        $this->promoRecord=null;
        $this->promoRecord['achiev']=$promoFor;
        $this->promoRecord['NAME']=$name;
        $query = "SELECT REQUIREMENT_TYPE AS TYPE, PASSED_DATE AS DATE, PERCENTAGE
            FROM REQUIREMENTS_PASSED 
            WHERE CAPID='".$this->capid."' AND ACHIEV_CODE='$promoFor'
            ORDER BY REQUIREMENT_TYPE";           //shows what they already passed
        $passed=  allResults(Query($query, $ident));           //all the passed requirements
        $query = "SELECT A.REQUIREMENT_TYPE AS TYPE, A.NUMBER_QUESTIONS AS NUMBER, PASSING_PERCENT AS PERCENT 
            FROM PROMOTION_REQUIREMENT A
            JOIN ACHIEVEMENT B ON A.ACHIEV_CODE=B.ACHIEV_CODE
            WHERE A.TEXT_SET IN('" . $this->text_set . "','ALL')
            AND B.MEMBER_TYPE='".$this->memberType."' and
            A.ACHIEV_CODE='$promoFor'
            ORDER BY REQUIREMENT_TYPE"; 
        $requirements =  allResults(Query($query, $ident));      //all requirements to actually promote
        $query = "SELECT A.REQUIRE_TYPE AS TYPE
                FROM TESTING_SIGN_UP A 
                WHERE A.CAPID='".$this->capid."'
                ORDER BY A.REQUIRE_TYPE";
        $sign_up=  allResults(Query($query, $ident));            //was signed up for
        $query="SELECT DATE_PROMOTED FROM PROMOTION_RECORD
            WHERE CAPID='".$this->capid."'
            AND ACHIEVEMENT='$promoFor'";
        $promotion=  allResults(Query($query, $ident));        //get the promotion date
        if(count($promotion)>0) {
            $this->promoRecord['PRO']=array('P',new DateTime($promotion[0]['DATE_PROMOTED']));   //get the date promoted
        }
        $specialRequires= specialPromoRequire($ident);            //the type of requirements that need event attendance
        $eventAttendance = getEventPromo($ident, $this->capid);
        $incomplete=0;   //var that tells how many are incomplete
        $inProg=0;      //the number of requirements that are in progress
        for($i=0;$i<count($requirements);$i++) {  //cycle through the requirements and say whats good and not
            $found= false;          //tells if the requirement has been found
            $current=$requirements[$i]['TYPE'];   //gets the searched require code
            for($j=0;$j<count($passed);$j++) {   //searches trough the passed requirements
                if($passed[$j]['TYPE']==$current) {
                    //mark this requirement as passed 0=>the marker P=Passed 1=>date
                    if($requirements[$i]['NUMBER']==null) {
                        $percent = $passed[$j]['PERCENTAGE'];
                    } else {
                        $percent = round($requirements[$i]['NUMBER']*$passed[$j]['PERCENTAGE'])."/".$requirements[$i]['NUMBER'];
                    }
                    $this->promoRecord[$current]=array('P',new DateTime($passed[$j]['DATE']),$percent, "percent"=>$requirements[$i]['PERCENT']);
                    $found = true;    //says its found, skip the rest
                    break;  //kill this for loop
                }
            }
            if(!$found) {  //if wasn't found then see if in progress
                for($j=0;$j<count($sign_up);$j++) {   //search for the test sign up
                    if($sign_up[$j]['TYPE']==$current) {  //if was testing then show it
                        $this->promoRecord[$current]=array('I', "percent"=>$requirements[$i]['PERCENT']);  //marks as incomplete
                        $inProg++;                 //increase count
                        $found=true;
                        break;
                    }                    
                }
            }
            if(!$found) {                           //if still not found try spec events
                if(in_array($current, $specialRequires)) {  //if is a special case then check it out.
                    if(($buffer=checkEventPromo($eventAttendance,$promoFor, $current))!=false) {  //if it was so then say so
                        $this->promoRecord[$current]=array('P',$buffer,"percent"=>$requirements[$i]['PERCENT']);
                        $found = true;
                    }  
                }
            }
            if(!$found) {
                $this->promoRecord[$current]=array('F',"percent"=>$requirements[$i]['PERCENT']); //if was never found then show this requirement as failed
                $incomplete++;  //count it
            }
        }
        return array($incomplete,$inProg);
    } 
    /**
     * 
     * @param array $header  the array of the requirement_types from the header section
     * @param boolean $disPlayDates weather or not to display the dates for promotions
     * @param boolean $canEdit   weather or not they can change the information or if read-only
     * @param boolean $approved weather or not the promotion is approved
     */
    function displayPromoRequest(array $header, $disPlayDates=false, $canEdit=false ,$approved=null,$showPromo=false) {
        if(!$showPromo) {
            echo "<td class=\"promotion\">".$this->link_report()."</td>";   //show member
        }
        if(isset($this->promoRecord['NAME'])&&$this->promoRecord['NAME']!=null)
            echo "<td class=\"promotion\">".$this->promoRecord['NAME']."</td>";
        for($j=0;$j<count($header);$j++) {              //TODO actually display stuff
            $index=$header[$j]['TYPE_CODE'];   //get the current requirement
            if(isset($this->promoRecord[$index])) {  //if has that requirement do stuff
                $current=$this->promoRecord[$index];  //load it
                echo '<td class="promotion '.$current[0].'">';
                $displayText = true;
                if($disPlayDates) {       //if displaying dates
                    if($canEdit) {
                        $date=null;
                        $percent=null;
                        if(isset($current[1]))       //if date and percent set get it
                            $date = $current[1];
                        if(isset($current[2])) 
                            $percent=$current[2];
                        $displayText=promoRequireInput($this->capid,$index, $date, $percent,$this->promoRecord['achiev']);  //display the input
                    } if(!$canEdit||($displayText)) {            //if can't edit
                        if($current[0]=="P") {
                            echo $current[1]->format(PHP_DATE_FORMAT);
                            $displayText=false;
                        }
                    }
                } if($displayText) {
                    switch ($current[0]) {
                        case "P":
                            echo "Passed";
                            break;
                        case "I":
                            echo "In progress";
                            break;
                        case "F":
                            echo "Needs Work";
                            break;
                    }
                }
                echo "</td>";
            } else  {            //else just leave blank
                echo '<td class="promotion">n/a</td>';
            }
            
        }   //display yes bubble if allowed to edit info
        if($canEdit&&is_numeric($approved)) {
            echo '<td><input type="radio" name="'.$this->getCapid().'" value="yes"';
            if($approved==1) 
                echo ' checked/>';
            else echo '/>';
            echo "Yes<br>";
            //display no bubble
            echo '<input type="radio" name="'.$this->getCapid().'" value="no"';
            if($approved==0)
                echo ' checked/>';
            else echo '/>';
            echo "No </td></tr>\n";
        } else {
            echo "</tr>\n";
        }
    }
    /**Parse the edited promotion requirements
     * 
     * @param mysqli_stmt $insert a prepared statement to insert the passed promotion requirement
     * @param mysqli_stmt $update prepared statement for updating ^
     * @param mysqli_stmt $delete delete test_sign_up if in process
     * @param array $header the requirement_types
     * @param array $input the post input
     * @param achiev the achievement code if multiple achievements
     */
    function parsePromoEdit(mysqli_stmt $insert, mysqli_stmt $update, mysqli_stmt $delete, array $header, array $input, $achiev=null) {
        if($achiev==null) {  //if achievement isn't specified
            $achiev=$this->promoRecord['achiev'];
        } 
        for($i=0;$i<count($header);$i++) {      //cycle through the requirements and parse them
            $type=$header[$i]['TYPE_CODE'];
            $append = $this->capid.$type.$achiev;            //the appended string
            $percent = parsePercent($append, $input, $this->promoRecord[$type]["percent"]); //parse the percentage
            $date=  parse_date_input($input, $append);                 //parse the date
            if($date!=null&&$percent!=false) {         //if date is valid and the percent is valid
                switch($this->promoRecord[$type][0]) {                     //switchfor choosing which prepared satement
                    case "P":
                        bind($update,"sdiss",array($date->format(PHP_TO_MYSQL_FORMAT),$percent,$this->capid,$achiev_code,$type));
                        execute($update);
                        break;
                    case "I":
                    Case "F":
                        bind($insert,"issssd",array($this->capid,$achiev_code,$type,$this->text_set,$date->format(PHP_TO_MYSQL_FORMAT),$percent));           //insert 
                        execute($insert);
                        $this->promoRecord[$type][0]='P';     //set it to passed to easily checked if passed all requirements
                        if($this->promoRecord[$type][0]=='F') 
                            break;  //break if they didn't sign up
                        bind($delete,'is',array($this->capid,$type));
                        execute($delete);            //execute and delete the sign-up
                        break;
                }
            }
        }
    }
    /**
     * Parses and inputs a complete member promotion report.
     * 
     * Hands the input 1 achievement at a time to $this->parsePromoEdit() to parse all inputs.
     * 
     * @param type $ident the database connection
     * @param array $input the input array from POST or other submission method
     */
    function parseWholeEdit($ident, array $input) {
        $approve=  prepare_statement($ident, "UPDATE PROMOTION_SIGN_UP SET APPROVED=?
        WHERE CAPID=? AND ACHIEV_CODE=?");  //create a prepared statement to approve one 
        $insert =  prepare_statement($ident,"INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET,PASSED_DATE,PERCENTAGE)
            VALUES(?,?,?,?,?,?)");         //create prepared statement to insert requirements
        $update = prepare_statement($ident,"UPDATE REQUIREMENTS_PASSED
            SET PASSED_DATE=?, PERCENTAGE=?
            WHERE CAPID=? AND ACHIEV_CODE=? AND REQUIREMENT_TYPE=?");
        $deleteTest =  prepare_statement($ident,"DELETE FROM TESTING_SIGN_UP
            WHERE CAPID=? AND REQUIRE_TYPE=?");
        $header=$_SESSION['header'];
        $query = "SELECT A.ACHIEV_CODE FROM ACHIEVEMENT A, ACHIEVEMENT B
            WHERE B.ACHIEV_CODE='".$this->achievement."' AND
                A.ACHIEV_NUM<=B.ACHIEV_NUM
                ORDER BY A.ACHIEV_NUM";                                 //get all the achievements needed in order
        $achiev=  allResults(Query($query,$ident));
        for($i=0;$i<count($achiev);$i++) {                     //cycles through all the achievements and parses them seperately
            $buffer=$achiev[$i]['ACHIEV_CODE'];
           $this->parsePromoEdit($insert,$update,$deleteTest,$header,$input,$buffer);   //parses the information for real this time 
        }
         close_stmt($approve);
        close_stmt($insert);
        close_stmt($update);
        close_stmt($deleteTest);
    }
    /**
     * Checks if all requirements for promotion are passed
     * @return boolean true if and only if all requirements are passed false otherwise
     */
    function checkPassing() {
        foreach($this->promoRecord as $key=>$buffer) {
            if($buffer[0]!='P'&&$key!='PRO')  //if not passed and isn't the promotion tell them they didn't pass
                return false;
        }
        return true;   //if found nothing then
    }
    /**gets the cpft requirements for the member
     * 
     * @param type $ident the database connection
     * @param type $capid the capid for the member
     * @return an array of requirements the key is the test code
     */
    function retrieveCPFTrequire($ident, DateTime $date= null) {
        $age=  $this->get_age($ident,$date);
        $return['age']=$age;
        if($age>17)       //round to 17
            $age=17;
        $query ="SELECT  A.TEST_TYPE, A.REQUIREMENT
            FROM ACHIEVEMENT B, ACHIEVEMENT D, CPFT_REQUIREMENTS A
            LEFT JOIN ACHIEVEMENT C ON END_ACHIEV=C.ACHIEV_CODE
            WHERE START_ACHIEV=B.ACHIEV_CODE
            AND D.ACHIEV_CODE='".$this->achievement."'
            AND A.AGE='$age' AND A.PHASE='".$this->get_phase($ident)."' and A.GENDER='".$this->gender."'
            and (D.ACHIEV_NUM BETWEEN B.ACHIEV_NUM AND C.ACHIEV_NUM
                OR (D.ACHIEV_NUM>=B.ACHIEV_NUM AND END_ACHIEV IS NULL))";
        $require=  allResults(Query($query, $ident));             //get requirements
        for($i=0;$i<count($require);$i++) {  //reorganizes the info and return it
            $return[$require[$i]['TEST_TYPE']]=  floatval($require[$i]['REQUIREMENT']);
        }
        return $return;
    }
    function get_age($ident, DateTime $date=null) {
        if($date==null) {  //if the date isn't specified assume today
            $date="CURDATE()";
        } else {             //else use the date specified
            $date="'".($date->format(PHP_TO_MYSQL_FORMAT))."'";
        }
        $query="SELECT FLOOR(DATEDIFF($date,DATE_OF_BIRTH)/365.25) as AGE from MEMBER
            WHERE CAPID='".$this->capid."'";
        $age = allResults(Query($query, $ident));   //get the cadet's age
        if(count($age>0))
            return floatval ($age[0]['AGE']);
        return false;
    }
    function get_phase($ident) {
        $query = "SELECT PHASE FROM ACHIEVEMENT
            WHERE ACHIEV_CODE='".$this->achievement."'";
        $results=  allResults(Query($query, $ident));
        if(count($results)>0)
            return $results[0]['PHASE'];
        return false;
    }
    function get_achievement() {
        return $this->achievement;
    }
    /**
     * 
     * @param type $ident the ident for the 
     * @return String the next achievement code false on an error
     */
    function get_next_achiev($ident) {
        $query="SELECT NEXT_ACHIEV FROM ACHIEVEMENT
            WHERE ACHIEV_CODE='".$this->achievement."'";
        $results=  allResults(Query($query, $ident));
        if(count($results)>0)
            return $results[0]['NEXT_ACHIEV'];
        return false;
    }
    /**
     * displays the emergency contact information in tabular form the emergency contact report
     */
    function display_Emergency() {
        $emergency=$this->emergencyContacts;
        $num=count($emergency);
        for($i=0;$i<$num;$i++) {
            echo "<tr><td></td>";   //displays empty cell for asthetics
            $buffer=$emergency[$i];
            echo "<td>".$buffer->getName()."- ".$buffer->getRelation."</td>";  //shows name
            echo "<td>".$buffer->getPhone."</td></tr>";   //displays phone number
        }
    }
}
class unit {
    private $charter_num;
    private $region;
    private $wing;
    function __construct($charter_num, $ident, $region = null, $wing = null) {
        if ($region == null || $wing == null) {
            $this->charter_num = cleanInputString($charter_num, 10, "Unit charter Number", false);
            $results = Query("SELECT REGION, WING FROM CAP_UNIT WHERE CHARTER_NUM='$this->charter_num'", $ident);
            $this->region = Result($results, 0, "REGION");
            $this->wing = Result($results, 0, "WING");
        } else {
            $this->charter_num = cleanInputString($charter_num, 10, "charter Number", false);
            $this->region = cleanInputString($region, 3, "Region code", false);
            $this->wing = cleanInputString($wing, 2, "Wing code", false);
        }
    }
    function getCharter() {
        return $this->charter_num;
    }
    function insert_unit($ident, $message) {
        return Query("INSERT INTO CAP_UNIT (CHARTER_NUM,REGION,WING)
VALUES('" . $this->charter_num . "','" . $this->region . "','" . $this->wing . ")", $ident, $message);
    }
}
class memberType {
    private $code;
    private $name;
    function __construct($code) {
        $this->code = cleanInputString($code, 1, "Member Type");
        $results = Query("SELECT MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES WHERE MEMBER_TYPE_CODE='$this->code'");
        $this->name = Result($results, 0, "MEMBER_TYPE_NAME");
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
        $this->name = cleanInputString($name, 32, "Contact Name",false);
        $this->relation = cleanInputString($relation, 2, "Contact's Relation",false);
        $this->phone = cleanInputString($phone, 12, "Contact phone num",false);
    }
    function getName() {
        return $this->name;
    }
    function getRelation() {
        return $this->relation;
    }
    function getRelation_full($ident) {
        $results=  Query("SELECT RELATION_NAME FROM CONTACT_RELATIONS 
            WHERE RELATION_CODE='".$this->relation."'", $ident);
        if(numRows($results)>=1)
            return Result ($results,0,"RELATION_NAME");
    }
    function getPhone() {
        return $this->phone;
    }
    public function setName($name) {
        $this->name = cleanInputString($name, 32, "Contact Name", false);
    }
    public function setPhone($phone) {
        $this->phone = cleanInputString($phone, 12, "Contact Phone Number", false);
    }
    function setRelation($relation) {
        $this->relation = cleanInputString($relation, 2, "relation", false);
    }
}
class relationShip {
    private $name;
    private $code;
    function __construct($name) {
        $this->name = cleanInputString($name, 20, "relationship name" ,false);
        $this->code = substr($this->name, 0, 2);
    }
    function insertRelat($ident, $message) {
        return Query("INSERT INTO CONTACT_RELATIONS(RELATION_CODE, RELATION_NAME)
VALUES('" . $this->code . "','" . $this->name . "')", $ident, $message);
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
    function __construct($Fname, $Lname) {
        $this->Fname = cleanInputString($Fname, 50, "First Name", false);
        $this->Lname = cleanInputString($Lname, 50, "Last Name",  false);
    }
    function getRestofFields($page) {
        newVisitor($page, $this->Fname, $this->Lname);
    }
    function setContName($contName) {
        $this->contName = cleanInputString($contName, 50, "Emergency Contact Name", false);
    }
    function setContPhone($contPhone) {
        $this->contPhone = cleanInputString($contPhone, 12, "Emergency Contact Phone Number", false);
    }
    function insert($ident, $message = null) {
        return Query("INSERT INTO NEW_MEMBER(NAME_LAST,NAME_FIRST,DATE_CAME,EMERGENCY_CONTACT_NAME,EMERGENCY_CONTACT_NUMBER)
VALUES(" . $this->Lname . "," . $this->Fname . ",CURDATE()," . $this->contName . "," . $this->contPhone . ")", $ident, $message);
    }
}
class searched_member {
    private $member;
    private $percent_match;
    private $capid_match;
    public function __construct($found_capid,$search_capid,$search_name_first, $search_name_last,$ident) {
        $this->member = new member($found_capid,1, $ident);
        $capid_match = $this->match_capid($search_capid);
        if($capid_match==100) {
            $this->capid_match = $capid_match;
            return;
        } else {
            $this->capid_match = $capid_match;
            $this->percent_match = $capid_match+$this->match_name_first($search_name_first)+$this->match_name_last($search_name_last);
        }
    }
    function __destruct() {
        $this->member=null;
        unset($this->member);
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
class chain_of_command {
    private $commanders= array(array());   //the commanders in 2d array 1st is level, then contents
   function  __construct($ident,$capid=null) {
       if($capid!=null) {                     //if for specific person
           $buffer=array();
           $query ="SELECT A.POS_CODE, A.NEXT_IN_CHAIN
                FROM CHAIN_OF_COMMAND A
                JOIN STAFF_POSITIONS_HELD D ON D.STAFF_POSITION=A.POS_CODE
                WHERE D.CAPID='$capid'";
            $result = Query($query, $ident, $_SERVER['SCRIPT_NAME']);   //gets all their staff positions
            $size = numRows($result);
            for($i=0;$i<$size;$i++) {
                $buffer[$i][0]=array(Result($result, $i, 'A.POS_CODE'),-1);  //store the bottom staff pos and capid =-1 so know at bottom
                $next_pos = Result($result, $i, 'A.NEXT_IN_CHAIN');   //get the pos code for the next commander
                do {                                       //gets all chains going up
                    $query="SELECT B.CAPID, A.NEXT_IN_CHAIN
                        FROM CHAIN_OF_COMMAND A
                        LEFT JOIN STAFF_POSITIONS_HELD B ON B.STAFF_POSITION=A.POS_CODE
                        WHERE A.POS_CODE='$next_pos'";
                    $recursive = Query($query, $ident);
                    if(numRows($result)>0) {                   //if returned the results then do our stuff
                        array_unshift($buffer[$i],array($next_pos,Result($recursive,0,'B.CAPID'))); //push to the front the next commander pos and their capid
                        $next_pos=  Result($recursive,0,'A.NEXT_IN_CHAIN');               //prep for next commander finding
                    }
                } while($next_pos!=null);
            }
           //next merge the chain of commands
            $length = array();  //array for the lenth of each chain
            for($i=0;$i<count($buffer);$i++) {         //get the length of each chain
                $length[$i]= count($buffer[$i]);  //store the length
            }
            for($i=0;$i<count($buffer);$i++) {    //cycle through all chains and merge on each line
                for($j=0;$j<$length[$i];$j++) {   //cycles down trough the chain
                    $temp = $buffer[$i][$j][1];
                    $found = false;
                    if(isset($this->commanders[$j])) {       //if there is a row here search it
                        for($k=0;$k<count($this->commanders[$j]);$k++) {   ///cycle through merged commanders at that level
                            if($temp==$this->commanders[$j][$k]->get_Capid()) {   //if found then just tell it to up the number
                                $this->commanders[$j][$k]->add_sub();  //add 1
                                $found = true;      //found it
                                break;
                            }
                        }
                    }
                    if(!$found) {                            //if it wasn't found then add commander
                        if($j>0) {                   //if not the top then find the next index
                            $next_capid = $buffer[$i][$j-1][1];
                            for($k=0;$k<count($this->commanders[$j-1]);$k++) {  //cycles through above in chain to find who's above
                                if($next_capid==$this->commanders[$j-1][$k]->get_capid()) {  //if found the one then create comm now
                                    if(!isset($this->commanders[$j]))      //if row isn't ther make it
                                        $this->commanders[$j]=array(); 
                                    array_push($this->commanders[$j], new commander($temp,$buffer[$i][$j][0],$ident,$k)); //create the commander on the row and use the index found
                                }
                            }
                        } else {             //if at the top then just store it
                            array_push($this->commanders[$i],new commander($temp,$buffer[$i][$j][0],$ident));
                        }
                    }  // end of not found
                }  //end of cycling down
            }   //end of for for cycling through chains
       }  //end if capid is specified 
   }  
   function display($ident) {
       $locations=array(array(array()));              //array for holding where in the table the one above was.
       $row_count = 0;
       ?>
        <table border="1" cellpadding="0">
        <?php
       for($i=0;$i<count($this->commanders);$i++) {               //cycle down the levels
           echo "<tr>";                                            //start a row for this level
           $buffer = array();                                       //buffer to show what each cell is
           for($j=0;$j<count($this->commanders[$i]);$j++) {       //cycle across line
               if($i>0) {  //if has someone above sort it under it not display jet
                   $temp = $this->commanders[$i][$j];             //the calculating commander
                   var_dump($temp);
                   $next = $temp->get_next();                    //the index for the next commander
                   var_dump($next);
                   print_r($locations);
                   $min= $locations[($i-1)][$next][0];             //get the bounds of the one above
                   if(!isset($buffer[$min])) {                   //if the left of it's commander isn't occupied then use
                       $buffer[$min]=$j;                        //show index used
                       for($k=$min;$k<$min+$temp->get_num_sub();$k++) {  //fill all intermidiate inexes with reference
                           $buffer[$k]=$j;
                       }
                   } else {                              //if is filled shift down
                       $k=$min;                             //get k to beginning
                       while(isset($buffer[$k])) {      //look for first available opening
                           $k++;
                       }
                       $buffer[$k]=$j;                     //fill data in
                       $maximum=$k+$temp->get_num_sub();
                       for($k=$k;$k<$maximum;$k++) {
                           $buffer[$k]=$j;              //fill it in
                       }
                   }
               } else {                                            //if at top then just show it
                   $width=$this->commanders[$i][$j]->get_num_sub();
                   echo '<td colspan="'.$width.'">';   //allow to lign up right
                   echo $this->commanders[$i][$j]->display($ident);
                   $locations[$i][$j]=array($row_count,$row_count+$width); //shows the bounds of cells taken
                   echo "</td>";
                   $row_count+=$width;                            //shift over
               }
           }
           if($i>0) {       //if had to be rearranged
               $index=-1;
               echo "Doing other ";
               for($j=0;count($buffer);$j++) {   //display each thing in the buffer
                   if($index!=$buffer[$j]) {     //if hasn't been displayed show it
                       $index = $buffer[$j];
                       $width=$this->commanders[$i][$index]->get_num_sub();
                        echo '<td colspan="'.$width.'">';   //allow to lign up right
                        echo $this->commanders[$i][$index]->display($ident);
                        $locations[$i][$j]=array($row_count,$row_count+$width); //shows the bounds of cells taken
                        echo "</td>";
                        $row_count+=$width;
                   }
               }
           }
           echo "</tr>\n";                                    // close the row
       }
       ?>
        </table>
        <?php
   }
   function __destruct() {
       for($i=0;count($this->commanders);$i++) {
           for($j=0;count($this->commanders[$i]);$j++) {
               $this->commanders[$i][$j]->__destruct();
           }
       }
       $this->commanders=null;
       unset($this->commanders);
   }
}
class commander {
 private $member;        //the member
 private $num_sub_pos = 1;  //the new number of people under them just 1 level below
 private $next_index;          //the index of the person above them
 private $pos_code;
 function __construct($capid,$pos_code,$ident,$next_index=null) {
     if($capid!=-1)                  //if isn't -1 the designated capid for the started member
         $this->member = new member($capid,1, $ident);
     else 
         $this->member=-1;
     if($next_index!= null)
         $this->next_index = $next_index;
     $this->pos_code = $pos_code;
 }
 function add_sub() {
     $this->num_sub_pos++;          //increases the number of commanders under this one
 }
 function get_Capid() {
     if(gettype($this->member)=='object')        //if is a member
         return $this->member->getCapid();
     else 
         return -1;
 }
 function get_next() {
     return $this->next_index;
 }
 function get_num_sub() {
     return $this->num_sub_pos;
 }
 function get_pos_name($ident) {
     $position='';
     $query ="SELECT B.FLIGHT_NAME, A.ELEMENT, C.STAFF_NAME
         FROM CHAIN_OF_COMMAND A
         LEFT JOIN FLIGHTS B ON A.FLIGHT=B.FLIGHT
         LEFT JOIN STAFF_POSITIONS C ON A.STAFF_CODE=C.STAFF_CODE
         WHERE A.POS_CODE='".$this->pos_code."'";
     $result=  Query($query, $ident);
     if(numRows($result)>0) {
         if(Result($result,0,'B.FLIGHT_NAME')!=null) {
                    $position= Result ($result, 0, 'B.FLIGHT_NAME')." flight ";
                    if(Result($result,0,'A.ELEMENT')!=null)
                            $position=$position.Result ($result, 0, 'A.ELEMENT').' element ';
            }
            $position=$position.Result($result,0,'C.STAFF_NAME');
     }
     return $position;
 }
 function display($ident) {
     if($this->get_Capid()!=-1) {
        $return = "<strong>".$this->get_pos_name($ident)."</strong><br>";
        $return = $return.$this->member->link_report();
     } else {
         $return = "<strong>".$this->get_pos_name($ident)."</strong>";
     }
     return $return;
 }
 function __destruct() {
     $this->member->__destruct();
     $this->member = null;
     unset($this->member);
 }
}
?>