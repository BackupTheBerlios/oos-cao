<?php
/* ----------------------------------------------------------------------
   $Id: process.php,v 1.1 2005/01/11 11:31:43 r23 Exp $
   ----------------------------------------------------------------------
   Contribution based on:  

   File: process.php,v 1.21 2005/01/11 10:07:17 r23
   ----------------------------------------------------------------------
   OOS [OSIS Online Shop]
   http://www.oos-shop.de/

   Copyright (c) 2003 - 2004 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: checkout_process.php,v 1.6.2.1 2003/05/03 23:41:23 wilt 
   orig: checkout_process.php,v 1.125 2003/02/16 13:21:43 thomasamoulton 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  /** ensure this file is being included by a parent file */
  defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );

  require(OOS_FUNCTIONS . 'function_address.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'modules' => $oosModules['checkout'], 'file' =>$oosFilename['checkout_payment']));
    oosRedirect(oosLink($oosModules['user'], $oosFilename['login'], '', 'SSL'));
  }
 
  if (!isset($_SESSION['sendto'])) {
    oosRedirect(oosLink($oosModules['checkout'], $oosFilename['checkout_payment'], '', 'SSL'));
  }

  if ( (oosNotNull(MODULE_PAYMENT_INSTALLED)) && (!isset($_SESSION['payment'])) ) {
    oosRedirect(oosLink($oosModules['checkout'], $oosFilename['checkout_payment'], '', 'SSL'));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      oosRedirect(oosLink($oosModules['checkout'], $oosFilename['checkout_shipping'], '', 'SSL'));
    }
  }

// load selected payment module
  require(OOS_CLASSES . 'class_payment.php');
  $payment_modules = new payment($_SESSION['payment']);

// load the selected shipping module
  require(OOS_CLASSES . 'class_shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

  require(OOS_CLASSES . 'class_order.php');
  $order = new order;

// load the before_process function from the payment modules
  $payment_modules->before_process();

  require(OOS_CLASSES . 'class_order_total.php');
  $order_total_modules = new order_total;

  $order_totals = $order_total_modules->process();

  $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                          'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                          'customers_company' => $order->customer['company'],
                          'customers_street_address' => $order->customer['street_address'],
                          'customers_suburb' => $order->customer['suburb'],
                          'customers_city' => $order->customer['city'],
                          'customers_postcode' => $order->customer['postcode'], 
                          'customers_state' => $order->customer['state'], 
                          'customers_country' => $order->customer['country']['title'], 
                          'customers_telephone' => $order->customer['telephone'], 
                          'customers_email_address' => $order->customer['email_address'],
                          'customers_address_format_id' => $order->customer['format_id'], 
                          'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'], 
                          'delivery_company' => $order->delivery['company'],
                          'delivery_street_address' => $order->delivery['street_address'], 
                          'delivery_suburb' => $order->delivery['suburb'], 
                          'delivery_city' => $order->delivery['city'], 
                          'delivery_postcode' => $order->delivery['postcode'], 
                          'delivery_state' => $order->delivery['state'], 
                          'delivery_country' => $order->delivery['country']['title'], 
                          'delivery_address_format_id' => $order->delivery['format_id'], 
                          'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'], 
                          'billing_company' => $order->billing['company'],
                          'billing_street_address' => $order->billing['street_address'], 
                          'billing_suburb' => $order->billing['suburb'], 
                          'billing_city' => $order->billing['city'], 
                          'billing_postcode' => $order->billing['postcode'], 
                          'billing_state' => $order->billing['state'], 
                          'billing_country' => $order->billing['country']['title'], 
                          'billing_address_format_id' => $order->billing['format_id'], 
                          'payment_method' => $order->info['payment_method'], 
                          'cc_type' => $order->info['cc_type'], 
                          'cc_owner' => $order->info['cc_owner'], 
                          'cc_number' => $order->info['cc_number'], 
                          'cc_expires' => $order->info['cc_expires'], 
                          'date_purchased' => 'now()', 
                          'last_modified' => 'now()',
                          'orders_status' => $order->info['order_status'], 
                          'currency' => $order->info['currency'], 
                          'currency_value' => $order->info['currency_value'],
                          'orders_language' => $_SESSION['language']);

  oosDBPerform($oosDBTable['orders'], $sql_data_array);
  $insert_id = $db->Insert_ID();
  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $sql_data_array = array('orders_id' => $insert_id,
                            'title' => $order_totals[$i]['title'],
                            'text' => $order_totals[$i]['text'],
                            'value' => $order_totals[$i]['value'], 
                            'class' => $order_totals[$i]['code'], 
                            'sort_order' => $order_totals[$i]['sort_order']);
    oosDBPerform($oosDBTable['orders_total'], $sql_data_array);
  }

  $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
  $sql_data_array = array('orders_id' => $insert_id, 
                          'orders_status_id' => $order->info['order_status'], 
                          'date_added' => 'now()', 
                          'customer_notified' => $customer_notification,
                          'comments' => $order->info['comments']);
  oosDBPerform($oosDBTable['orders_status_history'], $sql_data_array);

// initialized for the email confirmation
  $products_ordered = '';
  $subtotal = 0;
  $total_tax = 0;

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    // Stock Update - Joao Correia
    if (STOCK_LIMITED == 'true') {
      if (DOWNLOAD_ENABLED == 'true') {
        $stock_result_raw = "SELECT 
                                products_quantity, pad.products_attributes_filename 
                            FROM 
                                " . $oosDBTable['products'] . " p LEFT JOIN 
                                " . $oosDBTable['products_attributes'] . " pa 
                             ON p.products_id = pa.products_id LEFT JOIN 
                                " . $oosDBTable['products_attributes_download'] . " pad 
                             ON pa.products_attributes_id = pad.products_attributes_id
                            WHERE
                                p.products_id = '" . intval(oosGetPrid($order->products[$i]['id'])) . "'";
        // Will work with only one option for downloadable products
        // otherwise, we have to build the query dynamically with a loop
        $products_attributes = $order->products[$i]['attributes'];
        if (is_array($products_attributes)) {
          $stock_result_raw .= " AND pa.options_id = '" . intval($products_attributes[0]['option_id']) . "' AND pa.options_values_id = '" . intval($products_attributes[0]['value_id']) . "'";
        }
        $stock_result = $db->Execute($stock_result_raw);
      } else {
        $sql = "SELECT products_quantity 
                FROM " . $oosDBTable['products'] . " 
                WHERE products_id = '" . intval(oosGetPrid($order->products[$i]['id'])) . "'";
        $stock_result = $db->Execute($sql);
      }
      if ($stock_result->RecordCount() > 0) {
        $stock_values = $stock_result->fields;
        // do not decrement quantities if products_attributes_filename exists
        if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
          $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
        } else {
          $stock_left = $stock_values['products_quantity'];
        }
        $db->Execute("UPDATE " . $oosDBTable['products'] . " 
                    SET products_quantity = '" . oosDBInput($stock_left) . "' 
                    WHERE products_id = '" . intval(oosGetPrid($order->products[$i]['id'])) . "'");
        if ($stock_left < 1) {
          $db->Execute("UPDATE " . $oosDBTable['products'] . " 
                      SET products_status = '0' 
                      WHERE products_id = '" . intval(oosGetPrid($order->products[$i]['id'])) . "'");
        }
      }
    }

// Update products_ordered (for bestsellers list)
    $db->Execute("UPDATE " . $oosDBTable['products'] . " 
                  SET products_ordered = products_ordered + " . sprintf('%d', intval($order->products[$i]['qty'])) . " 
                  WHERE products_id = '" . intval(oosGetPrid($order->products[$i]['id'])) . "'");

    $sql_data_array = array('orders_id' => $insert_id, 
                            'products_id' => oosGetPrid($order->products[$i]['id']), 
                            'products_model' => $order->products[$i]['model'],
                            'products_ean' => $order->products[$i]['ean'],
                            'products_name' => $order->products[$i]['name'], 
                            'products_price' => $order->products[$i]['price'], 
                            'final_price' => $order->products[$i]['final_price'], 
                            'products_tax' => $order->products[$i]['tax'], 
                            'products_quantity' => $order->products[$i]['qty']);
    oosDBPerform($oosDBTable['orders_products'], $sql_data_array);
    $order_products_id = $db->Insert_ID();
    $order_total_modules->update_credit_account($i);//ICW ADDED FOR CREDIT CLASS SYSTEM
//------insert customer choosen option to order--------
    $attributes_exist = '0';
    $products_ordered_attributes = '';
    if (isset($order->products[$i]['attributes'])) {
      $attributes_exist = '1';
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        if (DOWNLOAD_ENABLED == 'true') {
          $attributes_result = "SELECT
                                   popt.products_options_name, poval.products_options_values_name, 
                                   pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, 
                                   pad.products_attributes_maxcount , pad.products_attributes_filename 
                               FROM
                                   " . $oosDBTable['products_options'] . " popt, 
                                   " . $oosDBTable['products_options_values'] . " poval, 
                                   " . $oosDBTable['products_attributes'] . " pa LEFT JOIN
                                   " . $oosDBTable['products_attributes_download'] . " pad
                                ON pa.products_attributes_id = pad.products_attributes_id
                               WHERE
                                   pa.products_id = '" . intval($order->products[$i]['id']) . "' AND
                                   pa.options_id = '" . intval($order->products[$i]['attributes'][$j]['option_id']) . "' AND
                                   pa.options_id = popt.products_options_id AND
                                   pa.options_values_id = '" . intval($order->products[$i]['attributes'][$j]['value_id']) . "' AND 
                                   pa.options_values_id = poval.products_options_values_id AND 
                                   popt.products_options_language = '" . $_SESSION['language'] . "' AND 
                                   poval.products_options_values_language = '" . $_SESSION['language'] . "'";
          $attributes = $db->Execute($attributes_result);
        } else {
          $sql = "SELECT
                      popt.products_options_name, poval.products_options_values_name, 
                      pa.options_values_price, pa.price_prefix 
                  FROM
                      " . $oosDBTable['products_options'] . " popt, 
                      " . $oosDBTable['products_options_values'] . " poval, 
                      " . $oosDBTable['products_attributes'] . " pa 
                  WHERE
                      pa.products_id = '" . intval($order->products[$i]['id']) . "' AND
                      pa.options_id = '" . intval($order->products[$i]['attributes'][$j]['option_id']) . "' AND
                      pa.options_id = popt.products_options_id AND
                      pa.options_values_id = '" . intval($order->products[$i]['attributes'][$j]['value_id']) . "' AND
                      pa.options_values_id = poval.products_options_values_id AND
                      popt.products_options_language = '" . $_SESSION['language'] . "' AND
                      poval.products_options_values_language = '" . $_SESSION['language'] . "'";
          $attributes = $db->Execute($sql);
        }
        $attributes_values = $attributes->fields;
        //clr 030714 update insert query.  changing to use values form $order->products for products_options_values.
        $sql_data_array = array('orders_id' => $insert_id,
                                'orders_products_id' => $order_products_id, 
                                'products_options' => $attributes_values['products_options_name'],
                                'products_options_values' => $order->products[$i]['attributes'][$j]['value'],
                                'options_values_price' => $attributes_values['options_values_price'],
                                'price_prefix' => $attributes_values['price_prefix']);
        // insert
        oosDBPerform($oosDBTable['orders_products_attributes'], $sql_data_array);

        if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && oosNotNull($attributes_values['products_attributes_filename'])) {
          $sql_data_array = array('orders_id' => $insert_id, 
                                  'orders_products_id' => $order_products_id, 
                                  'orders_products_filename' => $attributes_values['products_attributes_filename'], 
                                  'download_maxdays' => $attributes_values['products_attributes_maxdays'], 
                                  'download_count' => $attributes_values['products_attributes_maxcount']);
          // insert
          oosDBPerform($oosDBTable['orders_products_download'], $sql_data_array);
        }
        //clr 030714 changing to use values from $orders->products and adding call to oosDecodeSpecialChars()
        $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . oosDecodeSpecialChars($order->products[$i]['attributes'][$j]['value']);
       //$products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
      }
    }
//------insert customer choosen option eof ----
    $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
    $total_tax += oosCalculateTax($total_products_price, $products_tax) * $order->products[$i]['qty'];
    $total_cost += $total_products_price;

    $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
  }
  $order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
  // lets start with the email confirmation
  $email_order = STORE_NAME . "\n" . 
                 $lang['email_separator'] . "\n" . 
                 $lang['email_text_order_number'] . ' ' . $insert_id . "\n" .
                 $lang['email_text_invoice_url'] . ' ' . oosLink($oosModules['account'], $oosFilename['account_history_info'], 'order_id=' . $insert_id, 'SSL', false) . "\n" .
                 $lang['email_text_date_ordered'] . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
  if ($order->info['comments']) {
    $email_order .= oosDBOutput($order->info['comments']) . "\n\n";
  }
  $email_order .= $lang['email_text_products'] . "\n" . 
                  $lang['email_separator'] . "\n" . 
                  $products_ordered . 
                  $lang['email_separator'] . "\n";

  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
  }

  if ($order->content_type != 'virtual') {
    $email_order .= "\n" . $lang['email_text_delivery_address'] . "\n" . 
                    $lang['email_separator'] . "\n" .
                    oosAddressLabel($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
  }

  $email_order .= "\n" . $lang['email_text_billing_address'] . "\n" .
                  $lang['email_separator'] . "\n" .
                  oosAddressLabel($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n";
  if (is_object($$_SESSION['payment'])) {
    $email_order .= $lang['email_text_payment_method'] . "\n" . 
                    $lang['email_separator'] . "\n";
    $payment_class = $$_SESSION['payment'];
    $email_order .= $payment_class->title . "\n\n";
    if ($payment_class->email_footer) { 
      $email_order .= $payment_class->email_footer . "\n\n";
    }
  }
  if (!isset($_SESSION['man_key'])) {
    oosMail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], $lang['email_text_subject'], nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');
  }
  
// send emails to other people
  if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
    if (SEND_BANKINFO_TO_ADMIN == 'true') {
      if ($_POST['banktransfer_fax'] != "on"){
        $email_order .= "\n";
      $email_order .= "Kontoinhaber: ". $banktransfer_owner . "\n";
      $email_order .= "BLZ:          ". $banktransfer_blz . "\n";
      $email_order .= "Konto:        ". $banktransfer_number . "\n";
      $email_order .= "Bank:         ". $banktransfer_bankname . "\n";
   
      if ($_POST['banktransfer_status'] == 0 || $_POST['banktransfer_status'] == 2){
        $email_order .= "Prüfstatus:   OK\r\n";
      } else {
        $email_order .= "Prüfstatus:   Es ist ein Problem aufgetreten, bitte beobachten!\r\n";
      }
      } else {
        $email_order .= "\n";
        $email_order .= "Kontodaten werden per Fax bestätigt!\n";
      }
    }  
    oosMail('', SEND_EXTRA_ORDER_EMAILS_TO, $lang['email_text_subject'], nl2br($email_order), $order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], '1');
  }

// Include OSC-AFFILIATE 
// fetch the net total of an order
  $affiliate_total = 0;
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    $affiliate_total += $order->products[$i]['final_price'] * $order->products[$i]['qty'];
  }
  $affiliate_total = round($affiliate_total, 2);

// Check for individual commission
  $affiliate_percentage = 0;
  if (AFFILATE_INDIVIDUAL_PERCENTAGE == 'true') {
    $sql = "SELECT affiliate_commission_percent 
            FROM " . $oosDBTable['affiliate_affiliate'] . " 
            WHERE affiliate_id = '" . oosDBInput($_SESSION['affiliate_ref']) . "'";
    $affiliate_commission_result = $db->Execute($sql);
    $affiliate_commission = $affiliate_commission_result->fields;
    $affiliate_percent = $affiliate_commission['affiliate_commission_percent'];
  }
  if ($affiliate_percent < AFFILIATE_PERCENT) $affiliate_percent = AFFILIATE_PERCENT;
  $affiliate_payment = round(($affiliate_total * $affiliate_percent / 100), 2);
   
  if (isset($_SESSION['affiliate_ref'])) {
    $sql_data_array = array('affiliate_id' => $_SESSION['affiliate_ref'],
                            'affiliate_date' => $affiliate_clientdate,
                            'affiliate_browser' => $affiliate_clientbrowser,
                            'affiliate_ipaddress' => $affiliate_clientip,
                            'affiliate_value' => $affiliate_total,
                            'affiliate_payment' => $affiliate_payment,
                            'affiliate_orders_id' => $insert_id,
                            'affiliate_clickthroughs_id' => $_SESSION['affiliate_clickthroughs_id'],
                            'affiliate_percent' => $affiliate_percent);
    oosDBPerform($oosDBTable['affiliate_sales'], $sql_data_array);
  }

// load the after_process function from the payment modules
  $payment_modules->after_process();

  $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
  unset($_SESSION['sendto']);
  unset($_SESSION['billto']);
  unset($_SESSION['shipping']);
  unset($_SESSION['payment']);
  unset($_SESSION['comments']);

  $order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM

  oosRedirect(oosLink($oosModules['checkout'], $oosFilename['checkout_success'], '', 'SSL'));

?>