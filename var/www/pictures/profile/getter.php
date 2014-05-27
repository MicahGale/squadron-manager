<?php
/**
 * A secure way to see pictures on the server
 * 
 * All sensetive pictures (profile pictures) go through here so only people who should see pictures can
 * prevents from wandering through the pictures without a logon
 * 
 * $_GET
 * CAPID-the capid of the member to see the picture
 * @package Squadron-manager
 * @copyright (c) 2013, Micah Gale
 */
/*  Copyright 2013 Micah Gale
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
require("projectFunctions.php");
session_start();
header("content-type:image/jpeg");  //say it's a picture
if($_GET['capid']==='un') {        //if the image is unavailable then pick that one
    $path=PROFILE_PATH.DIRECTORY_SEPARATOR."unavailable.jpg";
    header("Content-Disposition: inline ; filename=unavailable.jpg");
} else {
   $capid=  cleanInputInt($_GET['capid'], 6, 'Picture CAPID'); 
   $path= PROFILE_PATH.DIRECTORY_SEPARATOR.$capid.".jpg";           //gets the absolute path to the file
   header("Content-Disposition: inline ; filename=$capid.jpg");
    if (pathinfo($path, PATHINFO_DIRNAME)!==PROFILE_PATH||!file_exists($path)||!isset($_SESSION['home'])) {        //if the file is not in the right place
        $path=PROFILE_PATH.DIRECTORY_SEPARATOR."unavailable.jpg";
    }
}

header("Pragma: no-cache");
header("Expires: 0");
$write = fopen("php://output", 'wb'); //open the output to write to 
$read= fopen("file://".$path,"rb");          //read the image
$content=  fread($read, filesize($path));
fwrite($write, $content);                            //write the image to the output
fclose($write);
fclose($read);       //close the file resources
?>
