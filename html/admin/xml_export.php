<?php
/* ----------------------------------------------------------------------
   $Id: xml_export.php,v 1.7 2005/01/10 11:07:51 r23 Exp $
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
*  Modul        : xml_Export.php                                                           *
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
*  - 25.06.2003 JP Version 0.1 released                                                    *
*  - 26.06.2003 HS beim Orderexport orderstatus und comment hinzugefuegt                   *
*  - 29.06.2003 JP order_update entfernt und in die Datei cao_update.php verschoben        *
*  - 20.07.2003 HS Shipping und Paymentklassen aufgenommen                                 *
*  - 02.08.2003 KL MANUFACTURERS_DESCRIPTION  language_id geändert in languages_id         *
*  - 09.08.2003 JP fuer das Modul Banktransfer werden jetzt die daten bei der Bestll-      *
*                  uebermittlung mit ausgegeben                                            *
*  - 10.08.2003 JP Geburtsdatum wird jetzt in den Bestellungen mit uebergeben              *
*  - 18.08.2003 JP Bug bei Products/URL beseitigt                                          *
*  - 18.08.2003 HS Bankdaten werden nur bei Banktransfer ausgelesen                        *
*  - 25.10.2003 RV Kunden-Export hinzugefügt                                               *
*  - 24.11.2003 HS Fix Kunden-Export - Newsletterexport hinzugefügt                        *
*  - 01.12.2003 RV Code für 3 Produktbilder-Erweiterung hinzugefügt.                       *
*  - 31.01.2004 JP Resourcenverbrauch minimiert                                            *
*                  tep_set_time_limit ist jetzt per DEFINE zu- und abschaltbar             *
*  - 06.06.2004 JP per DEFINE kann jetzt die Option "3 Produktbilder" geschaltet werden    *
*  - 09.10.2004 RV automatisch Erkennung von 3 Bilder Contrib laut readme                  *
*  - 09.10.2004 RV vereinheitlicher Adress-Export bei Bestellungen und Kunden              *
*  - 09.10.2004 RV Kunden Vor- und Nachname bei Bestellungen getrennt exportieren          *
*  - 09.10.2004 RV SQL-Cleanup                                                             *
*  - 09.10.2004 RV CODE-Cleanup                                                            *
*  - 14.10.2004 RV Länder bei Bestellungen als ISO-Code                                    *
*  - 03.12.2003 JP Bugfix beim Kunden-Export (Fehlende Felder)                             *
*               XTC  1.1 fixed bug with attributes and products qty > 1                    *
*               XTC  1.2 Updates for xt:C 3.0                                              *
*  - 10.12.2004 JP Anpassungen fuer CAO 1.2.6.x (customers_export, orders_export)          *
*******************************************************************************************/


  require('includes/cao_api.php');

  if (oosServerGetVar('HTTP_USER_AGENT') != 'CAO-Faktura') exit;
  
  $version_nr    = '1.37';
  $version_datum = '2004.12.10';
  
  define('CHARSET','iso-8859-1');

  // Steuer einstellungen für CAO-Faktura
  $order_total_class['ot_cod_fee']['prefix'] = '+';
  $order_total_class['ot_cod_fee']['tax'] = '16';

  $order_total_class['ot_customer_discount']['prefix'] = '-';
  $order_total_class['ot_customer_discount']['tax'] = '16';

  $order_total_class['ot_gv']['prefix'] = '-';
  $order_total_class['ot_gv']['tax'] = '0';

  $order_total_class['ot_loworderfee']['prefix'] = '+';
  $order_total_class['ot_loworderfee']['tax'] = '16';

  $order_total_class['ot_shipping']['prefix'] = '+';
  $order_total_class['ot_shipping']['tax'] = '16';


  // falls die MWST vom shop vertauscht wird, hier false setzen.
  define('SWITCH_MWST', 'true');

  // Um das Loggen einzuschalten false durch true ersetzen.
  define('LOGGER', 'true'); 


  // Default-Sprache
  $iso_639_2 = 'deu';

// check permissions for XML-Access
  $user = $_GET['user'];
  $password = $_GET['password'];

  if (substr($password,0,2) == '%%') {
    $password = md5(substr($password,2,40));
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

    $today = date("Y-m-d H:i:s");
    $method = oosServerGetVar('REQUEST_METHOD');
    $action = (isset($_POST['action']) ? $_POST['action'] : '');
    
    $sql = "INSERT INTO " . $oosDBTable['cao_log'] . "
            (date,
             user,
             pw,
             method,
             action,
             post_data,
             get_data) 
             VALUES (" . $db->DBTimeStamp($today) . ','
                       . $db->qstr($user) . ','
                       . $db->qstr($password) . ','
                       . $db->qstr($method) . ','
                       . $db->qstr($action) . ','
                       . $db->qstr($pdata) . ','
                       . $db->qstr($gdata) . ")";
    $result = $db->Execute($sql);
    if ($result === false) {

      header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
      header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
      header ("Pragma: no-cache"); // HTTP/1.0
      header ("Content-type: text/xml");

      $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                '<STATUS>' . "\n" .
                '<STATUS_DATA>' . "\n" .
                '<CODE>105</CODE>' . "\n" .
                '<MESSAGE>'. $db->ErrorMsg() .'</MESSAGE>' . "\n" .
                '<MESSAGE>WRONG PASSWORD</MESSAGE>' . "\n" .
                '</STATUS_DATA>' . "\n" .
                '</STATUS>' . "\n\n";
      
      echo $schema;

    } 
  }


  if ($user!='' and $password!='') {

    // security  1.check if admin user with this mailadress exits, and got access to xml-export
    //           2.check if pasword = true

    $check_admin_result = $db->Execute("SELECT admin_id as login_id, admin_groups_id as login_groups_id, 
                                               admin_firstname as login_firstname, admin_email_address as login_email_address, 
                                               admin_password as login_password, admin_modified as login_modified, 
                                               admin_logdate as login_logdate, admin_lognum as login_lognum 
                                        FROM " . $oosDBTable['admin'] . " 
                                        WHERE admin_email_address = '" . oosDBInput($user) . "'");
    if (!$check_admin_result->RecordCount()) {
      oosRedirect(OOS_HTTP_SERVER . OOS_ADMIN . $oosFilename['xml_export'] . '?error=WRONG+LOGIN&code=101');
    } else {
      $check_admin = $check_admin_result->fields;

      if ($password != $check_admin['login_password']) {
        oosRedirect(OOS_HTTP_SERVER . OOS_ADMIN . $oosFilename['xml_export'] . '?error=WRONG+LOGIN&code=101');
      } else {
        $filename = split('\?', basename($_SERVER['PHP_SELF'])); 
        $filename = $filename[0];
        $page_key = array_search($filename, $oosFilename);
        $login_groups_id = $check_admin[login_groups_id];
           
        $access_result = $db->Execute("SELECT admin_files_name FROM " . $oosDBTable['admin_files'] . " WHERE FIND_IN_SET( '" . $login_groups_id . "', admin_groups_id) AND admin_files_name = '" . $page_key . "'");
        if (!$access_result->RecordCount()) {
          oosRedirect(OOS_HTTP_SERVER . OOS_ADMIN . $oosFilename['xml_export'] . '?error=WRONG+LOGIN&code=101');
        } else {

          $db->Execute("UPDATE " . $oosDBTable['admin'] . " 
                        SET admin_logdate = now(), admin_lognum = admin_lognum+1 
                        WHERE admin_id = '" . $check_admin['login_id'] . "'");
      
          header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
          header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
          header ("Pragma: no-cache"); // HTTP/1.0
          header ("Content-type: text/xml");


          $action = (isset($_GET['action']) ? $_GET['action'] : '');

          if (oosNotNull($action)) {
            switch ($action) { 
              case 'categories_export':
                oosSetTimeLimit(0);

                $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                          '<CATEGORIES>' . "\n";
                    
                echo $schema;
                   
                $cat_query = "SELECT categories_id, categories_image, parent_id, sort_order, date_added, last_modified
                              FROM " . $oosDBTable['categories'] . " 
                              ORDER BY parent_id, categories_id";
                $cat_result = $db->Execute($cat_query);

                while ($cat = $cat_result->fields) {
                  $schema  = '<CATEGORIES_DATA>' . "\n" .
                             '<ID>' . $cat['categories_id'] . '</ID>' . "\n" .
                             '<PARENT_ID>' . $cat['parent_id'] . '</PARENT_ID>' . "\n" .
                             '<IMAGE_URL>' . htmlspecialchars($cat['categories_image']) . '</IMAGE_URL>' . "\n" .
                             '<SORT_ORDER>' . $cat['sort_order'] . '</SORT_ORDER>' . "\n" .
                             '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                             '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";

                  $detail_query = "SELECT cd.categories_id, cd.categories_language, cd.categories_name, 
                                          cd.categories_heading_title, cd.categories_description, 
                                          cd.categories_description_meta, categories_keywords_meta,
                                          l.languages_id, l.name as lang_name
                                   FROM " . $oosDBTable['categories_description'] . " cd,
                                        " . $oosDBTable['languages'] . " l
                                   WHERE cd.categories_id=" . $cat['categories_id'] . " 
                                     AND l.iso_639_2 = cd.categories_language";
                  $detail_result = $db->Execute($detail_query);
                 
                  while ($details = $detail_result->fields) {
                    $schema .= "<CATEGORIES_DESCRIPTION ID='" . $details["languages_id"] ."' CODE='" . $details["categories_language"] . "' NAME='" . $details["lang_name"] . "'>\n";
                    $schema .= "<NAME>" . htmlspecialchars($details["categories_name"]) . "</NAME>" . "\n";
                    $schema .= "<HEADING_TITLE>" . htmlspecialchars($details["categories_heading_title"]) . "</HEADING_TITLE>" . "\n";
                    $schema .= "<DESCRIPTION>" . htmlspecialchars($details["categories_description"]) . "</DESCRIPTION>" . "\n";
                    $schema .= "<META_TITLE>" . htmlspecialchars($details["categories_meta_title"]) . "</META_TITLE>" . "\n";
                    $schema .= "<META_DESCRIPTION>" . htmlspecialchars($details["categories_description_meta"]) . "</META_DESCRIPTION>" . "\n";
                    $schema .= "<META_KEYWORDS>" . htmlspecialchars($details["categories_keywords_meta"]) . "</META_KEYWORDS>" . "\n";
                    $schema .= "</CATEGORIES_DESCRIPTION>\n";

                    $detail_result->MoveNext();
                  }
         
                  // Produkte in dieser Categorie auflisten
                  $prod2cat_query = "SELECT categories_id, products_id 
                                     FROM " . $oosDBTable['products_to_categories'] . " 
                                     WHERE categories_id = '" . $cat['categories_id'] . "'";
                  $prod2cat_result = $db->Execute($prod2cat_query);
                                        
                  while ($prod2cat = $prod2cat_result->fields) {
                    $schema .="<PRODUCTS ID='" . $prod2cat["products_id"] ."'></PRODUCTS>" . "\n";

                    $prod2cat_result->MoveNext();
                  }
           
                  $schema .= '</CATEGORIES_DATA>' . "\n";
                  echo $schema;

                  $cat_result->MoveNext();
                }
                $schema = '</CATEGORIES>' . "\n";
        
                echo $schema;
                exit;

//-----------------------------------------------------------------------------         
             case 'manufacturers_export':
//----------------------------------------------------------------------------- 
               oosSetTimeLimit(0);

               $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                         '<MANUFACTURERS>' . "\n";
                  
               echo $schema;
                  
               $cat_query = "SELECT manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified 
                             FROM " . $oosDBTable['manufacturers'] . " 
                             ORDER BY manufacturers_id";
               $cat_result = $db->Execute($cat_query);

               while ($cat = $cat_result->fields) {
                 $schema  = '<MANUFACTURERS_DATA>' . "\n" .
                            '<ID>' . $cat['manufacturers_id'] . '</ID>' . "\n" .
                            '<NAME>' . htmlspecialchars($cat['manufacturers_name']) . '</NAME>' . "\n" .
                            '<IMAGE>' . htmlspecialchars($cat['manufacturers_image']) . '</IMAGE>' . "\n" .
                            '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                            '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";
                     
                 $sql = "SELECT mi.manufacturers_id, mi.manufacturers_language, mi.manufacturers_url, url_clicked,
                                date_last_click, l.languages_id, l.name as lang_name
                         FROM " .  $oosDBTable['manufacturers_info'] . " mi,
                              " . $oosDBTable['languages'] . " l
                         WHERE mi.manufacturers_id= " . $cat['manufacturers_id'] . " 
                         AND l.iso_639_2 = mi.manufacturers_language";
                 $detai_result = $db->Execute($sql);

                 while ($details = $detai_result->fields) {
                   $schema .= "<MANUFACTURERS_DESCRIPTION ID='" . $details["languages_id"] ."' CODE='" . $details["manufacturers_language"] . "' NAME='" . $details["lang_name"] . "'>\n";
                   $schema .= "<URL>" . htmlspecialchars($details["manufacturers_url"]) . "</URL>" . "\n";
                   $schema .= "<URL_CLICK>" . $details["url_clicked"] . "</URL_CLICK>" . "\n";
                   $schema .= "<DATE_LAST_CLICK>" . $details["date_last_click"] . "</DATE_LAST_CLICK>" . "\n";
                   $schema .= "</MANUFACTURERS_DESCRIPTION>\n";

                   $detai_result->MoveNext();
                 }
          
                 $schema .= '</MANUFACTURERS_DATA>' . "\n";
                 echo $schema;

                 $cat_result->MoveNext();
               }
               $schema = '</MANUFACTURERS>' . "\n";
        
               echo $schema;
               exit;

//----------------------------------------------------------------------------- 
             case 'orders_export':
// ----------------------------------------------------------------------------------------
               $order_from = oosDBPrepareInput($_GET['order_from']);
               $order_to = oosDBPrepareInput($_GET['order_to']);
               $order_status = oosDBPrepareInput($_GET['order_status']);
        
               oosSetTimeLimit(0);
               $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                         '<ORDER>' . "\n";
        
               echo $schema;
        
               $orders_query = "
          SELECT
            *
          FROM 
            " . $oosDBTable['orders'] . "
          WHERE
            orders_id >= '" . oosDBInput($order_from) . "'";
            
               if (!isset($order_status) && !isset($order_from)) {
                 $order_status = 1;
                 $orders_query .= " AND orders_status = " . $order_status;
               }
        
               $orders_result = $db->Execute($orders_query);
        
               while ($orders = $orders_result->fields) {
          
                 // Geburtsdatum laden
                 $cust_query = "SELECT customers_dob, customers_gender
                                FROM " . $oosDBTable['customers'] . "
                                WHERE customers_id=" . $orders['customers_id'];
                 $cust_result = $db->Execute ($cust_query);
          
                 if (tep_db_num_rows($cust_result) >0) {
                   $cust_data = $cust_result->fields;
                   $cust_dob = $cust_data['customers_dob'];
                   $cust_gender = $cust_data['customers_gender'];
                 } else {
                   $cust_dob = '';
                   $cust_gender = '';
                 }
          
          $schema  = '<ORDER_INFO>' . "\n" .
                     '<ORDER_HEADER>' . "\n" .
                     '<ORDER_ID>' . $orders['orders_id'] . '</ORDER_ID>' . "\n" .
                     '<CUSTOMER_ID>' . $orders['customers_id'] . '</CUSTOMER_ID>' . "\n" .
                     '<ORDER_DATE>' . $orders['date_purchased'] . '</ORDER_DATE>' . "\n" .
                     '<ORDER_STATUS>' . $orders['orders_status'] . '</ORDER_STATUS>' . "\n" .
                     '<ORDER_CURRENCY>' . htmlspecialchars($orders['currency']) . '</ORDER_CURRENCY>' . "\n" .
                     '<ORDER_CURRENCY_VALUE>' . $orders['currency_value'] . '</ORDER_CURRENCY_VALUE>' . "\n" .
                     '</ORDER_HEADER>' . "\n" .
                     '<BILLING_ADDRESS>' . "\n" .
                     '<COMPANY>' . htmlspecialchars($orders['billing_company']) . '</COMPANY>' . "\n" .
                     '<FIRSTNAME>' . htmlspecialchars($orders['billing_firstname']) . '</FIRSTNAME>' . "\n" .
                     '<LASTNAME>' . htmlspecialchars($orders['billing_lastname']) . '</LASTNAME>' . "\n" .
                     '<STREET>' . htmlspecialchars($orders['billing_street_address']) . '</STREET>' . "\n" .
                     '<POSTCODE>' . htmlspecialchars($orders['billing_postcode']) . '</POSTCODE>' . "\n" .
                     '<CITY>' . htmlspecialchars($orders['billing_city']) . '</CITY>' . "\n" .
                     '<SUBURB>' . htmlspecialchars($orders['billing_suburb']) . '</SUBURB>' . "\n" .
                     '<STATE>' . htmlspecialchars($orders['billing_state']) . '</STATE>' . "\n" .
                     '<COUNTRY>' . htmlspecialchars($orders['billing_country_iso_code_2']) . '</COUNTRY>' . "\n" .
                     '<TELEPHONE>' . htmlspecialchars($orders['customers_telephone']) . '</TELEPHONE>' . "\n" .
                     '<EMAIL>' . htmlspecialchars($orders['customers_email_address']) . '</EMAIL>' . "\n" .
                     '<BIRTHDAY>' . htmlspecialchars($cust_dob) . '</BIRTHDAY>' . "\n" .
                     '<GENDER>' . htmlspecialchars($cust_gender) . '</GENDER>' . "\n" .
                     '</BILLING_ADDRESS>' . "\n" .
                     '<DELIVERY_ADDRESS>' . "\n" .
                     '<COMPANY>' . htmlspecialchars($orders['delivery_company']) . '</COMPANY>' . "\n" .
                     '<FIRSTNAME>' . htmlspecialchars($orders['delivery_firstname']) . '</FIRSTNAME>' . "\n" .
                     '<LASTNAME>' . htmlspecialchars($orders['delivery_lastname']) . '</LASTNAME>' . "\n" .
                     '<STREET>' . htmlspecialchars($orders['delivery_street_address']) . '</STREET>' . "\n" .
                     '<POSTCODE>' . htmlspecialchars($orders['delivery_postcode']) . '</POSTCODE>' . "\n" .
                     '<CITY>' . htmlspecialchars($orders['delivery_city']) . '</CITY>' . "\n" .
                     '<SUBURB>' . htmlspecialchars($orders['delivery_suburb']) . '</SUBURB>' . "\n" .
                     '<STATE>' . htmlspecialchars($orders['delivery_state']) . '</STATE>' . "\n" .
                     '<COUNTRY>' . htmlspecialchars($orders['delivery_country_iso_code_2']) . '</COUNTRY>' . "\n" .
                     '</DELIVERY_ADDRESS>' . "\n" .
                     '<PAYMENT>' . "\n" . 
                     '<PAYMENT_METHOD>' . htmlspecialchars($orders['payment_method']) . '</PAYMENT_METHOD>'  . "\n" .
                     '<PAYMENT_CLASS>' . htmlspecialchars($orders['payment_class']) . '</PAYMENT_CLASS>'  . "\n";
          
          switch ($orders['payment_class']) {
            case 'banktransfer':
              // Bankverbindung laden, wenn aktiv
              $bank_name = '';
              $bank_blz  = '';
              $bank_kto  = '';
              $bank_inh  = '';
              $bank_stat = -1;
              
              $bank_query = "
                SELECT
                  *
                FROM " . $oosDBTable['banktransfer'] . "
                WHERE orders_id = " . $orders['orders_id'];
                  
              $bank_result = $db->Execute($bank_query);
              if ($bankdata = $bank_result->fields) {
                $bank_name = $bankdata['banktransfer_bankname'];
                $bank_blz  = $bankdata['banktransfer_blz'];
                $bank_kto  = $bankdata['banktransfer_number'];
                $bank_inh  = $bankdata['banktransfer_owner'];
                $bank_stat = $bankdata['banktransfer_status'];
              }
              $schema .= '<PAYMENT_BANKTRANS_BNAME>' . htmlspecialchars($bank_name) . '</PAYMENT_BANKTRANS_BNAME>' . "\n" .
                         '<PAYMENT_BANKTRANS_BLZ>' . htmlspecialchars($bank_blz) . '</PAYMENT_BANKTRANS_BLZ>' . "\n" .
                         '<PAYMENT_BANKTRANS_NUMBER>' . htmlspecialchars($bank_kto) . '</PAYMENT_BANKTRANS_NUMBER>' . "\n" .
                         '<PAYMENT_BANKTRANS_OWNER>' . htmlspecialchars($bank_inh) . '</PAYMENT_BANKTRANS_OWNER>' . "\n" .
                         '<PAYMENT_BANKTRANS_STATUS>' . htmlspecialchars($bank_stat) . '</PAYMENT_BANKTRANS_STATUS>' . "\n";
              break;
          }   
          
          $schema .= '</PAYMENT>' . "\n" . 
                     '<SHIPPING>' . "\n" . 
                     '<SHIPPING_METHOD>' . htmlspecialchars($orders['shipping_method']) . '</SHIPPING_METHOD>'  . "\n" .
                     '<SHIPPING_CLASS>' . htmlspecialchars($orders['shipping_class']) . '</SHIPPING_CLASS>'  . "\n" .
                     '</SHIPPING>' . "\n" .                      
                     '<ORDER_PRODUCTS>' . "\n";
                     
          $products_query = "
            SELECT
              orders_products_id,
              products_id,
              products_model,
              products_name,
              final_price,
              products_tax,
              products_quantity
            FROM 
              " . $oosDBTable['orders_products'] . "
            WHERE
              orders_id = '" . $orders['orders_id'] . "'";
              
          $products_result = $db->Execute($products_query);
          
          while ($products = tep_db_fetch_array($products_result)) {
          
            $schema .= '<PRODUCT>' . "\n" .
                       '<PRODUCTS_ID>' . $products['products_id'] . '</PRODUCTS_ID>' . "\n" .
                       '<PRODUCTS_QUANTITY>' . $products['products_quantity'] . '</PRODUCTS_QUANTITY>' . "\n" .
                       '<PRODUCTS_MODEL>' . htmlspecialchars($products['products_model']) . '</PRODUCTS_MODEL>' . "\n" .
                       '<PRODUCTS_NAME>' . htmlspecialchars($products['products_name']) . '</PRODUCTS_NAME>' . "\n" .
                       '<PRODUCTS_PRICE>' . $products['final_price'] . '</PRODUCTS_PRICE>' . "\n" .
                       '<PRODUCTS_TAX>' . $products['products_tax'] . '</PRODUCTS_TAX>' . "\n";
                       
            
            $attributes_query = "
              SELECT
                products_options,
                products_options_values,
                options_values_price,
                price_prefix
              FROM 
                " . $oosDBTable['orders_products_attributes'] . " 
              WHERE
                orders_id = '" .$orders['orders_id'] . "' AND 
                orders_products_id = '" . $products['orders_products_id'] . "'";
                
            $attributes_result = $db->Execute($attributes_query);
            
            
            if (tep_db_num_rows( $attributes_result ) > 0) 
            {
              while ($attributes = $attributes_result->fields) {
                $schema .= '<OPTION>' . "\n" .
                           '<PRODUCTS_OPTIONS>' .  htmlspecialchars($attributes['products_options']) . '</PRODUCTS_OPTIONS>' . "\n" . 
                           '<PRODUCTS_OPTIONS_VALUES>' .  htmlspecialchars($attributes['products_options_values']) . '</PRODUCTS_OPTIONS_VALUES>' . "\n" .
                           '<PRODUCTS_OPTIONS_PRICE>' .  $attributes['price_prefix'] . ' ' . $attributes['options_values_price'] . '</PRODUCTS_OPTIONS_PRICE>' . "\n" .
                           '</OPTION>' . "\n";
                $attributes_result->MoveNext();
              }
            }            
            $schema .=  '</PRODUCT>' . "\n";

          }
          
          $schema .= '</ORDER_PRODUCTS>' . "\n";                     
          $schema .= '<ORDER_TOTAL>' . "\n";
          
          $totals_query = "
            SELECT 
              title,
              value,
              class,
              sort_order
            FROM
              " . $oosDBTable['orders_total'] . "
            WHERE
              orders_id = '" . $orders['orders_id'] . "'
            ORDER BY
              sort_order";
              
          $totals_result = $db->Execute($totals_query);
          
          while ($totals = $totals_result->fields) {
          
            $total_prefix = "";
            $total_tax  = "";
            $total_prefix = $order_total_class[$totals['class']]['prefix'];
            $total_tax = $order_total_class[$totals['class']]['tax'];
            
            $schema .= '<TOTAL>' . "\n" .
                       '<TOTAL_TITLE>' . htmlspecialchars($totals['title']) . '</TOTAL_TITLE>' . "\n" .
                       '<TOTAL_VALUE>' . htmlspecialchars($totals['value']) . '</TOTAL_VALUE>' . "\n" .
                       '<TOTAL_CLASS>' . htmlspecialchars($totals['class']) . '</TOTAL_CLASS>' . "\n" .
                       '<TOTAL_SORT_ORDER>' . htmlspecialchars($totals['sort_order']) . '</TOTAL_SORT_ORDER>' . "\n" .
                       '<TOTAL_PREFIX>' . htmlspecialchars($total_prefix) . '</TOTAL_PREFIX>' . "\n" .
                       '<TOTAL_TAX>' . htmlspecialchars($total_tax) . '</TOTAL_TAX>' . "\n" . 
                       '</TOTAL>' . "\n";
            $totals_result->MoveNext();            
          }
          
          $schema .= '</ORDER_TOTAL>' . "\n";
          
          $comments_query = "
            SELECT
              comments
            FROM 
              " . $oosDBTable['orders_status_history'] . "
            WHERE
              orders_id = '" . $orders['orders_id'] . "' AND
              orders_status_id = '" . $orders['orders_status'] . "' ";
              
          $comments_result = $db->Execute ($comments_query);
          
          if ($comments = $comments_result->fields) {
            $schema .=  '<ORDER_COMMENTS>' . htmlspecialchars($comments['comments']) . '</ORDER_COMMENTS>' . "\n";
            $comments_result->MoveNext();
          }
          
          $schema .= '</ORDER_INFO>' . "\n\n";
          echo $schema;
        }
        
        $schema = '</ORDER>' . "\n\n";
        
        echo $schema;
        exit;


//----------------------------------------------------------------------------- 
             case 'products_export':
//-----------------------------------------------------------------------------       
               oosSetTimeLimit(0);

               $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<PRODUCTS>' . "\n";
               echo $schema;                
 
               $sql = "select products_id, products_quantity, products_model, products_image, products_price, " .
                      "products_date_added, products_last_modified, products_date_available, products_weight, " .
                     "products_status, products_tax_class_id, manufacturers_id, products_ordered from " . TABLE_PRODUCTS;
         
               $from = oosDBPrepareInput($_GET['products_from']);
               $anz  = oosDBPrepareInput($_GET['products_count']);
        
               if (isset($from)) {
                 if (!isset($anz)) {
                   $anz=1000;
                 }
                 $sql .= " limit " . $from . "," . $anz;
               }
                  
               $orders_query = $db->Execute($sql);
               while ($products = $orders_query->fields) {
        
          $schema  = '<PRODUCT_INFO>' . "\n" .
                     '<PRODUCT_DATA>' . "\n" .
                     '<PRODUCT_ID>' . $products['products_id'] . '</PRODUCT_ID>' . "\n" .
                     '<PRODUCT_QUANTITY>' . $products['products_quantity'] . '</PRODUCT_QUANTITY>' . "\n" .
                     '<PRODUCT_MODEL>' . htmlspecialchars($products['products_model']) . '</PRODUCT_MODEL>' . "\n" .
                     '<PRODUCT_IMAGE>' . htmlspecialchars($products['products_image']) . '</PRODUCT_IMAGE>' . "\n";
          
                     
          $schema .= '<PRODUCT_PRICE>' . $products['products_price'] . '</PRODUCT_PRICE>' . "\n" .
                     '<PRODUCT_WEIGHT>' . $products['products_weight'] . '</PRODUCT_WEIGHT>' . "\n" .
                     '<PRODUCT_STATUS>' . $products['products_status'] . '</PRODUCT_STATUS>' . "\n" .
                     '<PRODUCT_TAX_CLASS_ID>' . $products['products_tax_class_id'] . '</PRODUCT_TAX_CLASS_ID>' . "\n"  .
                     '<MANUFACTURERS_ID>' . $products['manufacturers_id'] . '</MANUFACTURERS_ID>' . "\n" .
                     
                     '<PRODUCT_DATE_ADDED>' . $products['products_date_added'] . '</PRODUCT_DATE_ADDED>' . "\n" .
                     '<PRODUCT_LAST_MODIFIED>' . $products['products_last_modified'] . '</PRODUCT_LAST_MODIFIED>' . "\n" .
                     '<PRODUCT_DATE_AVAILABLE>' . $products['products_date_available'] . '</PRODUCT_DATE_AVAILABLE>' . "\n" .
                     
                     '<PRODUCTS_ORDERED>' . $products['products_ordered'] . '</PRODUCTS_ORDERED>' . "\n" ;
                     
          
          $detail_query = "
            SELECT
              products_id,
              language_id,
              products_name,
              pd.products_description,
              products_url,
              name as language_name,
              code as language_code
            FROM
              " . $oosDBTable['products_description'] . " pd, 
              " . $oosDBTable['languages'] ." l
            WHERE
              pd.language_id = l.languages_id AND
              pd.products_id=" . $products['products_id'];
              
          
          $detail_result = $db->Execute($detail_query);

                 while ($details = $detail_result->fields) {
          
                   $schema .= "<PRODUCT_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["language_code"] . "' NAME='" . $details["language_name"] . "'>\n";
                       
                   if ($details["products_name"] !='Array') {
                     $schema .= "<NAME>" . htmlspecialchars($details["products_name"]) . "</NAME>" . "\n" ;
                   }
            
                   $schema .=  "<URL>" . htmlspecialchars($details["products_url"]) . "</URL>" . "\n" ;
            
                   $prod_details = $details["products_description"];
            
                   if ($prod_details != 'Array') {
                     $schema .=  "<DESCRIPTION>" . htmlspecialchars($prod_details) . "</DESCRIPTION>" . "\n";
                   }
            
                   $schema .= "</PRODUCT_DESCRIPTION>\n";
                 }
                 $schema .= '</PRODUCT_DATA>' . "\n" .
                            '</PRODUCT_INFO>' . "\n";
                 echo $schema;
               }
        
               $schema = '</PRODUCTS>' . "\n\n";
               echo $schema;
               exit;
//----------------------------------------------------------------------------- 
             case 'customers_export':
//----------------------------------------------------------------------------- 
               oosSetTimeLimit(0);

               $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
               '<CUSTOMERS>' . "\n";

               echo $schema;

      
               $from = oosDBPrepareInput($_GET['customers_from']);
               $anz  = oosDBPrepareInput($_GET['customers_count']);

    
    $address_query = "
      SELECT
        c.customers_gender,
        c.customers_id,
        c.customers_dob,
        c.customers_email_address,
        c.customers_telephone,
        c.customers_fax, 
        ci.customers_info_date_account_created, 
        a.entry_firstname,
        a.entry_lastname,
        a.entry_company,
        a.entry_street_address,
        a.entry_city,
        a.entry_postcode, 
	a.entry_suburb,
	a.entry_state,
        co.countries_iso_code_2 
      FROM
        ".$oosDBTable['customers']. " c, 
        ".$oosDBTable['customers_info']. " ci, 
        ".$oosDBTable['address_book'] . " a , 
        ".$oosDBTable['countries']." co 
      WHERE
        c.customers_id = ci.customers_info_id AND 
        c.customers_id = a.customers_id AND
        c.customers_default_address_id = a.address_book_id AND 
        a.entry_country_id  = co.countries_id";
        
               if (isset($from)) {
                 if (!isset($anz)) {
                   $anz = 1000;
                 }
                 $address_query.= " LIMIT " . $from . "," . $anz;
               }
    
               $address_result = $db->Execute($address_query);

               while ($address = $address_result->fields) {
      
                 $schema = '<CUSTOMERS_DATA>' . "\n" .
                           '<CUSTOMERS_ID>' . htmlspecialchars($address['customers_id']) . '</CUSTOMERS_ID>' . "\n" .
                           '<GENDER>' . htmlspecialchars($address['customers_gender']) . '</GENDER>' . "\n" .
                           '<COMPANY>' . htmlspecialchars($address['entry_company']) . '</COMPANY>' . "\n" .
                           '<FIRSTNAME>' . htmlspecialchars($address['entry_firstname']) . '</FIRSTNAME>' . "\n" .
                           '<LASTNAME>' . htmlspecialchars($address['entry_lastname']) . '</LASTNAME>' . "\n" .
                           '<STREET>' . htmlspecialchars($address['entry_street_address']) . '</STREET>' . "\n" .
                           '<POSTCODE>' . htmlspecialchars($address['entry_postcode']) . '</POSTCODE>' . "\n" .
                           '<CITY>' . htmlspecialchars($address['entry_city']) . '</CITY>' . "\n" .
                           '<SUBURB>' . htmlspecialchars($address['entry_suburb']) . '</SUBURB>' . "\n" .
                           '<STATE>' . htmlspecialchars($address['entry_state']) . '</STATE>' . "\n" .
                           '<COUNTRY>' . htmlspecialchars($address['countries_iso_code_2']) . '</COUNTRY>' . "\n" .
                           '<TELEPHONE>' . htmlspecialchars($address['customers_telephone']) . '</TELEPHONE>' . "\n" .
                           '<FAX>' . htmlspecialchars($address['customers_fax']) . '</FAX>' . "\n" .
                           '<EMAIL>' . htmlspecialchars($address['customers_email_address']) . '</EMAIL>' . "\n" .
                           '<BIRTHDAY>' . htmlspecialchars($address['customers_dob']) . '</BIRTHDAY>' . "\n" .
                           '<DATE_ACCOUNT_CREATED>' . htmlspecialchars($address['customers_info_date_account_created']) . '</DATE_ACCOUNT_CREATED>' . "\n" .
                           '</CUSTOMERS_DATA>' . "\n";
                 echo $schema;
                 $address_result->MoveNext();
               }
    
               $schema = '</CUSTOMERS>' . "\n\n";
               echo $schema;
               exit;

//----------------------------------------------------------------------------- 
             case 'customers_newsletter_export':
//----------------------------------------------------------------------------- 
              oosSetTimeLimit(0);

              $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                        '<CUSTOMERS>' . "\n".

              $from = oosDBPrepareInput($_GET['customers_from']);
              $anz  = oosDBPrepareInput($_GET['customers_count']);
    
              $address_query = "SELECT customers_id, customers_gender, customers_firstname, customers_lastname, customers_email_address
                                FROM " . $oosDBTable['customers']. " 
                                WHERE customers_newsletter = 1";

              if (isset($from)) {
                if (!isset($anz)) {
                  $anz = 1000;
                }
                $address_query.= " LIMIT " . $from . "," . $anz;
              }
    
    $address_result = $db->Execute($address_query);
    
              while ($address = tep_db_fetch_array($address_result)) {
                $schema .= '<CUSTOMERS_DATA>' . "\n";
                $schema .= '<CUSTOMERS_ID>' . $address['customers_id'] . '</CUSTOMERS_ID>' . "\n";
                $schema .= '<CUSTOMERS_GENDER>' . $address['customers_gender'] . '</CUSTOMERS_GENDER>' . "\n";
                $schema .= '<CUSTOMERS_FIRSTNAME>' . $address['customers_firstname'] . '</CUSTOMERS_FIRSTNAME>' . "\n";
                $schema .= '<CUSTOMERS_LASTNAME>' . $address['customers_lastname'] . '</CUSTOMERS_LASTNAME>' . "\n";
                $schema .= '<CUSTOMERS_EMAIL_ADDRESS>' . $address['customers_email_address'] . '</CUSTOMERS_EMAIL_ADDRESS>' . "\n";
                $schema .= '</CUSTOMERS_DATA>' . "\n";
              }
    
              $schema .= '</CUSTOMERS>' . "\n\n";
              echo $schema;
              exit;

//----------------------------------------------------------------------------- 
             case 'version':
//----------------------------------------------------------------------------- 
              // Ausgabe Scriptversion
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
               exit;        
             } // switch
           } else {

             header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
             header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
             header ("Pragma: no-cache"); // HTTP/1.0
             header ("Content-type: text/xml");

             $error = (isset($_GET['error']) ? $_GET['code'] : 'NO PASSWORD OR USERNAME');
             $code = (isset($_GET['code']) ? $_GET['code'] : '100');

             $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                       '<STATUS>' . "\n" .
                       '<STATUS_DATA>' . "\n" .
                       '<CODE>' . $code . '</CODE>' . "\n" .
                       '<MESSAGE>' . $error . '</MESSAGE>' . "\n" .
                       '</STATUS_DATA>' . "\n" .
                       '</STATUS>';

             echo $schema;

            } 
          }
        }
      }
    }
  
?>
