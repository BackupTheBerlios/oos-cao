<?php
/*******************************************************************************************
*                                                                                          *
*  CAO-Faktura f�r Windows Version 1.2 (http://www.cao-wawi.de)                            *
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
* (c) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Pl�nkers                                      *
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
*  - 02.08.2003 KL MANUFACTURERS_DESCRIPTION  language_id ge�ndert in languages_id         *
*  - 09.08.2003 JP fuer das Modul Banktransfer werden jetzt die daten bei der Bestll-      *
*                  uebermittlung mit ausgegeben                                            *
*  - 10.08.2003 JP Geburtsdatum wird jetzt in den Bestellungen mit uebergeben              *
*  - 18.08.2003 JP Bug bei Products/URL beseitigt                                          *
*  - 18.08.2003 HS Bankdaten werden nur bei Banktransfer ausgelesen                        *
*  - 25.10.2003 RV Kunden-Export hinzugef�gt                                               *
*  - 24.11.2003 HS Fix Kunden-Export - Newsletterexport hinzugef�gt                        *
*  - 01.12.2003 RV Code f�r 3 Produktbilder-Erweiterung hinzugef�gt.                       *
*  - 31.01.2004 JP Resourcenverbrauch minimiert                                            *
*                  tep_set_time_limit ist jetzt per DEFINE zu- und abschaltbar             *
*  - 06.06.2004 JP per DEFINE kann jetzt die Option "3 Produktbilder" geschaltet werden    *
*  - 09.10.2004 RV automatisch Erkennung von 3 Bilder Contrib laut readme                  *
*  - 09.10.2004 RV vereinheitlicher Adress-Export bei Bestellungen und Kunden              *
*  - 09.10.2004 RV Kunden Vor- und Nachname bei Bestellungen getrennt exportieren          *
*  - 09.10.2004 RV SQL-Cleanup                                                             *
*  - 09.10.2004 RV CODE-Cleanup                                                            *
*  - 14.10.2004 RV L�nder bei Bestellungen als ISO-Code                                    *
*  - 03.12.2003 JP Bugfix beim Kunden-Export (Fehlende Felder)                             *
*               XTC  1.1 fixed bug with attributes and products qty > 1                    *
*               XTC  1.2 Updates for xt:C 3.0                                              *
*  - 10.12.2004 JP Anpassungen fuer CAO 1.2.6.x (customers_export, orders_export)          *
*******************************************************************************************/

define('SET_TIME_LITMIT',0);   // use   xtc_set_time_limit(0);
define('CHARSET','iso-8859-1');
define('LANG_ID',2);
$version_nr    = '1.37';
$version_datum = '2004.12.10';

// falls die MWST vom shop vertauscht wird, hier false setzen.
define('SWITCH_MWST',true);

define ('LOGGER',false); // Um das Loggen einzuschalten false durch true ersetzen.

// Steuer einstellungen f�r CAO-Faktura

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



require('../includes/application_top_export.php');


// check permissions for XML-Access

$user=$_GET['user'];
$password=$_GET['password'];

if (substr($password,0,2)=='%%') {
 $password=md5(substr($password,2,40));
}

if (LOGGER==true) 
{
	// log data into db.

	$pdata ='';
	while (list($key, $value) = each($_POST))
	{
   	$pdata .= addslashes($key)." => ".addslashes($value)."\\r\\n";
	} 

	$gdata ='';
	while (list($key, $value) = each($_GET))
	{
   	$gdata .= addslashes($key)." => ".addslashes($value)."\\r\\n";
	} 

	xtc_db_query("INSERT INTO cao_log
              (date,user,pw,method,action,post_data,get_data) VALUES
              (NOW(),'".$user."','".$password."','".$REQUEST_METHOD."','".$_POST['action']."','".$pdata."','".$gdata."')");
}


if ($user!='' and $password!='') {

	require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
	require_once(DIR_FS_INC . 'xtc_redirect.inc.php');
	require_once(DIR_FS_INC . 'xtc_rand.inc.php');

	// security  1.check if admin user with this mailadress exits, and got access to xml-export
	//           2.check if pasword = true

	$check_customer_query=xtc_db_query("select customers_id,
                           customers_status,
                           customers_password
                           from " . TABLE_CUSTOMERS . " where
                           customers_email_address = '" . $user . "'");


    if (!xtc_db_num_rows($check_customer_query)) {
      xtc_redirect('xml_export.php?error=WRONG LOGIN&code=101');
    } else {
      $check_customer = xtc_db_fetch_array($check_customer_query);
      // check if customer is Admin
      if ($check_customer['customers_status']!='0') xtc_redirect('xml_export.php?error=WRONG LOGIN&code=101');
      // check if Admin is allowed to access xml_export
      $access_query=xtc_db_query("SELECT
                                  xml_export
                                  from admin_access
                                  WHERE customers_id='".$check_customer['customers_id']."'");
      $access_data = xtc_db_fetch_array($access_query);
      if ($access_data['xml_export']!=1)  xtc_redirect('xml_export.php?error=WRONG LOGIN&code=101');
      if ($check_customer['customers_password'] != $password) {
        xtc_redirect('xml_export.php?error=WRONG PASSWORD&code=102');
      } else {
      }


      header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer ge�ndert
      header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
      header ("Pragma: no-cache"); // HTTP/1.0
      header ("Content-type: text/xml");


  if ($_GET['action'])
  {
    switch ($_GET['action'])
    {
//---------------------------------------------------------------------------------------------------------
      case 'categories_export':
//---------------------------------------------------------------------------------------------------------
      if (SET_TIME_LITMIT==1) xtc_set_time_limit(0);

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<CATEGORIES>' . "\n";
                  
        echo $schema;
                  
        $cat_query = xtc_db_query("select categories_id, categories_image, parent_id, sort_order, date_added, last_modified ".
        	                         " from " . TABLE_CATEGORIES . " order by parent_id, categories_id");
        while ($cat = xtc_db_fetch_array($cat_query))
        {
          $schema  = '<CATEGORIES_DATA>' . "\n" .
                     '<ID>' . $cat['categories_id'] . '</ID>' . "\n" .
                     '<PARENT_ID>' . $cat['parent_id'] . '</PARENT_ID>' . "\n" .
                     '<IMAGE_URL>' . htmlspecialchars($cat['categories_image']) . '</IMAGE_URL>' . "\n" .
                     '<SORT_ORDER>' . $cat['sort_order'] . '</SORT_ORDER>' . "\n" .
                     '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                     '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";


          $detail_query = xtc_db_query("select categories_id, language_id,
                                               categories_name,
                                               categories_heading_title,
                                               categories_description,
                                               categories_meta_title,
                                               categories_meta_description,
                                               categories_meta_keywords, " . TABLE_LANGUAGES . ".code as lang_code, " . TABLE_LANGUAGES . ".name as lang_name from " . TABLE_CATEGORIES_DESCRIPTION . "," . TABLE_LANGUAGES .
												   " where " . TABLE_CATEGORIES_DESCRIPTION . ".categories_id=" . $cat['categories_id'] . " and " . TABLE_LANGUAGES . ".languages_id=" . TABLE_CATEGORIES_DESCRIPTION . ".language_id");
			
	       while ($details = xtc_db_fetch_array($detail_query))
          {
               $schema .= "<CATEGORIES_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["lang_code"] . "' NAME='" . $details["lang_name"] . "'>\n";
         		$schema .= "<NAME>" . htmlspecialchars($details["categories_name"]) . "</NAME>" . "\n";
                $schema .= "<HEADING_TITLE>" . htmlspecialchars($details["categories_heading_title"]) . "</HEADING_TITLE>" . "\n";
                $schema .= "<DESCRIPTION>" . htmlspecialchars($details["categories_description"]) . "</DESCRIPTION>" . "\n";
                $schema .= "<META_TITLE>" . htmlspecialchars($details["categories_meta_title"]) . "</META_TITLE>" . "\n";
                $schema .= "<META_DESCRIPTION>" . htmlspecialchars($details["categories_meta_description"]) . "</META_DESCRIPTION>" . "\n";
                $schema .= "<META_KEYWORDS>" . htmlspecialchars($details["categories_meta_keywords"]) . "</META_KEYWORDS>" . "\n";
         		$schema .= "</CATEGORIES_DESCRIPTION>\n";
          }
          
          // Produkte in dieser Categorie auflisten
          
          
          $prod2cat_query = xtc_db_query("select categories_id, products_id from " . TABLE_PRODUCTS_TO_CATEGORIES .
                                         " where categories_id='" . $cat['categories_id'] . "'");
                                       
          while ($prod2cat = xtc_db_fetch_array($prod2cat_query))
          {
            $schema .="<PRODUCTS ID='" . $prod2cat["products_id"] ."'></PRODUCTS>" . "\n";
          }
          
          $schema .= '</CATEGORIES_DATA>' . "\n";
          
          echo $schema;
        }
        $schema = '</CATEGORIES>' . "\n";
        
        echo $schema;

        exit;
//---------------------------------------------------------------------------------------------------------        
      case 'manufacturers_export':
//---------------------------------------------------------------------------------------------------------

        if (SET_TIME_LITMIT==1) xtc_set_time_limit(0);

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<MANUFACTURERS>' . "\n";
                  
        echo $schema;
                  
        $cat_query = xtc_db_query("select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified ".
        	                         " from " . TABLE_MANUFACTURERS . " order by manufacturers_id");
        while ($cat = xtc_db_fetch_array($cat_query))
        {
          $schema  = '<MANUFACTURERS_DATA>' . "\n" .
                     '<ID>' . $cat['manufacturers_id'] . '</ID>' . "\n" .
                     '<NAME>' . htmlspecialchars($cat['manufacturers_name']) . '</NAME>' . "\n" .
                     '<IMAGE>' . htmlspecialchars($cat['manufacturers_image']) . '</IMAGE>' . "\n" .
                     '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                     '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";
                     
          $sql = "select 
                    manufacturers_id, " . 
                    TABLE_MANUFACTURERS_INFO . ".languages_id, 
                    manufacturers_url, 
                    url_clicked, 
                    date_last_click, " . 
                    TABLE_LANGUAGES . ".code as lang_code, " . 
                    TABLE_LANGUAGES . ".name as lang_name 
                  from " . 
                    TABLE_MANUFACTURERS_INFO . "," . 
                    TABLE_LANGUAGES . " 
                  where " . 
                    TABLE_MANUFACTURERS_INFO . ".manufacturers_id=" . $cat['manufacturers_id'] . " and " . 
                    TABLE_LANGUAGES . ".languages_id=" . TABLE_MANUFACTURERS_INFO . ".languages_id";
                    
          
          $detail_query = xtc_db_query($sql);

	       while ($details = xtc_db_fetch_array($detail_query))
          {
               $schema .= "<MANUFACTURERS_DESCRIPTION ID='" . $details["languages_id"] ."' CODE='" . $details["lang_code"] . "' NAME='" . $details["lang_name"] . "'>\n";
         		$schema .= "<URL>" . htmlspecialchars($details["manufacturers_url"]) . "</URL>" . "\n" ;
         		$schema .= "<URL_CLICK>" . $details["url_clicked"] . "</URL_CLICK>" . "\n" ;
         		$schema .= "<DATE_LAST_CLICK>" . $details["date_last_click"] . "</DATE_LAST_CLICK>" . "\n" ;
         		$schema .= "</MANUFACTURERS_DESCRIPTION>\n";
          }
          
          $schema .= '</MANUFACTURERS_DATA>' . "\n";
          echo $schema;
        }
        $schema = '</MANUFACTURERS>' . "\n";
        
        echo $schema;
        exit;
//---------------------------------------------------------------------------------------------------------
      case 'orders_export':
//---------------------------------------------------------------------------------------------------------
        $order_from = xtc_db_prepare_input($_GET['order_from']);
        $order_to = xtc_db_prepare_input($_GET['order_to']);
        $order_status = xtc_db_prepare_input($_GET['order_status']);
        
        if (SET_TIME_LITMIT==1) xtc_set_time_limit(0); 

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<ORDER>' . "\n";
        
        echo $schema;
        
        $sql ="select * from " . TABLE_ORDERS . " where orders_id >= '" . xtc_db_input($order_from) . "'";
        if (!isset($order_status) && !isset($order_from)) 
        {
        	 $order_status = 1;
        	 $sql .= "and orders_status = " . $order_status;
        }
        if ($order_status!='') {

          $sql .= " and orders_status = " . $order_status;
        }
        $orders_query = xtc_db_query($sql);
        
        while ($orders = xtc_db_fetch_array($orders_query))
        {

          // Geburtsdatum laden
          $cust_sql = "select * from " . TABLE_CUSTOMERS . " where customers_id=" . $orders['customers_id'];
          $cust_query = xtc_db_query ($cust_sql);
          if (($cust_query) && ($cust_data = xtc_db_fetch_array($cust_query)))
          {
            $cust_dob = $cust_data['customers_dob'];
            $cust_gender = $cust_data['customers_gender'];
          } else {
            
            $cust_dob = '';
            $cust_gender = '';
          }


          if ($orders['billing_company']=='') $orders['billing_company']=$orders['delivery_company'];
          if ($orders['billing_name']=='')  $orders['billing_name']=$orders['delivery_name'];
          if ($orders['billing_street_address']=='') $orders['billing_street_address']=$orders['delivery_street_address'];
          if ($orders['billing_postcode']=='')  $orders['billing_postcode']=$orders['delivery_postcode'];
          if ($orders['billing_city']=='')  $orders['billing_city']=$orders['delivery_city'];
          if ($orders['billing_suburb']=='') $orders['billing_suburb']=$orders['delivery_suburb'];
          if ($orders['billing_state']=='')  $orders['billing_state']=$orders['delivery_state'];
          if ($orders['billing_country']=='')  $orders['billing_country']=$orders['delivery_country'];



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
  	          $bank_sql = "select * from banktransfer where orders_id = " . $orders['orders_id'];
              $bank_query = xtc_db_query($bank_sql);
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
                     
          $sql = "select 
                   orders_products_id,
                   allow_tax, 
                   products_id, 
                   products_model, 
                   products_name, 
                   final_price, 
                   products_tax, 
                   products_quantity 
                 from " . 
                   TABLE_ORDERS_PRODUCTS . " 
                 where 
                   orders_id = '" . $orders['orders_id'] . "'";
                     
          $products_query = xtc_db_query($sql);
          while ($products = xtc_db_fetch_array($products_query))
          {
          if ($products['allow_tax']==1) $products['final_price']=$products['final_price']/(1+$products['products_tax']*0.01);
            $schema .= '<PRODUCT>' . "\n" .
                       '<PRODUCTS_ID>' . $products['products_id'] . '</PRODUCTS_ID>' . "\n" .
                       '<PRODUCTS_QUANTITY>' . $products['products_quantity'] . '</PRODUCTS_QUANTITY>' . "\n" .
                       '<PRODUCTS_MODEL>' . htmlspecialchars($products['products_model']) . '</PRODUCTS_MODEL>' . "\n" .
                       '<PRODUCTS_NAME>' . htmlspecialchars($products['products_name']) . '</PRODUCTS_NAME>' . "\n" .
                       '<PRODUCTS_PRICE>' . $products['final_price']/$products['products_quantity'] . '</PRODUCTS_PRICE>' . "\n" .
                       '<PRODUCTS_TAX>' . $products['products_tax'] . '</PRODUCTS_TAX>' . "\n".
                       '<PRODUCTS_TAX_FLAG>' . $products['allow_tax'] . '</PRODUCTS_TAX_FLAG>' . "\n";

            
            $attributes_query = xtc_db_query("select products_options, products_options_values, options_values_price, price_prefix�from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" .$orders['orders_id'] . "' and orders_products_id = '" . $products['orders_products_id'] . "'");
            if (xtc_db_num_rows($attributes_query))
            {
              while ($attributes = xtc_db_fetch_array($attributes_query))
              {
              require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
              $attributes_model =xtc_get_attributes_model($products['products_id'],$attributes['products_options_values']);
                $schema .= '<OPTION>' . "\n" .
                           '<PRODUCTS_OPTIONS>' .  htmlspecialchars($attributes['products_options']) . '</PRODUCTS_OPTIONS>' . "\n" .
                           '<PRODUCTS_OPTIONS_VALUES>' .  htmlspecialchars($attributes['products_options_values']) . '</PRODUCTS_OPTIONS_VALUES>' . "\n" .
                           '<PRODUCTS_OPTIONS_MODEL>'.$attributes_model.'</PRODUCTS_OPTIONS_MODEL>'. "\n".
                           '<PRODUCTS_OPTIONS_PRICE>' .  $attributes['price_prefix'] . ' ' . $attributes['options_values_price'] . '</PRODUCTS_OPTIONS_PRICE>' . "\n" .
                           '</OPTION>' . "\n";
              }
            }            
            $schema .=  '</PRODUCT>' . "\n";

          }
		    $schema .= '</ORDER_PRODUCTS>' . "\n";                     
          $schema .= '<ORDER_TOTAL>' . "\n";
          
          $totals_query = xtc_db_query("select title, value, class, sort_order from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . $orders['orders_id'] . "' order by sort_order");
          while ($totals = xtc_db_fetch_array($totals_query))
          {
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
          }
          $schema .= '</ORDER_TOTAL>' . "\n";
          
          $sql = "select 
                    comments 
                  from " . 
                    TABLE_ORDERS_STATUS_HISTORY . " 
                  where 
                    orders_id = '" . $orders['orders_id'] . "' and 
                    orders_status_id = '" . $orders['orders_status'] . "' ";
          
          $comments_query = xtc_db_query($sql);
          if ($comments =  xtc_db_fetch_array($comments_query)) {
            $schema .=  '<ORDER_COMMENTS>' . htmlspecialchars($comments['comments']) . '</ORDER_COMMENTS>' . "\n";
          }
          $schema .= '</ORDER_INFO>' . "\n\n";
          echo $schema;
        }
        $schema = '</ORDER>' . "\n\n";
        
        echo $schema;
        exit;

//---------------------------------------------------------------------------------------------------------
      case 'products_export':
//---------------------------------------------------------------------------------------------------------      

        if (SET_TIME_LITMIT==1) xtc_set_time_limit(0);

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<PRODUCTS>' . "\n";
        echo $schema;
                  
        $sql = "select products_id,products_fsk18, products_quantity, products_model, products_image, products_price, " .
               "products_date_added, products_last_modified, products_date_available, products_weight, " .
               "products_status, products_tax_class_id, manufacturers_id, products_ordered from " . TABLE_PRODUCTS;
               
        $from = xtc_db_prepare_input($_GET['products_from']);
        $anz  = xtc_db_prepare_input($_GET['products_count']);
        if (isset($from))
        {
          if (!isset($anz)) $anz=1000;
          
          $sql .= " limit " . $from . "," . $anz;
        }

        $orders_query = xtc_db_query($sql);
        while ($products = xtc_db_fetch_array($orders_query))
        {
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

          require_once(DIR_FS_INC .'xtc_get_customers_statuses.inc.php');
          $customers_status=xtc_get_customers_statuses();
          for ($i=1,$n=sizeof($customers_status);$i<$n; $i++) {
            if ($customers_status[$i]['id']!=0) {
                 $schema .= "<PRODUCT_GROUP_PRICES ID='".$customers_status[$i]['id']."' NAME='".$customers_status[$i]['text']. "'>". "\n";
                 $group_price_query=xtc_db_query("SELECT
                                                  * FROM personal_offers_by_customers_status_".$customers_status[$i]['id']);
                 while ($group_price_data=xtc_db_fetch_array($group_price_query)) {
                    //if ($group_price_data['personal_offer']!='0') {
                    $schema .='<PRICE_ID>'.$group_price_data['price_id'].'</PRICE_ID>';
                    $schema .='<PRODUCT_ID>'.$group_price_data['products_id'].'</PRODUCT_ID>';
                    $schema .='<QTY>'.$group_price_data['quantity'].'</QTY>';
                    $schema .='<PRICE>'.$group_price_data['personal_offer'].'</PRICE>';
                    //}
                 }
            $schema .= "</PRODUCT_GROUP_PRICES>\n";
            }
          }
          // products Options

          $products_attributes='';
          $products_options_data=array();
          $products_options_array =array();
          $products_attributes_query = xtc_db_query("select count(*) as total
                                                     from " . TABLE_PRODUCTS_OPTIONS . "
                                                     popt, " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                     patrib where
                                                     patrib.products_id='" . $products['products_id'] . "'
                                                     and patrib.options_id = popt.products_options_id
                                                     and popt.language_id = '" . LANG_ID . "'");

          $products_attributes = xtc_db_fetch_array($products_attributes_query);

          if ($products_attributes['total'] > 0) {
            $products_options_name_query = xtc_db_query("select distinct
                                                         popt.products_options_id,
                                                         popt.products_options_name
                                                         from " . TABLE_PRODUCTS_OPTIONS . "
                                                         popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
                                                         where patrib.products_id='" . $products['products_id'] . "'
                                                         and patrib.options_id = popt.products_options_id
                                                         and popt.language_id = '" . LANG_ID . "' order by popt.products_options_name");
            $row = 0;
            $col = 0;
            $products_options_data=array();
            while ($products_options_name = xtc_db_fetch_array($products_options_name_query)) {
              $selected = 0;
              $products_options_array = array();
              $products_options_data[$row]=array(
                       'NAME'=>$products_options_name['products_options_name'],
                       'ID' => $products_options_name['products_options_id'],
                       'DATA' =>'');
              $products_options_query = xtc_db_query("select
                                                      pov.products_options_values_id,
                                                      pov.products_options_values_name,
                                                      pa.attributes_model,
                                                      pa.options_values_price,
                                                      pa.options_values_weight,
                                                      pa.price_prefix,
                                                      pa.weight_prefix,
                                                      pa.attributes_stock,
                                                      pa.attributes_model
                                                      from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                      pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                                      pov where
                                                      pa.products_id = '" . $products['products_id'] . "'
                                                      and pa.options_id = '" . $products_options_name['products_options_id'] . "' and
                                                      pa.options_values_id = pov.products_options_values_id and
                                                      pov.language_id = '" . LANG_ID . "' order by pov.products_options_values_name");
              $col = 0;
              while ($products_options = xtc_db_fetch_array($products_options_query)) {
               $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
               if ($products_options['options_values_price'] != '0') {
                 $products_options_array[sizeof($products_options_array)-1]['text'] .=  ' '.$products_options['price_prefix'].' '.$products_options['options_values_price'].' '.$_SESSION['currency'] ;
               }
               $price='';
               $products_options_data[$row]['DATA'][$col]=array(
                                    'ID' => $products_options['products_options_values_id'],
                                    'TEXT' =>$products_options['products_options_values_name'],
                                    'MODEL' =>$products_options['attributes_model'],
                                    'WEIGHT' =>$products_options['options_values_weight'],
                                    'PRICE' =>$products_options['options_values_price'],
                                    'WEIGHT_PREFIX' =>$products_options['weight_prefix'],
                                    'PREFIX' =>$products_options['price_prefix']);
               $col++;
              }
              $row++;
            }
          }
          if (sizeof($products_options_data)!=0) {
            for ($i=0,$n=sizeof($products_options_data);$i<$n;$i++) {
             $schema .= "<PRODUCT_ATTRIBUTES NAME='".$products_options_data[$i]['NAME']."'>";

             for ($ii=0,$nn=sizeof($products_options_data[$i]['DATA']);$ii<$nn;$ii++) {

               $schema .= '<OPTION>';
               $schema .= '<ID>'.$products_options_data[$i]['DATA'][$ii]['ID'].'</ID>';
               $schema .= '<MODEL>'.$products_options_data[$i]['DATA'][$ii]['MODEL'].'</MODEL>';
               $schema .= '<TEXT>'.$products_options_data[$i]['DATA'][$ii]['TEXT'].'</TEXT>';
               $schema .= '<WEIGHT>'.$products_options_data[$i]['DATA'][$ii]['WEIGHT'].'</WEIGHT>';
               $schema .= '<PRICE>'.$products_options_data[$i]['DATA'][$ii]['PRICE'].'</PRICE>';
               $schema .= '<WEIGHT_PREFIX>'.$products_options_data[$i]['DATA'][$ii]['WEIGHT_PREFIX'].'</WEIGHT_PREFIX>';
               $schema .= '<PREFIX>'.$products_options_data[$i]['DATA'][$ii]['PREFIX'].'</PREFIX>';
               $schema .= '</OPTION>';
             }
             $schema .= '</PRODUCT_ATTRIBUTES>';
            }
          }
          // group prices
          require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');
          if (SWITCH_MWST=='true') {
               // switch IDs
               if ($products['products_tax_class_id']==1) $products['products_tax_class_id']=2;
               if ($products['products_tax_class_id']==2) $products['products_tax_class_id']=1;
          }
          $schema .=
                     '<PRODUCT_WEIGHT>' . $products['products_weight'] . '</PRODUCT_WEIGHT>' . "\n" .
                     '<PRODUCT_STATUS>' . $products['products_status'] . '</PRODUCT_STATUS>' . "\n" .
                     '<PRODUCT_TAX_CLASS_ID>' . $products['products_tax_class_id'] . '</PRODUCT_TAX_CLASS_ID>' . "\n"  .
                     '<PRODUCT_TAX_RATE>' . xtc_get_tax_rate($products['products_tax_class_id']) . '</PRODUCT_TAX_RATE>' . "\n"  .
                     '<MANUFACTURERS_ID>' . $products['manufacturers_id'] . '</MANUFACTURERS_ID>' . "\n" .
                     '<PRODUCT_DATE_ADDED>' . $products['products_date_added'] . '</PRODUCT_DATE_ADDED>' . "\n" .
                     '<PRODUCT_LAST_MODIFIED>' . $products['products_last_modified'] . '</PRODUCT_LAST_MODIFIED>' . "\n" .
                     '<PRODUCT_DATE_AVAILABLE>' . $products['products_date_available'] . '</PRODUCT_DATE_AVAILABLE>' . "\n" .
                     '<PRODUCTS_ORDERED>' . $products['products_ordered'] . '</PRODUCTS_ORDERED>' . "\n" ;
          
          $categories_query=xtc_db_query("SELECT
                                          categories_id
                                          FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                          where products_id='".$products['products_id']."'");
          $categories=array();
          while ($categories_data=xtc_db_fetch_array($categories_query)) {
            $categories[]=$categories_data['categories_id'];
          }
          $categories=implode(',',$categories);

          $schema .= '<PRODUCTS_CATEGORIES>' . $categories . '</PRODUCTS_CATEGORIES>' . "\n" ;

          $detail_query = xtc_db_query("select
                                        products_id,
                                        language_id,
                                        products_name, " . TABLE_PRODUCTS_DESCRIPTION .
          								       ".products_description,
                                        products_short_description,
                                        products_meta_title,
                                        products_meta_description,
                                        products_meta_keywords,
                                        products_url,
                                        name as language_name, code as language_code " .
 												    "from " . TABLE_PRODUCTS_DESCRIPTION . ", " . TABLE_LANGUAGES . " " .
												    "where " . TABLE_PRODUCTS_DESCRIPTION . ".language_id=" . TABLE_LANGUAGES . ".languages_id " .
												    "and " . TABLE_PRODUCTS_DESCRIPTION . ".products_id=" . $products['products_id']);



	       while ($details = xtc_db_fetch_array($detail_query))
          {
         		$schema .= "<PRODUCT_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["language_code"] . "' NAME='" . $details["language_name"] . "'>\n";

         		if ($details["products_name"] !='Array')
         		{
         			$schema .= "<NAME>" . htmlspecialchars($details["products_name"]) . "</NAME>" . "\n" ;
         		}
         		$schema .=  "<URL>" . htmlspecialchars($details["products_url"]) . "</URL>" . "\n" ;

         		$prod_details = $details["products_description"];
         		if ($prod_details != 'Array')
         		{
         			$schema .=  "<DESCRIPTION>" . htmlspecialchars($details["products_description"]) . "</DESCRIPTION>" . "\n";
                    $schema .=  "<SHORT_DESCRIPTION>" . htmlspecialchars($details["products_short_description"]) . "</SHORT_DESCRIPTION>" . "\n";
                    $schema .=  "<META_TITLE>" . htmlspecialchars($details["products_meta_title"]) . "</META_TITLE>" . "\n";
                    $schema .=  "<META_DESCRIPTION>" . htmlspecialchars($details["products_meta_description"]) . "</META_DESCRIPTION>" . "\n";
                    $schema .=  "<META_KEYWORDS>" . htmlspecialchars($details["products_meta_keywords"]) . "</META_KEYWORDS>" . "\n";
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

//---------------------------------------------------------------------------------------------------------
  case 'customers_export':
//---------------------------------------------------------------------------------------------------------
		if (SET_TIME_LITMIT==1) xtc_set_time_limit(0);

		$schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
			'<CUSTOMERS>' . "\n";
			
		echo $schema;
			
		$from = xtc_db_prepare_input($_GET['customers_from']);
		$anz  = xtc_db_prepare_input($_GET['customers_count']);

		$address_query = "select
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
                      from 
                        " . TABLE_CUSTOMERS . " c, 
                        " . TABLE_CUSTOMERS_INFO . " ci, 
                        " . TABLE_ADDRESS_BOOK . " a , 
                        " . TABLE_COUNTRIES . " co
                      where
                        c.customers_id = ci.customers_info_id AND
                        c.customers_id = a.customers_id AND
                        c.customers_default_address_id = a.address_book_id AND
                        a.entry_country_id  = co.countries_id";
		if (isset($from)) 
		{
			if (!isset($anz)) $anz = 1000;
			$address_query.= " limit " . $from . "," . $anz;
		}
		$address_result = xtc_db_query($address_query);
		
		while ($address = xtc_db_fetch_array($address_result))  {
      
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
		}
    
		$schema = '</CUSTOMERS>' . "\n\n";
		echo $schema;
		exit;

//---------------------------------------------------------------------------------------------------------
  // Newsletter export
  case 'customers_newsletter_export':
//---------------------------------------------------------------------------------------------------------

		if (SET_TIME_LITMIT==1) xtc_set_time_limit(0);

		$schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
			'<CUSTOMERS>' . "\n".
			
		$from = xtc_db_prepare_input($_GET['customers_from']);
		$anz  = xtc_db_prepare_input($_GET['customers_count']);
		
		$address_query = "select *
                      from " . TABLE_CUSTOMERS. " 
                      where customers_newsletter = 1
                     ";
		if (isset($from)) {
			if (!isset($anz)) $anz = 1000;
			$address_query.= " limit " . $from . "," . $anz;
		}
		$address_result = xtc_db_query($address_query);
		
		while ($address = xtc_db_fetch_array($address_result))
		{
			$schema .= '<CUSTOMERS_DATA>' . "\n";
      	$schema .= '<CUSTOMERS_ID>' . $address['customers_id'] . '</CUSTOMERS_ID>' . "\n";
        $schema .= '<CUSTOMERS_CID>' . $address['customers_cid'] . '</CUSTOMERS_CID>' . "\n";
      	$schema .= '<CUSTOMERS_GENDER>' . $address['customers_gender'] . '</CUSTOMERS_GENDER>' . "\n";
      	$schema .= '<CUSTOMERS_FIRSTNAME>' . $address['customers_firstname'] . '</CUSTOMERS_FIRSTNAME>' . "\n";
      	$schema .= '<CUSTOMERS_LASTNAME>' . $address['customers_lastname'] . '</CUSTOMERS_LASTNAME>' . "\n";
      	$schema .= '<CUSTOMERS_EMAIL_ADDRESS>' . $address['customers_email_address'] . '</CUSTOMERS_EMAIL_ADDRESS>' . "\n";
			$schema .= '</CUSTOMERS_DATA>' . "\n";		
		}
		
		$schema .= '</CUSTOMERS>' . "\n\n";
		echo $schema;
		exit;
	
//---------------------------------------------------------------------------------------------------------
   case 'version':
//---------------------------------------------------------------------------------------------------------
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
     }
  }

  }
  } else {

  header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer ge�ndert
  header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header ("Pragma: no-cache"); // HTTP/1.0
  header ("Content-type: text/xml");

  if ($_GET['error']=='') $_GET['error']='NO PASSWORD OR USERNAME';
  if ($_GET['code']=='') $_GET['code']='100';

  $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
               '<STATUS>
               <STATUS_DATA>
               <CODE>'.$_GET['code'].'</CODE>
               <MESSAGE>'.$_GET['error'].'</MESSAGE>
               </STATUS_DATA>
               </STATUS>';

  echo $schema;

 }

?>