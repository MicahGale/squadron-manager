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
$fields= parse_ini_file('/etc/squadMan/squadMan.ini');
?>
<script type="text/javascript" src="/java_script/resize.js"></script>
<table id="head" style="width:1100px">    
    <tr>
        <td style="width:210px"><a href="/">
        <img alt="Squadron Logo" height="210" width="210" src="/patch.png"></a></td>
        <td>
            <table style="width:100%">
                <tr><td style="text-align:center; vertical-align: top"><h1>
                            <?php
                            if($fields!==false&&isset($fields['header_name']))
                                echo $fields['header_name'];
                            else 
                                echo 'Squadron Manager';
                            ?>
                        </h1></td></tr>
                <tr><td style="text-align:right; vertical-align: bottom">
                    <a href="<?php if($fields!==false&&isset($fields['squad_site'])) echo $fields['squad_site']; else echo 'http://capmembers.com'; ?>" target="_blank">Squadron Web-site</a><br>
                    <a href="<?php if($fields!==false&&isset($fields['squad_cal'])) echo $fields['squad_cal']; else echo "https://www.capnhq.gov/CAP.Calendar.Web/Modules/AdvSearch.aspx";?>" target="_blank">Squadron Calender</a><br>
                    <a href="http://www.capmembers.com/forms_publications__regulations/" target="_blank">CAP regulations, and forms</a><br>
                </td></tr>
            </table>
        </td>
        </tr>
</table>