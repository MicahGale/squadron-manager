<!DOCTYPE html>
<html>
    <head>
        <title>Edit Personal Information</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <link rel="shortcut icon" href="../patch.ico">
    </head>
    <body>
        <?php
        include("header.php");
        include("projectFunctions.php");
        session_start();
        if(!array_key_exists("member",$_SESSION)) {
            auditLog($_SERVER["REMOTE_ADDR"],"DC");
            echo"<meta HTTP-EQUIV=\"REFRESH\" content=\"0; url=../signIn/?CAPID=\">";
        }
       $ident =Connect( 'Sign-in');
        $member=$_SESSION["member"];
       $member->init(4,$ident);
        $member->editInformation("/signIn/finishEdit.php",$ident);
        ?>                 
        <a href="../index.php">go Home</a><br/>
        <a href="promotionReport.php">View Your Promotion Progress</a> <br/>
        <a href="logout.php">Logout</a>
        <?php include("footer.php");?>
    </body>
</html>
