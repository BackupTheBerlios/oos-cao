<?php
/* ----------------------------------------------------------------------
   $Id: cao_import.php,v 1.5 2005/01/07 09:13:14 r23 Exp $
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */
   
/*******************************************************************************************
*                                                                                          *
*  CAO-Faktura für Windows Version 1.2 (http://www.cao-wawi.de)                            *
*  Copyright (C) 2003 Jan Pokrandt / Jan@JP-SOFT.de                                        *
*                                                                                          *
*  This program is free software; you can redistribute it and/or                           *
*  modify it under the terms of the GNU General Public License                             *
*  as published by the Free Software Foundation; either version 2                          *
*  of the License, or any later version.                                                   *
*                                                                                          *
*  This program is distributed in the hope that it will be useful,                         *
*  but WITHOUT ANY WARRANTY; without even the implied warranty of                          *
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           *
*  GNU General Public License for more details.                                            *
*                                                                                          *
*  You should have received a copy of the GNU General Public License                       *
*  along with this program; if not, write to the Free Software                             *
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
*                                                                                          *
*  ******* CAO-Faktura comes with ABSOLUTELY NO WARRANTY ***************                   *
*                                                                                          *
********************************************************************************************
*                                                                                          *
* Eine Entfernung oder Veraenderung dieses Dateiheaders ist nicht zulaessig !!!            *
* Wenn Sie diese Datei veraendern dann fuegen Sie ihre eigenen Copyrightmeldungen          *
* am Ende diese Headers an                                                                 *
*                                                                                          *
********************************************************************************************
*                                                                                          *
*  Programm     : CAO-Faktura                                                              *
*  Modul        : cao_update.php                                                           *
*  Stand        : 10.12.2004                                                               *
*  Version      : 1.36                                                                     *
*  Beschreibung : Script zum Datenaustausch CAO-Faktura <--> osCommerce-Shop               *
*                                                                                          *
*  based on:                                                                               *
* (c) 2000 - 2001 The Exchange Project                                                     *
* (c) 2001 - 2003 osCommerce, Open Source E-Commerce Solutions                             *
* (c) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers                                      *
* (c) 2003 JP-Soft, Jan Pokrandt                                                           *
* (c) 2003 IN-Solution, Henri Schmidhuber                                                  *
* (c) 2003 www.websl.de, Karl Langmann                                                     *
* (c) 2003 RV-Design Raphael Vullriede                                                     *
* (c) 2004 XT-Commerce                                                                     *
*                                                                                          *
* Released under the GNU General Public License                                            *
*                                                                                          *
*  History :                                                                               *
*                                                                                          *
*  - 25.06.2003 Version 0.1 released Jan Pokrandt                                          *
*  - 29.06.2003 order_opdate aus xml_export.php hierher verschoben                         *
*  - 17.07.2003 tep_array_merge durch array_merge ersetzt                                  *
*  - 18.07.2003 Code fuer Image_Upload hinzugefuegt                                        *
*  - 23.08.2003 Code fuer Hersteller-Update hinzugefuegt                                   *
*  - 25.10.2003 Kunden-Update hinzugefügt                                                  *
*  - 01.11.2003 Statusänderung werden wenn möglich in der Bestellsprache ausgeführt        *
*             Copyright (c) 2004 XT-Commerce                                               *
*              1.1  switching POST/GET vars for CAO imageUpload                            *
*              1.2  mulitlang inserts for Categories                                       *
*              1.3  xt:C v3.0 update                                                       *
*  - 10.12.2004 JP Anpassungen an CAO-Faktura 1.2.6.1                                      *
*                                                                                          *
*******************************************************************************************/

  require('includes/cao_api.php');

 /* Beispiel fuer Useragent 
     if (oosServerGetVar('HTTP_USER_AGENT')!='CAO-Faktura') exit;
  */

  $version_nr    = '1.36';
  $version_datum = '2004.12.10';

  define('CHARSET','iso-8859-1');

  // falls die MWST vom shop vertauscht wird, hier false setzen.
  define('SWITCH_MWST', 'true');

  // Um das Loggen einzuschalten false durch true ersetzen.
  define('LOGGER', 'false'); 

  // Emails beim Kundenanlegen versenden ?
  define('SEND_ACCOUNT_MAIL', 'false');

  // Kundengruppen ID für Neukunden (default "neue Kunden einstellungen in OOS [OSIS Online Shop]")
  define('STANDARD_GROUP', DEFAULT_CUSTOMERS_STATUS_ID);
  
  define('CATEGORIES_STATUS', DEFAULT_CATEGORIES_STATUS); 
  define('DELETE_REVIEWS', 'true');

  // Default-Sprache
  $iso_639_2 = 'deu';
  $lang_folder = 'deu';


  // bufix for CAO image cao_upload (no GET vars!)
  if ($_POST['action']=='manufacturers_image_cao_upload' || 
      $_POST['action']=='categories_image_cao_upload' || 
      $_POST['action']=='products_image_cao_upload') {

    $user = $_POST['user'];
    $password = $_POST['password'];
  } else {
    $user = $_GET['user'];
    $password = $_GET['password'];
  }


  if (LOGGER == 'true') {
    // log data into db.
    $pdata = '';
    while (list($key, $value) = each($_POST)) {
       if (is_array($value)) {
         while (list($key1, $value1) = each($value)) {
           $pdata .= addslashes($key)."[" . addslashes($key1)."] => ".addslashes($value1)."\\r\\n";      
         }
       } else {
         $pdata .= addslashes($key)." => ".addslashes($value)."\\r\\n";
       }
    } 

    $gdata = '';
    while (list($key, $value) = each($_GET)) {
       $gdata .= addslashes($key)." => ".addslashes($value)."\\r\\n";
    } 

    $sql =("INSERT INTO cao_log
            (date,
             user,
             pw,
             method,
             action,
             post_data,
             get_data) 
             VALUES (NOW(),
                     '".$user."',
                     '".$password."',
                     '".$REQUEST_METHOD."',
                     '".$_POST['action']."',
                     '".$pdata."',
                     '".$gdata."')");
    $result = $db->Execute($sql);
    if ($result === false) {
      echo '<br />' .  $db->ErrorMsg();
    } 
  }

  if ($user != '' and $password != '') {

    require(OOS_CLASSES . 'class_cao_upload.php');
  
 
    function xtc_try_cao_upload($file = '', $destination = '', $permissions = '755', $extensions = '') {
      $file_object = new cao_upload($file, $destination, $permissions, $extensions);
      if ($file_object->filename != '') return $file_object; else return false;
    }
  
    // security  1.check if admin user with this mailadress exits, and got access to xml-export
    //           2.check if pasword = true
 
    $check_admin_result = $db->Execute("SELECT admin_id as login_id, admin_groups_id as login_groups_id, 
                                               admin_firstname as login_firstname, admin_email_address as login_email_address, 
                                               admin_password as login_password, admin_modified as login_modified, 
                                               admin_logdate as login_logdate, admin_lognum as login_lognum 
                                        FROM " . $oosDBTable['admin'] . " 
                                        WHERE admin_email_address = '" . oosDBInput($user) . "'");
    if (!$check_admin_result->RecordCount()) {
      header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
      header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
      header ("Pragma: no-cache"); // HTTP/1.0
      header ("Content-type: text/xml");
  
      $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                '<STATUS>' . "\n" .
                '<STATUS_DATA>' . "\n" .
                '<CODE>105</CODE>' . "\n" .
                '<MESSAGE>WRONG LOGIN</MESSAGE>' . "\n" .
                '</STATUS_DATA>' . "\n" .
                '</STATUS>' . "\n\n";
      echo $schema;
    } else {
      $check_admin = $check_admin_result->fields;
      if (!oosValidatePassword($password, $check_admin['login_password'])) {

        header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
        header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header ("Pragma: no-cache"); // HTTP/1.0
        header ("Content-type: text/xml");
    
        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<STATUS>' . "\n" .
                  '<STATUS_DATA>' . "\n" .
                  '<CODE>105</CODE>' . "\n" .
                  '<MESSAGE>WRONG PASSWORD</MESSAGE>' . "\n" .
                  '</STATUS_DATA>' . "\n" .
                  '</STATUS>' . "\n\n";
        echo $schema;
      } else {
        $filename = split('\?', basename($_SERVER['PHP_SELF'])); 
        $filename = $filename[0];
        $page_key = array_search($filename, $oosFilename);
        $login_groups_id = $check_admin[login_groups_id];
     
        $access_result = $db->Execute("SELECT admin_files_name FROM " . $oosDBTable['admin_files'] . " WHERE FIND_IN_SET( '" . $login_groups_id . "', admin_groups_id) AND admin_files_name = '" . $page_key . "'");
        if (!$access_result->RecordCount()) {

          header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
          header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
          header ("Pragma: no-cache"); // HTTP/1.0
          header ("Content-type: text/xml");
    
          $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                    '<STATUS>' . "\n" .
                    '<STATUS_DATA>' . "\n" .
                    '<CODE>105</CODE>' . "\n" .
                    '<MESSAGE>WRONG LOGIN</MESSAGE>' . "\n" .
                    '</STATUS_DATA>' . "\n" .
                    '</STATUS>' . "\n\n";    
          echo $schema;

        } else {

          $db->Execute("UPDATE " . $oosDBTable['admin'] . " 
                        SET admin_logdate = now(), admin_lognum = admin_lognum+1 
                        WHERE admin_id = '" . $check_admin['login_id'] . "'");

          header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
          header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
          header ("Pragma: no-cache"); // HTTP/1.0
          header ("Content-type: text/xml");

          if (($_POST['action']) && (oosServerGetVar('REQUEST_METHOD')=='POST')) {
            switch ($_POST['action']) {
              case 'manufacturers_image_cao_upload':
                if ($manufacturers_image = &xtc_try_cao_upload('manufacturers_image',DIR_FS_CATALOG.DIR_WS_IMAGES,'777', '', true)) {
                  $code = 0;
                  $message = 'OK';
                } else {
                  $code = -1;
	          $message = 'UPLOAD FAILED';
                }
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<STATUS>' . "\n" .
                          '<STATUS_DATA>' . "\n" .
                          '<CODE>' . $code . '</CODE>' . "\n" .
                          '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                          '<MESSAGE>' . $message . '</MESSAGE>' . "\n" . 
                          '<FILE_NAME>' . $manufacturers_image->filename . '</FILE_NAME>' . "\n" .
                          '</STATUS_DATA>' . "\n" .
                          '</STATUS>' . "\n\n";              
                echo $schema;  
                exit;

//-----------------------------------------------------------------------------	
              case 'categories_image_upload':
//-----------------------------------------------------------------------------
                if ( $categories_image = &xtc_try_cao_upload('categories_image',DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/','777', '', true)) {
                  $code = 0;
                  $message = 'OK';
                } else {
                  $code = -1;
                  $message = 'UPLOAD FAILED';
                }        
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<STATUS>' . "\n" .
                          '<STATUS_DATA>' . "\n" .
                          '<CODE>' . $code . '</CODE>' . "\n" .
                          '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                          '<MESSAGE>' . $message . '</MESSAGE>' . "\n" . 
                          '<FILE_NAME>' . $categories_image->filename . '</FILE_NAME>' . "\n" .
                          '</STATUS_DATA>' . "\n" .
                          '</STATUS>' . "\n\n";              
                echo $schema;
                exit;

//----------------------------------------------------------------------------- 
              case 'products_image_upload':
//-----------------------------------------------------------------------------
                if ($products_image = &xtc_try_cao_upload('products_image',DIR_FS_CATALOG.DIR_WS_ORIGINAL_IMAGES,'777', '', true)) {
                  $products_image_name = $products_image->filename;
                  // rewrite VALUES to use resample classes
  
                  // generate resampled images
                  require(DIR_FS_DOCUMENT_ROOT.'admin/includes/product_thumbnail_images.php');
                  require(DIR_FS_DOCUMENT_ROOT.'admin/includes/product_info_images.php');
                  require(DIR_FS_DOCUMENT_ROOT.'admin/includes/product_popup_images.php');
  
                  $code = 0;
                  $message = 'OK';
                } else {
                  $code = -1;
	          $message = 'UPLOAD FAILED';
                }        
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<STATUS>' . "\n" .
                          '<STATUS_DATA>' . "\n" .
                          '<CODE>' . $code . '</CODE>' . "\n" .
                          '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                          '<MESSAGE>' . $message . '</MESSAGE>' . "\n" . 
                          '<FILE_NAME>'.$products_image_name.'</FILE_NAME>' . "\n" .
                          '</STATUS_DATA>' . "\n" .
                          '</STATUS>' . "\n\n";             
                echo $schema;
                exit;

//-----------------------------------------------------------------------------
              case 'manufacturers_update':
//-----------------------------------------------------------------------------
                $manufacturers_id = oosDBPrepareInput($_POST['mID']);
                if (isset($manufacturers_id)) {
                  // Hersteller laden
                  $count_result = $db->Execute("SELECT manufacturers_id, manufacturers_name, manufacturers_image,
                                                      date_added, last_modified 
                                                FROM " . $oosDBTable['manufacturers'] . "
                                                WHERE manufacturers_id = '" . $manufacturers_id . "'");
                  if ($count_result->RecordCount() >= 1) {
                    $exists = 1;
                    // aktuelle Herstellerdaten laden
                    $manufacturer = $count_result->fields;
                    $manufacturers_name  = $manufacturer['manufacturers_name'];
                    $manufacturers_image = $manufacturer['manufacturers_image'];
                    $date_added          = $manufacturer['date_added'];
                    $last_modified       = $manufacturer['last_modified'];
                  } else {
                    $exists = 0; 
  
                     // Variablen nur ueberschreiben wenn als Parameter vorhanden !!!
                     if (isset($_POST['manufacturers_name'])) $manufacturers_name = oosDBPrepareInput($_POST['manufacturers_name']);
                     if (isset($_POST['manufacturers_image'])) $manufacturers_image = oosDBPrepareInput($_POST['manufacturers_image']);

                     $sql_data_array = array('manufacturers_id' => $manufacturers_id,
                                             'manufacturers_name' => $manufacturers_name,
                                             'manufacturers_image' => $manufacturers_image);
                  }
                  if ($exists == 0) {
                    // Neuanlage (ID wird von CAO vorgegeben !!!) 
                    $mode = 'APPEND';
                    $insert_sql_data = array('date_added' => 'now()');
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                    oosDBPerform($oosDBTable['manufacturers'], $sql_data_array);
                    $products_id = $db->Insert_ID();
                  } elseif ($exists == 1) {
                    //UPDATE
                    $mode = 'UPDATE';
                    $update_sql_data = array('last_modified' => 'now()');
                    $sql_data_array = array_merge($sql_data_array, $update_sql_data);
                    oosDBPerform($oosDBTable['manufacturers'], $sql_data_array, 'UPDATE', 'manufacturers_id = \'' . oosDBInput($manufacturers_id) . '\'');
                  }

                  $languages = oosGetLanguages();
                  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $iso_639_2 = $languages[$i]['iso_639_2'];
  
                    // Bestehende Daten laden
                    $desc_result = $db->Execute("SELECT manufacturers_id, manufacturers_language, manufacturers_url, url_clicked, date_last_click 
                                                FROM " . $oosDBTable['manufacturers_info'] . " 
                                                WHERE manufacturers_id = '" . $manufacturers_id . "' 
                                                  AND manufacturers_language = '" . $iso_639_2 . "'");
                    if ($desc = xtc_db_fetch_array($desc_result)) {
                      $manufacturers_url = $desc['manufacturers_url'];
                      $url_clicked       = $desc['url_clicked'];
                      $date_last_click   = $desc['date_last_click'];
                    }
              
                    // uebergebene Daten einsetzen
                    if (isset($_POST['manufacturers_url'][$iso_639_2])) $manufacturers_url = oosDBPrepareInput($_POST['manufacturers_url'][$iso_639_2]);
                    if (isset($_POST['url_clicked'][$iso_639_2]))       $url_clicked = oosDBPrepareInput($_POST['url_clicked'][$iso_639_2]);
                    if (isset($_POST['date_last_click'][$iso_639_2]))   $date_last_click = oosDBPrepareInput($_POST['date_last_click'][$language_id]);
                  
              
                    $sql_data_array = array('manufacturers_url' => $manufacturers_url);
             
                    if ($exists == 0) {
                      $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                               'manufacturers_language' => $iso_639_2);
                      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                      oosDBPerform($oosDBTable['manufacturers_info'], $sql_data_array);
                    } elseif ($exists == 1) {
                      // UPDATE
                      oosDBPerform($oosDBTable['manufacturers_info'], $sql_data_array, 'UPDATE', 'manufacturers_id = \'' . oosDBInput($manufacturers_id) . '\' AND manufacturers_language = \'' . $iso_639_2 . '\'');
                    }
                  }
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '<MODE>' . $mode . '</MODE>' . "\n" .
                            '<MANUFACTURERS_ID>' . $mID . '</MANUFACTURERS_ID>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;         
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";        
                  echo $schema;      
                }
                exit;

//-----------------------------------------------------------------------------
              case 'manufacturers_erase':
//-----------------------------------------------------------------------------
                $ManID  = oosDBPrepareInput($_POST['mID']);
        
                if (isset($ManID)) {
                  // Hersteller loeschen
                  $db->Execute("DELETE FROM " . $oosDBTable['manufacturers'] . " WHERE manufacturers_id = '" . (int)$ManID . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['manufacturers_info'] . " WHERE manufacturers_id = '" . (int)$ManID . "'");
                  // Herstellerverweis in den Artikeln loeschen
                  $db->Execute("UPDATE " . $oosDBTable['products'] . " SET manufacturers_id = '' WHERE manufacturers_id = '" . (int)$ManID . "'");

                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";                     
                  echo $schema;
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";  
                  echo $schema;
                }         
                exit;

//-----------------------------------------------------------------------------
              case 'products_update':
//-----------------------------------------------------------------------------
                $products_id = oosDBPrepareInput($_POST['pID']);
  
                // product laden
                $product_sql = "SELECT products_quantity, products_model, products_image,
                                       products_price, products_date_available, products_weight,
                                       products_status, products_tax_class_id, manufacturers_id 
                                FROM " . $oosDBTable['products'] . "
                                WHERE products_id = '" . $products_id . "'";
                $product_result = $db->Execute($product_sql);
          
                if (!$product_result->RecordCount()) { 
                  $exists = 0;
                } else {
                  $exists = 1;
                  $product = $product_result->fields;
                  // aktuelle Produktdaten laden
                  $products_quantity = $product['products_quantity'];
                  $products_model = $product['products_model'];
                  $products_image = $product['products_image'];
                  $products_price = $product['products_price'];
                  $products_date_available = $product['products_date_available'];
                  $products_weight = $product['products_weight'];
                  $products_status = $product['products_status'];
                  $products_tax_class_id = $product['products_tax_class_id'];
                  $manufacturers_id = $product['manufacturers_id'];
                  if (SWITCH_MWST == 'true') {
                   // switch IDs
                   if ($products_tax_class_id == 1) $products_tax_class_id = 2;
                   if ($products_tax_class_id == 2) $products_tax_class_id = 1;
                  }
                } 
                // Variablen nur ueberschreiben wenn als Parameter vorhanden !!!
                if (isset($_POST['products_quantity'])) $products_quantity = oosDBPrepareInput($_POST['products_quantity']);
                if (isset($_POST['products_model'])) $products_model = oosDBPrepareInput($_POST['products_model']);
                if (isset($_POST['products_image'])) $products_image = oosDBPrepareInput($_POST['products_image']);
                if (isset($_POST['products_price'])) $products_price = oosDBPrepareInput($_POST['products_price']);
                if (isset($_POST['products_date_available'])) $products_date_available = oosDBPrepareInput($_POST['products_date_available']);
                if (isset($_POST['products_weight'])) $products_weight = oosDBPrepareInput($_POST['products_weight']);
                if (isset($_POST['products_status'])) $products_status = oosDBPrepareInput($_POST['products_status']);
  
                if (SWITCH_MWST == 'true') {
                  // switch IDs
                  if ($_POST['products_tax_class_id'] == 1) $_POST['products_tax_class_id']= 2;
                  if ($_POST['products_tax_class_id'] == 2) $_POST['products_tax_class_id'] = 1;
                }
  
                if (isset($_POST['products_tax_class_id'])) $products_tax_class_id = oosDBPrepareInput($_POST['products_tax_class_id']);
                if (isset($_POST['manufacturers_id'])) $manufacturers_id = oosDBPrepareInput($_POST['manufacturers_id']);
  
                $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';
  
                $sql_data_array = array('products_id' => $products_id,
                                        'products_quantity' => $products_quantity,
                                        'products_model' => $products_model,
                                        'products_image' => ($products_image == 'none') ? '' : $products_image,
                                        'products_price' => $products_price,
                                        'products_date_available' => $products_date_available,
                                        'products_weight' => $products_weight,
                                        'products_status' => $products_status,
                                        'products_tax_class_id' => $products_tax_class_id,
                                        'manufacturers_id' => $manufacturers_id);

                if ($exists == 0) { 
                  $mode = 'APPEND';
                  $insert_sql_data = array('products_date_added' => 'now()',
                                           'products_base_price' => 1.0);
                  $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
  
                  // insert data
                  oosDBPerform($oosDBTable['products'], $sql_data_array); 
                  $products_id = $db->Insert_ID();    
                } elseif ($exists == 1) {
                  $mode = 'UPDATE';
                  $update_sql_data = array('products_last_modified' => 'now()');
                  $sql_data_array = array_merge($sql_data_array, $update_sql_data);
  
                  // UPDATE data
                  oosDBPerform($oosDBTable['products'], $sql_data_array, 'UPDATE', 'products_id = \'' . oosDBInput($products_id) . '\'');
                }
  
                $languages = oosGetLanguages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $iso_639_2 = $languages[$i]['iso_639_2'];
  
                  // Bestehende Daten laden
                  $desc_result = $db->Execute("SELECT products_id, products_name, products_description, products_short_description,
                                                     products_url, products_viewed, products_language
                                              FROM " . $oosDBTable['products_description'] . "
                                              WHERE products_id = '" . $products_id . "'
                                                AND products_language = '" . $iso_639_2 . "'");
  
                  $desc = $desc_result->fields;
                  $products_name = $desc['products_name'];
                  $products_description = $desc['products_description'];
                  $products_url = $desc['products_url'];
            
                  // uebergebene Daten einsetzen
                  if (isset($_POST['products_name'][$iso_639_2]))        $products_name=oosDBPrepareInput($_POST['products_name'][$iso_639_2]);
                  if (isset($_POST['products_description'][$iso_639_2])) $products_description=oosDBPrepareInput($_POST['products_description'][$iso_639_2]);
                  if (isset($_POST['products_short_description'][$iso_639_2]))    $products_short_description=oosDBPrepareInput($_POST['products_short_description'][$iso_639_2]);
                  if (isset($_POST['products_url'][$iso_639_2]))         $products_url=oosDBPrepareInput($_POST['products_url'][$iso_639_2]);
  
            
                  $sql_data_array = array('products_name' => $products_name,
                                          'products_description' => $products_description,
                                          'products_short_description' => $products_short_description,
                                          'products_url' => $products_url);
  
                  if ($exists == 0) {
                    $insert_sql_data = array('products_id' => $products_id,
                                             'products_language' => $iso_639_2);
  
                    // get customers groups
                    $db->Execute("INSERT INTO " . $oosDBTable['products_description'] . "
                                  (products_name,
                                   products_description,
                                   products_short_description,
                                   products_url,
                                   products_id,
                                   products_language) 
                                   VALUES ('".$products_name."',
                                           '". nl2br($products_description)."',
                                           '".$products_short_description."',
                                           '".$products_url."',
                                           '".$products_id."',
                                           '".$iso_639_2."')");
                  } elseif ($exists == 1) {
                    oosDBPerform($oosDBTable['products_description'], $sql_data_array, 'UPDATE', 'products_id = \'' . oosDBInput($products_id) . '\' AND products_language = \'' . $iso_639_2 . '\'');
                  }
                }
  
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                         '<STATUS>' . "\n" .
                         '<STATUS_DATA>' . "\n" .
                         '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                         '<CODE>' . '0' . '</CODE>' . "\n" .
                         '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" .
                         '<MODE>' . $mode . '</MODE>' . "\n" .
                         '<PRODUCTS_ID>' . $products_id . '</PRODUCTS_ID>' . "\n" .
                         '</STATUS_DATA>' . "\n" .
                         '</STATUS>' . "\n\n";
  
               echo $schema;
               exit;

//-----------------------------------------------------------------------------
              case 'products_erase':
//-----------------------------------------------------------------------------
                $ProdID  = oosDBPrepareInput($_POST['prodid']);
          
                if (isset($ProdID)) {
                  // Product loeschen
                  oosRemoveProduct($ProdID);
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '<SQL_RES1>' . $res1 . '</SQL_RES1>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";    
                  echo $schema;
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;
                }           
                exit;

//-----------------------------------------------------------------------------
              case 'categories_update': 
//-----------------------------------------------------------------------------
                $categories_id    = oosDBPrepareInput($_POST['catid']);
                $parent_id = oosDBPrepareInput($_POST['parentid']);
                $Sort     = oosDBPrepareInput($_POST['sort']);
                $categories_image    = oosDBPrepareInput($_POST['image']);
                $categories_name     = oosDBPrepareInput(UrlDecode($_POST['name']));
      
                if (isset($parent_id) && isset($categories_id)) {
             
                  $exists = 0;
                  $count_result = $db->Execute("SELECT COUNT(*) AS total FROM " . $oosDBTable['categories'] . " WHERE categories_id = '" . $categories_id . "'");
                  if ($count_result->fields['total'] > 0) {
                    $exists = 1;
        
                    $mode = 'UPDATE';
               
                    $values = "parent_id = '" . $parent_id . "', last_modified=now()";
               
                    if (isset($Sort)) $values .= ", sort_order = '" . $Sort . "'";
                    if (isset($categories_image)) $values .= ", categories_image = '" . $categories_image . "'";
               
                    $res1 = $db->Execute("UPDATE " . $oosDBTable['categories'] . " SET " . $values . " WHERE categories_id = '" . $categories_id . "'");
                  } else {  
                    $mode = 'APPEND';
  
                    if (!isset($Sort)) $Sort=0;
               
                    $felder  = "(categories_id, parent_id, date_added, sort_order, categories_status";
                    if (isset($categories_image)) $felder .= ", categories_image";
                    $felder .= ")";
             
                    $values  = "VALUES(" . "'" . $categories_id . "', '" . $parent_id . "', now(), '" . $Sort . "', '" . CATEGORIES_STATUS . "'";
                    if (isset($categories_image)) $values .= ", '" . $categories_image . "'";
                    $values .= ")";
               
                    $res1 = $db->Execute("INSERT INTO " . $oosDBTable['categories'] . " " . $felder . $values);  
                  }
             
                  // Namen setzen
                  if (isset($categories_name)) {
                    // added multilang support for categories
                    $languages = oosGetLanguages();
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $iso_639_2 = $languages[$i]['iso_639_2'];
                      $res2 = $db->Execute("REPLACE INTO " . $oosDBTable['categories_description'] . " 
                                            (categories_id, 
                                             categories_language, 
                                             categories_name) 
                                             VALUES ('" . $categories_id ."', 
                                                     '" . $iso_639_2 . "', 
                                                     '" . $categories_name . "')");
                    }
                  }
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '<MODE>' . $mode . '</MODE>' . "\n" .
                            '<SQL_RES1>' . $res1 . '</SQL_RES1>' . "\n" .
                            '<SQL_RES2>' . $res2 . '</SQL_RES2>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;       
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                             '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                             '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;
                }
                exit;

//-----------------------------------------------------------------------------
              case 'categories_erase':
//-----------------------------------------------------------------------------
                $categories_id  = oosDBPrepareInput($_POST['catid']);
        
                if (isset($categories_id)) {
                   // Categorie loeschen
                   $res1 = $db->Execute("DELETE FROM " . $oosDBTable['categories'] . " WHERE categories_id = '" . $categories_id . "'");
             
                   // ProductsToCategieries loeschen bei denen die Categorie = ... ist
                   $res2 = $db->Execute("DELETE FROM " . $oosDBTable['products_to_categories'] . " WHERE categories_id = '" . $categories_id . "'");
             
                   // CategieriesDescription loeschenm bei denen die Categorie = ... ist
                   $res3 = $db->Execute("DELETE FROM " . $oosDBTable['categories_description'] . " WHERE categories_id = '" . $categories_id . "'");
             
                   $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '<SQL_RES1>' . $res1 . '</SQL_RES1>' . "\n" .
                            '<SQL_RES2>' . $res2 . '</SQL_RES2>' . "\n" .
                            '<SQL_RES2>' . $res3 . '</SQL_RES2>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;       
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;
                } 
                exit;

//-----------------------------------------------------------------------------
              case 'prod2cat_update':
//-----------------------------------------------------------------------------
                $ProdID = oosDBPrepareInput($_POST['prodid']);
                $categories_id  = oosDBPrepareInput($_POST['catid']);
  
                if (isset($ProdID) && isset($categories_id)) {
                  $res = $db->Execute("REPLACE INTO " . $oosDBTable['products_to_categories'] . " (products_id, categories_id) VALUES ('" . $ProdID ."', '" . $categories_id . "')");
             
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '<SQL_RES>' . $res . '</SQL_RES>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
  
                  echo $schema;
                }
                exit;

//-----------------------------------------------------------------------------
              case 'prod2cat_erase':
//-----------------------------------------------------------------------------
                $ProdID = oosDBPrepareInput($_POST['prodid']);
                $categories_id  = oosDBPrepareInput($_POST['catid']);
        
                if (isset($ProdID) && isset($categories_id)) {
                  $res = $db->Execute("DELETE FROM " . $oosDBTable['products_to_categories'] . " WHERE products_id = '" . $ProdID ."' AND categories_id = '" . $categories_id . "'");
                   
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                            '<SQL_RES>' . $res . '</SQL_RES>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                      
                  echo $schema;
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                             '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                             '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                      
                  echo $schema;
                }
                exit; 

//-----------------------------------------------------------------------------
              case 'order_update': 
//-----------------------------------------------------------------------------
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" . "\n";
          
                if ((isset($_POST['order_id'])) && (isset($_POST['status']))) {
                  // Per Post übergebene Variablen
                  $oID = $_POST['order_id'];
                  $status = $_POST['status'];
                  $comments = oosDBPrepareInput($_POST['comments']);
  
                  //Status überprüfen
                  $check_status_result = $db->Execute("SELECT customers_name, customers_email_address, orders_status, orders_language, date_purchased FROM " . $oosDBTable['orders'] . " WHERE orders_id = '" . oosDBInput($oID) . "'");
                  $check_status = $check_status_result->fields;
if ($check_status = xtc_db_fetch_array($check_status_result)) {
                    if ($check_status['orders_status'] != $status || $comments != '' || ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) ) {
                      $db->Execute("UPDATE " . $oosDBTable['orders'] . " SET orders_status = '" . oosDBInput($status) . "', last_modified = now() WHERE orders_id = '" . oosDBInput($oID) . "'");
                      $check_status_result2 = $db->Execute("SELECT customers_name, customers_email_address, orders_status, date_purchased FROM " . $oosDBTable['orders'] . " WHERE orders_id = '" . oosDBInput($oID) . "'");
                      $check_status2 = $check_status_result2->fields;
                      if ( $check_status2['orders_status'] == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE ) {
                        $db->Execute("UPDATE " . $oosDBTable['orders_products_download'] . " SET download_maxdays = '" . oosGetConfigurationKeyValue('DOWNLOAD_MAX_DAYS') . "', download_count = '" . oosGetConfigurationKeyValue('DOWNLOAD_MAX_COUNT') . "' WHERE orders_id = '" . oosDBInput($oID) . "'");
                      }
                      $customer_notified = '0';

                      if ($_POST['notify'] == 'on') {
                        // Falls eine Sprach ID zur Order existiert die Emailbestätigung in dieser Sprache ausführen
                        if (oosNotNull($check_status['orders_language'])) {
                          $orders_status_result = $db->Execute("SELECT orders_status_id, orders_status_name FROM " . $oosDBTable['orders_status'] . " WHERE orders_language = '" . $check_status['orders_language'] . "'");
                        } else {
                          $orders_status_result = $db->Execute("SELECT orders_status_id, orders_status_name FROM " . $oosDBTable['orders_status'] . " WHERE orders_language = '" . $iso_639_2 . "'");
                        }
  
                        $orders_statuses = array();
                        $orders_status_array = array();
                        while ($orders_status = $orders_status_result->fields) {
                          $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                                                     'text' => $orders_status['orders_status_name']);
                          $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
                          $orders_status_result->MoveNext();
                        }
                        // status query
                        $orders_status_result = $db->Execute("SELECT orders_status_name FROM " . $oosDBTable['orders_status'] . " WHERE orders_language = '" . $iso_639_2 . "' AND orders_status_id = '".$status."'");
                        $o_status = $orders_status_result->fields;
                        $o_status = $o_status['orders_status_name'];
  
                        //ok lets generate the html/txt mail FROM Template
                        if ($_POST['notify_comments'] == 'on') {
                          $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
                        } else {
                          $comments = '';
                        }
  
// require functionblock for mails

                  $smarty = new Smarty;
  
                  $smarty->assign('language', $check_status['language']);
                  $smarty->caching = false;
  
  
                  $smarty->template_dir=DIR_FS_CATALOG.'templates';
                  $smarty->compile_dir=DIR_FS_CATALOG.'templates_c';
                  $smarty->config_dir=DIR_FS_CATALOG.'lang';
  
  
                  $smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
                  $smarty->assign('logo_path',HTTP_SERVER  . DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
  
  
                  $smarty->assign('NAME',$check_status['customers_name']);
                  $smarty->assign('ORDER_NR',$oID);
                  $smarty->assign('ORDER_LINK',xtc_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL'));
                  $smarty->assign('ORDER_DATE',xtc_date_long($check_status['date_purchased']));
                  $smarty->assign('NOTIFY_COMMENTS',$comments);
                  $smarty->assign('ORDER_STATUS',$o_status);
  
                  $html_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$check_status['language'].'/change_order_mail.html');
                  $txt_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$check_status['language'].'/change_order_mail.txt');
  
// send mail with html/txt template
                        $email_address = $check_status['customers_email_address'];
                        $name = $check_status['customers_name'];
                        oosMail($name, $email_address, EMAIL_SUPPORT_SUBJECT, nl2br($email_text), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '3');
                        $customer_notified = '1';
                      }
        
  
                      $db->Execute("INSERT INTO " . $oosDBTable['orders_status_history'] . " 
                                   (orders_id, 
                                     orders_status_id, 
                                     date_added, 
                                     customer_notified, 
                                     comments) 
                                     VALUES ('" . oosDBInput($oID) . "', 
                                             '" . oosDBInput($status) . "', 
                                             now(), 
                                             '" . $customer_notified . "', 
                                             '" . oosDBInput($comments)  . "')");
                      $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                                '<STATUS>' . "\n" .
                                '<STATUS_DATA>' . "\n" .
                                '<ORDER_ID>' . $oID . '</ORDER_ID>' . "\n" .
                                '<ORDER_STATUS>' . $status . '</ORDER_STATUS>' . "\n" .
                                '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                                '<CODE>' . '0' . '</CODE>' . "\n" .
                                '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" . 
                                '</STATUS_DATA>' . "\n" .
                                '</STATUS>' . "\n";
                    } else if ($check_status['orders_status'] == $status) {
                      // Status ist bereits gesetzt
                      $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                                '<STATUS>' . "\n" .
                                '<STATUS_DATA>' . "\n" .
                                '<ORDER_ID>' . $oID . '</ORDER_ID>' . "\n" .
                                '<ORDER_STATUS>' . $status . '</ORDER_STATUS>' . "\n" .
                                '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                                '<CODE>' . '1' . '</CODE>' . "\n" .
                                '<MESSAGE>' . 'NO STATUS CHANGE' . '</MESSAGE>' . "\n" . 
                                '</STATUS_DATA>' . "\n" .
                                '</STATUS>' . "\n";
                    }
                  } else {
                    // Fehler Order existiert nicht
                    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                              '<STATUS>' . "\n" .
                              '<STATUS_DATA>' . "\n" .
                              '<ORDER_ID>' . $oID . '</ORDER_ID>' . "\n" .
                              '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                              '<CODE>' . '2' . '</CODE>' . "\n" .
                              '<MESSAGE>' . 'ORDER_ID NOT FOUND OR SET' . '</MESSAGE>' . "\n" . 
                              '</STATUS_DATA>' . "\n" .
                              '</STATUS>' . "\n";
                  }
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" . 
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                }
                echo $schema;
                exit;

//-----------------------------------------------------------------------------
              case 'customers_update':
//-----------------------------------------------------------------------------
                $customers_id = -1;       
  
                if (isset($_POST['cID'])) $customers_id = oosDBPrepareInput($_POST['cID']);
  
                $sql_customers_data_array = array();
if (isset($_POST['customers_cid'])) $sql_customers_data_array['customers_cid'] = $_POST['customers_cid'];
                if (isset($_POST['customers_firstname'])) $sql_customers_data_array['customers_firstname'] = $_POST['customers_firstname'];
                if (isset($_POST['customers_lastname'])) $sql_customers_data_array['customers_lastname'] = $_POST['customers_lastname'];
                if (isset($_POST['customers_dob'])) $sql_customers_data_array['customers_dob'] = $_POST['customers_dob'];
                if (isset($_POST['customers_email'])) $sql_customers_data_array['customers_email_address'] = $_POST['customers_email'];
                if (isset($_POST['customers_tele'])) $sql_customers_data_array['customers_telephone'] = $_POST['customers_tele'];
                if (isset($_POST['customers_fax'])) $sql_customers_data_array['customers_fax'] = $_POST['customers_fax'];
                if (isset($_POST['customers_gender'])) $sql_customers_data_array['customers_gender'] = $_POST['customers_gender'];
                if (isset($_POST['customers_password'])) {
                  $sql_customers_data_array['customers_password'] = oosEncryptPassword($_POST['customers_password']);
                } else {
                  $newpass = oosCreateRandomValue(ENTRY_PASSWORD_MIN_LENGTH);
                  $crypted_password = oosEncryptPassword($newpass); 
                  $sql_customers_data_array['customers_password'] = $crypted_password;
                }
  
                $sql_address_data_array = array();
                if (isset($_POST['customers_firstname'])) $sql_address_data_array['entry_firstname'] = $_POST['customers_firstname'];
                if (isset($_POST['customers_lastname'])) $sql_address_data_array['entry_lastname'] = $_POST['customers_lastname'];
                if (isset($_POST['customers_company'])) $sql_address_data_array['entry_company'] = $_POST['customers_company'];
                if (isset($_POST['customers_street'])) $sql_address_data_array['entry_street_address'] = $_POST['customers_street'];
                if (isset($_POST['customers_city'])) $sql_address_data_array['entry_city'] = $_POST['customers_city'];
                if (isset($_POST['customers_postcode'])) $sql_address_data_array['entry_postcode'] = $_POST['customers_postcode'];
                if (isset($_POST['customers_gender'])) $sql_address_data_array['entry_gender'] = $_POST['customers_gender'];
  
                if (isset($_POST['customers_country_id'])) $country_code = $_POST['customers_country_id'];
                $country_result = "SELECT countries_id FROM " . $oosDBTable['countries'] . " WHERE countries_iso_code_2 = '". $country_code ."'";
                $country_result = $db->Execute($country_result);
                $row = $country_result->fields;
                $sql_address_data_array['entry_country_id'] = $row['countries_id'];
  
  
                $count_result = $db->Execute("SELECT COUNT(*) AS count FROM " . $oosDBTable['customers'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                $check = $count_result->fields;
  
                if ($check['count'] > 0) {
                  $mode = 'UPDATE';

                  $address_book_result = $db->Execute("SELECT customers_default_address_id FROM ".$oosDBTable['customers']." WHERE customers_id = '". (int)$customers_id ."'");
                  $customer = $address_book_result->fields;
                  oosDBPerform($oosDBTable['customers'], $sql_customers_data_array, 'UPDATE', "customers_id = '" . (int)$customers_id . "'");
                  oosDBPerform($oosDBTable['address_book'], $sql_address_data_array, 'UPDATE', "customers_id = '" . (int)$customers_id . "' AND address_book_id = '".$customer['customers_default_address_id']."'");
                  $db->Execute("UPDATE " . $oosDBTable['customers_info'] . " SET customers_info_date_account_last_modified = now() WHERE customers_info_id = '" . (int)$customers_id . "'");
                } else {
                  $mode= 'APPEND';
  
                  oosDBPerform($oosDBTable['customers'], $sql_customers_data_array);
                  $customers_id = $db->Insert_ID();
                  $sql_address_data_array['customers_id'] = $customers_id;
                  oosDBPerform($oosDBTable['address_book'], $sql_address_data_array);
                  $address_id = $db->Insert_ID();
                  $db->Execute("UPDATE " . $oosDBTable['customers'] . " SET customers_default_address_id = '" . (int)$address_id . "' WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("UPDATE " . $oosDBTable['customers'] . " SET customers_status = '" . STANDARD_GROUP . "' WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("INSERT INTO " . $oosDBTable['customers_info'] . " 
                                (customers_info_id, 
                                 customers_info_number_of_logons, 
                                 customers_info_date_account_created) 
                                 VALUES ('" . (int)$customers_id . "', '0', now())");
                }
  
                if (SEND_ACCOUNT_MAIL == 'true' && $mode=='APPEND' && $sql_customers_data_array['customers_email_address']!='') {
// generate mail for customer if customer=new

                  $smarty = new Smarty;
  
                  $smarty->assign('language', $check_status['language']);
                  $smarty->caching = false;
  
  
                  $smarty->template_dir=DIR_FS_CATALOG.'templates';
                  $smarty->compile_dir=DIR_FS_CATALOG.'templates_c';
                  $smarty->config_dir=DIR_FS_CATALOG.'lang';
  
  
                  $smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
                  $smarty->assign('logo_path',HTTP_SERVER  . DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
  
  
  
                  $smarty->assign('NAME',$sql_customers_data_array['customers_lastname'] . ' ' . $sql_customers_data_array['customers_firstname']);
                  $smarty->assign('EMAIL',$sql_customers_data_array['customers_email_address']);
  
                  $smarty->assign('PASSWORD',$pw);
  
                  $smarty->assign('language', $lang_folder);
                  $smarty->assign('content', $module_content);
                  $smarty->caching = false;
  
                  $html_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$lang_folder.'/create_account_mail.html');
                  $txt_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$lang_folder.'/create_account_mail.txt');
  
// send mail with html/txt template
                  $email_address = $sql_customers_data_array['customers_email_address'];
                  $name = $sql_customers_data_array['customers_lastname'] . ' ' . $sql_customers_data_array['customers_firstname'];
                  oosMail($name, $email_address, EMAIL_SUPPORT_SUBJECT, nl2br($email_text), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '3');
                }
  
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<STATUS>' . "\n" .
                          '<STATUS_DATA>' . "\n" .
                          '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                          '<CODE>' . '0' . '</CODE>' . "\n" .
                          '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" .
                          '<MODE>' . $mode . '</MODE>' . "\n" .
                          '<CUSTOMERS_ID>' . $customers_id . '</CUSTOMERS_ID>' . "\n" .
                          '</STATUS_DATA>' . "\n" .
                          '</STATUS>' . "\n\n";
                echo $schema;
                exit;

//-----------------------------------------------------------------------------
              case 'customers_erase':
//-----------------------------------------------------------------------------
                $customers_id = oosDBPrepareInput($_POST['cID']);
  
                if (isset($customers_id)) {
                  if (DELETE_REVIEWS == 'true') {
                    $reviews_result = $db->Execute("SELECT reviews_id FROM " . $oosDBTable['reviews'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                    while ($reviews = $reviews_result->fields) {
                      $db->Execute("DELETE FROM " . $oosDBTable['reviews_description'] . " WHERE reviews_id = '" . $reviews['reviews_id'] . "'");
                      $reviews_result->MoveNext();
                    }
                    $db->Execute("DELETE FROM " . $oosDBTable['reviews'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  } else {
                    $db->Execute("UPDATE " . $oosDBTable['reviews'] . " SET customers_id = null WHERE customers_id = '" . (int)$customers_id . "'");
                  }

                  $db->Execute("DELETE FROM " . $oosDBTable['address_book'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers_info'] . " WHERE customers_info_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers_basket'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers_basket_attributes'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers_wishlist'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers_wishlist_attributes'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['customers_status_history'] . " WHERE customers_id = '" . (int)$customers_id . "'");
                  $db->Execute("DELETE FROM " . $oosDBTable['whos_online'] . " WHERE customer_id = '" . (int)$customers_id . "'");

                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '0' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'OK' . '</MESSAGE>' . "\n" .
                            '<SQL_RES1>' . $res1 . '</SQL_RES1>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;
                } else {
                  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                            '<STATUS>' . "\n" .
                            '<STATUS_DATA>' . "\n" .
                            '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                            '<CODE>' . '99' . '</CODE>' . "\n" .
                            '<MESSAGE>' . 'PARAMETER ERROR' . '</MESSAGE>' . "\n" .
                            '</STATUS_DATA>' . "\n" .
                            '</STATUS>' . "\n\n";
                  echo $schema;
                }
                exit;

//-----------------------------------------------------------------------------
              case 'version':
//-----------------------------------------------------------------------------
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<STATUS>' . "\n" .
                          '<STATUS_DATA>' . "\n" .
                          '<ACTION>' . $_POST['action'] . '</ACTION>' . "\n" .
                          '<CODE>' . '111' . '</CODE>' . "\n" .              
                          '<SCRIPT_VER>' . $version_nr . '</SCRIPT_VER>' . "\n" . 
                          '<SCRIPT_DATE>' . $version_datum . '</SCRIPT_DATE>' . "\n" . 
                          '</STATUS_DATA>' . "\n" .
                          '</STATUS>' . "\n\n";
                echo $schema;
                exit;      
  
              default:      
                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<STATUS>' . "\n" .
                          '<STATUS_DATA>' . "\n" .
                          '<CODE>' . '100' . '</CODE>' . "\n" .
                          '<MESSAGE>' . 'UNKNOWN ACTION PARAMETER' . '</MESSAGE>' . "\n" . 
                          '</STATUS_DATA>' . "\n" .
                          '</STATUS>' . "\n\n";
                    
                echo $schema;
                exit;      
            } // switch

          } else {
      
            if (($_GET['action']) && (oosServerGetVar('REQUEST_METHOD')=='GET') && ($_GET['action'])) {
              $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                        '<STATUS>' . "\n" .
                        '<STATUS_DATA>' . "\n" .
                        '<ACTION>' . $_GET['action'] . '</ACTION>' . "\n" .
                        '<CODE>' . '111' . '</CODE>' . "\n" .              
                        '<SCRIPT_VER>' . $version_nr . '</SCRIPT_VER>' . "\n" .
                        '<SCRIPT_DATE>' . $version_datum . '</SCRIPT_DATE>' . "\n" .
                        '</STATUS_DATA>' . "\n" .
                        '</STATUS>' . "\n\n";
              echo $schema;
            } else {
              $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                        '<STATUS>' . "\n" .
                        '<STATUS_DATA>' . "\n" .
                        '<CODE>' . '101' . '</CODE>' . "\n" .
                        '<MESSAGE>' . 'METHOD NOT POST OR UNKNOWN PARAMETER' . '</MESSAGE>' . "\n" .
                        '</STATUS_DATA>' . "\n" .
                        '</STATUS>' . "\n\n";
              echo $schema;
            }
          }
        }
      }
    }
  } else {
    oosRedirect(oosLink($oosFilename['forbiden']));
  }
?>