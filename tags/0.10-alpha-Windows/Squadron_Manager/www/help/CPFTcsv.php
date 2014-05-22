<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" href="/patch.ico">
        <link rel="stylesheet" type="text/css" href="/main.css">
        <title>Using CPFT csv</title>
    </head>
    <body>
        <?php
        require("header.php");
        ?>
        <h1>Using Cadet Physical Fitness Test(CPFT) csv files for testing</h1>
        <p>A csv file is a basic spreadsheet file that can be opened by most  spreadsheet applications
        (i.e. Microsoft Office Excel, OpenOffice Calc, etc.)</p>
        
        <p>All of the Cadets that requested to take the CPFT will be listed in the CSV on their own row, followed by the requirements 
        they need to pass. All of the Cadets are listed in alphabetical order by last name, and the test events are listed 
        in alphabetical order. To enter test scores input the scores in the proper cells. For the mile run use the format
        minutes:seconds.  If the Cadet is exempt from any events due to being in Categories II, III, or IV put an "X"
        in the waiver cell.</p>
        
        <p>To save the tests, the csv file must be saved with all the scores, and then uploaded to the Server, through the 
        "manage CPFT Testing" link.  Once uploaded the contents of the csv file will be displayed to verify correctness.
        All test scores will be checked too verify that it is passing, and is based on the information stored in the 
        database, and not the requirements in the csv file.</p>
        
        <p><strong>Note:</strong> All uploads are logged to whoever uploaded them, and the file is also safely saved for
        accountability.  All uploads are cleaned and verified to prevent any malicious code.</p>
        Example table of a typical csv
        <table border="1">
            <tr><td>CAPID</td><td>Name</td><td>Curl-ups</td><td>Mile Run</td><td>Push-ups</td><td>Shuttle Run</td><td>Sit and Reach</td><td>Waiver</td></tr>
            <tr><td>123456</td><td>Doe, John</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>-----</td><td>Requirements</td><td>≡>1<≡</td><td>≡>10:45<≡</td><td>≡>2<≡</td><td>≡>10.2<≡</td><td>≡>3<≡</td><td>--</td></tr>
        </table>
        <?php
        require("footer.php");
        ?>
    </body>
</html>
