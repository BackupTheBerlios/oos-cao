<?php
/* ----------------------------------------------------------------------
   $Id: gui.php,v 1.1 2005/01/07 09:29:28 r23 Exp $
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

  function oosGetLocalPath($path) {
   if (substr($path, -1) == '/') $path = substr($path, 0, -1);

   return $path;
  }


function print_CHMcheck() {
   global $currentlang;

   echo '<font class="oos-title">' . DBINFO . ':&nbsp;</font><br />' . "\n" .
        '<font class="oos-normal">' . CHM_CHECK_1 . '</font><br /><br />' . "\n" .
        '<form action="index.php" method="post"><center>' . "\n";
   print_FormEditabletext(0);
   echo '<input type="hidden" name="currentlang" value="' . $currentlang .'">' . "\n" .
        '<input type="hidden" name="op" value="Submit"><br /><br />' . "\n" .
        '<input type="submit" value="' . BTN_SUBMIT . '"></center></form>' . "\n";

}  

function print_Next() {
   global $update;

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


function oosSuccess() {
   echo '<font class="oos-title">' . SUCCESS_1 . '</font>' . "\n" .
         '<font class="oos-normal">' . SUCCESS_2 . '<br /><br />' . "\n" .
         '<form action="index.php" method="post"><center><table width="50%">' . "\n";
   print_FormHidden();
   echo '<tr><td align=center><input type="hidden" name="op" value="Finish">' . "\n" .
        '<input type="submit" value="' . BTN_FINISH . '"></td>' . "\n" .
        '</tr></table></center></form></font><br /><br />' . "\n";
}



function print_Submit() {
  echo '<font class="oos-title">' . DBINFO . ':&nbsp;</font>' .
       '<font class="oos-normal">' . SUBMIT_1 . '</font><br /><br />' . "\n" .
       '<br /><center><font class="oos-normal">' . SUBMIT_2 . '</font><br /><br />';               
  print_FormText();
  echo '<form name="change info" action="index.php" method="post">' . "\n";
  print_FormHidden();
       '<br />' . "\n" .
       '<font class="oos-normal">' . SUBMIT_3 . '</font></center><br /><br />' . "\n" .
       '<table width="50%" align="center">' . "\n" .
       ' <tr>' . "\n" .
       '  <td>' . "\n";
  echo '<form name="new install" action="index.php" method="post">' . "\n";
  print_FormHidden();
  echo '<input type="hidden" name="op" value="Change_Info">' . "\n" .
       '<input type="submit" value="' . BTN_CHANGE_INFO . '">' . "\n" .
       '</form>' . "\n" .
       '  </td>' . "\n" .
       '  <td>' . "\n";
  echo '<form name="update" action="index.php" method="post">' . "\n";
  print_FormHidden();
  echo '<input type="hidden" name="op" value="Upgrade">' . "\n" .
       '<input type="submit" value="' . BTN_UPGARDE . '">' . "\n" .
       '</form></td>' . "\n" .
       ' </tr>' . "\n" .
       '</table></form>' . "\n";

}




function print_Start() {
   echo '<form action="index.php" method="post"><table class="content">' . "\n" .
        ' <tr>' . "\n" .
        '   <td align=center>' . "\n";
   print_FormHidden();
   echo '<input type="hidden" name="op" value="Confirm">' . "\n" .
         '<input type="submit" value="' . BTN_CONTINUE . '"></td>' . "\n" .
         ' </tr>' . "\n" .
         '</table></form>' . "\n";
           
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