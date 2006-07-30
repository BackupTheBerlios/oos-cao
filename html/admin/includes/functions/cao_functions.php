<?php
/* ----------------------------------------------------------------------
   $Id: cao_functions.php,v 1.10 2006/07/30 16:29:11 r23 Exp $

   Based on:

   File: cao_xtc_functions.php
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

/*******************************************************************************************
*                                                                                          *
*  CAO-Faktura fr Windows Version 1.2 (http://www.cao-wawi.de)                            *
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
* Eine Entfernung oder Veraenderung dieses Dateiheaders ist nicht zulaessig!            *
* Wenn Sie diese Datei veraendern dann fuegen Sie ihre eigenen Copyrightmeldungen          *
* am Ende diese Headers an                                                                 *
*                                                                                          *
********************************************************************************************
*                                                                                          *
*  Programm     : CAO-Faktura                                                              *
*  Modul        : cao_xtc.php                                                              *
*  Stand        : 07.11.2005                                                               *
*  Version      : 1.51                                                                     *
*  Beschreibung : Script zum Datenaustausch CAO-Faktura <--> xtCommerce-Shop               *
*                                                                                          *
*  based on:                                                                               *
* (c) 2000 - 2001 The Exchange Project                                                     *
* (c) 2001 - 2003 osCommerce, Open Source E-Commerce Solutions                             *
* (c) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Pl�kers                                      *
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
*  - 26.09.2005 JP Funktionen aus xml_export.php und cao_import.php erstellt               *
*  - 04.10.2005 JP/KL Version 1.44 released, Scripte komplett ueberarbeitet                *
*  - 06.10.2005 KL/JP Bugfix bei xtc_set_time_limit                                        *
*  - 17.10.2005 JP Bugfixes fuer XTC 304                                                   *
*  - 21.10.2005 KL/JP Bugfix fuer XTC 2.x Spalte products_Ean angelegt                     *
*  - 23.10.2005 hartleib Fehlende $LangID in OrderUpdate hinzugefuegt                      *
*  - 02.11.2005 JP Fehler bei doppelter Funktion xtDBquery gefixt                          *
*  - 07.11.2005 JP Export Orders/VAT_ID implementiert                                      *
*******************************************************************************************/


  function SendScriptVersion () {
    global $version_nr, $version_datum;

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
  }



  function print_xml_status ($code, $action, $msg, $mode, $item, $value) {

    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<STATUS>' . "\n" .
              '<STATUS_DATA>' . "\n" .
              '<CODE>' . $code . '</CODE>' . "\n" .
              '<ACTION>' . $action . '</ACTION>' . "\n" .
              '<MESSAGE>' . $msg . '</MESSAGE>' . "\n";

    if (strlen($mode)>0) {
      $schema .= '<MODE>' . $mode . '</MODE>' . "\n";
    }

    if (strlen($item)>0) {
      $schema .= '<' . $item . '>' . $value . '</' . $item . '>' . "\n";
    }
    $schema .= '</STATUS_DATA>' . "\n" .
               '</STATUS>' . "\n\n";

    echo $schema;

    return;
  }

  function SendCategories () {

    if (!get_cfg_var('safe_mode')) {
      @set_time_limit(0);
    }

    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<CATEGORIES>' . "\n";
    echo $schema;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $query = "SELECT categories_id, categories_image, parent_id, sort_order, date_added, last_modified
              FROM " . $oosDBTable['categories'] . "
              ORDER BY parent_id, categories_id";
    $result = $db->Execute($query);

    while ($cat = $result->fields) {
      $schema  = '<CATEGORIES_DATA>' . "\n" .
                 '<ID>' . $cat['categories_id'] . '</ID>' . "\n" .
                 '<PARENT_ID>' . $cat['parent_id'] . '</PARENT_ID>' . "\n" .
                 '<IMAGE_URL>' . htmlspecialchars($cat['categories_image']) . '</IMAGE_URL>' . "\n" .
                 '<SORT_ORDER>' . $cat['sort_order'] . '</SORT_ORDER>' . "\n" .
                 '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                 '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";

      $detail_query = "SELECT cd.categories_id, l.language_id, cd.categories_name, cd.categories_heading_title,
                              cd.categories_description, cd.categories_meta_title, cd.categories_meta_description,
                              cd.categories_meta_keywords, l.code as lang_code, l.name as lang_name
                       FROM " . $oosDBTable['categories_description'] . " cd,
                            " . $oosDBTable['languages'] . " l
                      WHERE cd.categories_id = " . $cat['categories_id'] . "
                        AND l.languages_id = cd.language_id";
      $detail_result = $db->Execute($detail_query);

      while ($details = $detail_result->fields) {
        $schema .= "<CATEGORIES_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["lang_code"] . "' NAME='" . $details["lang_name"] . "'>\n";
        $schema .= "<NAME>" . htmlspecialchars($details["categories_name"]) . "</NAME>" . "\n";
        $schema .= "<HEADING_TITLE>" . htmlspecialchars($details["categories_heading_title"]) . "</HEADING_TITLE>" . "\n";
        $schema .= "<DESCRIPTION>" . htmlspecialchars($details["categories_description"]) . "</DESCRIPTION>" . "\n";
        $schema .= "<META_TITLE>" . htmlspecialchars($details["categories_meta_title"]) . "</META_TITLE>" . "\n";
        $schema .= "<META_DESCRIPTION>" . htmlspecialchars($details["categories_meta_description"]) . "</META_DESCRIPTION>" . "\n";
        $schema .= "<META_KEYWORDS>" . htmlspecialchars($details["categories_meta_keywords"]) . "</META_KEYWORDS>" . "\n";
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

      $result->MoveNext();
    }
    $schema = '</CATEGORIES>' . "\n";
    echo $schema;
  }


  function SendManufacturers () {

    if (!get_cfg_var('safe_mode')) {
      @set_time_limit(0);
    }


    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<MANUFACTURERS>' . "\n";
    echo $schema;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();


    $query = "SELECT manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified
              FROM " . $oosDBTable['manufacturers'] . "
              ORDER BY manufacturers_id";
    $result = $db->Execute($query);

    while ($cat = $result->fields) {
      $schema  = '<MANUFACTURERS_DATA>' . "\n" .
                 '<ID>' . $cat['manufacturers_id'] . '</ID>' . "\n" .
                 '<NAME>' . htmlspecialchars($cat['manufacturers_name']) . '</NAME>' . "\n" .
                 '<IMAGE>' . htmlspecialchars($cat['manufacturers_image']) . '</IMAGE>' . "\n" .
                 '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                 '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";

      $detail_query = "SELECT mi.manufacturers_id, mi.languages_id, mi.manufacturers_url, mi.url_clicked,
                              mi.date_last_click, l.code as lang_code, l.name as lang_name
                       FROM " . $oosDBTable['manufacturers_info']. " mi,
                            " . $oosDBTable['languages'] . " l
                       WHERE mi.manufacturers_id= " . $cat['manufacturers_id'] . "
                         AND l.languages_id=mi.languages_id";
      $detail_result = $db->Execute($detail_query);
      while ($details = $detail_result->fields) {
        $schema .= "<MANUFACTURERS_DESCRIPTION ID='" . $details["languages_id"] ."' CODE='" . $details["lang_code"] . "' NAME='" . $details["lang_name"] . "'>\n";
        $schema .= "<URL>" . htmlspecialchars($details["manufacturers_url"]) . "</URL>" . "\n" ;
        $schema .= "<URL_CLICK>" . $details["url_clicked"] . "</URL_CLICK>" . "\n" ;
        $schema .= "<DATE_LAST_CLICK>" . $details["date_last_click"] . "</DATE_LAST_CLICK>" . "\n" ;
        $schema .= "</MANUFACTURERS_DESCRIPTION>\n";

        $detail_result->MoveNext();
      }
      $schema .= '</MANUFACTURERS_DATA>' . "\n";
      echo $schema;

      $result->MoveNext();
    }
    $schema = '</MANUFACTURERS>' . "\n";
    echo $schema;
  }


  function SendOrders () {
    global $order_total_class;

    $order_from = oosDBPrepareInput($_GET['order_from']);
    $order_to = oosDBPrepareInput($_GET['order_to']);
    $order_status = oosDBPrepareInput($_GET['order_status']);

    if (!get_cfg_var('safe_mode')) {
      @set_time_limit(0);
    }


    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<ORDER>' . "\n";
    echo $schema;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $sql = "SELECT * FROM " . $oosDBTable['orders'] . " WHERE orders_id >= '" . oosDBInput($order_from) . "'";
    if (!isset($order_status) && !isset($order_from)) {
      $order_status = 1;
      $sql .= "AND orders_status = " . $order_status;
    }
    if ($order_status!='') {
      $sql .= " AND orders_status = " . $order_status;
    }
    $result = $db->Execute($query);

    while ($orders = xtc_db_fetch_array($orders_query->fields) {
      // Geburtsdatum laden
      $cust_sql = "SELECT customers_gender, customers_dob
                   FROM " . $oosDBTable['customers'] . " 
                   WHERE customers_id = " . $orders['customers_id'];
      $cust_result = $db->Execute($cust_sql);
      $cust_data = $cust_result->fields;

      $cust_dob = $cust_data['customers_dob'];
      $cust_gender = $cust_data['customers_gender'];


      if ($orders['billing_company'] == '') $orders['billing_company'] = $orders['delivery_company'];
      if ($orders['billing_name'] == '')  $orders['billing_name'] = $orders['delivery_name'];
      if ($orders['billing_street_address'] == '') $orders['billing_street_address'] = $orders['delivery_street_address'];
      if ($orders['billing_postcode'] == '')  $orders['billing_postcode'] = $orders['delivery_postcode'];
      if ($orders['billing_city'] == '')  $orders['billing_city'] = $orders['delivery_city'];
      if ($orders['billing_suburb'] == '') $orders['billing_suburb'] = $orders['delivery_suburb'];
      if ($orders['billing_state'] == '')  $orders['billing_state'] = $orders['delivery_state'];
      if ($orders['billing_country'] == '')  $orders['billing_country'] = $orders['delivery_country'];

      $schema  = '<ORDER_INFO>' . "\n" .
                 '<ORDER_HEADER>' . "\n" .
                 '<ORDER_ID>' . $orders['orders_id'] . '</ORDER_ID>' . "\n" .
                 '<CUSTOMER_ID>' . $orders['customers_id'] . '</CUSTOMER_ID>' . "\n" .
                 '<CUSTOMER_CID>' . $orders['customers_cid'] . '</CUSTOMER_CID>' . "\n" .
                 '<CUSTOMER_GROUP>' . $orders['customers_status'] . '</CUSTOMER_GROUP>' . "\n" .
                 '<ORDER_DATE>' . $orders['date_purchased'] . '</ORDER_DATE>' . "\n" .
                 '<ORDER_STATUS>' . $orders['orders_status'] . '</ORDER_STATUS>' . "\n" .
                 '<ORDER_IP>' . $orders['customers_ip'] . '</ORDER_IP>' . "\n" .
                 '<ORDER_CURRENCY>' . htmlspecialchars($orders['currency']) . '</ORDER_CURRENCY>' . "\n" .
                 '<ORDER_CURRENCY_VALUE>' . $orders['currency_value'] . '</ORDER_CURRENCY_VALUE>' . "\n" .
                 '</ORDER_HEADER>' . "\n" .
                 '<BILLING_ADDRESS>' . "\n" .
                 '<VAT_ID>' . htmlspecialchars($orders['customers_vat_id']) . '</VAT_ID>' . "\n" . //JP07112005 (Existiert erst ab XTC 3.x)
                 '<COMPANY>' . htmlspecialchars($orders['billing_company']) . '</COMPANY>' . "\n" .
                 '<NAME>' . htmlspecialchars($orders['billing_name']) . '</NAME>' . "\n" .
                 '<FIRSTNAME>' . htmlspecialchars($orders['billing_firstname']) . '</FIRSTNAME>' . "\n" .
                 '<LASTNAME>' . htmlspecialchars($orders['billing_lastname']) . '</LASTNAME>' . "\n" .
                 '<STREET>' . htmlspecialchars($orders['billing_street_address']) . '</STREET>' . "\n" .
                 '<POSTCODE>' . htmlspecialchars($orders['billing_postcode']) . '</POSTCODE>' . "\n" .
                 '<CITY>' . htmlspecialchars($orders['billing_city']) . '</CITY>' . "\n" .
                 '<SUBURB>' . htmlspecialchars($orders['billing_suburb']) . '</SUBURB>' . "\n" .
                 '<STATE>' . htmlspecialchars($orders['billing_state']) . '</STATE>' . "\n" .
                 '<COUNTRY>' . htmlspecialchars($orders['billing_country_iso_code_2']) . '</COUNTRY>' . "\n" .
                 '<TELEPHONE>' . htmlspecialchars($orders['customers_telephone']) . '</TELEPHONE>' . "\n" . // JAN
                 '<EMAIL>' . htmlspecialchars($orders['customers_email_address']) . '</EMAIL>' . "\n" . // JAN
                 '<BIRTHDAY>' . htmlspecialchars($cust_dob) . '</BIRTHDAY>' . "\n" .
                 '<GENDER>' . htmlspecialchars($cust_gender) . '</GENDER>' . "\n" .
                 '</BILLING_ADDRESS>' . "\n" .
                 '<DELIVERY_ADDRESS>' . "\n" .
                 '<COMPANY>' . htmlspecialchars($orders['delivery_company']) . '</COMPANY>' . "\n" .
                 '<NAME>' . htmlspecialchars($orders['delivery_name']) . '</NAME>' . "\n" .
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

      switch ($orders['payment_class'])  {
        case 'banktransfer':
           // Bankverbindung laden, wenn aktiv
           $bank_name = '';
           $bank_blz  = '';
           $bank_kto  = '';
           $bank_inh  = '';
           $bank_stat = -1;

           $bank_sql = "SELECT * FROM " . $oosDBTable['banktransfer'] " .  WHERE orders_id = " . $orders['orders_id'];
           $bank_query = $db->Execute($bank_sql);
           $result = $db->Execute($query);
           if (($bank_query) && ($bankdata = xtc_db_fetch_array($bank_query))) {
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

      $sql = "SELECT
             orders_products_id,
             allow_tax,
             products_id,
             products_model,
             products_name,
             final_price,
             products_tax,
             products_quantity
            FROM " . 
             $oosDBTable['orders_products'] . "
            WHERE 
             orders_id = '" . $orders['orders_id'] . "'";

      $products_query = $db->Execute($sql);
      while ($products = xtc_db_fetch_array($products_query->fields) {
        if ($products['allow_tax']==1) $products['final_price'] = $products['final_price']/(1+$products['products_tax']*0.01);

        $schema .= '<PRODUCT>' . "\n" .
                   '<PRODUCTS_ID>' . $products['products_id'] . '</PRODUCTS_ID>' . "\n" .
                   '<PRODUCTS_QUANTITY>' . $products['products_quantity'] . '</PRODUCTS_QUANTITY>' . "\n" .
                   '<PRODUCTS_MODEL>' . htmlspecialchars($products['products_model']) . '</PRODUCTS_MODEL>' . "\n" .
                   '<PRODUCTS_NAME>' . htmlspecialchars($products['products_name']) . '</PRODUCTS_NAME>' . "\n" .
                   '<PRODUCTS_PRICE>' . $products['final_price']/$products['products_quantity'] . '</PRODUCTS_PRICE>' . "\n" .
                   '<PRODUCTS_TAX>' . $products['products_tax'] . '</PRODUCTS_TAX>' . "\n".
                   '<PRODUCTS_TAX_FLAG>' . $products['allow_tax'] . '</PRODUCTS_TAX_FLAG>' . "\n";

        $attributes_query = $db->Execute("SELECT products_options, products_options_values, options_values_price, price_prefixFROM " . $oosDBTable['orders_products_attributes'] . " WHERE orders_id = '" .$orders['orders_id'] . "' and orders_products_id = '" . $products['orders_products_id'] . "'");
        $result = $db->Execute($query);
        if (xtc_db_num_rows($attributes_query)) {
          while ($attributes = xtc_db_fetch_array($attributes_query->fields) {
            require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
            $attributes_model =xtc_get_attributes_model($products['products_id'],$attributes['products_options_values']);
            $schema .= '<OPTION>' . "\n" .
                       '<PRODUCTS_OPTIONS>' .  htmlspecialchars($attributes['products_options']) . '</PRODUCTS_OPTIONS>' . "\n" .
                       '<PRODUCTS_OPTIONS_VALUES>' .  htmlspecialchars($attributes['products_options_values']) . '</PRODUCTS_OPTIONS_VALUES>' . "\n" .
                       '<PRODUCTS_OPTIONS_MODEL>'.$attributes_model.'</PRODUCTS_OPTIONS_MODEL>'. "\n".
                       '<PRODUCTS_OPTIONS_PRICE>' .  $attributes['price_prefix'] . ' ' . $attributes['options_values_price'] . '</PRODUCTS_OPTIONS_PRICE>' . "\n" .
                       '</OPTION>' . "\n";
            $result->MoveNext();
          }
        }
        $schema .=  '</PRODUCT>' . "\n";

        $result->MoveNext();
      }
      $schema .= '</ORDER_PRODUCTS>' . "\n";
      $schema .= '<ORDER_TOTAL>' . "\n";

      $totals_query = $db->Execute("SELECT title, value, class, sort_order FROM " . $oosDBTable['orders_total'] . " WHERE orders_id = '" . $orders['orders_id'] . "' ORDER BY sort_order");
      $result = $db->Execute($query);
      while ($totals = xtc_db_fetch_array($totals_query->fields) {
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
        $result->MoveNext();
      }
      $schema .= '</ORDER_TOTAL>' . "\n";

      $sql = "SELECT
             comments
            FROM " .
             $oosDBTable['orders_status_history'] . "
            WHERE
             orders_id = '" . $orders['orders_id'] . "' and
             orders_status_id = '" . $orders['orders_status'] . "' ";

      $comments_query = $db->Execute($sql);
      if ($comments =  xtc_db_fetch_array($comments_query))  {
        $schema .=  '<ORDER_COMMENTS>' . htmlspecialchars($comments['comments']) . '</ORDER_COMMENTS>' . "\n";
      }
      $schema .= '</ORDER_INFO>' . "\n\n";
      echo $schema;
    }
    $schema = '</ORDER>' . "\n\n";
    echo $schema;
  }


  function SendProducts () {
    global $LangID;

    if (!get_cfg_var('safe_mode')) {
      @set_time_limit(0);
    }

    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<PRODUCTS>' . "\n";
    echo $schema;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $sql = "SELECT products_id,products_fsk18, products_quantity, products_model, products_image, products_price, " .
           "products_date_added, products_last_modified, products_date_available, products_weight, " .
           "products_status, products_tax_class_id, manufacturers_id, products_ordered FROM " .  $oosDBTable['products'];
    $from = oosDBPrepareInput($_GET['products_from']);
    $anz  = oosDBPrepareInput($_GET['products_count']);

    if (isset($from)) {
      if (!isset($anz)) $anz=1000;
      $sql .= " limit " . $from . "," . $anz;
    }

    $result = $db->Execute($query);
    $orders_query = $db->Execute($sql);
    while ($products = xtc_db_fetch_array($orders_query->fields) {
      $schema  = '<PRODUCT_INFO>' . "\n" .
                 '<PRODUCT_DATA>' . "\n" .
                 '<PRODUCT_ID>'.$products['products_id'].'</PRODUCT_ID>' . "\n" .
                 '<PRODUCT_DEEPLINK>'. HTTP_SERVER.DIR_WS_CATALOG.$xtc_filename['product_info'].'?products_id='.$products['products_id'].'</PRODUCT_DEEPLINK>' . "\n" .
                 '<PRODUCT_QUANTITY>' . $products['products_quantity'] . '</PRODUCT_QUANTITY>' . "\n" .
                 '<PRODUCT_MODEL>' . htmlspecialchars($products['products_model']) . '</PRODUCT_MODEL>' . "\n" .
                 '<PRODUCT_FSK18>' . htmlspecialchars($products['products_fsk18']) . '</PRODUCT_FSK18>' . "\n" .
                 '<PRODUCT_IMAGE>' . htmlspecialchars($products['products_image']) . '</PRODUCT_IMAGE>' . "\n";

      if ($products['products_image']!='') {
        $schema .= '<PRODUCT_IMAGE_POPUP>'.HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_POPUP_IMAGES.$products['products_image'].'</PRODUCT_IMAGE_POPUP>'. "\n" .
                   '<PRODUCT_IMAGE_SMALL>'.HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_INFO_IMAGES.$products['products_image'].'</PRODUCT_IMAGE_SMALL>'. "\n" .
                   '<PRODUCT_IMAGE_THUMBNAIL>'.HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_THUMBNAIL_IMAGES.$products['products_image'].'</PRODUCT_IMAGE_THUMBNAIL>'. "\n" .
                   '<PRODUCT_IMAGE_ORIGINAL>'.HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$products['products_image'].'</PRODUCT_IMAGE_ORIGINAL>'. "\n";
      }

      $schema .= '<PRODUCT_PRICE>' . $products['products_price'] . '</PRODUCT_PRICE>' . "\n";


      require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');

      if (SWITCH_MWST=='true') {
        // switch IDs
        if ($products['products_tax_class_id']==1) {
          $products['products_tax_class_id'] = 2;
        } else {
          if ($products['products_tax_class_id']==2) {
            $products['products_tax_class_id'] = 1;
          }
        }
      }

      $schema .= '<PRODUCT_WEIGHT>' . $products['products_weight'] . '</PRODUCT_WEIGHT>' . "\n" .
                 '<PRODUCT_STATUS>' . $products['products_status'] . '</PRODUCT_STATUS>' . "\n" .
                 '<PRODUCT_TAX_CLASS_ID>' . $products['products_tax_class_id'] . '</PRODUCT_TAX_CLASS_ID>' . "\n"  .
                 '<PRODUCT_TAX_RATE>' . xtc_get_tax_rate($products['products_tax_class_id']) . '</PRODUCT_TAX_RATE>' . "\n"  .
                 '<MANUFACTURERS_ID>' . $products['manufacturers_id'] . '</MANUFACTURERS_ID>' . "\n" .
                 '<PRODUCT_DATE_ADDED>' . $products['products_date_added'] . '</PRODUCT_DATE_ADDED>' . "\n" .
                 '<PRODUCT_LAST_MODIFIED>' . $products['products_last_modified'] . '</PRODUCT_LAST_MODIFIED>' . "\n" .
                 '<PRODUCT_DATE_AVAILABLE>' . $products['products_date_available'] . '</PRODUCT_DATE_AVAILABLE>' . "\n" .
                 '<PRODUCTS_ORDERED>' . $products['products_ordered'] . '</PRODUCTS_ORDERED>' . "\n" ;


      $detail_query = $db->Execute("SELECT
                                   products_id,
                                   language_id,
                                   products_name, " .  $oosDBTable['products_description'] .
                                   ".products_description,
                                   products_short_description,
                                   products_meta_title,
                                   products_meta_description,
                                   products_meta_keywords,
                                   products_url,
                                   name as language_name, code as language_code " .
                                   "FROM " .  $oosDBTable['products_description'] . ", " . $oosDBTable['languages'] . " " .
                                   "WHERE " .  $oosDBTable['products_description'] . ".language_id=" . $oosDBTable['languages'] . ".languages_id " .
                                   "and " .  $oosDBTable['products_description'] . ".products_id=" . $products['products_id']);

    $result = $db->Execute($query);

      while ($details = xtc_db_fetch_array($detail_query->fields) {
        $schema .= "<PRODUCT_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["language_code"] . "' NAME='" . $details["language_name"] . "'>\n";

        if ($details["products_name"] !='Array') {
          $schema .= "<NAME>" . htmlspecialchars($details["products_name"]) . "</NAME>" . "\n" ;
        }
        $schema .=  "<URL>" . htmlspecialchars($details["products_url"]) . "</URL>" . "\n" ;

        $prod_details = $details["products_description"];
        if ($prod_details != 'Array') {
          $schema .=  "<DESCRIPTION>" . htmlspecialchars($details["products_description"]) . "</DESCRIPTION>" . "\n";
          $schema .=  "<SHORT_DESCRIPTION>" . htmlspecialchars($details["products_short_description"]) . "</SHORT_DESCRIPTION>" . "\n";
          $schema .=  "<META_TITLE>" . htmlspecialchars($details["products_meta_title"]) . "</META_TITLE>" . "\n";
          $schema .=  "<META_DESCRIPTION>" . htmlspecialchars($details["products_meta_description"]) . "</META_DESCRIPTION>" . "\n";
          $schema .=  "<META_KEYWORDS>" . htmlspecialchars($details["products_meta_keywords"]) . "</META_KEYWORDS>" . "\n";
        }
        $schema .= "</PRODUCT_DESCRIPTION>\n";

        $result->MoveNext();
      }

       // NEU JP 26.08.2005 Aktionspreise exportieren
      $special_query = "SELECT * FROM " . $oosDBTable['specials'] . " " .
                         "WHERE products_id=" . $products['products_id'] . " limit 0,1";

      $special_result = $db->Execute($special_query);

      while ($specials = xtc_db_fetch_array($special_result->fields) {
        $schema .= '<SPECIAL>' . "\n" .
                   '<SPECIAL_PRICE>' . $specials['specials_new_products_price'] . '</SPECIAL_PRICE>' . "\n" .
                   '<SPECIAL_DATE_ADDED>' . $specials['specials_date_added'] . '</SPECIAL_DATE_ADDED>' . "\n" .
                   '<SPECIAL_LAST_MODIFIED>' . $specials['specials_last_modified'] . '</SPECIAL_LAST_MODIFIED>' . "\n" .
                   '<SPECIAL_DATE_EXPIRES>' . $specials['expires_date'] . '</SPECIAL_DATE_EXPIRES>' . "\n" .
                   '<SPECIAL_STATUS>' . $specials['status'] . '</SPECIAL_STATUS>' . "\n" .
                   '<SPECIAL_DATE_STATUS_CHANGE>' . $specials['date_status_change'] . '</SPECIAL_DATE_STATUS_CHANGE>' . "\n" .
                   '</SPECIAL>' . "\n";
        $result->MoveNext();
      }

      $schema .= '</PRODUCT_DATA>' . "\n" .
                 '</PRODUCT_INFO>' . "\n";
      echo $schema;

      $result->MoveNext();
    }
    $schema = '</PRODUCTS>' . "\n\n";
    echo $schema;
  }


  function SendCustomers () {

    if (!get_cfg_var('safe_mode')) {
      @set_time_limit(0);
    }

    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<CUSTOMERS>' . "\n";
    echo $schema;

    $from = oosDBPrepareInput($_GET['customers_from']);
    $anz  = oosDBPrepareInput($_GET['customers_count']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $address_query = "SELECT
                    c.customers_gender, 
                    c.customers_id,
                    c.customers_cid, 
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
                    " . $oosDBTable['customers'] . " c,
                    " . $oosDBTable['customers_info'] . " ci,
                    " . $oosDBTable['address_book'] . " a ,
                    " . $oosDBTable['countries'] . " co
                   WHERE
                    c.customers_id = ci.customers_info_id AND
                    c.customers_id = a.customers_id AND
                    c.customers_default_address_id = a.address_book_id AND
                    a.entry_country_id  = co.countries_id";

    if (isset($from)) {
      if (!isset($anz)) $anz = 1000;
      $address_query.= " limit " . $from . "," . $anz;
    }

    $result = $db->Execute($query);

    $address_result = $db->Execute($address_query);

    while ($address = xtc_db_fetch_array($address_result->fields) {
      $schema = '<CUSTOMERS_DATA>' . "\n" .
                '<CUSTOMERS_ID>' . htmlspecialchars($address['customers_id']) . '</CUSTOMERS_ID>' . "\n" .
                '<CUSTOMERS_CID>' . htmlspecialchars($address['customers_cid']) . '</CUSTOMERS_CID>' . "\n" .
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
                '<TELEPHONE>' . htmlspecialchars($address['customers_telephone']) . '</TELEPHONE>' . "\n" . // JAN
                '<FAX>' . htmlspecialchars($address['customers_fax']) . '</FAX>' . "\n" . // JAN
                '<EMAIL>' . htmlspecialchars($address['customers_email_address']) . '</EMAIL>' . "\n" . // JAN
                '<BIRTHDAY>' . htmlspecialchars($address['customers_dob']) . '</BIRTHDAY>' . "\n" .
                '<DATE_ACCOUNT_CREATED>' . htmlspecialchars($address['customers_info_date_account_created']) . '</DATE_ACCOUNT_CREATED>' . "\n" .
                '</CUSTOMERS_DATA>' . "\n";
      echo $schema;

      $result->MoveNext();
    }
    $schema = '</CUSTOMERS>' . "\n\n";
    echo $schema;
  }


  function SendCustomersNewsletter () {
    if (!get_cfg_var('safe_mode')) {
      @set_time_limit(0);
    }


    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<CUSTOMERS>' . "\n".

    $from = oosDBPrepareInput($_GET['customers_from']);
    $anz  = oosDBPrepareInput($_GET['customers_count']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $address_query = "SELECT *
                      FROM " . $oosDBTable['customers']. " 
                      WHERE customers_newsletter = 1";

    if (isset($from)) {
      if (!isset($anz)) $anz = 1000;
      $address_query.= " limit " . $from . "," . $anz;
    }
    $result = $db->Execute($query);
    $address_result = $db->Execute($address_query);
    while ($address = xtc_db_fetch_array($address_result->fields) {
      $schema .= '<CUSTOMERS_DATA>' . "\n";
      $schema .= '<CUSTOMERS_ID>' . $address['customers_id'] . '</CUSTOMERS_ID>' . "\n";
      $schema .= '<CUSTOMERS_CID>' . $address['customers_cid'] . '</CUSTOMERS_CID>' . "\n";
      $schema .= '<CUSTOMERS_GENDER>' . $address['customers_gender'] . '</CUSTOMERS_GENDER>' . "\n";
      $schema .= '<CUSTOMERS_FIRSTNAME>' . $address['customers_firstname'] . '</CUSTOMERS_FIRSTNAME>' . "\n";
      $schema .= '<CUSTOMERS_LASTNAME>' . $address['customers_lastname'] . '</CUSTOMERS_LASTNAME>' . "\n";
      $schema .= '<CUSTOMERS_EMAIL_ADDRESS>' . $address['customers_email_address'] . '</CUSTOMERS_EMAIL_ADDRESS>' . "\n";
      $schema .= '</CUSTOMERS_DATA>' . "\n";

      $result->MoveNext();
    }
    $schema .= '</CUSTOMERS>' . "\n\n";
    echo $schema;
  }



  function SendShopConfig () {
    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
              '<CONFIG>' . "\n" .
              '<CONFIG_DATA>' . "\n" ;
    echo $schema;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $query = "SELECT configuration_id, configuration_key, configuration_value, configuration_group_id,
                     sort_order, last_modified, date_added, use_function, set_function
              FROM " . $oosDBTable['configuration'];
    $result = $db->Execute($query);

    while ($config = $result->fields) {
      $schema = '<ENTRY ID="' . $config['configuration_id'] . '">' .  "\n" .
                '<PARAM>' . htmlspecialchars($config['configuration_key']) . '</PARAM>' . "\n" .
                '<VALUE>' . htmlspecialchars($config['configuration_value']) . '</VALUE>' . "\n" .
                '<TITLE></TITLE>' . "\n" .
                '<DESCRIPTION></DESCRIPTION>' . "\n" .
                '<GROUP_ID>' . htmlspecialchars($config['configuration_group_id']) . '</GROUP_ID>' . "\n" .
                '<SORT_ORDER>' . htmlspecialchars($config['sort_order']) . '</SORT_ORDER>' . "\n" .
                '<USE_FUNCTION>' . htmlspecialchars($config['use_function']) . '</USE_FUNCTION>' . "\n" .
                '<SET_FUNCTION>' . htmlspecialchars($config['set_function']) . '</SET_FUNCTION>' . "\n" .
                '</ENTRY>' . "\n";
      echo $schema;

      $result->MoveNext();
    }


    $schema = '</CONFIG_DATA>' . "\n";
    echo $schema;

    $schema = '<TAX_CLASS>' . "\n";
    echo $schema;

    $tax_class_sql = "SELECT tax_class_id, tax_class_title, tax_class_description, last_modified, date_added 
                      FROM " . $oosDBTable['tax_class'];
    $tax_class_res = $db->Execute($tax_class_sql);

    while ($tax_class = $tax_class_res->fields) {
      $schema = '<CLASS ID="' . $tax_class['tax_class_id'] . '">' . "\n" .
                '<TITLE>' . htmlspecialchars($tax_class['tax_class_title']) . '</TITLE>' . "\n" .
                '<DESCRIPTION>' . htmlspecialchars($tax_class['tax_class_description']) . '</DESCRIPTION>' . "\n" .
                '<LAST_MODIFIED>' . htmlspecialchars($tax_class['last_modified']) . '</LAST_MODIFIED>' . "\n" .
                '<DATE_ADDED>' . htmlspecialchars($tax_class['date_added']) . '</DATE_ADDED>' . "\n" .
                '</CLASS>'. "\n";
      echo $schema;

      $result->MoveNext();
    }

    $schema = '</TAX_CLASS>' . "\n";
    echo $schema;
    $schema = '<TAX_RATES>' . "\n";
    echo $schema;

    $tax_rates_sql = "SELECT tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate,
                             tax_description, last_modified, date_added
                     FROM " . $oosDBTable['tax_rates'];
    $tax_rates_res = $db->Execute($tax_rates_sql);

    while ($tax_rates = $tax_rates_res->fields) {
      $schema = '<RATES ID=">' . $tax_rates['tax_rates_id'] . '">' . "\n" .
                '<ZONE_ID>' .  htmlspecialchars($tax_rates['tax_zone_id']) . '</ZONE_ID>' . "\n" .
                '<CLASS_ID>' . htmlspecialchars($tax_rates['tax_class_id']) . '</CLASS_ID>' . "\n" .
                '<PRIORITY>' . htmlspecialchars($tax_rates['tax_priority']) . '</PRIORITY>' . "\n" .
                '<RATE>' . htmlspecialchars($tax_rates['tax_rate']) . '</RATE>' . "\n" .
                '<DESCRIPTION>' . htmlspecialchars($tax_rates['tax_description']) . '</DESCRIPTION>' . "\n" .
                '<LAST_MODIFIED>' . htmlspecialchars($tax_rates['last_modified']) . '</LAST_MODIFIED>' . "\n" .
                '<DATE_ADDED>' . htmlspecialchars($tax_rates['date_added']) . '</DATE_ADDED>' . "\n" .
                '</RATES>' . "\n";
      echo $schema;

      $result->MoveNext();
    }
    $schema = '</TAX_RATES>' . "\n";
    echo $schema;
    $schema = '</CONFIG>' . "\n";
    echo $schema;
  }


  function SendXMLHeader () {
    header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer ge�dert
    header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header ("Pragma: no-cache"); // HTTP/1.0
    header ("Content-type: text/xml");
  }



  function SendHTMLHeader () {
    header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer ge�dert
    header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header ("Pragma: no-cache"); // HTTP/1.0
    header ("Content-type: text/html");
  }


  function ShowHTMLMenu () {
    global $version_nr, $version_datum, $user, $password;

    SendHTMLHeader;

    $Url = $_SERVER['PHP_SELF'] . "?user=" . $user . "&password=" . $password;
?>
<html><head></head><body>
<h3>CAO-Faktura - xt:Commerce Shopanbindung</h3>
<h4>Version <?php echo $version_nr; ?> Stand : <?php echo $version_datum; ?></h4>
<br>
<br><b>m&ouml;gliche Funktionen :</b><br><br>
<a href="<?php echo $Url; ?>&action=version">Ausgabe XML Scriptversion</a><br>
<br>
<a href="<?php echo $Url; ?>&action=manufacturers_export">Ausgabe XML Manufacturers</a><br>
<a href="<?php echo $Url; ?>&action=categories_export">Ausgabe XML Categories</a><br>
<a href="<?php echo $Url; ?>&action=products_export">Ausgabe XML Products</a><br>
<a href="<?php echo $Url; ?>&action=customers_export">Ausgabe XML Customers</a><br>
<a href="<?php echo $Url; ?>&action=customers_newsletter_export">Ausgabe XML Customers-Newsletter</a><br>
<br>
<a href="<?php echo $Url; ?>&action=orders_export">Ausgabe XML Orders</a><br>
<br>
<a href="<?php echo $Url; ?>&action=config_export">Ausgabe XML Shop-Config</a><br>
<br>
<a href="<?php echo $Url; ?>&action=update_tables">MySQL-Tabellen aktualisieren</a><br>
</body>
</html>
<?php
  }

/*
  function UpdateTables () {
    global $version_nr, $version_datum;

    SendHTMLHeader;

    echo '<html><head></head><body>';
    echo '<h3>Tabellen-Update / Erweiterung fr CAO-Faktura</h3>';
    echo '<h4>Version ' . $version_nr . ' Stand : ' . $version_datum .'</h4>';

    $sql[1]  = 'ALTER TABLE ' .  $oosDBTable['products'] . ' ADD products_ean VARCHAR(128) AFTER products_id';
    $sql[2]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD payment_class VARCHAR(32) NOT NULL';
  $sql[3]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD shipping_method VARCHAR(32) NOT NULL';
  $sql[4]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD shipping_class VARCHAR(32) NOT NULL';
  $sql[5]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD billing_country_iso_code_2 CHAR(2) NOT NULL AFTER billing_country';
  $sql[6]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD delivery_country_iso_code_2 CHAR(2) NOT NULL AFTER delivery_country';
  $sql[7]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD billing_firstname VARCHAR(32) NOT NULL AFTER billing_name';
  $sql[8]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD billing_lastname VARCHAR(32) NOT NULL AFTER billing_firstname';
  $sql[9]  = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD delivery_firstname VARCHAR(32) NOT NULL AFTER delivery_name';
  $sql[10] = 'ALTER TABLE ' . $oosDBTable['orders'] . ' ADD delivery_lastname VARCHAR(32) NOT NULL AFTER delivery_firstname';
  $sql[11] = 'ALTER TABLE ' . $oosDBTable['orders'] . ' CHANGE payment_method payment_method VARCHAR(255) NOT NULL';
  $sql[12] = 'ALTER TABLE ' . $oosDBTable['orders'] . ' CHANGE shipping_method shipping_method VARCHAR(255) NOT NULL';
  $sql[13] = 'CREATE TABLE cao_log ( id int(11) NOT NULL auto_increment, date datetime NOT NULL default "0000-00-00 00:00:00",'.
             'user varchar(64) NOT NULL default "", pw varchar(64) NOT NULL default "", method varchar(64) NOT NULL default "",'.
             'action varchar(64) NOT NULL default "", post_data mediumtext, get_data mediumtext, PRIMARY KEY  (id))';

  $link = 'db_link';

  global $$link, $logger;

  for ($i=1;$i<=13;$i++)
  {
    echo '<b>SQL:</b> ' . $sql[$i] . '<br>';;

    if (mysql_query($sql[$i], $$link))
    {
      echo '<b>Ergebnis : OK</b>';
      } else {
      $error = mysql_error();
      $pos=strpos($error,'Duplicate column name');

      if ($pos===False)
      {
        $pos=strpos($error,'already exists');
        if ($pos===False)
        {
          echo '<b>Ergebnis : </b><font color="red"><b>' . $error . '</b></font>';
      } else {
    echo '<b>Ergebnis : OK, Tabelle existierte bereits !</b>';
  }
      } else {
     echo '<b>Ergebnis : OK, Spalte existierte bereits !</b>';
   }
 }
    echo '<br><br>';
  }
  echo '</body></html>';
}

*/

  function xtc_try_upload ($file = '', $destination = '', $permissions = '777', $extensions = '') {
    $file_object = new upload($file, $destination, $permissions, $extensions);
    if ($file_object->filename != '') return $file_object; else return false;
  }


  function clear_string($value) {
    $string = str_replace("'",'',$value);
    $string = str_replace(')','',$string);
    $string = str_replace('(','',$string);
    $array = explode(',',$string);
    return $array;
  }


  function xtc_RandomString($length) {
    $chars = array( 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J',  'k', 'K', 'l', 'L', 'm', 'M', 'n','N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v','V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

    $max_chars = count($chars) - 1;
    srand( (double) microtime()*1000000);

    $rand_str = '';
    for ($i=0;$i<$length;$i++) {
      $rand_str = ( $i == 0 ) ? $chars[rand(0, $max_chars)] : $rand_str . $chars[rand(0, $max_chars)];
    }

    return $rand_str;
  }




  function oosRemoveProduct($product_id) {
    global $LangID, $customers_status_array;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $product_image_query = $db->Execute("SELECT products_image FROM " .  $oosDBTable['products'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $product_image = xtc_db_fetch_array($product_image_query);
    $result = $db->Execute($query);

    $duplicate_image_query = $db->Execute("SELECT count(*) as total FROM " .  $oosDBTable['products'] . " WHERE products_image = '" . oosDBInput($product_image['products_image']) . "'");
    $duplicate_image = xtc_db_fetch_array($duplicate_image_query);
    $result = $db->Execute($query);

    if ($duplicate_image['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_POPUP_IMAGES . $product_image['products_image'])) {
        @unlink(DIR_FS_CATALOG_POPUP_IMAGES . $product_image['products_image']);
      }
      // START CHANGES
      $image_subdir = BIG_IMAGE_SUBDIR;
      if (substr($image_subdir, -1) != '/') $image_subdir .= '/';
      if (file_exists(DIR_FS_CATALOG_IMAGES . $image_subdir . $product_image['products_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $image_subdir . $product_image['products_image']);
      }
      // END CHANGES
    }

    $db->Execute("DELETE FROM " . $oosDBTable['specials'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $db->Execute("DELETE FROM " . $oosDBTable['products'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $db->Execute("DELETE FROM " . $oosDBTable['products_to_categories'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $db->Execute("DELETE FROM " . $oosDBTable['products_description'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $db->Execute("DELETE FROM " . $oosDBTable['products_attributes'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $db->Execute("DELETE FROM " . $oosDBTable['customers_basket']. " WHERE products_id = '" . oosDBInput($product_id) . "'");
    $db->Execute("DELETE FROM " . $oosDBTable['customers_basket_attributes'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");


    // get statuses
    $customers_statuses_array = array(array());

    $customers_statuses_query = $db->Execute("SELECT * FROM " . $oosDBTable['customers_status'] . " WHERE language_id = '".$LangID."' ORDER BY customers_status_id");

    while ($customers_statuses = xtc_db_fetch_array($customers_statuses_query->fields) {
      $customers_statuses_array[] = array('id' => $customers_statuses['customers_status_id'],
                                          'text' => $customers_statuses['customers_status_name']);

      $result->MoveNext();
    }

    for ($i=0,$n=sizeof($customers_status_array);$i<$n;$i++) {
      $db->Execute("DELETE FROM personal_offers_by_customers_status_" . $i . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    }

    $product_reviews_query = $db->Execute("SELECT reviews_id FROM " . $oosDBTable['reviews'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
    while ($product_reviews = xtc_db_fetch_array($product_reviews_query->fields) {
      $db->Execute("DELETE FROM " . $oosDBTable['reviews_description'] . " WHERE reviews_id = '" . $product_reviews['reviews_id'] . "'");

      $result->MoveNext();
    }
    $db->Execute("DELETE FROM " . $oosDBTable['reviews'] . " WHERE products_id = '" . oosDBInput($product_id) . "'");
  }


  function ManufacturersImageUpload () {

    if ($manufacturers_image = &xtc_try_upload('manufacturers_image',DIR_FS_CATALOG.DIR_WS_IMAGES,'777', '', true)) {
      $code = 0;
      $message = 'OK';
    } else {
      $code = -1;
      $message = 'UPLOAD FAILED';
    }
    print_xml_status ($code, $_POST['action'], $message, '', 'FILE_NAME', $manufacturers_image->filename);
  }


  function CategoriesImageUpload () {

    if ( $categories_image = &xtc_try_upload('categories_image',DIR_FS_CATALOG.DIR_WS_IMAGES.'categories/','777', '', true)) {
      $code = 0;
      $message = 'OK';
    } else {
      $code = -1;
      $message = 'UPLOAD FAILED';
    }
    print_xml_status ($code, $_POST['action'], $message, '', 'FILE_NAME', $categories_image->filename);
  }


  function ProductsImageUpload () {

    if ($products_image = &xtc_try_upload('products_image',DIR_FS_CATALOG.DIR_WS_ORIGINAL_IMAGES,'777', '', true))  {
      $products_image_name = $products_image->filename;
      // rewrite values to use resample classes
      define('DIR_FS_CATALOG_ORIGINAL_IMAGES',DIR_FS_CATALOG.DIR_WS_ORIGINAL_IMAGES);
      define('DIR_FS_CATALOG_INFO_IMAGES',DIR_FS_CATALOG.DIR_WS_INFO_IMAGES);
      define('DIR_FS_CATALOG_POPUP_IMAGES',DIR_FS_CATALOG.DIR_WS_POPUP_IMAGES);
      define('DIR_FS_CATALOG_THUMBNAIL_IMAGES',DIR_FS_CATALOG.DIR_WS_THUMBNAIL_IMAGES);
      define('DIR_FS_CATALOG_IMAGES',DIR_FS_CATALOG.DIR_WS_IMAGES);

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
    print_xml_status ($code, $_POST['action'], $message, '', 'FILE_NAME', $products_image->filename);
  }

  function ManufacturersUpdate () {
    $manufacturers_id = oosDBPrepareInput($_POST['mID']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($manufacturers_id)) {
      // Hersteller laden
      $count_query = $db->Execute("SELECT 
                                  manufacturers_id,
                               manufacturers_name,
                               manufacturers_image,
                               date_added,
                               last_modified FROM " . $oosDBTable['manufacturers'] . "
                               WHERE manufacturers_id='" . $manufacturers_id . "'");

      if ($manufacturer = xtc_db_fetch_array($count_query)) {
        $exists = 1;
        // aktuelle Herstellerdaten laden
        $manufacturers_name  = $manufacturer['manufacturers_name'];
        $manufacturers_image = $manufacturer['manufacturers_image'];
        $date_added          = $manufacturer['date_added'];
        $last_modified       = $manufacturer['last_modified'];
      } else {
         $exists = 0;
      }
      // Variablen nur ueberschreiben wenn als Parameter vorhanden!
      if (isset($_POST['manufacturers_name'])) $manufacturers_name = oosDBPrepareInput($_POST['manufacturers_name']);
      if (isset($_POST['manufacturers_image'])) $manufacturers_image = oosDBPrepareInput($_POST['manufacturers_image']);

      $sql_data_array = array('manufacturers_id' => $manufacturers_id,
                         'manufacturers_name' => $manufacturers_name,
                         'manufacturers_image' => $manufacturers_image);

      if ($exists==0)  {
        // Neuanlage (ID wird von CAO vergegeben!)
        $mode='APPEND';
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        xtc_db_perform($oosDBTable['manufacturers'], $sql_data_array);
        $products_id = mysql_insert_id();
      } elseif ($exists==1) {
        $mode='UPDATE';
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        xtc_db_perform($oosDBTable['manufacturers'], $sql_data_array, 'update', 'manufacturers_id = \'' . oosDBInput($manufacturers_id) . '\'');
      }
      $languages_query = $db->Execute("SELECT languages_id, name, code, image, directory FROM " . $oosDBTable['languages'] . " ORDER BY sort_order");
      while ($languages = xtc_db_fetch_array($languages_query->fields) {
        $languages_array[] = array('id' => $languages['languages_id'],
                              'name' => $languages['name'],
                              'code' => $languages['code'],
                              'image' => $languages['image'],
                              'directory' => $languages['directory']);

        $result->MoveNext();

      }
      $languages = $languages_array;
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];

        // Bestehende Daten laden
        $desc_query = $db->Execute("SELECT manufacturers_id,languages_id,manufacturers_url,url_clicked,date_last_click FROM " .
                           $oosDBTable['manufacturers_info']. " WHERE manufacturers_id='" . $manufacturers_id . "' and languages_id='" . $language_id . "'");
        if ($desc = xtc_db_fetch_array($desc_query)) {
          $manufacturers_url = $desc['manufacturers_url'];
          $url_clicked       = $desc['url_clicked'];
          $date_last_click   = $desc['date_last_click'];
        }

        // uebergebene Daten einsetzen
        if (isset($_POST['manufacturers_url'][$language_id])) $manufacturers_url=oosDBPrepareInput($_POST['manufacturers_url'][$language_id]);
        if (isset($_POST['url_clicked'][$language_id]))       $url_clicked=oosDBPrepareInput($_POST['url_clicked'][$language_id]);
        if (isset($_POST['date_last_click'][$language_id]))   $date_last_click=oosDBPrepareInput($_POST['date_last_click'][$language_id]);

        $sql_data_array = array('manufacturers_url' => $manufacturers_url);

        if ($exists==0) {
          // Insert
          $insert_sql_data = array('manufacturers_id' => $products_id,
                           'languages_id' => $language_id);
          $sql_data_array = /*xtc_*/array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform($oosDBTable['manufacturers']_INFO, $sql_data_array);
        } elseif ($exists==1) {
          xtc_db_perform($oosDBTable['manufacturers']_INFO, $sql_data_array, 'update', 'manufacturers_id = \'' . oosDBInput($manufacturers_id) . '\' and languages_id = \'' . $language_id . '\'');
        }
      }
      print_xml_status (0, $_POST['action'], 'OK', $mode ,'MANUFACTURERS_ID', $mID);

    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }


  function ManufacturersErase () {
    $ManID  = oosDBPrepareInput($_POST['mID']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ManID)) {
      // Hersteller loeschen
      $db->Execute("DELETE FROM " . $oosDBTable['manufacturers'] . " WHERE manufacturers_id = '" . (int)$ManID . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['manufacturers_info']. " WHERE manufacturers_id = '" . (int)$ManID . "'");
      // Herstellerverweis in den Artikeln loeschen
      $db->Execute("UPDATE " .  $oosDBTable['products'] . " SET manufacturers_id = '' WHERE manufacturers_id = '" . (int)$ManID . "'");

      print_xml_status (0, $_POST['action'], 'OK', '', '', '');
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }


  function ProductsUpdate () {
    global $LangID;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $languages_query = $db->Execute("SELECT languages_id, name, code, image, directory FROM " . $oosDBTable['languages'] . " ORDER BY sort_order");
    while ($languages = xtc_db_fetch_array($languages_query->fields) {
      $languages_array[] = array('id' => $languages['languages_id'],
                               'name' => $languages['name'],
                               'code' => $languages['code'],
                               'image' => $languages['image'],
                               'directory' => $languages['directory']);

      $result->MoveNext();

    }
    $products_id = oosDBPrepareInput($_POST['pID']);

    // product laden
    $count_query = $db->Execute("SELECT products_quantity,
                            products_model,
                            products_image,
                            products_price,
                            products_date_available,
                            products_weight,
                            products_status,
                            products_ean,
                            products_fsk18,
                            products_shippingtime,
                            products_tax_class_id,
                            manufacturers_id FROM " .  $oosDBTable['products'] . "
                            WHERE products_id='" . $products_id . "'");

    if ($product = xtc_db_fetch_array($count_query)) {
      $exists = 1;
      // aktuelle Produktdaten laden
      $products_quantity = $product['products_quantity'];
      $products_model = $product['products_model'];
      $products_image = $product['products_image'];
      $products_price = $product['products_price'];
      $products_date_available = $product['products_date_available'];
      $products_weight = $product['products_weight'];
      $products_status = $product['products_status'];
      $products_ean = $product['products_ean'];
      $products_fsk18 = $product['products_fsk18'];
      $products_shippingtime = $product['products_shippingtime'];
      $products_tax_class_id = $product['products_tax_class_id'];
      $manufacturers_id = $product['manufacturers_id'];
    } else
      $exists = 0;

  // Variablen nur ueberschreiben wenn als Parameter vorhanden!
  if (isset($_POST['products_quantity'])) $products_quantity = oosDBPrepareInput($_POST['products_quantity']);
  if (isset($_POST['products_model'])) $products_model = oosDBPrepareInput($_POST['products_model']);
  if (isset($_POST['products_image'])) $products_image = oosDBPrepareInput($_POST['products_image']);
  if (isset($_POST['products_price'])) $products_price = oosDBPrepareInput($_POST['products_price']);
  if (isset($_POST['products_date_available'])) $products_date_available = oosDBPrepareInput($_POST['products_date_available']);
  if (isset($_POST['products_weight'])) $products_weight = oosDBPrepareInput($_POST['products_weight']);
  if (isset($_POST['products_status'])) $products_status = oosDBPrepareInput($_POST['products_status']);
  if (isset($_POST['products_ean'])) $products_ean = oosDBPrepareInput($_POST['products_ean']);
  if (isset($_POST['products_fsk18'])) $products_fsk18 = oosDBPrepareInput($_POST['products_fsk18']);
  if (isset($_POST['products_shippingtime'])) $products_shippingtime = oosDBPrepareInput($_POST['products_shippingtime']);
  if (isset($_POST['products_me'])) $products_vpe = oosDBPrepareInput($_POST['products_me']);
  if (isset($_POST['products_tax_class_id'])) $products_tax_class_id = oosDBPrepareInput($_POST['products_tax_class_id']);

  if (file_exists('cao_produpd_1.php')) { include('cao_produpd_1.php'); }

  // Comment: SWITCH_MWST nun an der richtigen Var. ; TKI 2005-08-24
  if (SWITCH_MWST==true)  {
    // switch IDs
    if ($products_tax_class_id==1) {
      $products_tax_class_id=2;
      } else {
      if ($products_tax_class_id==2) {
        $products_tax_class_id=1;
      }
    }
  }

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
                       'products_ean' => $products_ean,
                       'products_fsk18' => $products_fsk18,
                       'products_shippingtime' => $products_shippingtime,
                       'products_tax_class_id' => $products_tax_class_id,
                       'manufacturers_id' => $manufacturers_id);

  if ($exists==0)  {
    // Neuanlage (ID wird an CAO zurueckgegeben!)
    // SET groupaccees

    $permission_sql = 'show columns FROM ' .  $oosDBTable['products'] . ' like "group_permission_%"';
    $permission_query = $db->Execute ($permission_sql);

    if (xtc_db_num_rows($permission_query)) {
      // ist XTC 3.0.4
      $permission_array = array ();
      while ($permissions = xtc_db_fetch_array($permission_query->fields) {
        $permission_array = array_merge($permission_array, array ($permissions['Field'] => '1'));

        $result->MoveNext();

      }

      $insert_sql_data = array('products_date_added' => 'now()',
                            'products_shippingtime'=>1);

      $insert_sql_data = array_merge($insert_sql_data, $permission_array);  
    } else {
      // XTC bis 3.0.3
      $customers_statuses_array = array(array());
      $customers_statuses_query = $db->Execute("SELECT customers_status_id,
                                             customers_status_name
                                             FROM " . $oosDBTable['customers_status'] . "
                                             WHERE language_id = '".$LangID."' ORDER BY
                                             customers_status_id");
      $i=1;        // this is changed FROM 0 to 1 in cs v1.2
      while ($customers_statuses = xtc_db_fetch_array($customers_statuses_query))
      {
        $i=$customers_statuses['customers_status_id'];
        $customers_statuses_array[$i] = array('id' => $customers_statuses['customers_status_id'],
                                           'text' => $customers_statuses['customers_status_name']);
      }

       $group_ids='c_all_group,';
      for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
        $group_ids .='c_'.$customers_statuses_array[$i]['id'].'_group,';
      }

     $insert_sql_data = array('products_date_added' => 'now()',
                            'products_shippingtime'=>1,
                            'group_ids'=>$group_ids);
    }

    $mode='APPEND';

   $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

    // insert data
    xtc_db_perform( $oosDBTable['products'], $sql_data_array);

    $products_id = mysql_insert_id();

  }
  elseif ($exists==1) //Update
  {
    $mode='UPDATE';
    $update_sql_data = array('products_last_modified' => 'now()');
    $sql_data_array = array_merge($sql_data_array, $update_sql_data);

    // UPDATE data
    xtc_db_perform( $oosDBTable['products'], $sql_data_array, 'update', 'products_id = \'' . oosDBInput($products_id) . '\'');
  }

  $languages = $languages_array;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++)
  {
    $language_id = $languages[$i]['id'];

    // Bestehende Daten laden
    $desc_query = $db->Execute("SELECT
                             products_id,
                             products_name,
                             products_description,
                             products_short_description,
                             products_meta_title,
                             products_meta_description,
                             products_meta_keywords,
                             products_url,
                             products_viewed,
                             language_id
                             FROM " .
                              $oosDBTable['products_description'] . "
                             WHERE products_id='" . $products_id . "'
                             and language_id='" . $language_id . "'");

    if ($desc = xtc_db_fetch_array($desc_query))
    {
      $products_name = $desc['products_name'];
      $products_description = $desc['products_description'];
      $products_short_description = $desc['products_short_description'];
      $products_meta_title = $desc['products_meta_title'];
      $products_meta_description = $desc['products_meta_description'];
      $products_meta_keywords = $desc['products_meta_keywords'];
      $products_url = $desc['products_url'];
    }

    // uebergebene Daten einsetzen
    if (isset($_POST['products_name'][$LangID]))              $products_name              = oosDBPrepareInput($_POST['products_name'][$LangID]);
    if (isset($_POST['products_description'][$LangID]))       $products_description       = oosDBPrepareInput($_POST['products_description'][$LangID]);
    if (isset($_POST['products_short_description'][$LangID])) $products_short_description = oosDBPrepareInput($_POST['products_short_description'][$LangID]);
    if (isset($_POST['products_meta_title'][$LangID]))        $products_meta_title        = oosDBPrepareInput($_POST['products_meta_title'][$LangID]);
    if (isset($_POST['products_meta_description'][$LangID]))  $products_meta_description  = oosDBPrepareInput($_POST['products_meta_description'][$LangID]);
    if (isset($_POST['products_meta_keywords'][$LangID]))     $products_meta_keywords     = oosDBPrepareInput($_POST['products_meta_keywords'][$LangID]);
    if (isset($_POST['products_url'][$LangID]))               $products_url               = oosDBPrepareInput($_POST['products_url'][$LangID]);
 
    //NEU 20051004 JP
    if (isset($_POST['products_shop_long_description'][$LangID]))  $products_description       = oosDBPrepareInput($_POST['products_shop_long_description'][$LangID]);
    if (isset($_POST['products_shop_short_description'][$LangID])) $products_short_description = oosDBPrepareInput($_POST['products_shop_short_description'][$LangID]);

    $sql_data_array = array('products_name' => $products_name,
                            'products_description' => $products_description,
                            'products_short_description' => $products_short_description,
                            'products_meta_title' => $products_meta_title,
                            'products_meta_description' => $products_meta_description,
                            'products_meta_keywords' => $products_meta_keywords,
                            'products_url' => $products_url);

    if ($exists==0) // Insert
    {
      $insert_sql_data = array('products_id' => $products_id,
                               'language_id' => $language_id);

      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
      xtc_db_perform( $oosDBTable['products_description'], $sql_data_array);
    }
    elseif (($exists==1)and($language_id==$LangID)) // Update
    {
      // Nur die Daten in der akt. Sprache aendern !
      xtc_db_perform( $oosDBTable['products_description'], $sql_data_array, 'update', 'products_id = \'' . oosDBInput($products_id) . '\' and language_id = \'' . $language_id . '\'');
    }
    }
    if (file_exists('cao_produpd_2.php')) { include('cao_produpd_2.php'); }

    print_xml_status (0, $_POST['action'], 'OK', $mode, 'PRODUCTS_ID', $products_id);
  }



  function ProductsErase () {

    $ProdID  = oosDBPrepareInput($_POST['prodid']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ProdID)) {
      // ProductsToCategieries loeschen bei denen die products_id = ... ist
      $res1 = $db->Execute("DELETE FROM " . $oosDBTable['products_to_categories'] . " WHERE products_id='" . $ProdID . "'");

      // Product loeschen
      oosRemoveProduct($ProdID);
      $code = 0;
      $message = 'OK';
    } else {
      $code = 99;
      $message = 'FAILED';
    }
    print_xml_status (0, $_POST['action'], 'OK', '', 'SQL_RES1', $res1);
  }



  function ProductsSpecialPriceUpdate () {

    $ProdID  = oosDBPrepareInput($_POST['prodid']);

    $Price  = oosDBPrepareInput($_POST['price']);
    $Status = oosDBPrepareInput($_POST['status']);
    $Expire = oosDBPrepareInput($_POST['expired']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ProdID))  {
      /*
      1. Ermitteln ob Produkt bereits einen Spezialpreis hat
      2. wenn JA -> Update / NEIN -> INSERT
     */
      $sp_sql = "SELECT specials_id FROM " . $oosDBTable['specials'] . " " .
              "WHERE products_id='" . (int)$ProdID . "'";
      $sp_query = $db->Execute($sql);

      if ($sp = xtc_db_fetch_array($sp_query)) {
        // es existiert bereits ein Datensatz -> Update
        $SpecialID = $sp['specials_id'];

        $db->Execute(
              "UPDATE " . $oosDBTable['specials'] .
              " SET specials_new_products_price = '" . $Price . "'," .
              " specials_last_modified = now()," .
              " expires_date = '" . $Expire .
              "' WHERE specials_id = '" . (int)$SpecialID. "'");

        print_xml_status (0, $_POST['action'], 'OK', 'UPDATE', '', '');
      } else {
        // Neuanlage
        $db->Execute(
                "INSERT INTO " . $oosDBTable['specials'] .
                " (products_id, specials_new_products_price, specials_date_added, expires_date, status) " .
                " values ('" . (int)$ProdID . "', '" . $Price . "', now(), '" . $Expire . "', '1')");

        print_xml_status (0, $_POST['action'], 'OK', 'APPEND', '', '');
      }
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }



  function ProductsSpecialPriceErase () {

    $ProdID  = oosDBPrepareInput($_POST['prodid']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ProdID))  {
      $db->Execute("DELETE FROM " . $oosDBTable['specials'] . " WHERE products_id = '" . (int)$ProdID . "'");
      print_xml_status (0, $_POST['action'], 'OK', '', '', '');
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }

  function CategoriesUpdate () {
    global $LangID;

    $CatID    = oosDBPrepareInput($_POST['catid']);
    $ParentID = oosDBPrepareInput($_POST['parentid']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ParentID) && isset($CatID)) {
      // product laden
      $SQL = "SELECT categories_id, parent_id, date_added, sort_order, categories_image " .
             "FROM " . $oosDBTable['categories'] . " WHERE categories_id='" . $CatID . "'";


      $count_query = $db->Execute($SQL);
      if ($categorie = xtc_db_fetch_array($count_query)) {
        $exists = 1;

        $ParentID = $categorie['parent_id'];
        $Sort     = $categorie['sort_order'];
        $Image    = $categorie['categories_image'];
      } else $exists = 0;

    // Variablen nur ueberschreiben wenn als Parameter vorhanden!
    if (isset($_POST['parentid'])) $ParentID = oosDBPrepareInput($_POST['parentid']);
    if (isset($_POST['sort']))     $Sort     = oosDBPrepareInput($_POST['sort']);
    if (isset($_POST['image']))    $Image    = oosDBPrepareInput($_POST['image']);


    $sql_data_array = array('categories_id'    => $CatID,
                            'parent_id'        => $ParentID,
                            'sort_order'       => $Sort,
                            'categories_image' => $Image,
                            'last_modified'    => 'now()');

      if ($exists==0) {
        // Neuanlage
        $mode='APPEND';

        // SET groupaccees
        $permission_sql = 'show columns FROM ' . $oosDBTable['categories'] . ' like "group_permission_%"';
        $permission_query = $db->Execute ($permission_sql);

        if (xtc_db_num_rows($permission_query))  {
          // ist XTC 3.0.4
          $permission_array = array ();
          while ($permissions = xtc_db_fetch_array($permission_query->fields)
            $permission_array = array_merge($permission_array, array ($permissions['Field'] => '1'));
          }

          $insert_sql_data = array('date_added' => 'now()');

          $insert_sql_data = array_merge($insert_sql_data, $permission_array);  
        } else {
          // XTC bis 3.0.3
          $customers_statuses_array = array(array());
          $customers_statuses_query = $db->Execute("SELECT customers_status_id,
                                               customers_status_name
                                                 FROM " . $oosDBTable['customers_status'] . "
                                                 WHERE language_id = '".$LangID."' ORDER BY
                                                 customers_status_id");
          $i=1;        // this is changed FROM 0 to 1 in cs v1.2
          while ($customers_statuses = xtc_db_fetch_array($customers_statuses_query->fields) {
            $i=$customers_statuses['customers_status_id'];
            $customers_statuses_array[$i] = array('id' => $customers_statuses['customers_status_id'],
                                                'text' => $customers_statuses['customers_status_name']);
          }

          $group_ids='c_all_group,';
          for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
            $group_ids .='c_'.$customers_statuses_array[$i]['id'].'_group,';
          }
           $insert_sql_data = array('date_added' => 'now()',
                                 'group_ids'  => $group_ids);
        }

        $sql_data_array = /*xtc_*/array_merge($sql_data_array, $insert_sql_data);

        xtc_db_perform($oosDBTable['categories'], $sql_data_array);
      } elseif ($exists==1) {
        $mode='UPDATE';

        xtc_db_perform($oosDBTable['categories'], $sql_data_array, 'update', 'categories_id = \'' . oosDBInput($CatID) . '\'');
      }

      //$languages = xtc_get_languages();

      $languages_query = $db->Execute("SELECT languages_id, name, code, image, directory FROM " . $oosDBTable['languages'] . " ORDER BY sort_order");
      while ($languages = xtc_db_fetch_array($languages_query->fields) {
        $languages_array[] = array('id' => $languages['languages_id'],
                                   'name' => $languages['name'],
                                   'code' => $languages['code'],
                                   'image' => $languages['image'],
                                   'directory' => $languages['directory']);
      }

      $languages = $languages_array;

      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];

        // Bestehende Daten laden
        $SQL = "SELECT categories_id,language_id,categories_name,categories_description,categories_heading_title,".
               "categories_meta_title,categories_meta_description,categories_meta_keywords";

        $desc_query = $db->Execute($SQL . " FROM " . $oosDBTable['categories_description'] . " WHERE categories_id='" . $CatID . "' and language_id='" . $language_id . "'");
        if ($desc = xtc_db_fetch_array($desc_query))  {
          $categories_name             = $desc['categories_name'];
          $categories_description      = $desc['$categories_description'];
          $categories_heading_title    = $desc['categories_heading_title'];
          $categories_meta_title       = $desc['categories_meta_title'];
          $categories_meta_description = $desc['categories_meta_description'];
          $categories_meta_keywords    = $desc['categories_meta_keywords'];
        }

        // uebergebene Daten einsetzen
        if (isset($_POST['name']))                        $categories_name             = oosDBPrepareInput(UrlDecode($_POST['name']));
        if (isset($_POST['descr']))                       $categories_description = oosDBPrepareInput(UrlDecode($_POST['descr']));
        if (isset($_POST['categories_heading_title']))    $categories_heading_title    = oosDBPrepareInput(UrlDecode($_POST['categories_heading_title']));  
        if (isset($_POST['categories_meta_title']))       $categories_meta_title       = oosDBPrepareInput(UrlDecode($_POST['categories_meta_title']));	  
        if (isset($_POST['categories_meta_description'])) $categories_meta_description = oosDBPrepareInput(UrlDecode($_POST['categories_meta_description']));
        if (isset($_POST['categories_meta_keywords']))    $categories_meta_keywords    = oosDBPrepareInput(UrlDecode($_POST['categories_meta_keywords']));    

        $sql_data_array = array('categories_name'             => $categories_name,
                             'categories_description'      => $categories_description,
                           'categories_heading_title'    => $categories_heading_title,
                           'categories_meta_title'       => $categories_meta_title,
                           'categories_meta_description' => $categories_meta_description,
                           'categories_meta_keywords'    => $categories_meta_keywords);

        if ($exists==0) {
          $insert_sql_data = array('categories_id' => $CatID,
                                 'language_id' => $language_id);

          $sql_data_array = /*xtc_*/array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform($oosDBTable['categories_description'], $sql_data_array);
        } elseif (($exists==1)and($language_id==$LangID)) {
          // Nur 1 Sprache aktualisieren
          xtc_db_perform($oosDBTable['categories_description'], $sql_data_array, 'update', 'categories_id = \'' . oosDBInput($CatID) . '\' and language_id = \'' . $language_id . '\'');
        }
      }
      print_xml_status (0, $_POST['action'], 'OK', $mode, '', '');
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }



  function CategoriesErase () {

    $CatID  = oosDBPrepareInput($_POST['catid']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($CatID)) {
      // Categorie loeschen
      $res1 = $db->Execute("DELETE FROM " . $oosDBTable['categories'] . " WHERE categories_id='" . $CatID . "'");
      // ProductsToCategieries loeschen bei denen die Categorie = ... ist
      $res2 = $db->Execute("DELETE FROM " . $oosDBTable['products_to_categories'] . " WHERE categories_id='" . $CatID . "'");
      // CategieriesDescription loeschenm bei denen die Categorie = ... ist
      $res3 = $db->Execute("DELETE FROM " . $oosDBTable['categories_description'] . " WHERE categories_id='" . $CatID . "'");

      print_xml_status (0, $_POST['action'], 'OK', '', 'SQL_RES1', $res1);
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }


  function Prod2CatUpdate () {

    $ProdID = oosDBPrepareInput($_POST['prodid']);
    $CatID  = oosDBPrepareInput($_POST['catid']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ProdID) && isset($CatID)) {
      $res = $db->Execute("replace into " . $oosDBTable['products_to_categories'] . " (products_id, categories_id) Values ('" . $ProdID ."', '" . $CatID . "')");
      print_xml_status (0, $_POST['action'], 'OK', '', 'SQL_RES', $res);
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }


  function Prod2CatErase () {

    $ProdID = oosDBPrepareInput($_POST['prodid']);
    $CatID  = oosDBPrepareInput($_POST['catid']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if (isset($ProdID) && isset($CatID)) {
      $res = $db->Execute("DELETE FROM " . $oosDBTable['products_to_categories'] . " WHERE products_id='" . $ProdID ."' and categories_id='" . $CatID . "'");
      print_xml_status (0, $_POST['action'], 'OK', '', 'SQL_RES', $res);
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }


  function OrderUpdate () {
    global $LangID;

    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" . "\n";

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    if ((isset($_POST['order_id'])) && (isset($_POST['status']))) {
      // Per Post bergebene Variablen
      $oID = $_POST['order_id'];
      $status = $_POST['status'];
      $comments = oosDBPrepareInput($_POST['comments']);

      //Status berprfen
      $check_status_query = $db->Execute("SELECT * FROM " . $oosDBTable['orders'] . " WHERE orders_id = '" . oosDBInput($oID) . "'");
      if ($check_status = xtc_db_fetch_array($check_status_query)) {
        if ($check_status['orders_status'] != $status || $comments != '') {
          $db->Execute("UPDATE " . $oosDBTable['orders'] . " SET orders_status = '" . oosDBInput($status) . "', last_modified = now() WHERE orders_id = '" . oosDBInput($oID) . "'");
          $customer_notified = '0';
          if ($_POST['notify'] == 'on') {
             // Falls eine Sprach ID zur Order existiert die Emailbest�igung in dieser Sprache ausfhren
             if (isset($check_status['orders_language_id']) && $check_status['orders_language_id'] > 0 ) {
               $orders_status_query = $db->Execute("SELECT orders_status_id, orders_status_name FROM " . $oosDBTable['orders']_STATUS . " WHERE language_id = '" . $check_status['orders_language_id'] . "'");
               if (xtc_db_num_rows($orders_status_query) == 0) {
                 $orders_status_query = $db->Execute("SELECT orders_status_id, orders_status_name FROM " . $oosDBTable['orders']_STATUS . " WHERE language_id = '" . $languages_id . "'");
               }
             } else {
               $orders_status_query = $db->Execute("SELECT orders_status_id, orders_status_name FROM " . $oosDBTable['orders']_STATUS . " WHERE language_id = '" . $languages_id . "'");
             }
             $orders_statuses = array();
             $orders_status_array = array();
             while ($orders_status = xtc_db_fetch_array($orders_status_query->fields) {
              $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                                         'text' => $orders_status['orders_status_name']);
              $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
            }
            // status query
            $orders_status_query = $db->Execute("SELECT orders_status_name FROM " . $oosDBTable['orders']_STATUS . " WHERE language_id = '" . $LangID . "' and orders_status_id='".$status."'");
            $o_status=xtc_db_fetch_array($orders_status_query);
            $o_status=$o_status['orders_status_name'];

            //ok lets generate the html/txt mail FROM Template
            if ($_POST['notify_comments'] == 'on')  {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            } else {
              $comments='';
            }

            // require functionblock for mails
            require_once(DIR_WS_CLASSES.'class.phpmailer.php');
            require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');
            require_once(DIR_FS_INC . 'xtc_add_tax.inc.php');
            require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
            require_once(DIR_FS_INC . 'changedataout.inc.php');
            require_once(DIR_FS_INC . 'xtc_href_link.inc.php');
            require_once(DIR_FS_INC . 'xtc_date_long.inc.php');
            require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');
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
            xtc_php_mail(EMAIL_BILLING_ADDRESS,
                       EMAIL_BILLING_NAME ,
                       $check_status['customers_email_address'],
                       $check_status['customers_name'],
                       '',
                       EMAIL_BILLING_REPLY_ADDRESS,
                       EMAIL_BILLING_REPLY_ADDRESS_NAME,
                       '',
                       '',
                       EMAIL_BILLING_SUBJECT,
                       $html_mail ,
                       $txt_mail);

            $customer_notified = '1';
          }
          $db->Execute("INSERT INTO " . $oosDBTable['orders_status_history'] . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . oosDBInput($oID) . "', '" . oosDBInput($status) . "', now(), '" . $customer_notified . "', '" . oosDBInput($comments)  . "')");
          $schema .= '<STATUS>' . "\n" .
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
          $schema .= '<STATUS>' . "\n" .
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
        $schema .= '<STATUS>' . "\n" .
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
  }


  function CustomersUpdate () {
    global $Lang_folder;

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $customers_id = -1;
    // include PW function
    require_once(DIR_FS_INC . 'xtc_encrypt_password.inc.php');

    if (isset($_POST['cID'])) $customers_id = oosDBPrepareInput($_POST['cID']);

    // security check, if user = admin, dont allow to perform changes
    if ($customers_id!=-1) {
      $sec_query=$db->Execute("SELECT customers_status FROM ".$oosDBTable['customers']." WHERE customers_id='".$customers_id."'");
      $sec_data=xtc_db_fetch_array($sec_query);
      if ($sec_data['customers_status']==0) {
        print_xml_status (120, $_POST['action'], 'CAN NOT CHANGE ADMIN USER!', '', '', '');
        return;
      }
    }
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
      $sql_customers_data_array['customers_password'] = xtc_encrypt_password($_POST['customers_password']);
    }
    $sql_address_data_array =array();

    if (isset($_POST['customers_firstname'])) $sql_address_data_array['entry_firstname'] = $_POST['customers_firstname'];
    if (isset($_POST['customers_lastname'])) $sql_address_data_array['entry_lastname'] = $_POST['customers_lastname'];
    if (isset($_POST['customers_company'])) $sql_address_data_array['entry_company'] = $_POST['customers_company'];
    if (isset($_POST['customers_street'])) $sql_address_data_array['entry_street_address'] = $_POST['customers_street'];
    if (isset($_POST['customers_city'])) $sql_address_data_array['entry_city'] = $_POST['customers_city'];
    if (isset($_POST['customers_postcode'])) $sql_address_data_array['entry_postcode'] = $_POST['customers_postcode'];
    if (isset($_POST['customers_gender'])) $sql_address_data_array['entry_gender'] = $_POST['customers_gender'];
    if (isset($_POST['customers_country_id'])) $country_code = $_POST['customers_country_id'];

    $country_query = "SELECT countries_id FROM ".$oosDBTable['countries']." WHERE countries_iso_code_2 = '".$country_code ."' LIMIT 1";
    $country_result = $db->Execute($country_query);
    $row = xtc_db_fetch_array($country_result);
    $sql_address_data_array['entry_country_id'] = $row['countries_id'];

    $count_query = $db->Execute("SELECT count(*) as count FROM " . $oosDBTable['customers'] . " WHERE customers_id='" . (int)$customers_id . "' LIMIT 1");
    $check = xtc_db_fetch_array($count_query);

    if ($check['count'] > 0) {
      $mode = 'UPDATE';
      $address_book_result = $db->Execute("SELECT customers_default_address_id FROM ".$oosDBTable['customers']." WHERE customers_id = '". (int)$customers_id ."' LIMIT 1");
      $customer = xtc_db_fetch_array($address_book_result);
      xtc_db_perform($oosDBTable['customers'], $sql_customers_data_array, 'update', "customers_id = '" . intval($cID) . "' LIMIT 1");
      xtc_db_perform($oosDBTable['address_book'], $sql_address_data_array, 'update', "customers_id = '" . intval($cID) . "' AND address_book_id = '".$customer['customers_default_address_id']."' LIMIT 1");
      $db->Execute("UPDATE " .  $oosDBTable['customers_info'] . " SET customers_info_date_account_last_modified = now() WHERE customers_info_id = '" . (int)$customers_id . "'  LIMIT 1");
    } else {
      $mode= 'APPEND';
      if (strlen($_POST['customers_password'])==0) {
        // generate PW if empty
        $pw = xtc_RandomString(8);
        $sql_customers_data_array['customers_password']=xtc_create_password($pw);
      }
      xtc_db_perform($oosDBTable['customers'], $sql_customers_data_array);
      $customers_id = xtc_db_insert_id();
      $sql_address_data_array['customers_id'] = $customers_id;
      xtc_db_perform($oosDBTable['address_book'], $sql_address_data_array);
      $address_id = xtc_db_insert_id();
      $db->Execute("UPDATE " . $oosDBTable['customers'] . " SET customers_default_address_id = '" . (int)$address_id . "' WHERE customers_id = '" . (int)$customers_id . "'");
      $db->Execute("UPDATE " . $oosDBTable['customers'] . " SET customers_status = '" . STANDARD_GROUP . "' WHERE customers_id = '" . (int)$customers_id . "'");
      $db->Execute("INSERT INTO " .  $oosDBTable['customers_info'] . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customers_id . "', '0', now())");
    }

    if (SEND_ACCOUNT_MAIL==true && $mode=='APPEND' && $sql_customers_data_array['customers_email_address']!='')  {
      // generate mail for customer if customer=new
      require_once(DIR_WS_CLASSES.'class.phpmailer.php');
      require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');
      require_once(DIR_FS_INC . 'xtc_add_tax.inc.php');
      require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
      require_once(DIR_FS_INC . 'changedataout.inc.php');
      require_once(DIR_FS_INC . 'xtc_href_link.inc.php');
      require_once(DIR_FS_INC . 'xtc_date_long.inc.php');
      require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');

      $smarty = new Smarty;

      //$smarty->assign('language', $check_status['language']);
      $smarty->assign('language', $Lang_folder);

      $smarty->caching = false;
      $smarty->template_dir=DIR_FS_CATALOG.'templates';
      $smarty->compile_dir=DIR_FS_CATALOG.'templates_c';
      $smarty->config_dir=DIR_FS_CATALOG.'lang';
      $smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
      $smarty->assign('logo_path',HTTP_SERVER  . DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
      $smarty->assign('NAME',$sql_customers_data_array['customers_lastname'] . ' ' . $sql_customers_data_array['customers_firstname']);
      $smarty->assign('EMAIL',$sql_customers_data_array['customers_email_address']);
      $smarty->assign('PASSWORD',$pw);
      //$smarty->assign('language', $Lang_folder);
      $smarty->assign('content', $module_content);
      $smarty->caching = false;
      $html_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$Lang_folder.'/create_account_mail.html');
      $txt_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$Lang_folder.'/create_account_mail.txt');

      // send mail with html/txt template
      xtc_php_mail(
      EMAIL_SUPPORT_ADDRESS,
      EMAIL_SUPPORT_NAME ,
      $sql_customers_data_array['customers_email_address'],
      $sql_customers_data_array['customers_lastname'] . ' ' . $sql_customers_data_array['customers_firstname'],
      '',
      EMAIL_SUPPORT_REPLY_ADDRESS,
      EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
      '',
      '',
      EMAIL_SUPPORT_SUBJECT,
      $html_mail ,
      $txt_mail);
    }
    print_xml_status (0, $_POST['action'], 'OK', $mode, 'CUSTOMERS_ID', $customers_id);
  }


  function CustomersErase () {

    $cID  = oosDBPrepareInput($_POST['cID']);

    $db =& oosDBGetConn();
    $oosDBTable = oosDBGetTables();

    $sec_query = $db->Execute("SELECT customers_status FROM ".$oosDBTable['customers']." WHERE customers_id='".intval($cID)."'");
    $sec_data = xtc_db_fetch_array($sec_query);


    if (isset($cID))  {

      $reviews_result = $db->Execute("SELECT reviews_id FROM " . $oosDBTable['reviews'] . " WHERE customers_id = '" . intval($cID) . "'");
      while ($reviews = $reviews_result->fields) {
        $db->Execute("DELETE FROM " . $oosDBTable['reviews_description'] . " WHERE reviews_id = '" . $reviews['reviews_id'] . "'");
        $reviews_result->MoveNext();
      }
      $db->Execute("DELETE FROM " . $oosDBTable['reviews'] . " WHERE customers_id = '" . intval($cID) . "'");

      $db->Execute("DELETE FROM " . $oosDBTable['address_book'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers_info'] . " WHERE customers_info_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers_basket'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers_basket_attributes'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers_wishlist'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers_wishlist_attributes'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['customers_status_history'] . " WHERE customers_id = '" . intval($cID) . "'");
      $db->Execute("DELETE FROM " . $oosDBTable['whos_online'] . " WHERE customer_id = '" . intval($cID) . "'");

      print_xml_status (0, $_POST['action'], 'OK', '', 'SQL_RES1', $res1);
    } else {
      print_xml_status (99, $_POST['action'], 'PARAMETER ERROR', '', '', '');
    }
  }






  $table_has_products_image_medium = true;
  $table_has_products_image_large = false;

  $images_query = $db->Execute(' SHOW COLUMNS FROM '. $oosDBTable['products']);
  while($column = xtc_db_fetch_array($images_query->fields) {
    if ($column['Field'] == 'products_image_medium') {
      $table_has_products_image_medium = true;
    }
    if ($column['Field'] == 'products_image_large') {
      $table_has_products_image_large = true;
    }
  }
  if ($table_has_products_image_medium && $table_has_products_image_large) {
      define('DREI_PRODUKTBILDER', true);
  } else {
      define('DREI_PRODUKTBILDER', false);
  }


  if (LOGGER==true) {
     // log data into db.

  $pdata ='';
  while (list($key, $value) = each($_POST)) {
    if (is_array($value)) {
      while (list($key1, $value1) = each($value)) {
        $pdata .= addslashes($key)."[" . addslashes($key1)."] => ".addslashes($value1)."\\r\\n";
      }
    } else {
      $pdata .= addslashes($key)." => ".addslashes($value)."\\r\\n";
    }
  }

  $gdata ='';
  while (list($key, $value) = each($_GET)) {
    $gdata .= addslashes($key)." => ".addslashes($value)."\\r\\n";
  }

  $db =& oosDBGetConn();
  $oosDBTable = oosDBGetTables();

  $db->Execute("INSERT INTO cao_log
              (date,user,pw,method,action,post_data,get_data) VALUES
              (NOW(),'".$user."','".$password."','".$REQUEST_METHOD."','".$_POST['action']."','".$pdata."','".$gdata."')");
  }


  class upload {
    var $file, $filename, $destination, $permissions, $extensions, $tmp_filename;

    function upload($file = '', $destination = '', $permissions = '777', $extensions = '') {

      $this->set_file($file);
      $this->set_destination($destination);
      $this->set_permissions($permissions);
      $this->set_extensions($extensions);

      if (xtc_not_null($this->file) && xtc_not_null($this->destination)) {
        if ( ($this->parse() == true) && ($this->save() == true) ) {
          return true;
        } else {
          return false;
        }
      }
    }

    function parse() {
      global $messageStack;
      if (isset($_FILES[$this->file])) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      } elseif (isset($_FILES[$this->file])) {

        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      } else {
        $file = array('name' => $GLOBALS[$this->file . '_name'],
                      'type' => $GLOBALS[$this->file . '_type'],
                      'size' => $GLOBALS[$this->file . '_size'],
                      'tmp_name' => $GLOBALS[$this->file]);
      }

      if ( xtc_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
        if (sizeof($this->extensions) > 0) {
          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
            //$messageStack->add_session(ERROR_FILETYPE_NOT_ALLOWED, 'error');

            return false;
          }
        }

        $this->set_file($file);
        $this->set_filename($file['name']);
        $this->set_tmp_filename($file['tmp_name']);

        return $this->check_destination();
      } else {

             //if ($file['tmp_name'] == 'none') $messageStack->add_session(WARNING_NO_FILE_UPLOADED, 'warning');
        return false;
      }
    }

    function save() {
      global $messageStack;

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      // GDlib check
      if (!function_exists(imagecreateFROMgif)) {

        // check if uploaded file = gif
        if ($this->destination==DIR_FS_CATALOG_ORIGINAL_IMAGES) {
            // check if merge image is defined .gif
            if (strstr(PRODUCT_IMAGE_THUMBNAIL_MERGE,'.gif') ||
                strstr(PRODUCT_IMAGE_INFO_MERGE,'.gif') ||
                strstr(PRODUCT_IMAGE_POPUP_MERGE,'.gif')) {

                //$messageStack->add_session(ERROR_GIF_MERGE, 'error');
                return false;

            }
            // check if uploaded image = .gif
            if (strstr($this->filename,'.gif')) {
             //$messageStack->add_session(ERROR_GIF_UPLOAD, 'error');
             return false;
            }

        }

      }

      if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
        chmod($this->destination . $this->filename, $this->permissions);

        //$messageStack->add_session(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');

        return true;
      } else {
        //$messageStack->add_session(ERROR_FILE_NOT_SAVED, 'error');

        return false;
      }
    }

    function set_file($file) {
      $this->file = $file;
    }

    function set_destination($destination) {
      $this->destination = $destination;
    }

    function set_permissions($permissions) {
      $this->permissions = octdec($permissions);
    }

    function set_filename($filename) {
      $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
      $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
      if (xtc_not_null($extensions)) {
        if (is_array($extensions)) {
          $this->extensions = $extensions;
        } else {
          $this->extensions = array($extensions);
        }
      } else {
        $this->extensions = array();
      }
    }

    function check_destination() {
      global $messageStack;

      if (!is_writeable($this->destination)) {
        if (is_dir($this->destination)) {
          //$messageStack->add_session(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
        } else {
          //$messageStack->add_session(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
        }

        return false;
      } else {
        return true;
      }
    }
  }
?>