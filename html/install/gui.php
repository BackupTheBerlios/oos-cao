<?php
/* ----------------------------------------------------------------------
   $Id: gui.php,v 1.2 2005/01/10 10:41:16 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: gui.php,v 1.18.2.1 2002/04/03 21:03:19 jgm 
   ----------------------------------------------------------------------
   POST-NUKE Content Management System
   Copyright (C) 2001 by the Post-Nuke Development Team.
   http://www.postnuke.com/
   ----------------------------------------------------------------------
   Based on:
   PHP-NUKE Web Portal System - http://phpnuke.org/
   Thatware - http://thatware.org/
   ----------------------------------------------------------------------
   LICENSE

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License (GPL)
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   To read the license please visit http://www.gnu.org/copyleft/gpl.html
   ----------------------------------------------------------------------
   Original Author of file:  Gregor J. Rothfuss
   Purpose of file: Provide gui rendering functions for the installer.
   ---------------------------------------------------------------------- */

  function oosPrepareInput($string) {
    return trim(stripslashes($string));
  }


 

/**
 * This function prints the <input type=hidden> area 
 */
function print_FormHidden() {
   global $update;

   reset($_POST);
   while (list($key, $value) = each($_POST)) {
     if (!is_array($_POST[$key])) {
       echo '<input type="hidden" name="'. $key . '" value="' . htmlspecialchars(stripslashes($value)) . '">' . "\n";
     }
   }

}

function print_Next() {
   global $update;
   
   echo '<br /><br /><br /><br /><br /><br />';
   echo '<form action="index.php" method="post"><center><table width="50%">' . "\n";
   echo '<tr><td align=center><input type="hidden" name="op" value="Finish">' . "\n" .
        '<input type="submit" value="' . BTN_FINISH . '"></td></tr></table></center></form>' . "\n";             
}


function print_oosFinish() {
   global $currentlang;

   $root_path = str_replace("\\","/",getcwd()); // "
   $root_path = str_replace("/install", "", $root_path);

   echo '<font class="oos-title">' . FINISH_1 . '&nbsp;</font>';
   echo '<font class="oos-normal">' . FINISH_2 . '<br /><br /><br />';
   echo '<form action="index.php" method="post">';
   echo '<center><textarea name="license" cols=50 rows=8>';

   include("lang/" . $currentlang . "/credits.txt");

   echo '</textarea></form>';
   echo '<br /><br />' . FINISH_3 . '<br /></font>';
   echo '<br /><font class="oos-title">' .  $root_path . '</font><font class="oos-error">/install</font>';
   echo '<br /><br /><font class="oos-title"><a href="../admin/index.php">' . FINISH_4 . '</a></font>';
   echo '</center><br /><br />';
}


function print_Admin() {
   global $currentlang;

   echo '<font class="oos-title">' . CONTINUE_1 . ':&nbsp;</font>' . "\n" .
        '<font class="oos-normal">' . CONTINUE_2 . '</font>' . "\n" .
        '<br /><br />' . "\n" .
        '<center><form action="index.php" method="post"><table class="content">' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_GENDER . '</font></td>' . "\n" .
        '  <td><font class="oos-normal"><input type="radio" name="gender" value="m" checked>&nbsp;' . MALE . '&nbsp;&nbsp;<input type="radio" name="gender" value="f">&nbsp;' . FEMALE . '&nbsp;</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_FIRSTNAME . '</font></td>' . "\n" .
        '  <td><input type="text" name="firstname" SIZE=30 maxlength=80 value=""></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_NAME . '</font></td>' . "\n" .
        '  <td><input type="text" name="name" SIZE=30 maxlength=80 value="Admin"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_PASS . '</font></td>' . "\n" .
        '  <td><input type="password" name="pwd" SIZE=30 maxlength=80 value=""></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_REPEATPASS . '</font></td>' . "\n" .
        '  <td><input type="password" name="repeatpwd" SIZE=30 maxlength=80 value=""></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_EMAIL . '</font></td>' . "\n" .
        '  <td><input type="text" name="email" SIZE=30 maxlength=80 value="none@myoos.de"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_PHONE . '</font></td>' . "\n" .
        '  <td><input type="text" name="phone" SIZE=30 maxlength=80 value=""></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_FAX . '</font></td>' . "\n" .
        '  <td><input type="text" name="fax" SIZE=30 maxlength=80 value=""></td>' . "\n" .
        ' </tr>' . "\n" .
        '</table>' . "\n" .
        '<br /><br />' . "\n";
   print_FormHidden();
   echo '<input type="hidden" name="op" value="Login">' . "\n" .
        '<input type="submit" value="' . BTN_CONTINUE . '">' . "\n" .
        '</form></center>' . "\n";

}


function print_ChangeLogin() {
   global $currentlang, $gender, $firstname, $name, $pwd, $repeatpwd, $email, $phone, $fax; 

   echo '<font class="oos-title">' . CONTINUE_1 . '</font>' . "\n";

   if ($pwd == '') {
     echo '<br /><font class="oos-error">' . ADMIN_ERROR . '&nbsp;' . ADMIN_PASSWORD_ERROR . '</font>' . "\n";
   } 
   if ($email == '') {
     echo '<br /><font class="oos-error">' . ADMIN_ERROR . '&nbsp;' . ADMIN_EMAIL_ERROR. '</font>' . "\n";
   }
   if ($pwd != $repeatpwd) {
     echo '<br /><font class="oos-error">' . ADMIN_ERROR . '&nbsp;' . PASSWORD_ERROR . '</font>' . "\n";
   } 
   if ($gender == 'm') {
     $oos_radio_gender = '<input type="radio" name="gender" value="m" checked>&nbsp;' . MALE . '&nbsp;&nbsp;<input type="radio" name="gender" value="f">&nbsp;' . FEMALE . '&nbsp';
   } else {
     $oos_radio_gender = '<input type="radio" name="gender" value="m">&nbsp;' . MALE . '&nbsp;&nbsp;<input type="radio" name="gender" value="f" checked>&nbsp;' . FEMALE . '&nbsp';
   }
   echo '<br /><br />' . "\n" .
        '<center><form action="index.php" method="post"><table class="content">' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_GENDER . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $oos_radio_gender . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_FIRSTNAME . '</font></td>' . "\n" .
        '  <td><input type="text" name="firstname" SIZE=30 maxlength=80 value="' . $firstname . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_NAME . '</font></td>' . "\n" .
        '  <td><input type="text" name="name" SIZE=30 maxlength=80 value="' . $name . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_PASS . '</font></td>' . "\n" .
        '  <td><input type="password" name="pwd" SIZE=30 maxlength=80 value="' . $pwd . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_REPEATPASS . '</font></td>' . "\n" .
        '  <td><input type="password" name="repeatpwd" SIZE=30 maxlength=80 value="' . $repeatpwd . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_EMAIL . '</font></td>' . "\n" .
        '  <td><input type="text" name="email" SIZE=30 maxlength=80 value="' . $email . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_PHONE . '</font></td>' . "\n" .
        '  <td><input type="text" name="phone" SIZE=30 maxlength=80 value="' . $phone . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_FAX . '</font></td>' . "\n" .
        '  <td><input type="text" name="fax" SIZE=30 maxlength=80 value="' . $fax . '"></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left">&nbsp;</td>' . "\n" .
        '  <td>&nbsp;</td>' . "\n" .
        ' </tr>' . "\n" .
        '</table>' . "\n";
   echo '<input type="hidden" name="op" value="Login">' . "\n" .
        '<input type="submit" value="' . BTN_SET_LOGIN . '">' . "\n" .
        '</form></center>' . "\n";

}

function print_Login() {
   global $currentlang, $gender, $firstname, $name, $pwd, $repeatpwd, $email, $phone, $fax; 

   $oos_gender = ($gender == 'm') ? MALE : FEMALE;

   echo '<font class="oos-title">' . CONTINUE_1 . ':&nbsp;</font>' . "\n" .
        '<font class="oos-normal">' . CONTINUE_3 . '</font>' . "\n" .
        '<br /><br />' . "\n" .
        '<center><form name="change login" action="index.php" method="post"><table class="content">' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_GENDER . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $oos_gender . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_FIRSTNAME . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $firstname . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_NAME . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $name . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_PASS . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . PASSWORD_HIDDEN . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_REPEATPASS . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . PASSWORD_HIDDEN . '</td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_EMAIL . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $email . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_PHONE . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $phone . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left"><font class="oos-normal">' . ADMIN_FAX . '</font></td>' . "\n" .
        '  <td><font class="oos-normal">' . $fax . '</font></td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="left">&nbsp;</td>' . "\n" .
        '  <td>&nbsp;</td>' . "\n" .
        ' </tr>' . "\n" .
        ' <tr>' . "\n" .
        '  <td>&nbsp;</td>' . "\n" .
        '  <td>' . "\n";
   print_FormHidden();
   echo '<input type="hidden" name="op" value="Change Login">' . "\n" . 
        '<input type="submit" value="' . BTN_CHANGE_INFO . '"><br />' . "\n" .
        '  </td>' . "\n" .
        ' </tr>' . "\n" .
        '</table></form>' . "\n" .
        '<font class="oos-normal">' . ADMIN_INSTALL . '</font>' . "\n" .
        '<form name="login install" action="index.php" method="post"><table width="80%" border="0" align="right">' . "\n" .
        ' <tr>' . "\n" .
        '  <td align="right">' . "\n";
   print_FormHidden();
   echo '<input type="hidden" name="op" value="CAO-Install">' . "\n" .
        '<input type="submit" value="' . BTN_LOGIN_SUBMIT . '">' . "\n" .
        ' </td>' . "\n" .
        ' </tr>' . "\n" .
        '</table>' . "\n" .
        '</form></center>' . "\n";

}


function print_oosDefault() {
   global $currentlang;
   echo '<table width="800" border="0" cellspacing="0" cellpadding="0">
    <tr>
    <td width="21"><img src="images/step_2.png" alt="" width="21" height="21" /></td>
    <td width="758" class="table_head_setuptitle">' . DEFAULT_1  . '</td>
    <td width="21"><img src="images/table_head_right.png" alt="" width="21" height="21" /></td>
    </tr>
    </table>' . "\n";
   echo '<br /><b>' . DEFAULT_2  . '</b><br /><br />';
   echo '<form name="next" action="index.php" method="post">';
   echo '<div class="license-form">
         <div align="middle"  class="form-block" style="padding: 0px;">
       <iframe src="gpl.html" class="license" frameborder="0" scrolling="auto"></iframe>
         </div>
         </div>';
   echo '<input type="hidden" name="currentlang" value="' . $currentlang . '">';
   echo '<input type="hidden" name="op" value="PHP_Check">';
   echo '<center>';
   echo '<input type="checkbox" name="agreecheck" ><font class="oos-normal">' . DEFAULT_3 . '</font><br />';
   echo '<input type="submit" value="' . BTN_NEXT . '"><br />';
   echo '</center>'; 
   echo '</form>'; 
}



?>