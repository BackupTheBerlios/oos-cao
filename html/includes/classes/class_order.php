<?php
/* ----------------------------------------------------------------------
   $Id: class_order.php,v 1.2 2005/05/26 22:16:13 r23 Exp $
   ----------------------------------------------------------------------
   Contribution based on:  

   File: class_order.php,v 1.2 2005/01/11 10:07:16 r23 
   ----------------------------------------------------------------------
   OOS [OSIS Online Shop]
   http://www.oos-shop.de/

   Copyright (c) 2003 - 2004 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: order.php,v 1.29 2003/02/11 21:13:39 dgw_ 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  /** ensure this file is being included by a parent file */
  defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );

  class order {
    var $info;
    var $totals;
    var $products;
    var $customer;
    var $delivery;
    var $content_type;

    function order($order_id = '') {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      if (oosNotNull($order_id)) {
        $this->query($order_id);
      } else {
        $this->cart();
      }
    }

    function query($order_id) {

      $order_id = oosDBPrepareInput($order_id);    
      
      $db =& oosDBGetConn();
      $oosDBTable = oosDBGetTables();
      $language = oosVarPrepForOS($_SESSION['language']);

      $sql = "SELECT 
                  customers_id, customers_name, customers_company, customers_street_address, 
                  customers_suburb, customers_city, customers_postcode, customers_state, 
                  customers_country, customers_telephone, customers_email_address, 
                  customers_address_format_id, delivery_name, delivery_company, 
                  delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, 
                  delivery_state, delivery_country, delivery_address_format_id, billing_name, 
                  billing_company, billing_street_address, billing_suburb, billing_city, 
                  billing_postcode, billing_state, billing_country, billing_address_format_id, 
                  payment_method, cc_type, cc_owner, cc_number, cc_expires, currency, currency_value, 
                  date_purchased, orders_status, last_modified 
              FROM 
                  " . $oosDBTable['orders'] . " 
              WHERE 
                  orders_id = '" . oosDBInput($order_id) . "'";
      $order_result = $db->Execute($sql);
      $order = $order_result->fields;
 
      $sql = "SELECT title, text 
              FROM " . $oosDBTable['orders_total'] . " 
              WHERE orders_id = '" . oosDBInput($order_id) . "' 
              ORDER BY sort_order";
      $totals_result = $db->Execute($sql);
      while ($totals = $totals_result->fields) {
        $this->totals[] = array('title' => $totals['title'],
                                'text' => $totals['text']);
        $totals_result->MoveNext();
      }
      
      $sql = "SELECT text 
              FROM " . $oosDBTable['orders_total'] . " 
              WHERE orders_id = '" . oosDBInput($order_id) . "' 
                AND class = 'ot_total'";
      $order_total_result = $db->Execute($sql);
      $order_total = $order_total_result->fields;
      
      $sql = "SELECT title 
              FROM " . $oosDBTable['orders_total'] . " 
              WHERE orders_id = '" . oosDBInput($order_id) . "' 
                AND class = 'ot_shipping'";
      $shipping_method_result = $db->Execute($sql);
      $shipping_method = $shipping_method_result->fields;
      
      $sql = "SELECT orders_status_name 
              FROM " . $oosDBTable['orders_status'] . " 
              WHERE orders_status_id = '" . $order['orders_status'] . "' 
                AND orders_language = '" .  oosDBInput($language) . "'";
      $order_status_result = $db->Execute($sql);
      $order_status = $order_status_result->fields;

      $this->info = array('currency' => $order['currency'],
                          'currency_value' => $order['currency_value'],
                          'payment_method' => $order['payment_method'],
                          'cc_type' => $order['cc_type'],
                          'cc_owner' => $order['cc_owner'],
                          'cc_number' => $order['cc_number'],
                          'cc_expires' => $order['cc_expires'],
                          'date_purchased' => $order['date_purchased'],
                          'orders_status' => $order_status['orders_status_name'],
                          'last_modified' => $order['last_modified'],
                          'total' => strip_tags($order_total['text']),
                          'shipping_method' => ((substr($shipping_method['title'], -1) == ':') ? substr(strip_tags($shipping_method['title']), 0, -1) : strip_tags($shipping_method['title'])));

      $this->customer = array('id' => $order['customers_id'],
                              'name' => $order['customers_name'],
                              'company' => $order['customers_company'],
                              'street_address' => $order['customers_street_address'],
                              'suburb' => $order['customers_suburb'],
                              'city' => $order['customers_city'],
                              'postcode' => $order['customers_postcode'],
                              'state' => $order['customers_state'],
                              'country' => $order['customers_country'],
                              'format_id' => $order['customers_address_format_id'],
                              'telephone' => $order['customers_telephone'],
                              'email_address' => $order['customers_email_address']);

      $this->delivery = array('name' => $order['delivery_name'],
                              'company' => $order['delivery_company'],
                              'street_address' => $order['delivery_street_address'],
                              'suburb' => $order['delivery_suburb'],
                              'city' => $order['delivery_city'],
                              'postcode' => $order['delivery_postcode'],
                              'state' => $order['delivery_state'],
                              'country' => $order['delivery_country'],
                              'format_id' => $order['delivery_address_format_id']);

      if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
        $this->delivery = false;
      }

      $this->billing = array('name' => $order['billing_name'],
                             'company' => $order['billing_company'],
                             'street_address' => $order['billing_street_address'],
                             'suburb' => $order['billing_suburb'],
                             'city' => $order['billing_city'],
                             'postcode' => $order['billing_postcode'],
                             'state' => $order['billing_state'],
                             'country' => $order['billing_country'],
                             'format_id' => $order['billing_address_format_id']);

      $index = 0;
      
      $sql = "SELECT 
                  orders_products_id, products_id, products_name, products_model,
                  products_ean, products_serial_number, products_price, products_tax,
                  products_quantity, final_price 
              FROM 
                  " . $oosDBTable['orders_products'] . " 
              WHERE 
                  orders_id = '" . oosDBInput($order_id) . "'";
      $orders_products_result = $db->Execute($sql);
      while ($orders_products = $orders_products_result->fields) {
        $this->products[$index] = array('qty' => $orders_products['products_quantity'],
	                                'id' => $orders_products['products_id'],
                                        'name' => $orders_products['products_name'],
                                        'model' => $orders_products['products_model'],
                                        'ean' => $orders_products['products_ean'],
                                        'serial_number' => $orders_products['products_serial_number'],
                                        'tax' => $orders_products['products_tax'],
                                        'price' => $orders_products['products_price'],
                                        'final_price' => $orders_products['final_price']);

        $subindex = 0;
        $sql = "SELECT products_options, products_options_values, options_values_price, price_prefix 
                FROM " . $oosDBTable['orders_products_attributes'] . " 
                WHERE orders_id = '" . oosDBInput($order_id) . "' 
                  AND orders_products_id = '" . $orders_products['orders_products_id'] . "'";
        $attributes_result = $db->Execute($sql);
        if ($attributes_result->RecordCount()) {
          while ($attributes = $attributes_result->fields) {
            $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options'],
                                                                     'value' => $attributes['products_options_values'],
                                                                     'prefix' => $attributes['price_prefix'],
                                                                     'price' => $attributes['options_values_price']);

            $subindex++;
            $attributes_result->MoveNext();
          }
        }

        $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

        $index++;
        
        $orders_products_result->MoveNext();
      }
    }

    function cart() {
      global $currency, $currencies;

      $this->content_type = $_SESSION['cart']->get_content_type();
      
      $db =& oosDBGetConn();
      $oosDBTable = oosDBGetTables();     
      $language = oosVarPrepForOS($_SESSION['language']);

      
      $sql = "SELECT 
                  c.customers_firstname, c.customers_lastname, c.customers_telephone, c.customers_email_address, 
                  ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, 
                  ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, 
                  co.countries_iso_code_3, co.address_format_id, ab.entry_state 
              FROM 
                  " . $oosDBTable['customers'] . " c, 
                  " . $oosDBTable['address_book'] . " ab LEFT JOIN
                  " . $oosDBTable['zones'] . " z 
               ON (ab.entry_zone_id = z.zone_id) LEFT JOIN
                  " . $oosDBTable['countries'] . " co 
               ON (ab.entry_country_id = co.countries_id) 
              WHERE 
                  c.customers_id = '" . intval($_SESSION['customer_id']) . "' AND 
                  ab.customers_id = '" . intval($_SESSION['customer_id']) . "' AND 
                  c.customers_default_address_id = ab.address_book_id";
      $customer_address_result = $db->Execute($sql);
      $customer_address = $customer_address_result->fields;

      $sql = "SELECT 
                  ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address,
                  ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, 
                  ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, 
                  c.countries_iso_code_3, c.address_format_id, ab.entry_state 
              FROM 
                  " . $oosDBTable['address_book'] . " ab LEFT JOIN 
                  " . $oosDBTable['zones'] . " z 
               ON (ab.entry_zone_id = z.zone_id) LEFT JOIN 
                  " . $oosDBTable['countries'] . " c ON
                  (ab.entry_country_id = c.countries_id) 
              WHERE 
                  ab.customers_id = '" . intval($_SESSION['customer_id']) . "' AND 
                  ab.address_book_id = '" . intval($_SESSION['sendto']) . "'";
      $shipping_address_result = $db->Execute($sql);
      $shipping_address = $shipping_address_result->fields;
  
      $sql = "SELECT 
                  ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, 
                  ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, 
                  ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, 
                  c.countries_iso_code_3, c.countries_moneybookers, c.address_format_id, ab.entry_state 
              FROM
                  " . $oosDBTable['address_book'] . " ab LEFT JOIN 
                  " . $oosDBTable['zones'] . " z 
               ON (ab.entry_zone_id = z.zone_id) LEFT JOIN 
                  " . $oosDBTable['countries'] . " c ON
                  (ab.entry_country_id = c.countries_id) 
              WHERE 
                  ab.customers_id = '" . intval($_SESSION['customer_id']) . "' AND 
                  ab.address_book_id = '" . intval($_SESSION['billto']) . "'";
      $billing_address_result = $db->Execute($sql);
      $billing_address = $billing_address_result->fields;
 
      $class =& $_SESSION['payment'];

      $this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
                          'currency' => $currency,
                          'currency_value' => $currencies->currencies[$currency]['value'],
                          'payment_method' => $GLOBALS[$class]->title,
                          'cc_type' => (isset($GLOBALS['cc_type']) ? $GLOBALS['cc_type'] : ''),
                          'cc_owner' => (isset($GLOBALS['cc_owner']) ? $GLOBALS['cc_owner'] : ''),
                          'cc_number' => (isset($GLOBALS['cc_number']) ? $GLOBALS['cc_number'] : ''),
                          'cc_expires' => (isset($GLOBALS['cc_expires']) ? $GLOBALS['cc_expires'] : ''),
                          'cc_cvv' => (isset($GLOBALS['cc_cvv']) ? $GLOBALS['cc_cvv'] : ''),
                          'shipping_method' => $_SESSION['shipping']['title'],
                          'shipping_cost' => $_SESSION['shipping']['cost'],
                          'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
                          'shipping_class' =>  ( (strpos($shipping['id'],'_') > 0) ?  substr( strrev( strchr(strrev($shipping['id']),'_') ),0,-1) : $shipping['id'] ),
                          'payment_class' => $_SESSION['payment'],
                          );

      if (isset($GLOBALS['payment']) && is_object($GLOBALS['payment'])) {
        $this->info['payment_method'] = $GLOBALS['payment']->title;

        if ( isset($GLOBALS['payment']->order_status) && is_numeric($GLOBALS['payment']->order_status) && ($GLOBALS['payment']->order_status > 0) ) {
          $this->info['order_status'] = $GLOBALS['payment']->order_status;
        }
      }

      $this->customer = array('firstname' => $customer_address['customers_firstname'],
                              'lastname' => $customer_address['customers_lastname'],
                              'company' => $customer_address['entry_company'],
                              'street_address' => $customer_address['entry_street_address'],
                              'suburb' => $customer_address['entry_suburb'],
                              'city' => $customer_address['entry_city'],
                              'postcode' => $customer_address['entry_postcode'],
                              'state' => ((oosNotNull($customer_address['entry_state'])) ? $customer_address['entry_state'] : $customer_address['zone_name']),
                              'zone_id' => $customer_address['entry_zone_id'],
                              'country' => array('id' => $customer_address['countries_id'], 'title' => $customer_address['countries_name'], 'iso_code_2' => $customer_address['countries_iso_code_2'], 'iso_code_3' => $customer_address['countries_iso_code_3']),
                              'format_id' => $customer_address['address_format_id'],
                              'telephone' => $customer_address['customers_telephone'],
                              'email_address' => $customer_address['customers_email_address']);

      $this->delivery = array('firstname' => $shipping_address['entry_firstname'],
                              'lastname' => $shipping_address['entry_lastname'],
                              'company' => $shipping_address['entry_company'],
                              'street_address' => $shipping_address['entry_street_address'],
                              'suburb' => $shipping_address['entry_suburb'],
                              'city' => $shipping_address['entry_city'],
                              'postcode' => $shipping_address['entry_postcode'],
                              'state' => ((oosNotNull($shipping_address['entry_state'])) ? $shipping_address['entry_state'] : $shipping_address['zone_name']),
                              'zone_id' => $shipping_address['entry_zone_id'],
                              'country' => array('id' => $shipping_address['countries_id'], 'title' => $shipping_address['countries_name'], 'iso_code_2' => $shipping_address['countries_iso_code_2'], 'iso_code_3' => $shipping_address['countries_iso_code_3']),
                              'country_id' => $shipping_address['entry_country_id'],
                              'format_id' => $shipping_address['address_format_id']);


      $this->billing = array('firstname' => $billing_address['entry_firstname'],
                             'lastname' => $billing_address['entry_lastname'],
                             'company' => $billing_address['entry_company'],
                             'street_address' => $billing_address['entry_street_address'],
                             'suburb' => $billing_address['entry_suburb'],
                             'city' => $billing_address['entry_city'],
                             'postcode' => $billing_address['entry_postcode'],
                             'state' => ((oosNotNull($billing_address['entry_state'])) ? $billing_address['entry_state'] : $billing_address['zone_name']),
                             'country' => array('id' => $billing_address['countries_id'], 'title' => $billing_address['countries_name'], 'iso_code_2' => $billing_address['countries_iso_code_2'], 'iso_code_3' => $billing_address['countries_iso_code_3'], 'moneybookers' => $billing_address['countries_moneybookers']),
                             'country_id' => $billing_address['entry_country_id'],
                             'format_id' => $billing_address['address_format_id']);
      $index = 0;
      $products = $_SESSION['cart']->get_products();
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $this->products[$index] = array('qty' => $products[$i]['quantity'],
                                        'name' => $products[$i]['name'],
                                        'model' => $products[$i]['model'],
                                        'ean' => $products[$i]['ean'],
                                        'tax' => oosGetTaxRate($products[$i]['tax_class_id'], $billing_address['entry_country_id'], $$billing_address['entry_zone_id']),
                                        'tax_description' => oosGetTaxDescription($products[$i]['tax_class_id'], $billing_address['entry_country_id'], $billing_address['entry_zone_id']),
                                        'price' => $products[$i]['price'],
                                        'final_price' => $products[$i]['price'] + $_SESSION['cart']->attributes_price($products[$i]['id']),
                                        'weight' => $products[$i]['weight'],
                                        'towlid' => $products[$i]['towlid'],
                                        'id' => $products[$i]['id']);

        if ($products[$i]['attributes']) {
          $subindex = 0;
          reset($products[$i]['attributes']);
          while (list($option, $value) = each($products[$i]['attributes'])) {
            $sql = "SELECT 
                        popt.products_options_name, poval.products_options_values_name, pa.options_values_price, 
                        pa.price_prefix 
                    FROM 
                        " . $oosDBTable['products_options'] . " popt, 
                        " . $oosDBTable['products_options_values'] . " poval, 
                        " . $oosDBTable['products_attributes'] . " pa 
                    WHERE 
                        pa.products_id = '" . oosDBInput($products[$i]['id']) . "' AND 
                        pa.options_id = '" . oosDBInput($option) . "' AND 
                        pa.options_id = popt.products_options_id AND 
                        pa.options_values_id = '" . oosDBInput($value) . "' AND 
                        pa.options_values_id = poval.products_options_values_id AND 
                        popt.products_options_language = '" .  oosDBInput($language) . "' AND 
                        poval.products_options_values_language = '" .  oosDBInput($language) . "'";
            $attributes_result = $db->Execute($sql);
            $attributes = $attributes_result->fields;
            if ($value == PRODUCTS_OPTIONS_VALUE_TEXT_ID){
              $attr_value = $products[$i]['attributes_values'][$option];
            } else {
              $attr_value = $attributes['products_options_values_name'];
            }
            $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options_name'],
                                                                     'value' => $attr_value,
                                                                     'option_id' => $option,
                                                                     'value_id' => $value,
                                                                     'prefix' => $attributes['price_prefix'],
                                                                     'price' => $attributes['options_values_price']);
            $subindex++;
          }
        }

        $shown_price = oosAddTax($this->products[$index]['final_price'], $this->products[$index]['tax']) * $this->products[$index]['qty'];
        $this->info['subtotal'] += $shown_price;

        $products_tax = $this->products[$index]['tax'];
        if ($_SESSION['member']->group['show_price_tax'] == 1) {
          $this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          $this->info['tax_groups']["$products_tax"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
        } else {
          $this->info['tax'] += ($products_tax / 100) * $shown_price;
          $this->info['tax_groups']["$products_tax"] += ($products_tax / 100) * $shown_price;
        }

        $index++;
      }

      if ($_SESSION['member']->group['show_price_tax'] == 1) {
        $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
      } else {
        $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
      }
    }
  }
?>
