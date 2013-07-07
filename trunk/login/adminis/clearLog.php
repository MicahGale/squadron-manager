<?php
require("projectFunctions.php");
$ident = connect('login');
session_secure_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Clear System Logs</title>
    </head>
    <body>
        <?php
        require("squadManHeader.php");
        require("squadManFooter.php");
        ?>
    </body>
</html>
