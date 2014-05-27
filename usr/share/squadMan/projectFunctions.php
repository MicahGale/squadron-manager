<?php
/**
 * The main API file for the project
 * 
 * All the APIs for the project.  This is never directly displayed, nor can,
 * but is included in the all the pages, and its functions are invoked by the 
 * other pages.
 * @package Squadron-Manager
 * @license http://www.gnu.org/licenses/gpl.txt GNU GPL V3
 * @copyright (c) 2014, Micah Gale
 */
/* Copyright 2014 Micah Gale
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
/*
 * **********************FOR v. 0.10*****************************
 * TODO finish populating db
 * TODO create installer
 * TODO visitor page
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
$input=  parse_ini_file("/etc/squadMan/squadMan.ini");
if($input!==false) {  //if the file was parsed then get the inputs
    /**
     * @constant PHP_DATE_FORMAT 
     * 
     * The date format to universally display the date
     */
    define("PHP_DATE_FORMAT",$input['date_format']);  
    define("PHP_TIMESTAMP_FORMAT",PHP_DATE_FORMAT." ".$input['time_format']);  //the datetime format
    define('CSV_SAVE_PATH',$input['csv_path']);  //the constant for where csv files go
    define('PROFILE_PATH',$input['profile_path']);  //the path to the profile pictures stored outside document root
    define("BAD_LOGIN_WAIT",$input['login_wait']);
    define("LOCK_TIME",$input['lock_time']);
    define("MAX_LOGIN",$input['lock_count']);
    define('PASSWORD_LIFE',$input['password_life']);
    define('PASSWORD_NOTIF',$input['password_notif']);
    define('LOG_PER_PAGE',$input['log_per_page']);
    date_default_timezone_set($input['time_zone']);  //sets the timezone
} else {
    define("PHP_DATE_FORMAT","d M y");  
    define("PHP_TIMESTAMP_FORMAT",PHP_DATE_FORMAT." H:i:s");  //the datetime format
    define('CSV_SAVE_PATH',"/var/upload/csv");  //the constant for where csv files go
    define('PROFILE_PATH',"/var/upload/profile");  //the path to the profile pictures stored outside document root
    define("BAD_LOGIN_WAIT",5);
    date_default_timezone_set("America/Denver");
    /**
    * The constant for how long to have an account in SQL time format
    */
    define("LOCK_TIME","00:30:00");
    /**
    * The maximum number of bad login attempts in account lockout time before the account is locked
    */
    define("MAX_LOGIN",8);
    /**
    * The maximum password life
    */
    define('PASSWORD_LIFE',180);
     /**
     * The days to wait to notify the password experiation
     */
    define('PASSWORD_NOTIF',14);
    define('LOG_PER_PAGE',40);
}
 /**
  * how to format to insert into mysql
  */
 define("PHP_TO_MYSQL_FORMAT","Y-m-d");   
 /**
  * The format for inserting a complete date time into SQL
  */
 define("SQL_INSERT_DATE_TIME","o-m-d H:i:s");
 define("EVENT_CODE_DATE",'dMy');         //date for creating event codes
 define("CPFT_RUNNING_REQ",1);            //the amount of running events that must be passed
 define("CPFT_OTHER_REQ",2);             //the amount of non-running events that must be passed
 define('NOTIF_PATH','/etc/squadMan/notifications.csv');  //the csv that holds the notification information
 define("PSSWD_INI",'/etc/squadMan/psswd.ini');
 /**
  * the interval that must be waited between promotions in seconds
  */
 define("PROMOTION_WAIT",4838400);
 /**
  * Stores and auditable event to the AUDIT_LOG table.
  * If we have the user's CAPID that will be stored along with the log
  * 
  * @param String $ip the IP address of the client
  * @param String $type - the type of event from the table INTRUSION_TYPE
  * @return String the date and time of the Event formatted for SQL
  */
function auditLog($ip, $type) {
    $timeStamp = date(SQL_INSERT_DATE_TIME);
    $time=  microtime(true);
    $time= $time-intval($time);
    $passes= parse_ini_file(PSSWD_INI);
    $ident= mysqli_connect('localhost', 'Logger', $passes['Logger'],"SQUADRON_MANAGER");
    mysqli_query($ident,"INSERT INTO AUDIT_LOG(TIME_OF_INTRUSION, MICROSECONDS, INTRUSION_TYPE, PAGE,IP_ADDRESS)
        VALUES('$timeStamp','$time','$type','".$_SERVER['SCRIPT_NAME']."','$ip')");
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
    $ident=connect('Logger');
    $timeStamp= date(SQL_INSERT_DATE_TIME);
    $passes= parse_ini_file(PSSWD_INI);
    $ident= mysqli_connect('localhost', 'Logger', $passes['Logger'],"SQUADRON_MANAGER");
    mysqli_query($ident,"INSERT INTO AUDIT_DUMP(TIME_OF_INTRUSION,MICROSECONDS, FIELD_NAME, FIELD_VALUE)
        VALUES('$timeStamp','$time','$fieldName','$fieldValue')");
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
    $ident=connect( 'Logger');
    $ip = $_SERVER['REMOTE_ADDR'];
    Query("INSERT INTO LOGIN_LOG(TIME_LOGIN, CAPID, IP_ADDRESS, SUCEEDED,LOG_OFF)
                 VALUES('$time','$capid','$ip','$success',NULL)", $ident);
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
    $ident = connect('ViewNext');   //logon to view account locks
    $results = Query("SELECT VALID_UNTIL FROM ACCOUNT_LOCKS
                        WHERE CAPID='$capid'", $ident);  //get account locks
    if(numRows($results)>0) {                             //if account lock found
        $time = new dateTime(Result($results,0,"VALID_UNTIL"));
        if($time->diff(new DateTime)->format("%R")=="-") {         //compares current time to lock time if difference is - then not time
            return false;               //return that the account is locked
        } else {                  //if no longer valid just remove lock and allow
            $ident =connect('Logger');
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
            $ident =connect('Logger');
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
    ?>
    CAPID:<input type="text" name="CAPID" value="<?php echo $capid?>" size="1"/><br>   
    Last Name:<input type="text" name="Lname" size="4"/><br>
    First Name:<input type="text" name="Fname" size="4"/><br>
    Gender:<select name="Gender"><option value="M">Male</option><option value="F">Female</option></select><br>
    Date of Birth:
    <?php
    enterDate(true,'DoB');
    echo "<br>CAP Grade"; //SELECT A.ACHIEV_CODE, CONCAT(A.ACHIEV_CODE,'-',B.GRADE_NAME) FROM ACHIEVEMENT A JOIN SQAUDRON_INFO.GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY B.GRADE_NUM
    dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS HI FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.MEMBER_TYPE, A.ACHIEV_NUM", "achiev", $identifier, false);
    echo "<br>Member Type";
    dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES WHERE MEMBER_TYPE_CODE<>'A'", "member", $identifier, false);  //creates drop down menu for membership types
    echo "<br>Textbook Set";
    dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL' ORDER BY TEXT_SET_NAME", 'text', $identifier);  //creates drop down menu for text sets
    echo "<br>Unit Charter Number:";
    dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM CAP_UNIT", 'unit', $identifier, true,result(Query("SELECT CHARTER_NUM FROM CAP_UNIT WHERE DEFAULT_UNIT=TRUE", $identifier),0,'CHARTER_NUM'),0,'CHARTER_NUM');  //creates drop down menu for text sets
    echo "<br>Date Joined CAP:";
    enterDate(true,'DoJ');
    echo "<br><br><strong>Also add at least One emergency Contact</strong>";
    newContact(FALSE, $identifier);
    echo '<input type="Submit" value="Create Member"/></form>';
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
    echo '<table class="table"><tr>
            <th class="table">Contact Name</th><th class="table">Contact\'s Relation</th><th class="table">Contact\'s Phone Number</th></tr>'."\n";
    $row = 0;
    while ($row < 5) {
        echo '<tr class="table"><td class="table"><input type="text" name="ContName'.$row.'" size="7"/></td><td class="table">';
        dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true);
        echo "</td><td class=\"table\"><input type=\"text\" name=\"number$row\" size=\"16\"/></td>\n";
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
    $results = Query($query, $identifier);                    
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
    $date= new DateTime();
    auditDump($time, 'Error Code', $errorno);
    $link=  mysqli_connect();
    auditDump($time, 'Error Message', mysqli_real_escape_string($link,$error));  //escape the ''
    mysqli_close($link);
    echo"<br><strong>there was an error with processing the request</strong><br>
        Please give the following information to you Squadron's IT Officer(s)<br>\n";
//    echo $errorno . " " .$error;
    echo "<br><strong>Time:</strong>".$date->format(PHP_TIMESTAMP_FORMAT)."\n";
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
function connect($username,$password=null,$server="localhost",$db="SQUADRON_MANAGER"){
    if($password==null) {
        $passes=  parse_ini_file(PSSWD_INI);
        $password=$passes[$username];
    }
    $connection=  mysqli_connect($server, $username, $password, $db);
    if(!$connection) {                         //if had error
        reportDbError(mysqli_connect_errno(), mysqli_connect_error());
        die;
    } else{
        return $connection;                    //else just give them the resource
    }
}
/**
 * Returns a single result from a query
 * 
 * @param mysqli_result $result the result to be parsed
 * @param type $row the row you are looking for
 * @param type $field the column you want 
 * @return string returns the cell
 */
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
/**
 * Creates a 2-d array from a query result
 * 
 * The first level of the array is the row, and the second the column
 * $result[0]['hi'] would be the column hi for the first row.
 * 
 * @param mysqli_result $result the query result being parsed
 * @return Array
 */
function allResults(mysqli_result $result) {
    $array=array();
    for($row=0;$row<mysqli_num_rows($result);$row++) {       //get all the rows and gett array
        $array[$row]=  mysqli_fetch_assoc($result);
    }
    return $array;
}
/**
 * Returns the number of rows in a mysql_result
 * 
 * @param mysqli_result $result the result to count
 * @return int the number of rows returned
 */
function numRows(mysqli_result $result) {
    if(!is_bool($result))                  //if is actually a result
        return mysqli_num_rows($result);
    else
        return 0;                //else return 0
}
/**
 * Reutrns the number of rows affected by the last query
 * @param mysqli $ident the database connection from 
 * @return int the number of rows affected
 */
function rows_affected(mysqli $ident) {
    return mysqli_affected_rows($ident);
}
/**
 * Closes a database connection
 * 
 * @param mysqli $ident the database connection to close
 * @return boolean true on success false otherwise
 */
function close(mysqli $ident) {
    return mysqli_close($ident);
}
/**
 * Prepares a prepared-statement from the query provided and the database connection
 * 
 * @param mysqli $ident the database connection to use for the prepared statement
 * @param type $query the query for the prepared statement
 * @return mysqli_stmt the prepared statement created
 */
function prepare_statement(mysqli $ident,$query) {
    $stmt= mysqli_stmt_init($ident);
    if(!mysqli_stmt_prepare($stmt, $query))
        reportDbError (mysqli_errno ($ident), mysqli_error ($ident));
    return $stmt;
}
/**
 * Binds the values to the prepared statement and prepares it for execution.
 * 
 * Mysqli_stmt::Bind_param uses referential variables for this, but this function doesn't support that
 * do not use in a refferential variable way
 * 
 * @param mysqli_stmt $stmt the prepared statement being used
 * @param String $types the string of types being used. see mysqli_stmt::bind_param <http://php.net/manual/en/mysqli-stmt.bind-param.php>
 * @param array $bind the array of values to actually bind
 */
function bind(mysqli_stmt $stmt,$types, array $bind) {
    for($i=0;$i<count($bind);$i++) {
        $buffer[$i]=&$bind[$i];
    }
    $pass = array_merge(array($stmt,$types), $buffer);
    call_user_func_array("mysqli_stmt_bind_param", $pass);
}
/**
 * Executes a prepared Statement
 * 
 * You need to bind the parameters before executing this.
 * 
 * @param mysqli_stmt $stmt the prepared statement to execute.
 * @return mixed for Select a mysqli_result, else true for success and false on failure
 */
function execute(mysqli_stmt $stmt) { 
    if(!($success=mysqli_stmt_execute($stmt))) {                       //if there was an error with the execution
        reportDbError (mysqli_stmt_errno ($stmt), mysqli_stmt_error($stmt));
    } else {
        if(($result=mysqli_stmt_get_result($stmt))!=false) {
            return $result;
        }
    }
    return $success;       //if no results then return the success
}
/**
 * Closes a prepared Statement
 * 
 * @param mysqli_stmt $stmt the statement to close
 * @return bool true on success false on failure
 */
function close_stmt(mysqli_stmt $stmt) {
    return mysqli_stmt_close($stmt);                 //closes the prepared statement
}
/**
 * CleanInputInt-cleans input number
 * 
 * This cleans input numbers against SQL injection, XSS, and remote Execution and file traversing.
 * It uses the mysqli_real_escape_string htmlspecialchars, and escapshellcmd to do this.
 * It also checks length, and parses it as a number to prevent other issues. Any issues and the event will be 
 * logged along with the sanatized form of the input
 * 
 * This 
 * @param String $input the raw Input
 * @param Int $length the absolue length or maximum lenght the number must be, depending on $exact 
 * @param String $fieldName the name of the input field used for logging
 * @param bool $exact true for $length to be exact false for $length to be the max length
 * @return float The Input Number parsed and cleaned as a floating point 
 */
function cleanInputInt($input, $length, $fieldName,$exact=true) {
    $link = mysqli_connect();
    $clean = escapeshellcmd(htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8'));
    if ((strlen($clean)!= $length&&$exact)||  (strlen($clean)>$length&&!$exact) || !is_numeric($clean) || $clean != $input) {
        $time = auditLog( $_SERVER['REMOTE_ADDR'], "SI");
        auditDump($time, $fieldName, $clean);
        echo "<font color=\"red\">$fieldName is not a valid number it must be $length digits long.</font><br>";
        if (strlen($clean) > $length ||(strlen($clean)!=$length&&$exact)|| !is_numeric($clean)) {          //nulls if wrong type
            $clean = null;
        }
    }
    $clean = floatval($clean);                                            //cast it to int
    return $clean;
}
/**
 * Cleans an input String
 * 
 * See cleanInputInt for how it cleans the text
 * 
 * @param String $input The raw input
 * @param Int $length the maximum allowed length
 * @param String $fieldName the field name to be logged
 * @param bool $empty false if the String can't be empty, true if it can be empty
 * @param bool $shellClean true if needs to clean against shell characters
 * @return String returns the cleaned text
 */
function cleanInputString($input, $length, $fieldName, $empty, $shellClean=true) {                      //clean and log numbers
    $link= mysqli_connect();
    $clean=htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
    if($shellClean)
        $clean=  escapeshellcmd ($clean);
    if (strlen($clean) > $length || $clean != $input || $clean == "" || $clean == null) {
        if (strlen($clean) == 0&& $empty == false) {
            echo "<font color=\"red\"> $fieldName can not be empty</font><br>";
        } else if ($empty == false) {
            echo "<font color=\"red\"> $fieldName is not valid Maximum is: $length</font><br>";
        }
         $time = auditLog( $_SERVER['REMOTE_ADDR'], 'SI');
        auditDump($time, "$fieldName", $clean);
        if (strlen($clean) > $length) {
            $clean = null;
        }
    }
    return $clean;
}
/**
 * Cleans an input text and matches its pattern against a regular expression
 * 
 * See CleanInputInt for how the text is cleaned, except this does not use 
 * cleanShellargs()
 * 
 * @param String $input the Raw input
 * @param String $regex the regular expression to check the pattern against
 * @param int $length the maximum length of the field
 * @param String $fieldName the field name for logging purposed
 * @return String the cleaned input or null if it doesn't match the regex or is too long
 */
function cleanInputDate($input, $regex, $length, $fieldName) {                      //clean and log numbers
    $link = mysqli_connect();
    $clean = htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
    if (strlen($clean) > $length || $clean != $input || (preg_match($regex, $clean)!==1)) {
        echo "<font color=\"red\"> $fieldName is not a valid date.</font><br>";
        $time = auditLog($_SERVER['SCRIPT_NAME'], $_SERVER['REMOTE_ADDR'], 'SI');
        auditDump($time, $fieldName, $input);
        if (strlen($clean) > $length || (preg_match($regex, $clean))) {
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
    $locat=$saveDir.DIRECTORY_SEPARATOR.$hash.'_'.$now->format(EVENT_CODE_DATE).".".$ext;
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
/**
 * Starts a session, and checks for foul play
 * 
 * This checks first for session hijacking by checking the request is from the 
 * right IP address, it will also update the session key. It will then check if
 * the user is allowed to view this page using a list from session_predict_path.
 * If there is a threat it will force the user to resign in.
 * 
 * Uses the following Session variables 
 * ip_addr= the ip_address to be used
 * predicted- the pages the user can see
 * last- the last visited page
 * -request - an array of the last used request header
 *       -agent- the user agent
 *       -accept- the accept header
 *       -lang_char- the accepted language and charset
 *       -encoding- the accepted encoding
 * - resignin- force user to resign in 0 - must resignin 1- another request
 * 
 * @param Int $capid the capid of the user for creating the session should be used only
 * by /login/index.php
 */
function session_secure_start($capid=null) {
    session_start();                     //starts the session
    if(isset($_SESSION['resignin'])) {  //limits amount of requests before killing session
        if($_SESSION['resignin']<1) {
            $_SESSION['resignin']++;
            session_resign_in (true);
        } else {
            $time=  auditLog($_SERVER['REMOTE_ADDR'], 'KS');
            auditDump($time, "User Agent",$_SERVER['HTTP_USER_AGENT']);
            auditDump($time,"Language and Charset", $_SERVER['HTTP_ACCEPT_LANGUAGE']." ".$_SERVER['HTTP_ACCEPT_CHARSET']);
            session_resign_in(false); //kill the session
        }
    }
    if(!isset($_SESSION['ip_addr'])) {  //checks if it's a new session
        $ident=  connect('Logger');   //check if there are any standing log-ins
        $query="SELECT TIME_LOGIN FROM LOGIN_LOG 
            WHERE LOG_OFF IS NULL 
                AND SUCEEDED=TRUE
                AND IP_ADDRESS='".$_SERVER['REMOTE_ADDR']."'";
        $results=  Query($query, $ident);
        close($ident);
        if(numRows($results)>0&&!isset($capid)) {  //if outstanding sessions, let it expire
            session_resign_in(false);
        } else  {   //else assume new and set it-up
            if(!empty($_SERVER['HTTPS'])) { //make sure the connection is secure
                $temp= array('/','/index.php','/login/','/login/index.php');
                $referrers= array();
                foreach($temp as $buffer) {
                    array_push($referrers, "https://".$_SERVER['SERVER_NAME'].$buffer);
                    array_push($referrers, 'https://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$buffer);
                }
                if($_SERVER['SCRIPT_NAME']=='/login/index.php'&&in_array($_SERVER['HTTP_REFERER'],$referrers)) { //ensures that the start is through proper channels
                    $_SESSION['ip_addr']=$_SERVER['REMOTE_ADDR'];  //store the ip addr
                    $ident=connect('ViewNext');
                    session_predict_path($ident,$capid);   //predict the path that users will use
                    close($ident);
                    $_SESSION['last']=array("https://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'],"https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME']);
                    $_SESSION['request']['agent']=$_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['request']['accept']=$_SERVER['HTTP_ACCEPT']; 
                    $_SESSION['request']['lang_char']=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
                    $_SESSION['request']['encoding']=$_SERVER['HTTP_ACCEPT_ENCODING'];
                } else {  //else force to the login
                     header("refresh:0;url=/login/");
                    auditLog($_SERVER['REMOTE_ADDR'],'DC');
                    log_off();
                    exit;
                }
            } else {  //send to https
                header("refresh:0;url=https://".$_SERVER['SERVER_NAME']."/login/");
                log_off();
                exit;
            }
        }
    } else { //if not a new session then start testing the info for accuracy.
        $error_total=0; //counts minor errors that independently dictacte a hijacking
        if(empty($_SERVER['HTTPS'])||$_SERVER['HTTPS']=='off') {  //if not https send them bac
            header("refresh:0;url=https://".$_SERVER['SERVER_NAME']."/login/");
            log_off();
            exit;
        }
        if(isset($_SERVER['HTTP_REFERER'])&&!in_array($_SERVER['HTTP_REFERER'],$_SESSION['last']))
            $error_total+=0.5;  //if not the right referrer 
        if(isset($_SERVER['HTTP_ACCEPT'])&&$_SERVER['HTTP_ACCEPT']!=$_SESSION['request']['accept'])
            $error_total+=0.5;    //if not the same content accepted
        if($_SERVER['HTTP_ACCEPT_ENCODING']!=$_SESSION['request']['encoding'])
            $error_total+=0.5;
        
        if($error_total >=1||$_SESSION['ip_addr']!=$_SERVER['REMOTE_ADDR']||!in_array($_SERVER['SCRIPT_NAME'],$_SESSION['predicted'])||
            $_SESSION['request']['agent']!=$_SERVER['HTTP_USER_AGENT']||
            $_SESSION['request']['lang_char']!=$_SERVER['HTTP_ACCEPT_LANGUAGE']) { //if it's the right ip continue
            If(!isset($_SESSION['resignin'])||$_SESSION['resignin']==0) {  //if the session needs to be verified by 
                $time= auditLog($_SERVER['REMOTE_ADDR'],'SH');
                auditDump($time, "User Agent",$_SERVER['HTTP_USER_AGENT']);
                auditDump($time, "Language", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                $_SESSION['resignin']=0;
                session_resign_in(true);
            }
        } else  { //if clean get ready for the next Request
            $_SESSION['last']=array("https://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'],"https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME']);
            $ident=Connect('ViewNext');
            session_predict_path($ident);
            close($ident);
            session_regenerate_id();
            header("refresh:1800; url=/login/logout.php");
        }
    }
}
/**
 * Creates an array of pages the user is allowed to see.
 * 
 * The pages the user has permissions are added. Then pages that are allowed to be seen
 * after the page the user is on. i.e. the page to confirm a member deletion from 
 * the member delete page.
 * 
 * @param type $ident The databse connection of user ViewNext
 * @param type $capid The user we're looking at only for establishing sessions
 * @param type $page The page to create the list for if not the current page
 */
function session_predict_path($ident,$capid=null,$page=null) {     //creates an array of pages that the user may visit next    
    $results=array();
    if($page==null)                          //if page isn't specified use current page
        $path = $_SERVER['SCRIPT_NAME'];
    else 
        $path=$page;
    $path = substr($path, strpos($path, "/", 1) + 1);            //cuts off leading /login/ offset by 1 to ignore first /
    $query = "SELECT NEXT_URL FROM NEXT_VISIT, TASKS
        WHERE LAST_CODE=TASK_CODE
        AND URL='$path'";                           //query to find next 
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
                        LEFT JOIN STAFF_POSITIONS_HELD C ON A.STAFF_CODE=C.STAFF_POSITION
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
    array_push($results,'/pictures/profile/getter.php');    //add the profile pictures as 
    if($page!=null) {
        return $results;                                      //if page is given give results now
    } else {
        $_SESSION['predicted']=$results;                   //else put in session array 
    }
    array_push($_SESSION['predicted'], $_SERVER['SCRIPT_NAME']);  //push current page onto predicted
}
/**
 * Forces a user to resign in if there is a threat against their session.
 * 
 * @param boolean allowSignIn false won't resign in just show dead session true allows to resign in
 */
function session_resign_in($allowSignIn=true) {                        //requires person to resign in
    if($allowSignIn) { //if allowed to 
        header("refresh:0;url=/login/reSignIn.php");  //go to resignin
        exit;
    } else  {
        header('refresh:0;url=/login/endSession.php'); //display death of session
        log_off();
        exit;
    }
}
/**
 * Destroys the user session. And logs the log off.
 */
function log_off() {
      $ident=  connect('Logger');
    if(isset($_SESSION['log_time'])) {
        $capid=$_SESSION['member']->getCapid();
        $time = date(SQL_INSERT_DATE_TIME);
        $log_in_time=date(SQL_INSERT_DATE_TIME,$_SESSION['log_time']);
        $query="UPDATE LOGIN_LOG SET LOG_OFF='$time'
            WHERE TIME_LOGIN='$log_in_time'
            AND CAPID='$capid'
            AND IP_ADDRESS='".$_SERVER['REMOTE_ADDR']."'";
        Query($query, $ident);
        
    } else {
        $query="SELECT MAX(TIME_LOGIN)AS MAX FROM LOGIN_LOG
            WHERE LOG_OFF IS NULL AND 
            IP_ADDRESS='".$_SERVER['REMOTE_ADDR']."'";
        $time=  Result(Query($query, $ident),0, 'MAX');
        $query="UPDATE LOGIN_LOG SET LOG_OFF=CURRENT_TIMESTAMP()
            WHERE TIME_LOGIN='$time' AND IP_ADDRESS='".$_SERVER['REMOTE_ADDR']."'";
        Query($query, $ident);        
    }
    close($ident);
    session_destroy();
    setcookie(session_name(),"0",0,"/");   //deletes the cookie
}
/**
 * Creates a input for entering a date
 * 
 * @param bool $sameLine true to have all the inputs inline, false stacks them
 * @param String $append any string to add to the end of the input name to distinguish multiple date inputs
 * @param DateTime $default the Default date to show if neccessary
 */
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
/**
 * Parses and cleans the input from an enterDate() form
 * 
 * @param array $input the array input usually $_POST
 * @param Strin $append the string that was appended in enterDate()
 * @return null|DateTime null if there was no input the DateTime object if a date was given
 */
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
/**
 * Returns an array of the months for enterDate
 * @return array of the months
 */
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
/**
 * Displays the input form to search for a specific event.
 * 
 * @param type $ident The database connection
 */
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
/**
 * Searchs for an event from the display_event_search_in()
 * 
 * If there is a single result it will call the specified function. Otherwise it
 * will display the results as links to link.  The links will have the event codes as a get field
 * ?eCode=9Mar13M
 * 
 * @param type $ident The database Connection
 * @param type $callable the function to call on a single result
 * @param type $link the link to go to if there are mutliple results
 */
function searchEvent($ident,$callable,$link="/login/attendance/event.php"){      //if didn't provide complete then search
    ?>
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
    if(isset($_POST['Date'])&&$_POST['Date']!="") {                              //if date given add it to 
        $date=  parse_date_input($_POST);
        if($isFirst)
            $query.=" WHERE "; 
        else
            $query.=" AND ";
        $query.=" A.EVENT_DATE='".$date->format(PHP_TO_MYSQL_FORMAT)."'";
        $isFirst=false;
    }
    if(isset($_POST['location'])&&$_POST['location']!="null") {
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
        ?>
<table class="table"><tr class="table"><th class="table">Event Date</th><th class="table">Event Type</th><th class="table">Event Location</th></tr>
    <?php
        for($i=0;$i<$size;$i++) {
            echo "<tr class=\"table\"><td class=\"table\">";
            echo '<a href="'.$link.'?eCode='.$result[$i]['EVENT_CODE'].'">';
            $date=new DateTime($result[$i]['EVENT_DATE']);
            echo $date->format(PHP_DATE_FORMAT)."</a></td><td>";
            echo $result[$i]['EVENT_TYPE_NAME']."</td>";
            echo '<td class="table">'.$result[$i]['LOCAT_NAME']."</td></tr>\n";
        }
    }
    ?>
     </table>
    <?php
}
/**
 * Gets a list of passed promotion requirements that are attendance based.
 * 
 * This includes:
 * Squadron Activities
 * Safety classes
 * Character Development
 * Encampment
 * 
 * @param type $ident The database connection
 * @param type $capid the user you're researching
 * @return DateTime[] an array of the dates passed
 * The first level is the promotion it is for  the second level is the event type
 * AC= squadron activity
 * the subevent code from SUbevent_Types for the others
 */
function getEventPromo($ident,$capid) {
    $query ="SELECT A.ACHIEVEMENT, A.DATE_PROMOTED
            FROM PROMOTION_RECORD A JOIN ACHIEVEMENT B
            ON A.ACHIEVEMENT=B.ACHIEV_CODE
            WHERE CAPID='$capid'
            ORDER BY B.ACHIEV_NUM";
    $promotions= allResults(Query($query, $ident));
    $query="SELECT B.EVENT_DATE, B.EVENT_CODE FROM ATTENDANCE A
            JOIN EVENT B ON A.EVENT_CODE=B.EVENT_CODE
            WHERE A.CAPID='$capid'
            AND B.EVENT_TYPE<>'M'
            AND B.EVENT_DATE BETWEEN ? AND ?";
    $activ=  prepare_statement($ident, $query);
    $query ="SELECT B.EVENT_DATE, B.EVENT_CODE, C.SUBEVENT_CODE 
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
    $query="SELECT BOARD_DATE FROM PROMOTION_BOARD
        WHERE CAPID='$capid'
        AND APPROVED=TRUE
        AND BOARD_DATE BETWEEN ? AND ?";
    $promo_board=  prepare_statement($ident, $query);
    $query="SELECT B.EVENT_DATE, B.EVENT_CODE FROM ATTENDANCE A, EVENT B
        WHERE B.EVENT_CODE=A.EVENT_CODE
        AND B.EVENT_TYPE='ENC'
        AND A.CAPID='$capid'";
    $encampment=  allResults(Query($query, $ident));
    $results=array();
    if(count($encampment)>0) {             //find encampment
        $results['BMI']['ENC']=array(new DateTime($encampment[0]['EVENT_DATE']),$encampment[0]['EVENT_CODE']);
    }
    if(count($promotions)>0) {
        for($i=0;$i<count($promotions)+1;$i++) {
            if($i==0) {                            //if at 0 so the first one try dec 1,1941-first promo
                bind($activ,"ss", array("1941-12-1",$promotions[$i]['DATE_PROMOTED']));
                bind($subevent,"ss",array("1941-12-1",$promotions[$i]['DATE_PROMOTED']));
              bind($promo_board,"ss",array("1941-12-1",$promotions[$i]['DATE_PROMOTED']));
                $promoFor=$promotions[$i]['ACHIEVEMENT'];
            } else if($i<count($promotions)) {          //if is less then the count so in bounds then bind by 2 promos
                bind($activ,"ss",array($promotions[$i-1]['DATE_PROMOTED'],$promotions[$i]['DATE_PROMOTED']));
                bind($subevent,"ss",array($promotions[$i-1]['DATE_PROMOTED'],$promotions[$i]['DATE_PROMOTED']));
                bind($promo_board,"ss",array($promotions[$i-1]['DATE_PROMOTED'],$promotions[$i]['DATE_PROMOTED']));
                $promoFor=$promotions[$i]['ACHIEVEMENT'];
            } else {                                                 //if hit top then try between last promo and now
                bind($activ,'ss',array($promotions[$i-1]['DATE_PROMOTED'],'curdate()'));
                bind($subevent,'ss',array($promotions[$i-1]['DATE_PROMOTED'],'curdate()'));
                bind($promo_board,'ss',array($promotions[$i-1]['DATE_PROMOTED'],'curdate()'));
                $query='SELECT NEXT_ACHIEV FROM ACHIEVEMENT 
                    WHERE ACHIEV_CODE=\''.$promotions[$i-1]['ACHIEVEMENT']."'";
                $promoFor=Result(Query($query, $ident),0,'NEXT_ACHIEV');
            }
            $activity = allResults(execute($activ));                  //execute the prepared statements and get results
            $subs = allResults(execute($subevent));
            $board=  allResults(execute($promo_board));   //execute the promotin board
            if(count($board)>0) {
                $results[$promoFor]['PB']=array(new DateTime($board[0]['BOARD_DATE'])); //get the promo board
            }
            if(count($activity)>0) //if had activity for promo then show it
                $results[$promoFor]['AC']=array(new DateTime($activity[0]['EVENT_DATE']),$activity[0]['EVENT_CODE']);  //shown
            for($j=0;$j<count($subs);$j++) {         //parse subevent results
                $results[$promoFor][$subs[$j]['SUBEVENT_CODE']]=array(new DateTime($subs[$j]['EVENT_DATE']),$subs[$j]['EVENT_CODE']);//ORGANIZE INTO ARRAY BY SUB_CODE AND STORE DATE
            }
        }
    }
    close_stmt($activ);
    close_stmt($subevent);
    return $results;
}
/**
 * Checks if an event-based promotion requirements was passed
 * 
 * It checks if they did attend that event
 * 
 * @param array $results The array of the requirements from this->promotionRequirements
 * @param String $achiev the achievement searching for
 * @param String $code the code of the requirement typ
 * @return dateTime|boolean if the requirement was passed then return the date, false if failed
 */
function checkEventPromo(array $results, $achiev,$code) {        //checks if the event exists for such promo
    if(isset($results[$achiev][$code]))        //if isset then return the date
        return $results[$achiev][$code];
    else 
        return false;                        //otherwise assume not and return false
}
/**
 * Returns an array of all the promotion requirements that are event based
 * 
 * @param Mysqli $ident the database connection
 * @return Array the type codes of all the promotion requirements that are attendance based.
 */
function specialPromoRequire($ident) {
    $results=array('AC','PB','EC');
    $query='SELECT TYPE_CODE FROM REQUIREMENT_TYPE
        WHERE IS_SUBEVENT=TRUE';
    $result =  allResults(Query($query, $ident));
    for($i=0;$i<count($result);$i++) {            //get all the results
        array_push($results,$result[$i]['TYPE_CODE']);
    }
    return $results;
}
/**
 * Displays the table for aproving the promotion requests.
 * 
 * @param mysqli $ident the database connection
 * @param String $memberType the member type that the promotion requests are to be displayed for
 * @param boolean $approve whether or not the member can approve the promotion
 */
function promotionAprove($ident,$memberType,$approve=false) {
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
        $signUps[$i][3]=(bool)$results[$i]['APPROVED'];                   //store if they have been approved
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
    <table class="table">
        <tr class="table"><th class="table">Member</th><th class="table">Promotion to:</th>
    <?php
    for($i=0;$i<count($header);$i++) {  //displays the headers
        echo "<th class=\"table\">".$header[$i]['TYPE_NAME']."</th>";  //show header for each thinger
    }
    if($approve)
        echo "<th class=\"table\">Approved</th></tr>\n";   //display approval header
    for($i=0;$i<count($signUps);$i++) {  //cycle trhough member sign-up
        echo "<tr class=\"table\">";
        if($signUps[$i][0]->check_promo_halt($ident))
            echo '<tr class="table"><td class="table" colspan="'.(count($header)+3).'" style="color:red">This member\'s Promotions are halted due to a retention in grade</td></tr>';
        if($approve)
            $approved=$signUps[$i][3];
        else
            $approved=null;
        $signUps[$i][0]->displayPromoRequest($header,true,true,$approved,false,$approve);
    }
    $_SESSION['signUps']=$signUps;
    $_SESSION['header']=$header;
    ?>
    </table>
    <?php
}
/**
 * A custom usort function for sorting promotion requests
 * 
 * Sorts the requests based upon which member has the least amount of requirements completed
 * it is based 1*the number of incomplete requirements+.5* the number of in-progress tasks
 * @param array $a the first item
 * @param array $b the second item
 * @return int 0 if they are equal 1 if a is less -1 if a is greater
 */
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
 * @param Integer $capid The capid of who it is
 * @param String $type the requirement type this is for
 * @param DateTime $date The date passed if any
 * @param float $percentage the percentage stored if already passed
 * @param string $achiev  The achievement this is for
 * @param string $code The event code for attendance requirements
 * @return returns whether or not more needs to be displayed.
 */
function promoRequireInput($capid, $type, DateTime $date= null,$percentage=null, $achiev=null, $tester=null,$code=null) {
    $append = $capid.$type.$achiev;
    if(in_array($type, array('LT','AE','DT'))) {
        if(is_numeric($percentage)) {   //if percent is a decimal change to percent
            $display =  round($percentage*100,2)."%";
        } else{
            $display = $percentage;
        }
        echo '%:<input type="text" size="1" maxlength="10" name="percentage'.$append.'" value="'.$display.'"/><br>';
    } if(!in_array($type,array('PB','PT'))) {
        if(!in_array($type, array('CD','SA','EC','AC'))) {
            echo 'ID:<input type="text" maxlength="6" size="1" name="tester'.$append.'"';
            if(isset($tester)) 
                echo ' value="'.$tester.'"';
            echo '/><br>';
        }
        enterDate(false, $append, $date);
        if(isset($code))
            echo '<a href="/login/attendance/event.php?eCode='.$code.'">View Event</a>';
        return false;
    } else {
        if($type=="PT") {      //if was pt test link to page for pt test
            echo '<a href="/login/testing/PTtest.php?capid='.$capid.'&achiev='.$achiev.'" target="_blank">enter PT test</a><br>';
        } else {        //if was promo board give link
        ?>
        <a href="/login/testing/promoBoard.php?field=enter&capid=<?php echo $capid; if(isset($date)) echo "&date=".$date->format(PHP_TO_MYSQL_FORMAT); ?>" target="_blank">enter Promotion Board</a><br>
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
    $approve=  prepare_statement($ident, "INSERT INTO PROMOTION_RECORD(CAPID,ACHIEVEMENT,DATE_PROMOTED,APROVER)
        VALUES(?,?,?,?)");  //create a prepared statement to approve one 
    $insert =  prepare_statement($ident,"INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET,PASSED_DATE,PERCENTAGE, TESTER)
        VALUES(?,?,?,?,?,?,?)");         //create prepared statement to insert requirements
    $update = prepare_statement($ident,"UPDATE REQUIREMENTS_PASSED
        SET PASSED_DATE=?, PERCENTAGE=?, TESTER=?
        WHERE CAPID=? AND ACHIEV_CODE=? AND REQUIREMENT_TYPE=?");
    $deleter=connect("delete");
    $deleteTest =  prepare_statement($deleter,"DELETE FROM TESTING_SIGN_UP
        WHERE CAPID=? AND REQUIRE_TYPE=?");
    $deleteRequest = prepare_statement($deleter,"DELETE FROM PROMOTION_SIGN_UP
        WHERE CAPID=?");
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
                    bind($approve,"issi", array($capid,$current[1],new DateTime(),$_SESSION['member']->getCapid()));  //aprove it and record promotion
                    execute($approve);
                    bind($deleteRequest,"i",$capid);
                    execute($deleteRequest);          //delete the request
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
    close($deleter);
}
/**
 * Parses percentage input and cleans it.
 * 
 * @param type $append the appended stuff to post array
 * @param array $inputs the post array
 * @param type $passing the passing percentage as a decimal
 * @return null|boolean|float returns null if no input, false if incorrent or not passing, and the percentage as a float
 */
function parsePercent($append, array $inputs, $passing) {
    if(isset($inputs['percentage'.$append]))
        $input = $inputs['percentage'.$append];
    else 
        return null;
    if($input=="") 
        return null;
    $input= str_replace("%","", $input);      //strips out percent signs
    if(strpos($input,"/")===false) {          //if there is no / assume decimal or percent
        $percent=  cleanInputInt($input,5,"percentage".$append,false); //clean and parse as num
        if($percent>1) {         //if was a percent i.e. >1 and a big num
            $percent = $percent/100;
        }  //else assume is decimal and is all good
    } else {
       $input =  cleanInputDate($input,"#^[0-9]+/[0-9]+$#",strlen($input),"percentage$append");
       $input = explode("/", $input);   //split into numerator and denominator
       $numerator = cleanInputInt($input[0],3,"numerator$append",false);    //take the numerator from first thing
       $denominator = cleanInputInt($input[1],3, 'denominator'.$append,false);  //take the denom from second position
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
    if(count($exploded)==1)
        return $exploded[0];
    $minute=$exploded[0];
    $seconds=$exploded[1];
    return $minute+$seconds/60;
}
/**
 * Verifies a password meets password requirements
 * 
 * Verifies the given passwords match. Sanatizes the password.
 * Then verifies it meets CAPR110-1 guidance.
 * 
 * @param String $pass
 * @param String $retype
 * @param String $old - the old password to check the passwords did change
 * @param Boolean $checkOld true to check the old password, false if this the first password
 * @return array 0=>boolean, true passed, false otherwise, 1=>the password on success,1+=> the error message(s) otherwise each message it's own index
 */
function verify_password($pass, $retype,$old,$checkOld=true) {
    $passes=true;
    $errors=array();
    if($pass!=$retype) {             //if the passwords din't match exit out
        return array(false, "The passwords didn't match");
    } else {             //if they did match go on
        if(strlen($pass)<8) {  //if has less than min length log
            $passes=false;
            array_push($errors, "Password must be at least 8 characters");
        }
        if($checkOld&&$pass===$old) {
            $passes=false;
            array_push($errors,'Password can not be your old password');
        }   
        $groups=array('#[a-z]#','#[A-Z]#','#[0-9]#','~[`\!@#\$%\^&\*\(\)\+\=_\-\{\}\[\]\\\|\:;"\'\?/\<\>,\.]~');
        $count=0;
        for($i=0;$i<count($groups);$i++) {  //checks all the various classs requirements
            if(preg_match($groups[$i],$pass)===1) {  //if found one of the criteria
                $count++;
            }
            if($count>=3)
                break;
        }
        if($count>=3&&$passes)
            return array(true,$pass);
        else
            array_push($errors,"Did not have three of the four categories");
        return array_merge(array(false),$errors);  //returns the errors
    }
}
/**
 * A class for CAP members
 * 
 * It holds all the functions, and data for members. The amount of Data held is based on the init levels
 * 
 * init levels :
 * -1= all from input 
 * 0=capid 
 * 1=capid+name+gender+achievement 
 * 2=1+text+member_type+picture 
 * 3=2+dates 
 * 4=3+emergency+unit  
 */
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
    private $isEmpty = false;     //member doesn't exist    
    private $initLevel;
    private $promoRecord=array();
    /**
     * Creaes the member object
     * 
     * @param Integer|numeric string $capid the capid of the member
     * @param Integer $level the init level to start at, -1 if you want to enter the data and not get it from the database
     * @param mysqli $ident the database connection
     * @param String $name_last the Last name
     * @param String $name_first The first name
     * @param string $gender the gender of the member
     * @param dateTime $DoB the member's date of birth
     * @param String $memberType the member's member type
     * @param String $achievement the member's current achievement
     * @param String $text_set the member's textbook set
     * @param String $unit the member's home unit
     * @param DateTime $Date_of_Join the date the member joined 
     */
    public function __construct($capid, $level, $ident, $name_last = null, $name_first = null, $gender = null, dateTime $DoB = null, $memberType = null, $achievement = null, $text_set = null, $unit = null, DateTime $Date_of_Join = null) {
        $this->capid = cleanInputInt($capid, 6, "CAPID");
        if ($level == -1) {             //levels -1= all from input 0=capid 1=capid+name+gender+achievement 2=1+text+member_type 3=2+dates 4=3+emergency+unit         
            $this->badInput = false;
            $this->capid = $capid;
            $this->name_last = $name_last;
            $this->name_first = $name_first;
            $this->gender = $gender;
            $this->DoB = $DoB;
            $this->memberType = new memberType(cleanInputString($memberType, 1, "Member Type",false),$ident);
            $this->achievement = $achievement;
            $this->text_set = $text_set;
            $this->unit = new unit(cleanInputString($unit, 10, "unit",false),$ident);
            $this->Date_of_Join = $Date_of_Join;
            $this->initLevel=4;
        } else {
            $this->init($level, $ident);
        }
    }
    public function addEmergencyContact($Name, $relation, $number) {
        array_push($this->emergencyContacts, new Contact($Name, $relation, $number, true));
    }
    public function addEmeregencyContactArray(array $input) {
        for($i=0;$i<5;$i++) {
            if(isset($input["ContName$i"],$input["relation$i"],$input["number$i"])) {
                if($input["ContName$i"]!=""&&$input["relation$i"]!=""&&$input["number$i"]!="") {
                    $name=  cleanInputString($input["ContName$i"],32,"Contact Name $i", false);
                    $relation=  cleanInputString($input["relation$i"],2,"Relation $i",false);
                    $phone= cleanInputString($input["number$i"],12, "contact number $i",false,true);
                    array_push($this->emergencyContacts,new contact($name,$relation,$phone));
                }
            }
        }
    }
    public function insertMember($ident) {
        $date_current=$this->Date_of_Join;
        $now=new DateTime();
        while($date_current->format('U')-$now->format('U')<0) {  //while the date current is less than right now
            $date_current->add(new DateInterval('P1Y'));  //add 1 year until past right now
        }
        $query = "INSERT INTO MEMBER (CAPID,NAME_LAST,NAME_FIRST,GENDER,DATE_OF_BIRTH,ACHIEVEMENT,MEMBER_TYPE,TEXTBOOK_SET,HOME_UNIT,DATE_JOINED, DATE_CURRENT)
            VALUES('$this->capid','$this->name_last','$this->name_first','$this->gender','" .$this->DoB->format(PHP_TO_MYSQL_FORMAT) ."','".$this->achievement."','" . $this->memberType->getCode(). "','".$this->text_set."',
                '".$this->unit->getCharter() . "','".$this->Date_of_Join->format(PHP_TO_MYSQL_FORMAT)."','".$date_current->format(PHP_TO_MYSQL_FORMAT)."')";
        return Query($query, $ident);
    }
    public function insertEmergency($ident) {
        $stmt = prepare_statement($ident, "INSERT INTO EMERGENCY_CONTACT (CAPID,RELATION,CONTACT_NAME,CONTACT_NUMBER) 
            VALUES('".$this->capid."',?,?,?)");
        $success=true;
        for ($row=0;$row < count($this->emergencyContacts);$row++) {
            $con = $this->emergencyContacts[$row]->getName();
            $relat = $this->emergencyContacts[$row]->getRelation();
            $num = $this->emergencyContacts[$row]->getPhone();
            bind($stmt,"sss",array($relat,$con,$num));
            if(!execute($stmt))
                $success=false;
        }
        return $success;
    }
    public function sign_in($ident, $message, $event_code = null) {
        if ($event_code == null) {                         //assume current event
            $results= Query("SELECT EVENT_CODE FROM EVENT WHERE IS_CURRENT=TRUE", $ident, $message);
            if(numRows($results)>0)
                $event_code=  Result ($results, 0,"EVENT_CODE");
        }              //else use provided event
        $results=  Query("SELECT CAPID FROM ATTENDANCE WHERE CAPID='".$this->capid."' AND EVENT_CODE='$event_code'", $ident);
        if(numRows($results)<=0)
            return Query("INSERT INTO ATTENDANCE(CAPID,EVENT_CODE)
                VALUES('" . $this->capid . "','$event_code')", $ident, $message);   //if not already inserted then let's insert it
        
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
            <table class=\"table\">
                <tr class=\"table\"><th class=\"table\">Sign-Up</th><th class=\"table\">Test Type</th><th class=\"table\">Test Name</th><th class=\"table\">Achievement</th></tr>\n";
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
                    E.CAPID='" . $this->capid . "')
                    and A.REQUIREMENT_TYPE NOT IN(SELECT REQUIREMENT_TYPE FROM 
                    TESTING_SIGN_UP WHERE CAPID='".$this->capid."')", $ident);  //get all available requirement sign_up
        if (numRows($results) > 0) {                                   //if can sign up for testing
            for ($row = 0; $row < numRows($results); $row++) {
                echo "<tr class=\"table\"><td class=\"table\"><input type=\"checkbox\" name=\"signup[]\" value=\"" . Result($results, $row, "REQUIREMENT_TYPE") . "\"/></td>"; //create checkbox
                echo"<td class=\"table\">" .Result($results, $row, "TYPE_NAME") . "</td>";         //show test type
                if (is_null(Result($results, $row, "NAME"))) {                   //if the name is null for the test just echo n/a
                    echo "<td class=\"table\">n/a</td>";
                } else {
                    echo "<td class=\"table\">" . Result($results, $row, "NAME") . "</td>";    //display test name
                }
                echo "<td class=\"table\">$achievName</td></tr>";                      //displays achievement name
            }
        } if(!$this->check_promo_halt($ident)){               //display promotion sign up if available
            if(numRows(query("SELECT CAPID FROM PROMOTION_SIGN_UP WHERE CAPID='".$this->capid."'",$ident))==0) {
                echo "<tr class=\"table\"><td class=\"table\"><input type=\"checkbox\" name=\"signup[]\" value=\"PR\"/></td>";
                echo "<td class=\"table\">Promotion</td><td class=\"table\">n/a</td><td>$achievName</td></tr>";
            }
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
                    VALUES('" . $this->capid ."','$code', CURDATE())";
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
                        <table class=\"table\">
                        <tr class=\"table\"><th class=\"table\">Achievement</th>";                     //creates headers of rows based on requirement types
                    for ($row = 0; $row < count($header); $row++) { 
                        echo "<th class=\"table\">" .$header[$row]["TYPE_NAME"] . "</th>";  //make them into headers
                    }
                    echo "</tr>\n";
                    $query = "SELECT CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS ACHIEV_NAME, A.ACHIEV_CODE FROM ACHIEVEMENT A, GRADE B 
                        WHERE A.MEMBER_TYPE='".$this->memberType."'   
                        AND A.ACHIEV_CODE <> '0'
                        AND B.GRADE_ABREV=GRADE
                        AND A.ACHIEV_NUM <= ( SELECT 
                        D.ACHIEV_NUM FROM ACHIEVEMENT D, ACHIEVEMENT B
                        JOIN MEMBER C ON C.ACHIEVEMENT=B.ACHIEV_CODE
                        WHERE C.CAPID='".$this->capid."'
                        AND B.NEXT_ACHIEV=D.ACHIEV_CODE)
                        ORDER BY ACHIEV_NUM";
                    $achievements = allResults(Query($query, $ident));    //get all the achievements ^
                    for($i=0;$i<count($achievements);$i++) {      //loop through rows
                        $this->getPromotionInfo($achievements[$i]['ACHIEV_CODE'], $ident);
                        $promo_wait=null;
                        if(isset($this->promoRecord['PRO']))
                            $promo_wait=$this->promoRecord['PRO'][1];
                        $is_ok=$this->check_promotion_wait($ident, $achievements[$i]['ACHIEV_CODE'], $promo_wait);
                        if($i==count($achievements)-1) {  //if last achievement, then
                            if($this->check_promo_halt($ident)&&$date)
                                echo '<tr class="table"><td class="table" colspan="'.(count($header)+2).'" style="color:red">Promotions have been halted due to a retention in grade from a promotion Board</td></tr>'."\n";
                        }
                        if($is_ok!==true)
                            echo '<tr class="table"><td class="table" colspan="'.(count($header)+2) .'" style="color:red">The time since last promotion is too short: You must wait until:'.$is_ok->format(PHP_DATE_FORMAT)."</td></tr>";
                        echo '<tr class="table"><td class="table">'.$achievements[$i]['ACHIEV_NAME'].'</td>';
                        $this->displayPromoRequest($header, $date, $edit,null,true);
                    }
                    ?>
        </table>
    </table>
    <?php
    }
    public function editInformation($page, $identifier) {
                    //displays table for input
                    echo "<form action=\"$page\" method=\"post\"><table class=\"table\"><tr class=\"table\"><th class=\"table\">Last Name</th><th class=\"table\">First Name</th>
            </tr>";
                    //displays input fields
                    echo "<tr class=\"table\"><td class=\"table\"><input type=\"text\" name=\"Lname\" value=\"" . $this->name_last . "\" size=\"4\"/></td>
        <td class=\"table\"><input type=\"text\" name=\"Fname\" value=\"" . $this->name_first . "\" size=\"4\"/></td>
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
        echo "<table class=\"table\"><tr class=\"table\">
    <th class=\"table\">Contact Name</th><th class=\"table\">Contact's Relation</th><th class=\"table\">Contact's Phone Number</th></tr>\n";
        $row = 0;
        while ($row < 5) {
            if (array_key_exists($row, $this->emergencyContacts)) {
                echo "<tr class=\"table\"><td class=\"table\"><input type=\"text\" name=\"ContName$row\" value=\"" . $this->emergencyContacts[$row]->getName() . "\" size=\"7\"/></td><td>";
                dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true, $this->emergencyContacts[$row]->getRelation());
                echo "</td><td class=\"table\"><input type=\"text\" name=\"number$row\" placeholder=\"###-###-####\" size=\"16\"/></td>\n";
                $row++;
            } else {
                echo "<tr><td class=\"table\"><input type=\"text\" name=\"ContName$row\" size=\"7\"/></td><td>";
                dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true);
                echo "</td><td class=\"table\"><input type=\"text\" name=\"number$row\" size=\"16\" v/></td>\n";
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
                    $results = Query("SELECT TEXTBOOK_SET,MEMBER_TYPE FROM MEMBER
            WHERE CAPID='" . $this->capid . "'", $ident);
                    if (numRows($results) > 0) {
                        $this->text_set = Result($results, 0, "TEXTBOOK_SET");
                        $this->memberType = Result($results, 0, "MEMBER_TYPE");
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
            if (isset($input["ContName".$row])) {
                if ($input["ContName".$row] !== "") {
                    if (array_key_exists($row, $this->emergencyContacts)) {                      //if not a new one edit it
                        $oldRelat = $this->emergencyContacts[$row]->getRelation();
                        $this->emergencyContacts[$row]->setName($input["ContName" . $row]);
                        $this->emergencyContacts[$row]->setRelation($input["relation" . $row]);
                        if(isset($input["number".$row])&&$input['number'.$row]!=="")
                            $this->emergencyContacts[$row]->setPhone($input["number" . $row]);
                        if (!$this->updateContact($row, $oldRelat, $ident)) {
                            $contactSuccess = false;
                        }
                    } else {
                        array_push($this->emergencyContacts, new contact($input["ContName".$row], $input["relation" . $row], $input["number" . $row]));
                        if (!$this->insertSingleContact($row, $ident)) {
                            $contactSuccess = false;
                        }
                    }                 
                }
            }
        }
        return $contactSuccess;
    }
    public function updateContact($row, $oldRelat, $ident) {
        $query = "UPDATE EMERGENCY_CONTACT
    SET RELATION='" . $this->emergencyContacts[$row]->getRelation() . "',
    CONTACT_NAME='" . $this->emergencyContacts[$row]->getName() . "',
        CONTACT_NUMBER='" . $this->emergencyContacts[$row]->getPhone() . "'
            WHERE CAPID='" . $this->capid . "'
                AND RELATION='" . $oldRelat . "'";
        return Query($query, $ident);
    }
    public function insertSingleContact($row, $ident) {
        $query = "INSERT INTO EMERGENCY_CONTACT (CAPID,RELATION,CONTACT_NAME,CONTACT_NUMBER) VALUES";
        if(isset($this->emergencyContacts[$row])&& get_class($this->emergencyContacts[$row])==="contact") {
            $con = $this->emergencyContacts[$row]->getName();
            $relat = $this->emergencyContacts[$row]->getRelation();
            $num = $this->emergencyContacts[$row]->getPhone();
            $query = $query . "('".$this->capid."','$relat','$con','$num')";
            return Query($query, $ident);
        }
    }
    public function updateFields($ident) {
        $query = "UPDATE MEMBER 
    SET NAME_LAST='" . $this->name_last . "',
    NAME_FIRST='" . $this->name_first . "',
    DATE_OF_BIRTH='".$this->DoB->format(PHP_TO_MYSQL_FORMAT)."',
    DATE_JOINED='".$this->Date_of_Join->format(PHP_TO_MYSQL_FORMAT)."',
    ACHIEVEMENT='" . $this->achievement . "',
    MEMBER_TYPE='" . $this->memberType . "',
    TEXTBOOK_SET='" . $this->text_set . "'
    WHERE CAPID='" . $this->capid . "'";
        return Query($query, $ident);
    }
    public function approveFields($ident) {
        echo "<tr class=\"table\"><td class=\"table\"><input type=\"checkbox\" name=\"approve[]\" value=\"" . $this->capid . "\"/></td>";
        echo "<td class=\"table\"><input type=\"text\" size=\"1\" name=\"capid" . $this->capid . "\" value=\"" . $this->capid . "\"/></td>";
        echo "<td class=\"table\"><input type=\"text\" size=\"1\" name=\"Lname" . $this->capid . "\" value=\"" . $this->name_last . "\"/></td>";
        echo "<td class=\"table\"><input type=\"text\" size=\"1\" name=\"Fname" . $this->capid . "\" value=\"" . $this->name_first . "\"/></td>";
        echo "<td class=\"table\"><select name=\"gender" . $this->capid . "\">";
        echo "<option value=\"M\" ";                   //drop down menu for gender
        if ($this->gender == "M") {                          //sets default to male if so
            echo "selected=\"yes\"";
        }
        echo ">male</option><option value=\"F\" ";
        if ($this->gender == "F")
            echo "selected=\"yes\"";
        echo ">female</option>";
        echo "</select></td><td class=\"table\">";                                  //end of drop down
        enterDate(false, "DoB" . $this->capid, $this->DoB);
        echo "</td><td class=\"table\">";
        dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) AS GRADE FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "grade" . $this->capid, $ident, false, $this->achievement);
        echo "</td><td class=\"table\">";
        dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES", "member" . $this->capid, $ident, false, $this->memberType);
        echo "</td><td class=\"table\">";
        dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL'", "text" . $this->capid, $ident, false, $this->text_set);
        echo "</td><td class=\"table\">";
        dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM CAP_UNIT", 'unit' . $this->capid, $ident, false, $this->unit->getCharter());
        echo "</td><td class=\"table\">";
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
        $query = "UPDATE MEMBER SET
            NAME_LAST='" . $this->name_last . "',
            NAME_FIRST='" . $this->name_first . "',
            GENDER='" . $this->gender . "',
            DATE_OF_BIRTH='" . $this->DoB->format(PHP_TO_MYSQL_FORMAT) . "',
            ACHIEVEMENT='" . $this->achievement . "',
            MEMBER_TYPE='" . $this->memberType . "',
            TEXTBOOK_SET='" . $this->text_set . "',
            HOME_UNIT='" . $this->unit . "',
            DATE_JOINED='" . $this->Date_of_Join->format(PHP_TO_MYSQL_FORMAT) . "',
            APPROVED=TRUE";
        return Query($query, $ident);
    }
    public function getPicture() {
        return "/pictures/profile/getter.php?capid=".$this->capid;
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
       $ident=  connect("Sign-in");
       $buffer= $this->getGrade($ident, true)." ".$this->name_last.", ".$this->name_first."-".$this->capid;
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
    public function link_report($new_tab=false) {
        $buffer= '<a href="/login/member/report.php?capid='.$this->capid.'"';
        if($new_tab)
            $buffer.= ' target="_blank"';
        $buffer.='>'.$this->title().'</a>';
        return $buffer;
    }
    public function get_text() {
        return $this->text_set;
    }
    public function getPromotionInfo($promoFor, mysqli $ident,$name=null) {
        $this->promoRecord=null;
        $this->promoRecord['achiev']=$promoFor;
        $this->promoRecord['NAME']=$name;
        $query = "SELECT REQUIREMENT_TYPE AS TYPE, PASSED_DATE AS DATE, PERCENTAGE, TESTER
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
        } else {                                //else get the field to display the input
            $this->promoRecord['PRO']=array('I',null);
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
                    $this->promoRecord[$current]=array('P',new DateTime($passed[$j]['DATE']),$percent, "percent"=>$requirements[$i]['PERCENT'],"tester"=>$passed[$j]['TESTER']);
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
                        if(isset($buffer[1]))
                            $code =$buffer[1];   //get the event code
                        else
                            $code=null;
                        $this->promoRecord[$current]=array('P',$buffer[0],"percent"=>$requirements[$i]['PERCENT'],"code"=>$code);
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
     * @param boolean $approved weather or not the promotion is approved if null the approve field will not be displayed
     * @param boolean $showPromo whether or not to show as a whole promotion report, false will show name in the row, true won't
     */
    function displayPromoRequest(array $header, $disPlayDates=false, $canEdit=false ,$approved=null,$showPromo=false) {
        if(!$showPromo) {
            echo "<td class=\"table\">".$this->link_report()."</td>";   //show member
        }
        if(isset($this->promoRecord['NAME'])&&$this->promoRecord['NAME']!=null)
            echo "<td class=\"table\">".$this->promoRecord['NAME']."</td>";
        for($j=0;$j<count($header);$j++) {             
            $index=$header[$j]['TYPE_CODE'];   //get the current requirement
            if(isset($this->promoRecord[$index])) {  //if has that requirement do stuff
                $current=$this->promoRecord[$index];  //load it
                echo '<td class="table '.$current[0].'">';
                $displayText = true;
                if($disPlayDates) {       //if displaying date
                    if($canEdit) {
                        $date=null;
                        $percent=null;
                        if(isset($current[1])) {       //if date and percent set get it
                            $date = $current[1];
                        }if(isset($current[2])) { 
                            $percent=$current[2];
                        }
                        if(isset($this->promoRecord[$index]['tester']))
                            $tester=$this->promoRecord[$index]['tester'];
                        else
                            $tester=null;
                        if(isset($this->promoRecord[$index]['code']))
                            $code=$this->promoRecord[$index]['code'];
                        else
                            $code=null;         //get the event code
                        $displayText=promoRequireInput($this->capid,$index, $date, $percent,$this->promoRecord['achiev'],$tester,$code);  //display the input
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
                echo '<td class="table">n/a</td>';
            }
            
        }   //display yes bubble if allowed to edit info
        if($canEdit&&  is_bool($approved)) {
            echo '<td><input type="radio" name="'.$this->getCapid().'" value="yes"';
            if($approved) 
                echo ' checked/>';
            else echo '/>';
            echo "Yes<br>";
            //display no bubble
            echo '<input type="radio" name="'.$this->getCapid().'" value="no"';
            if(!$approved)
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
            if(isset($this->promoRecord[$type])) {
                $append = $this->capid.$type.$achiev;            //the appended string
                $has_percent=isset($this->promoRecord[$type]['percent']);
                if($has_percent)
                    $percent = parsePercent($append, $input, $this->promoRecord[$type]["percent"]); //parse the percentage
                else 
                    $percent=null;
                $date=  parse_date_input($input, $append);                 //parse the date
                if(isset($input['tester'.$append])&&$input['tester'.$append]!=0&&$input['tester'.$append]!=null) {
                    $ident=  connect('login');
                    $tester= new member($input["tester".$append],1,$ident);
                    close($ident);
                }
                //if date isn't null and (percent isn't specified or if it is and is valid and (tester is valid or is not specified
                if(!isset($tester)||(isset($tester)&&$tester->exists())) { //check that it is a valid tester
                    if($date!=null&&(($has_percent&&$percent!=false&&$this->promoRecord[$type]['percent']!==null)||!$has_percent||$this->promoRecord[$type]['percent']===null)) {         //if date is valid and the percent is valid
                        switch($this->promoRecord[$type][0]) {                     //switchfor choosing which prepared satement
                            case "P":
                                bind($update,"sdiiss",array($date->format(PHP_TO_MYSQL_FORMAT),$percent,$tester->getCapid(),$this->capid,$achiev,$type));
                                execute($update);
                                break;
                            case "I": //goes down to next case
                            Case "F":
                                bind($insert,"issssdi",array($this->capid,$achiev,$type,$this->text_set,$date->format(PHP_TO_MYSQL_FORMAT),$percent,$tester->getcapid()));           //insert 
                                execute($insert);
                                $this->promoRecord[$type][0]='P';     //set it to passed to easily checked if passed all requirements
                                if($this->promoRecord[$type][0]=='F') 
                                    break;  //break if they didn't sign up
                                bind($delete,'is',array($this->capid,$type));
                                execute($delete);            //execute and delete the sign-up
                                break;
                        }
                    }
                } else {  //if the tester is invalid yell at people!
                    echo '<span class="F">The tester CAPID:'.$tester->getCapid()." is invalid.</span>";
                }
            }
            $tester=null;
            unset($tester);
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
        $insert =  prepare_statement($ident,"INSERT INTO REQUIREMENTS_PASSED(CAPID, ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET,PASSED_DATE,PERCENTAGE, TESTER)
            VALUES(?,?,?,?,?,?,?)");         //create prepared statement to insert requirements
        $update = prepare_statement($ident,"UPDATE REQUIREMENTS_PASSED
            SET PASSED_DATE=?, PERCENTAGE=?, TESTER=?
            WHERE CAPID=? AND ACHIEV_CODE=? AND REQUIREMENT_TYPE=?");
        $deleteTest =  prepare_statement($ident,"DELETE FROM TESTING_SIGN_UP
            WHERE CAPID=? AND REQUIRE_TYPE=?");
        $header=$_SESSION['header'];
        $query = "SELECT A.ACHIEV_CODE FROM ACHIEVEMENT A, ACHIEVEMENT B, ACHIEVEMENT C
            WHERE B.ACHIEV_CODE='".$this->achievement."' AND
                C.ACHIEV_CODE=B.NEXT_ACHIEV AND
                A.ACHIEV_NUM<=C.ACHIEV_NUM
                ORDER BY A.ACHIEV_NUM";                                 //get all the achievements needed in order
        $achiev=  allResults(Query($query,$ident));
        for($i=0;$i<count($achiev);$i++) {                     //cycles through all the achievements and parses them seperately
            $buffer=$achiev[$i]['ACHIEV_CODE'];
            $this->getPromotionInfo($buffer, $ident);                //store the right promotion information and requirements
            $this->parsePromoEdit($insert,$update,$deleteTest,$header,$input,$buffer);   //parses the information for real this time 
            $append=$this->capid.'PRO'.$buffer;
            $date=  parse_date_input($input, $append);  //get the date
            if($this->checkPassing($ident,$date)&&$date!==null) {
                    $this->promote($buffer,$date,$ident);
            }
        }
         close_stmt($approve);
        close_stmt($insert);
        close_stmt($update);
        close_stmt($deleteTest);
    }
    /**
     * Checks if all requirements for promotion are passed
     * @param mysqli $ident the database connection
     * @param dateTime $date the date for the promotion
     * @return boolean true if and only if all requirements are passed false otherwise
     */
    function checkPassing($ident,  DateTime $date=null) {
        foreach($this->promoRecord as $key=>$buffer) {
            if($buffer[0]!='P'&&!in_array($key, array('PRO','achiev','NAME'))) { //if not passed and isn't the promotion tell them they didn't pass
                return false;
            }
        }
        if($this->check_promotion_wait($ident,$this->promoRecord['achiev'],$date))
            return true;   //if found nothing then
//        return false;
    }
    /**
     * Checks if there is the regulation required waiting period between a promtion
     * and the one before it.
     * 
     * @param type $ident the databse connection
     * @param type $achiev the achievement your checking for
     * @param dateTime the date to check for this 
     * @return booleann|dateTime returns true if enough time has passed, otherwise returns a dateTime of when it will 
     * be enough time
     */
    function check_promotion_wait($ident,$achiev=null,  DateTime $date=null) {
        if($ident==null)
            $achiev=$this->get_next_achiev ($ident);
        if($date==null)
                $date=new DateTime();
        $query="SELECT DATE_PROMOTED FROM PROMOTION_RECORD A, ACHIEVEMENT B
            WHERE A.ACHIEVEMENT=B.ACHIEV_CODE
            AND A.CAPID='".$this->capid."'
            AND B.NEXT_ACHIEV='$achiev'";
        $results=  allResults(query($query,$ident));
        if(count($results)==0)  //if no promotion date assume it's fine
            return true;
        $last_promo=new dateTime($results[0]['DATE_PROMOTED']);
        $interval=PROMOTION_WAIT;
        if(($date->format("U")-$last_promo->format("U"))<$interval) { //if under time say so
            return $last_promo->add(DateInterval::createFromDateString(PROMOTION_WAIT." seconds"));    //returns the date you need to wait to
        }
        return true;
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
            and ((D.ACHIEV_NUM BETWEEN B.ACHIEV_NUM AND C.ACHIEV_NUM)
                OR END_ACHIEV IS NULL)";
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
        $query = "SELECT B.PHASE FROM ACHIEVEMENT A, ACHIEVEMENT B
            WHERE A.ACHIEV_CODE='".$this->achievement."'
            AND A.NEXT_ACHIEV=B.ACHIEV_CODE";
        $results=  allResults(Query($query, $ident));
        if(count($results)>0)
            return $results[0]['PHASE'];
        return false;
    }
    function get_achievement() {
        return $this->achievement;
    }
    /**
     * Enters a member promotion. If promotion is most recent update member records
     * 
     * @param String $achiev the achievement the promotion's for
     * @param dateTime $date the date of the promotion
     * @param mysqli $ident the database connection
     * @return boolean True on success false on failure
     */
    function promote($achiev, dateTime $date, mysqli $ident) {
        $insert=true;           //if need to insert the promotion
        $complete=false;        //if we need to hit everything for most recent promotion
        $success=true;
        if($this->get_next_achiev($ident)==$achiev) {            //if this is the most recent one hit with oodles
            $insert=true;
            $complete=true;
        } else {
            $query="SELECT COUNT(*) AS COUNT FROM PROMOTION_RECORD 
                WHERE CAPID = ".$this->capid." AND  ACHIEVEMENT = '$achiev'";  //checks if we have it on record already
            $result=  allResults(Query($query, $ident));
            if($result[0]['COUNT']>0)
                $insert=false;
        }
        if($insert) {          //if just needs a clean insert
            $query="INSERT INTO PROMOTION_RECORD(CAPID, ACHIEVEMENT, DATE_PROMOTED)
                VALUES('".$this->capid."','$achiev','".$date->format(PHP_TO_MYSQL_FORMAT)."')";  //insert
            if(!Query($query, $ident))
                    $success=false;
        } else {
            $query="UPDATE PROMOTION_RECORD SET DATE_PROMOTED='".$date->format(PHP_TO_MYSQL_FORMAT).
                "' WHERE CAPID='".$this->capid."' AND ACHIEVEMENT='$achiev'";  //update the promotion date
            if(!Query($query, $ident))
                    $success=false;
        }
        if($complete) {
            $query="DELETE FROM PROMOTION_SIGN_UP
                WHERE CAPID='".$this->capid."' AND ACHIEV_CODE='$achiev'";  //delete the promotion request
            if(!Query($query, $ident))
                    $success=false;
            $query="UPDATE MEMBER SET ACHIEVEMENT='".$this->get_next_achiev($ident).
                    "' WHERE CAPID='".$this->capid."'";                //update the member's file for the promotion
            if(!Query($query, $ident))
                    $success=false;
        }
        $this->reload($ident);
        return $success;
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
    function display_Emergency($ident) {
        $emergency=$this->emergencyContacts;
        $num=count($emergency);
        for($i=0;$i<$num;$i++) {
            echo "<tr><td class=\"blank\"></td>";   //displays empty cell for asthetics
            $buffer=$emergency[$i];
            echo "<td>".$buffer->getName()."- ".$buffer->getRelation_full($ident)."</td>";  //shows name
            echo "<td>".$buffer->getPhone()."</td></tr>";   //displays phone number
        }
    }
    /**
     * Reloads all the membership information from the database
     * @param mysqli $ident the database connection
     */
    function reload($ident) {
        $init=$this->initLevel;
        $this->initLevel=0;
        $this->init($init,$ident);
    }
    /**
     * Creates a password hash.
     * 
     * Creates a SHA512 hash from the password, salted with the member's capid, 
     * and the passed salt.
     * 
     * @param type $pass the password to create a hash from
     * @param type $salt the salt to add to the password
     * @return String 128 digit hash 
     */
    function hash_password($pass,$salt) {
        $to_hash=$salt.$pass.$this->capid;
        return base64_encode(hash("sha512",$to_hash,true));
    }
    /**
     * Verifies the user provided the right password
     * 
     * Uses member::has_password() to create a password hash, and compares it to
     * the hash in the member table
     * 
     * @param mysqli $ident the database connection
     * @param String $pass the password to check
     * @param String $salt the system salt
     * 
     * @return boolean true if it matches false otherwise
     */
    function check_password($ident,$pass, $salt) {
        $passes=  allResults(Query("SELECT PASS_HASH FROM MEMBER WHERE CAPID=".$this->capid, $ident));
        if(count($passes)>0)
            return $passes[0]['PASS_HASH']==$this->hash_password ($pass, $salt);
        return false;
    }
    /**
     *Changes the password in the database.
     *  
     * @param mysqli $ident the database connection
     * @param String $hash the hash of the password from hash_password()
     * @return Boolean true on success.
     */
    function set_password($ident,$hash) {
        $query="UPDATE MEMBER SET PASS_HASH='$hash', LAST_PASS_CHANGE=CURDATE() WHERE CAPID='".$this->capid."'";
        return Query($query, $ident);
    }
    /**
     * Checks if the member's password is expired
     * 
     * @param  mysqli $ident the database connection
     * @return mixed true if the password is expired, otherwise the number of days to the expiration
     */
    function check_pass_life($ident) {
        $query="SELECT DATEDIFF(CURDATE(),LAST_PASS_CHANGE) AS DIFF FROM MEMBER WHERE CAPID='".$this->capid."'";
        $results = allResults(Query($query, $ident));
        $diff=$results[0]['DIFF'];
        if($diff>=PASSWORD_LIFE)
            return true;
        else 
            return PASSWORD_LIFE-$diff;
    }
    /**
     * Checks if a member's membership is terminated
     * 
     * @param mysqli $ident the database connection
     * @return boolean true if the member is terminated false if not
     */
    function check_terminated($ident) {
        $query="SELECT DATE_TERMINATED IS NOT NULL AS TERM FROM MEMBER
            WHERE CAPID='".$this->capid."'";
        $results=  allResults(Query($query, $ident));
        if(count($results)>0&&$results[0]['TERM']==="1")
            return true;
        return false;
    }
    /**
     * Insert Staff Positions into the database
     * 
     * @param array $input the array of the checked boxes
     * @param type $ident the database connection
     * @return boolean true success false if fail!
     */
    function insert_staff_position(array $input, $ident) {
        $success=true;
        $stmt= prepare_statement($ident,"INSERT INTO STAFF_POSITIONS_HELD(STAFF_POSITION, CAPID)
            VALUES(?,'".$this->capid."')");
        for($i=0;$i<count($input);$i++) {
            bind($stmt,"s",  array(cleanInputString($input[$i],6,"Staff position code",false)));
            if(!execute($stmt))
                $success=false;
        }
        return $success;
    }
    /**
     * Returns the member's membership type
     * 
     * @return string the member's membership type
     */
    function get_member_type() {
        if(is_object($this->memberType))
            return $this->memberType->getCode();
        else
            return $this->memberType;
    }
    /**
     * Checks if there is a promotion halt due to a failed promotion board
     * 
     * @param type $ident the database connection
     * @return boolean true if the promotions are halted, false if otherwise
     */
    function check_promo_halt($ident) {
        $query= "SELECT BOARD_DATE FROM PROMOTION_BOARD
            WHERE CAPID='".$this->capid."'
            AND APPROVED=FALSE
            AND DATEDIFF(NEXT_SCHEDULED,CURDATE())>=0";
        $size=count(allResults(Query($query, $ident)));  //get the number of results 
        if($size>=1)
            return true;
        else
            return false;
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
    function __construct($code,$ident) {
        $this->code = cleanInputString($code, 1, "Member Type",false);
        $results = Query("SELECT MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES WHERE MEMBER_TYPE_CODE='$this->code'",$ident);
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
/* class chain_of_command {
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
                   $next = $temp->get_next();                    //the index for the next commander
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
}*/
?>
