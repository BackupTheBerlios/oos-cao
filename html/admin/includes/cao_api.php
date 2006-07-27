<?php
/* ----------------------------------------------------------------------
   $Id: cao_api.php,v 1.3 2006/07/27 01:54:45 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: oos_main.php,v 1.37 2005/01/06 09:54:16 r23 
   ----------------------------------------------------------------------
   Based on:

   File: application_top.php,v 1.155 2003/02/17 16:54:11 hpdl 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */


// for debug set the level of error reporting
   error_reporting(E_ALL & ~E_NOTICE);
//   error_reporting(0);


// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/config.php')) {
    include('includes/local/config.php');
  }

// Include application configuration parameters
  require('includes/config.php');
  require(OOS_INCLUDES . 'define.php');


  require(OOS_INCLUDES . 'oos_filename.php');
  require(OOS_INCLUDES . 'oos_tables.php');

  require(OOS_FUNCTIONS . 'function_kernel.php');

// Load server utilities  
  require(OOS_FUNCTIONS . 'function_server.php');

  // include the database functions
  require(OOS_ADODB . 'toexport.inc.php');
  require(OOS_ADODB . 'adodb-errorhandler.inc.php');
  require(OOS_ADODB . 'adodb.inc.php');
  require(OOS_FUNCTIONS . 'function_db.php');

  // make a connection to the database... now
  if (!oosDBInit()) {
    die('Unable to connect to database server!');
  }

  $db =& oosDBGetConn();

// set application wide parameters
  $sql = "SELECT configuration_key AS cfgKey, configuration_value AS cfgValue 
          FROM " . $oosDBTable['configuration'];
  $configuration_result = $db->Execute($sql);
  while ($configuration = $configuration_result->fields) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
    $configuration_result->MoveNext();
  }



// define our general functions used application-wide
  require(OOS_FUNCTIONS . 'function_output.php');
  require(OOS_FUNCTIONS . 'function_password.php');


// entry/item info classes
  require(OOS_CLASSES . 'class_object_info.php');

// email classes
  include(OOS_CLASSES . 'phpmailer/class.phpmailer.php');

?>