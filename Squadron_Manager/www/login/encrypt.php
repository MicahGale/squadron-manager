<form method="get">
    <input type="text" name="capid">
    <input type="password" name="password">
    <input type="submit">
</form>
<?php
include('projectFunctions.php');
if(isset($_GET['capid'])) {
    $passes=  parse_ini_file(PSSWD_INI);
    $ident=  connect("login");
    $salt= $passes['salt'];
    $member= new member($_POST['capid'],1,$ident);
    echo $member->hash_password("Password!", $salt);
}
//$file=  fopen('/dev/urandom','r');
//echo base64_encode(fread($file, 32));
//fclose($file);
 ?>