<?php
/* ----------------------------------------------------------------------
   $Id: newtables.php,v 1.2 2005/01/11 18:26:20 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: newtables.php,v 1.40.2.1 2002/04/03 21:02:06 
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
   Original Author of file:
   Purpose of file: 
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

function dosql($table, $flds) {
   GLOBAL $db;

   $dict = NewDataDictionary($db);
 
   $taboptarray = array('mysql' => 'TYPE=MyISAM', 'REPLACE'); 
  
   $sqlarray = $dict->CreateTableSQL($table, $flds, $taboptarray);
   $dict->ExecuteSQLArray($sqlarray); 
 
   echo '<br><img src="images/yes.gif" alt="" border="0" align="absmiddle"> <font class="oos-title">' . $table . " " . MADE . '</font>';   
}

function idxsql($idxname, $table, $idxflds) {
   GLOBAL $db;
   
   $dict = NewDataDictionary($db);
   
   $sqlarray = $dict->CreateIndexSQL($idxname, $table, $idxflds);
   $dict->ExecuteSQLArray($sqlarray);
}


$table = $prefix_table . 'cao_log';
$flds = "
  id I NOTNULL AUTO PRIMARY,
  date T,
  user C(64) NOTNULL,
  pw C(64) NOTNULL,
  method C(64) NOTNULL,
  action C(64) NOTNULL,
  post_data X,
  get_data X
";
dosql($table, $flds);


$table = $prefix_table . 'orders';
$result = $db->Execute("ALTER TABLE " . $table . " ADD `payment_class` VARCHAR( 32 ) NOT NULL AFTER `payment_method`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
} 

$result = $db->Execute("ALTER TABLE " . $table . " ADD  `shipping_method` VARCHAR( 255 ) NOT NULL AFTER `payment_class`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
} 

$result = $db->Execute("ALTER TABLE " . $table . " ADD `shipping_class` VARCHAR( 32 ) NOT NULL AFTER `shipping_method`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
} 

$result = $db->Execute("ALTER TABLE " . $table . " ADD `billing_country_iso_code_2` VARCHAR( 2 ) NOT NULL AFTER `billing_country`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}

$result = $db->Execute("ALTER TABLE " . $table . " ADD `delivery_country_iso_code_2` VARCHAR( 2 ) NOT NULL AFTER `delivery_country`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}


$result = $db->Execute("ALTER TABLE " . $table . " ADD `billing_firstname` VARCHAR( 32 ) NOT NULL AFTER `billing_name`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}

$result = $db->Execute("ALTER TABLE " . $table . " ADD `billing_lastname` VARCHAR( 32 ) NOT NULL AFTER `billing_firstname`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}

$result = $db->Execute("ALTER TABLE " . $table . " ADD `delivery_firstname` VARCHAR( 32 ) NOT NULL AFTER `delivery_name`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}

$result = $db->Execute("ALTER TABLE " . $table . " ADD `delivery_lastname` VARCHAR( 32 ) NOT NULL AFTER `delivery_firstname`");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}

$result = $db->Execute("ALTER TABLE " . $table . " CHANGE `payment_method` `payment_method` VARCHAR( 255 ) NOT NULL");
if ($result === false) {
  echo '<br /><img src="images/no.gif" alt="" border="0" align="absmiddle"><font class="oos-error">' .  $db->ErrorMsg() . NOTMADE . '</font>';
}

echo '<br /><img src="images/yes.gif" alt="" border="0" align="absmiddle"><font class="oos-title">' . $table . ' ' . UPDATED .'</font>';


?>