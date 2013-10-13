<form method="get">
    <input type="text" name="capid">
    <input type="submit">
</form>
<?php
include('projectFunctions.php');
if(isset($_GET['capid'])) {
    $passes=  parse_ini_file(PSSWD_INI);
    $salt= $passes['salt'];
    echo hash_password("Password!", $salt);
}
function hash_password($pass,$salt) {
        $to_hash=$salt.$pass.$this->capid;
        return hash("sha512",$to_hash);
    }
 ?>