<?php
/* ----------------------------------------------------------------------
   $Id: index.php,v 1.1 2005/01/07 09:29:28 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: install.php,v 1.91 2002/02/05 11:09:04 jgm 
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
   ---------------------------------------------------------------------- */
 
 // Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
 
  if (!get_cfg_var('safe_mode')) {
    @set_time_limit(0);
  }
  define('OOS_VALID_MOD', 'yes');
  $currentlang = 'deu';
 
  if (file_exists($file='../includes/oos_version.php')) {
    @include $file;
  } else {
    $server = $_SERVER['HTTP_HOST'];
    $url = trim('http://' . $server . '/');
    header('Location: ' . $url);
    exit;
  }

  include('../includes/config.php');

  include_once('../' . OOS_ADODB . 'adodb-errorhandler.inc.php');
  include_once('../' . OOS_ADODB . 'adodb.inc.php');
 
  include_once 'gui.php'; 
  include_once 'db.php';
  include_once 'check.php'; 
  include_once 'lang/deu/global.php';

  if (isset($_POST)) {
    foreach ($_POST as $k=>$v) {
      $$k = oosPrepareInput($v);
    }
  }

  $dbtype = OOS_DB_TYPE;
  $dbhost = OOS_DB_SERVER;
  $dbname = OOS_DB_DATABASE;
  $prefix_table = OOS_DB_PREFIX;
    
  // Decode encoded DB parameters
  if (OOS_ENCODED == '1') {
    $dbuname = base64_decode(OOS_DB_USERNAME);
    $dbpass = base64_decode(OOS_DB_PASSWORD);
  } else {
    $dbuname = OOS_DB_USERNAME;
    $dbpass = OOS_DB_PASSWORD;
  }

  include_once 'header.php';

/*  This starts the switch statement that filters through the form options.
 *  the @ is in front of $op to suppress error messages if $op is unset and E_ALL
 *  is on
 */
 switch (@$op) {
 
    case "Finish":
      print_oosFinish();
      break;     
   
    case "CAO-Install":      
      oosDBInit($dbhost, $dbuname, $dbpass, $dbname, $dbtype);
    #  oosDoUpgrade131($dbhost, $dbuname, $dbpass, $dbname, $prefix_table, $dbtype);
      print_Next();
    break;

    case 'PHP_Check':
      if ($_POST['agreecheck'] == false) {
        print_oosDefault();
      } else {     
        oosCheckPHP();
      }
      break;

    default:
      print_oosDefault();
      break;
  }
  include_once 'footer.php';

?>