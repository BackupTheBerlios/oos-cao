<?php
/* ----------------------------------------------------------------------
   $Id: newinstall.php,v 1.1 2005/01/10 10:41:16 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: newinstall.php,v 1.5 2002/02/09 12:50:40 
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

   This program is free software; you can redistribute it AND/or
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
   Purpose of file: Provide functions for a new install.
   ---------------------------------------------------------------------- */

  /**
   * This function inserts the default data on new installs 
   */
  function oosInputData($gender, $firstname, $name, $pwd, $repeatpwd, $email, $phone, $fax, $prefix_table, $update) {
    global $db;


    echo '<font class="oos-title">' . INPUT_DATA . '</font>';
    echo '<table align="center"><tr><td align="left">';
    
    if (!$prefix_table == '') $prefix_table = $prefix_table . '_';

    include('newtables.php');


    // Put basic information in first
    $today = date("Y-m-d H:i:s");

    $owp_pwd = md5($pwd);
    $admin_groups_name = 'CAO-Faktura';

    $sql = "INSERT INTO ". $prefix_table . "admin_groups 
            (admin_groups_name) 
             VALUES (" . $db->qstr($admin_groups_name) . ")";
    $result = $db->Execute($sql);
    if ($result === false) {
      echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
    } else {
      echo '<br /><img src="images/yes.gif" alt="" border="0" align="absmiddle"> <font class="oos-title">' . $prefix_table . 'admin_groups&nbsp;'. UPDATED . '</font>';
    }
    $admin_groups_id = $db->Insert_ID();
    $sql = "INSERT INTO ". $prefix_table . "admin
            (admin_groups_id,
             admin_gender,
             admin_firstname,
             admin_lastname,
             admin_email_address,
             admin_telephone,
             admin_fax,
             admin_password,
             admin_created)
             VALUES (" . $db->qstr($admin_groups_id) . ','
                       . $db->qstr($gender) . ','
                       . $db->qstr($firstname) . ','
                       . $db->qstr($name) . ','
                       . $db->qstr($email) . ','
                       . $db->qstr($phone) . ','
                       . $db->qstr($fax) . ','
                       . $db->qstr($owp_pwd) . ','
                       . $db->DBTimeStamp($today) . ")";
    $result = $db->Execute($sql);
    if ($result === false) {
      echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
    } else {
      echo '<br /><img src="images/yes.gif" alt="" border="0" align="absmiddle"> <font class="oos-title">' . $prefix_table . 'admin&nbsp;'. UPDATED . '</font>';
    }
    $boxes = 0;
    $files_name = 'cao_import';
    $sql = "INSERT INTO ". $prefix_table . "admin_files
            (admin_files_name, 
             admin_files_is_boxes, 
             admin_files_to_boxes, 
             admin_groups_id)
             VALUES (" . $db->qstr($files_name) . ','
                       . $db->qstr($boxes) . ','
                       . $db->qstr($boxes) . ','
                       . $db->qstr($admin_groups_id). ")";
    $result = $db->Execute($sql);
    if ($result === false) {
      echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
    } 
    $files_name = 'xml_export';
    $sql = "INSERT INTO ". $prefix_table . "admin_files
            (admin_files_name, 
             admin_files_is_boxes, 
             admin_files_to_boxes, 
             admin_groups_id)
             VALUES (" . $db->qstr($files_name) . ','
                       . $db->qstr($boxes) . ','
                       . $db->qstr($boxes) . ','
                       . $db->qstr($admin_groups_id). ")";
    $result = $db->Execute($sql);
    if ($result === false) {
      echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
    } else {
      echo '<br /><img src="images/yes.gif" alt="" border="0" align="absmiddle"> <font class="oos-title">' . $prefix_table . 'admin_files&nbsp;'. UPDATED . '</font>';
    }
    
    echo '</td></tr></table>';
  }
?>