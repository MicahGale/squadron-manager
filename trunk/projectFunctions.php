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
/** 
 * **********************FOR v. .10*****************************
 * TODO Create basic Attendence: Report<, create event, enter
 * TODO create testing controls and entering
 * TODO add admin to add other users
 * TODO create page for units
 * TODO debug session hijacking resign-in also do post keep thingy with foreach and allow to go back to where logout
 *TODO membership termination and deletion and edit members
 * TODO allow to change password
 * TODO notifications
 * TODO finish populating db
 * TODO populate pictures
 * ***************************Debug/fix*******************************************
 * TODO consider promo boards
 * TODO just kill views
 * TODO fix member side
 * 
 * *******************FOR LATER******************************
 * 
 * TODO debug commanders and add chain of command
 * TODO  add scheduling
 * TODO add edit member and add picture
 * TODO regulations page and update regsupdater
 * TODO add statistics esp. for attendance
 * TODO use css  
 */
/**
 *Function to change to port to different DBMS
 * CleanInputInt-sql escape function
 * CleanInputString -''
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
 define("PHP_DATE_FORMAT","d M y");
 define("PHP_TO_MYSQL_FORMAT","Y-m-d");
 define( "SQL_DATE_FORMAT", "%d-%m-%Y");
 define("EVENT_CODE_DATE",'dMy');
function auditLog($ip, $type) {
    $time = date('o-m-d H:i:s');
    $ident= Connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf','localhost');
    mysqli_query($ident,"INSERT INTO AUDIT_LOG(TIME_OF_INTRUSION, INTRUSION_TYPE, PAGE,IP_ADDRESS)
        VALUES('$time','$type','".$_SERVER['SCRIPT_NAME']."','$ip')");
    close($ident);
    return $time;
}
function auditDump($time, $fieldName, $fieldValue) {
    $ident=connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf',"localhost");
    mysqli_query($ident,"INSERT INTO AUDIT_DUMP(TIME_OF_INTRUSION, FIELD_NAME, FIELD_VALUE)
        VALUES('$time','$fieldName','$fieldValue')");
    close($ident);
}
function logLogin($capid, $success) {
    $capid= cleanInputInt($capid,6,'capid');
    $time = date('o-m-d H:i:s');
    $ident=connect( 'Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf','localhost');
    $ip = $_SERVER['REMOTE_ADDR'];
    Query("INSERT INTO LOGIN_LOG(TIME_LOGIN, CAPID, IP_ADDRESS, SUCEEDED)
                 VALUES('$time','$capid','$ip','$success')", $ident);
    close($ident);
}
function checkAccountLocks($capid) {
    $maxLogin = 8;
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
                    AND TIME_LOGIN >=(SUBTIME(NOW(),'00:30:00'))";
        $results=Query($query,$ident);
        if(Result($results,0, "COUNT(*)")>=$maxLogin) {             //if tried too many times then lock it
            $ident =connect('Logger', 'alkjdn332lkj4230932324hwndsfkldsfkjldf','localhost');
            Query("INSERT INTO ACCOUNT_LOCKS(CAPID, VALID_UNTIL)
                VALUES('$capid',ADDTIME(NOW(),'00:30:00'))", $ident);
            return false;
        } else {                                //else says it's fine
            return true;
        }
    }
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
    echo "<br>CAP Grade"; //SELECT A.ACHIEV_CODE, CONCAT(A.ACHIEV_CODE,'-',B.GRADE_NAME) FROM ACHIEVEMENT A JOIN SQAUDRON_INFO.GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY B.GRADE_NUM
    dropDownMenu("SELECT A.ACHIEV_CODE, CONCAT(B.GRADE_NAME,' - ',A.ACHIEV_NAME) FROM ACHIEVEMENT A JOIN GRADE B ON A.GRADE=B.GRADE_ABREV ORDER BY A.ACHIEV_NUM", "achiev", $identifier, false);
    echo "<br>Member Type";
    dropDownMenu("SELECT MEMBER_TYPE_CODE,MEMBER_TYPE_NAME FROM MEMBERSHIP_TYPES", "member", $identifier, false);  //creates drop down menu for membership types
    echo "<br>Textbook Set";
    dropDownMenu("SELECT TEXT_SET_CODE,TEXT_SET_NAME FROM TEXT_SETS WHERE TEXT_SET_CODE <> 'ALL'", 'text', $identifier, false);  //creates drop down menu for text sets
    echo "<br>Unit Charter Number:";
    dropDownMenu("SELECT CHARTER_NUM, CHARTER_NUM FROM CAP_UNIT", 'unit', $identifier, true,'RMR-ID-073');  //creates drop down menu for text sets
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
        dropDownMenu("SELECT RELATION_CODE,RELATION_NAME FROM CONTACT_RELATIONS", "relation$row", $identifier, true);
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
    dropDownMenu("SELECT REGION_CODE,REGION_NAME FROM REGION", "region", $identifier, false);
    echo"<br>\nWing:";
    dropDownMenu("SELECT WING, WING_NAME FROM WING", "wing", $identifier, false);
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
function dropDownMenu($query, $name, $identifier, $hasotherfield, $default = null, $hasNoSelect=false) {      //drop down menu 1st field is code 2nd is name
    $results = Query($query, $identifier);                     //TODO include error handlin
    $row = 0;
    echo "<select name=\"$name\">";
    if($hasNoSelect==true) {                          //if has no select show empty drop down
        echo '<option selected="selected" value="null">-Please Select One-</option>';
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
function Query($query, $ident, $message = null) {         //kill $page sig on all queries
    $results = mysqli_query($ident, $query);
    if ($results == false) {
        reportDbError(mysqli_errno($ident),  mysqli_error($ident));
    } else if ($results == true) {
        echo $message;
    }
    return $results;
}
function connect($username,$password,$server="localhost",$db="SQUADRON_INFO") {
//    echo "<br>user:$username<Br><br>$password<br><br>$server<br>";
    $connection=  mysqli_connect($server, $username, $password, $db);
    if(!$connection) {                         //if had error
        reportDbError(mysqli_connect_errno(), mysqli_connect_error());
        die;
    } else{
        return $connection;                    //else just give them the resource
    }
}
function Result($result,$row,$field) {
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
function allResults($result) {
    $array=array();
    for($row=0;$row<mysqli_num_rows($result);$row++) {       //get all the rows and gett array
        $array[$row]=  mysqli_fetch_assoc($result);
    }
    return $array;
}
function numRows($result) {
    if(!is_bool($result))                  //if is actually a result
        return mysqli_num_rows($result);
    else
        return 0;                //else return 0
}
function close($ident) {
    return mysqli_close($ident);
}
function prepare_statement($ident,$query) {
    $stmt= mysqli_stmt_init($ident);
    if(!mysqli_stmt_prepare($stmt, $query))
        reportDbError (mysqli_errno ($ident), mysqli_error ($ident));
    return $stmt;
}
function bind($ident,$types) {
    $bindings=array($ident,$types);
    var_dump(func_get_args());
    $buffer= array_merge($bindings,  func_get_args());     //get all args into array to pass into function
//    var_dump($buffer);
    if(!call_user_func("mysqli_stmt_bind_param", $bindings))
        reportDbError (mysqli_errno ($ident), mysqli_error($ident));
}
function execute($ident) {
    if(!mysqli_stmt_execute($ident))
        reportDbError (mysqli_errno ($ident), mysqli_error($ident));
    else {
        if(!($result=mysqli_stmt_get_result($ident))) {
            reportDbError(mysqli_errno($ident), mysqli_error($ident));
        }
        return $result;
    }
}
function close_stmt($stmt) {
    mysqli_stmt_close($stmt);                 //closes the prepared statement
}
function cleanInputInt($input, $length, $fieldName) {
    $link = mysqli_connect();
    $clean = htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
    if (strlen($clean) != $length || !is_numeric($clean) || $clean != $input) {
        $badInput = true;
        $time = auditLog( $_SERVER['REMOTE_ADDR'], "SI");
        auditDump($time, $fieldName, $clean);
        echo "<font color=\"red\">$fieldName is not a valid number it must be $length digits long.</font><br>";
        if (strlen($clean) != $length || !is_numeric($clean)) {          //nulls if wrong type
            $clean = null;
        }
    }
    $clean = intval($clean);                                            //cast it to int
    return $clean;
}
function cleanInputString($input, $length, $fieldName, $empty) {                      //clean and log numbers
    $link= mysqli_connect();
    $clean = htmlspecialchars(mysqli_real_escape_string($link,$input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
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
function cleanInputDate($input, $regex, $length, $fieldName, $page) {                      //clean and log numbers
    $clean = htmlspecialchars(mysqli_real_escape_string($input), ENT_QUOTES | 'ENT_HTML5', 'UTF-8');
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
    if ($append == null) {
        $month = cleanInputString($input["month"], 2, "month", false);
        $day = cleanInputString($input["Date"], 2, "day", false);
        $year = cleanInputInt($input["Year"], 4, "year");
    } else {
        $month = cleanInputString($input["month" . $append], 2, "month", false);
        $day = cleanInputString($input["Date" . $append], 2, "day", false);
        $year = cleanInputInt($input["Year" . $append], 4, "year");
    }
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
function search($ident,$callable){      //if didn't provide complete then search
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
            echo '<a href="/login/attendance/event.php?eCode='.$result[$i]['EVENT_CODE'].'">';
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
                $query = "INSERT INTO TESTING_SIGN_UP(CAPID,ACHIEV_CODE,REQUIRE_TYPE,REQUESTED_DATE)
                    SELECT '" . $this->capid . "',A.ACHIEV_CODE,'$code', CURDATE() FROM ACHIEVEMENT A
                        JOIN ACHIEVEMENT B ON B.NEXT_ACHIEV=A.ACHIEV_CODE
                        WHERE B.ACHIEV_CODE='" . $this->achievement . "'";
            }
            return Query($query, $ident, $message);
        }
    }
    public function promotionReport($ident, $header=true) {
        ?>
        <table border="0" width="900"><tr><td valign="top" align="center">
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
                    $header = Query("SELECT TYPE_NAME, TYPE_CODE FROM REQUIREMENT_TYPE
                        WHERE MEMBER_TYPE='".$this->memberType."'
                            OR MEMBER_TYPE IS NULL
            ORDER BY TYPE_CODE", $ident);    //QUERY TO get requirement types
                    echo "<tr><td align=\"center\">    
            <table border =\"1\" cellspacing=\"1\" width=\"900\">
            <tr><th>Achievement</th>";                     //creates headers of rows based on requirement types
                    for ($row = 0; $row < numRows($header); $row++) { 
                        echo "<th>" .Result($header, $row, "TYPE_NAME") . "</th>";  //make them into headers
                    }
                    echo "<th>Date Promoted</th></tr>\n";
                    $query = "SELECT B.ACHIEV_CODE AS ACHIEV, A.REQUIREMENT_TYPE AS TYPE, A.PASSED_DATE AS DATE
            FROM REQUIREMENTS_PASSED A JOIN ACHIEVEMENT B
            ON A.ACHIEV_CODE=B.ACHIEV_CODE
            WHERE A.CAPID='" . $this->capid . "'
            ORDER BY B.ACHIEV_NUM, A.REQUIREMENT_TYPE";           //shows what they already passed
                    $passed = Query($query, $ident);             //get all the requirements the  ^cadet passed
                    $query = "SELECT ACHIEV_NAME, ACHIEV_CODE FROM ACHIEVEMENT 
            WHERE MEMBER_TYPE='".$this->memberType."'   
            AND ACHIEV_CODE <> '0'
            ORDER BY ACHIEV_NUM";
                    $achievements = Query($query, $ident);    //get all the achievements ^
                    $query = "SELECT A.ACHIEV_CODE, A.REQUIREMENT_TYPE FROM PROMOTION_REQUIREMENT A
                        JOIN ACHIEVEMENT B 
                        ON A.ACHIEV_CODE=B.ACHIEV_CODE
            WHERE A.TEXT_SET IN('" . $this->text_set . "','ALL')
                AND B.MEMBER_TYPE='".$this->memberType."'
            ORDER BY ACHIEV_NUM, REQUIREMENT_TYPE";              
                    $requirements = Query($query, $ident);          //get all the requirements ^
                    $max = Query("SELECT A.ACHIEV_CODE FROM ACHIEVEMENT A
            JOIN ACHIEVEMENT B ON A.ACHIEV_CODE=B.NEXT_ACHIEV
            WHERE B.ACHIEV_CODE='" . $this->achievement . "'", $ident);
                    $promoted = Query("SELECT ACHIEVEMENT, DATE_PROMOTED
                        FROM PROMOTION_RECORD WHERE CAPID='".$this->capid."'",$ident);  //gets promotion dates
                    $maxAchiev = Result($max, 0, "ACHIEV_CODE");             //get the next achievement so don't list to spaatz
                    $size_achiev = numRows($achievements);
                    $size_passed = numRows($passed);
                    $promo_index =0;                                 //index of promotion
                    for ($achievRow = 0; $achievRow < $size_achiev; $achievRow++) {  //loop to create rows
                        echo "<tr><td>" . Result($achievements, $achievRow, "ACHIEV_NAME") . "</td>"; //shows name of achievemnt
                        $achievCode = Result($achievements, $achievRow, "ACHIEV_CODE");
                        $passedRequire = null;
                        settype($passedRequire, "array");             //gets all the passed requirements for this row's achievemtn
                        for ($passedRow = 0; $passedRow < $size_passed; $passedRow++) {
                            if (Result($passed, $passedRow, "ACHIEV") == $achievCode) {  //if it is for this achievement
                                array_push($passedRequire, array("Code" => Result($passed, $passedRow, "TYPE"),
                                    "Date" => new DateTime(Result($passed, $passedRow, "DATE")))); //PUSH THE TYPE CODE AND DATE ONTO THE ARRAYfr
                            }
                        }
                        $require = null;             //gets all requirements for this achievemnets promo
                        settype($require, "array");
                        for ($passedRow = 0; $passedRow < numRows($requirements); $passedRow++) {
                            if (Result($requirements, $passedRow, "ACHIEV_CODE") == $achievCode) {  //if it is for this achievement
                                array_push($require, Result($requirements, $passedRow, "REQUIREMENT_TYPE")); //PUSH THE TYPE CODE on to the array
                            }
                        }
                        $passedRow = 0;
                        $requireRow = 0;
                        for ($row = 0; $row < numRows($header); $row++) {    //cylces through requirements to display them 
                            $testCode = Result($header, $row, "TYPE_CODE");
                            if (array_key_exists($passedRow, $passedRequire)) {     //if have record for that passed just show it
                                if ($passedRequire[$passedRow]["Code"] == $testCode) {        //checks if has been passed
                                    echo"<td>" . $passedRequire[$passedRow]["Date"]->format(PHP_DATE_FORMAT) . "</td>";
                                    $passedRow++;
                                    $requireRow++;           //increment other counters up
                                } elseif (array_key_exists($requireRow, $require)) {       //else sees if there are any requirements for that
                                    if ($require[$requireRow] == $testCode) {             //sees if required
                                        echo "<td></td>";             //leaves cell blank than if no entry, but required
                                        $requireRow++;
                                    } else {        //else assume not required
                                        echo "<td>n/a</td>";
                                    }
                                } else {
                                    echo "<td>n/a</td>";           //assumes not required
                                }
                            } elseif (array_key_exists($requireRow, $require)) {   //else sees if there are any requirements for that
                                if ($require[$requireRow] == $testCode) {
                                    echo "<td></td>";          //say its incomplete blank than if no entry, but required
                                    $requireRow++;
                                } else {
                                    echo "<td>n/a</td>";
                                }
                            } else {
                                echo "<td>n/a</td>";           //assumes not required
                            }
                        }
                        if(numRows($promoted)>$promo_index) {
                            if(Result($promoted, $promo_index,'ACHIEVEMENT')==$achievCode) {           //if the promoted date is for the right row
                                $date = new DateTime(Result($promoted, $promo_index,'DATE_PROMOTED'));
                                echo "<td>".$date->format(PHP_DATE_FORMAT)."</td>";  //echo the date promoted
                                $promo_index++;
                            } else {
                                echo "<td></td>";           //else show a blank cell
                            }
                        } else {
                            echo "<td></td>";
                        }
                        echo "</tr>\n";          //ends this row of the table
                        if ($achievCode == $maxAchiev) {                       //break if hit next achievement
                            break;
                        }                        
                } 
                ?>
                </table>
                <?php
                echo "</table>\n";
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
        $size= numRows($result);
        for($i=0;$i<$size;$i++) {                        //loop through the result
            echo "<tr><td>";
            echo '<a href="/login/discipline/details.php?capid='.$this->capid.'&ToA='.Result($result, $i,'A.TYPE_OF_ACTION').'&event='.Result($result, $i,'A.EVENT_CODE').'&O='.  Result($result, $i, 'A.OFFENSE').'&given='.  Result($result, $i,'A.GIVEN_BY').'">';
            echo Result($result, $i,'B.DISCIPLINE_NAME').'</a></td>';         
            $date = new DateTime(Result($result, $i,'C.EVENT_DATE'));
            echo '<td>'.$date->format(PHP_DATE_FORMAT).'</td>';
            echo "<td>".Result($result, $i, 'D.OFFENSE_NAME').'</td>';
            echo '<td>'.Result($result, $i,'A.SEVERITY').'</td>';
            $capid=  Result($result, $i, 'A.GIVEN_BY');
            $given = new member(Result($result, $i,'A.GIVEN_BY'),1,$_SERVER['SCRIPT_NAME'],$ident);
            echo '<td><a href="/login/member/report?capid='."$capid\">".$given->title()."</a>";
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