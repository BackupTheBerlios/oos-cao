<?php
/* ----------------------------------------------------------------------
   $Id: check.php,v 1.1 2005/01/07 09:29:28 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: install.php,v 1.7 2004/08/26 05:20:59 rcastley 
   ----------------------------------------------------------------------
   copyright (C) 2000 - 2004 Miro International Pty Ltd
   license http://www.gnu.org/copyleft/gpl.html GNU/GPL
   Mambo is Free Software
   ----------------------------------------------------------------------
   Based on:

   File: check.php,v 1.6 2002/02/04 18:51:32 voll 
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
   Original Author of file: Gregor J. Rothfuss
   Purpose of file: Provide checks for the installer.
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */
   

function oosCheckPHP() {
   global $currentlang;

   echo '<font class="oos-title">' . PHP_CHECK_1 . '</font><br />';
   echo '<font class="oos-normal">' . PHP_CHECK_2. '</font><br />';

   $check_php = true;
?>
<table class="content">
<tr> 
  <td>PHP version >= 4.2.0</td>
  <td align="left"><?php echo phpversion() < '4.2' ? '<b><font color="red">No</font></b>' : '<b><font color="green">Yes</font></b>';?></td>
</tr>
<tr>    
  <td>&nbsp; - zlib compression support</td>
  <td align="left"><?php echo extension_loaded('zlib') ? '<b><font color="green">Available</font></b>' : '<b><font color="red">Unavailable</font></b>';?></td>
</tr>
<tr> 
  <td>&nbsp; - XML support</td>
  <td align="left"><?php echo extension_loaded('xml') ? '<b><font color="green">Available</font></b>' : '<b><font color="red">Unavailable</font></b>';?>
  </td>
</tr>
<tr> 
  <td>&nbsp; - MySQL support</td> 
  <td align="left"><?php echo function_exists( 'mysql_connect' ) ? '<b><font color="green">Available</font></b>' : '<b><font color="red">Unavailable</font></b>';?>
  </td>
</tr>
<tr> 
  <td>Session save path</td>
  <td align="left">
  <b><?php echo (($sp=ini_get('session.save_path'))?$sp:'Not set'); ?></b>, 
  <?php echo is_writable( $sp ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>';?>
  </td>
</tr>
</table>
<br />
<br />
<table class="content">
<tr>
  <td class="toggle">Directive</td>
  <td class="toggle">Recommended</td>
  <td class="toggle">Actual</td>
</tr>
<tr>
  <td>Safe Mode:</td>
  <td class="toggle">OFF</td>
  <td>
<?php 
  if ( oosGetPHPSetting('safe_mode') == 'OFF' ) {
?>
    <font color="green"><b>
<?php
   } else {
?>
    <font color="red"><b>
<?php
   }
  echo oosGetPHPSetting('safe_mode');
?>
  </b></font><td>
</tr>
<tr>
  <td>Display Errors:</td>
  <td class="toggle">OFF</td>
  <td>
<?php 
  if ( oosGetPHPSetting('display_errors') == 'OFF' ) {
?>
    <font color="green"><b>
<?php
  } else {
?>
    <font color="red" style="bold"><b>
<?php
  }
  echo oosGetPHPSetting('display_errors');
?>
  </b></font></td>
</tr>
<tr>
  <td>File Uploads:</td>
  <td class="toggle">ON</td>
  <td>
<?php 
  if ( oosGetPHPSetting('file_uploads') == 'ON' ) {
?>
    <font color="green"><b>
<?php
  } else {
?>
    <font color="red" style="bold"><b>
<?php
  }
  echo oosGetPHPSetting('file_uploads');
?>
  </b></font></td>
</tr>
<tr>
  <td>Magic Quotes GPC:</td>
  <td class="toggle">ON</td>
  <td>
<?php 
  if ( oosGetPHPSetting('magic_quotes_gpc') == 'ON' ) {
?>
    <font color="green"><b>
<?php
  } else {
?>
    <font color="red" style="bold"><b>
<?php
   } 
  echo oosGetPHPSetting('magic_quotes_gpc');
?>
  </b></font></td>
</tr>
<tr>
  <td>Magic Quotes Runtime:</td>
  <td class="toggle">OFF</td>
  <td>
<?php
  if ( oosGetPHPSetting('magic_quotes_runtime') == 'OFF' ) {
?>
    <font color="green"><b>
<?php
  } else {
     $check_php = false;
?>
    <font color="red" style="bold"><b>
<?php
   } 
  echo oosGetPHPSetting('magic_quotes_runtime');
?>
  </b></font></td>
</tr>
   
<tr>
  <td>Register Globals:
  </td>
  <td class="toggle">OFF</td>
  <td>
<?php 
  if ( oosGetPHPSetting('register_globals') == 'OFF' ) {
?>
    <font color="green"><b>
<?php
  } else {
?>
    <font color="red" style="bold"><b>
<?php
  }
  echo oosGetPHPSetting('register_globals');
?>
  </b></font></td>
</tr>
<tr>
  <td>Output Buffering:</td>
  <td class="toggle">OFF</td>
  <td>
<?php 
  if ( oosGetPHPSetting('output_buffering') == 'OFF' ) {
?>
    <font color="green"><b>
<?php
  } else {
?>
    <font color="red" style="bold"><b>
<?php
  }
  echo oosGetPHPSetting('output_buffering');
?>
  </b></font></td>
</tr>
<tr>
  <td>Session auto start:</td>
  <td class="toggle">OFF</td>
  <td>
<?php 
  if ( oosGetPHPSetting('session.auto_start') == 'OFF' ) {
?>
    <font color="green"><b>
<?php
  } else {
    $check_php = false;
?>
    <font color="red" style="bold"><b>
<?php
  }
  echo oosGetPHPSetting('session.auto_start');
?>
  </b></font></td>
</tr>
</table>
<?php 
  
   if (!isset($_SERVER['PHP_SELF'])) {
     echo '<br /><font class="oos-error">' . PHP_CHECK_16 . '</font><br />';
     echo '<font class="oos-title">' . PHP_CHECK_17 . '</font><br /><br />';
     $check_php = false;
   }


   if ($check_php == true) {
     echo '<font class="oos-normal">' . PHP_CHECK_OK . '</font><br />';
     echo '<p><form action="index.php" method="post">';
     echo '<input type="hidden" name="currentlang" value="' . $currentlang . '">';
     echo '<input type="hidden" name="op" value="CAO-Install">';
     echo '<center><input type="submit" value="' . BTN_CONTINUE . '"></center></form></p>';
   } else {
     echo '<p><form action="index.php" method="post">';
     echo '<input type="hidden" name="currentlang" value="' . $currentlang . '">';
     echo '<input type="hidden" name="op" value="PHP_Check">';
     echo '<center><input type="submit" value="' . BTN_RECHECK . '"></center></form></p>';
   }   
}





   

function oosGetPHPSetting($val) {
  $r =  (ini_get($val) == '1' ? 1 : 0);
  return $r ? 'ON' : 'OFF';
}
?>