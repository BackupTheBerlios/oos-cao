<?php
/* ----------------------------------------------------------------------
   $Id: oos_filename.php,v 1.1 2005/01/10 11:02:07 r23 Exp $
   ----------------------------------------------------------------------
   Contribution based on:  

   File: oos_filename.php,v 1.38 2004/10/02 20:49:07 vexoid
   ----------------------------------------------------------------------
   OOS [OSIS Online Shop]
   http://www.oos-shop.de/

   Copyright (c) 2003 - 2004 by the OOS Development Team.
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

  $prefix_filename = '';

  if (!$prefix_filename == '') $prefix_filename =  $prefix_filename . '_';

  $oosFilename = array();  
  $oosFilename['admin_account'] = $prefix_filename . 'admin_account.php'; 
  $oosFilename['admin_files'] = $prefix_filename . 'admin_files.php'; 
  $oosFilename['admin_members'] = $prefix_filename . 'admin_members.php';  
  $oosFilename['affiliate'] = $prefix_filename . 'affiliate_affiliates.php';
  $oosFilename['affiliate_banners'] = $prefix_filename . 'affiliate_banners.php';
  $oosFilename['affiliate_banners_manager'] = $prefix_filename . 'affiliate_banners.php';
  $oosFilename['affiliate_clicks'] = $prefix_filename . 'affiliate_clicks.php';
  $oosFilename['affiliate_contact'] = $prefix_filename . 'affiliate_contact.php';
  $oosFilename['affiliate_invoice'] = $prefix_filename . 'affiliate_invoice.php';
  $oosFilename['affiliate_payment'] = $prefix_filename . 'affiliate_payment.php';
  $oosFilename['affiliate_popup_image'] = $prefix_filename . 'affiliate_popup_image.php';
  $oosFilename['affiliate_sales'] = $prefix_filename . 'affiliate_sales.php';
  $oosFilename['affiliate_statistics'] = $prefix_filename . 'affiliate_statistics.php';
  $oosFilename['affiliate_summary'] = $prefix_filename . 'affiliate_summary.php';
  $oosFilename['affiliate_reset'] = $prefix_filename . 'affiliate_reset.php'; 
  $oosFilename['advanced_search_result'] = $prefix_filename . 'advanced_search_result.php';
  $oosFilename['backup'] = $prefix_filename . 'backup.php';
  $oosFilename['banner_manager'] = $prefix_filename . 'banner_manager.php';
  $oosFilename['banner_statistics'] = $prefix_filename . 'banner_statistics.php';
  $oosFilename['cache'] = $prefix_filename . 'cache.php';
  $oosFilename['catalog_product_info'] = $prefix_filename . 'product_info.php';
  $oosFilename['categories'] = $prefix_filename . 'categories.php';
  $oosFilename['configuration'] = $prefix_filename . 'configuration.php';
  $oosFilename['content_block'] = $prefix_filename . 'content_block.php';
  $oosFilename['content_information'] = $prefix_filename . 'content_information.php';
  $oosFilename['content_news'] = $prefix_filename . 'content_news.php';
  $oosFilename['content_page_type'] = $prefix_filename . 'content_page_type.php';
  $oosFilename['countries'] = $prefix_filename . 'countries.php';
  $oosFilename['create_spider_site'] = $prefix_filename . 'create_spider_site.php';
  $oosFilename['currencies'] = $prefix_filename . 'currencies.php';
  $oosFilename['customers'] = $prefix_filename . 'customers.php';
  $oosFilename['customers_status'] = $prefix_filename . 'customers_status.php';
  $oosFilename['coupon_admin'] = $prefix_filename . 'coupon_admin.php';
  $oosFilename['default'] = $prefix_filename . 'index.php';
  $oosFilename['define_language'] = $prefix_filename . 'define_language.php';
  $oosFilename['easypopulate'] = $prefix_filename . 'easypopulate.php';
  $oosFilename['edit_orders'] = $prefix_filename . 'edit_orders.php';
  $oosFilename['export_preissuchmaschine'] = $prefix_filename . 'export_psm.php';
  $oosFilename['featured'] = $prefix_filename . 'featured.php';
  $oosFilename['file_manager'] = $prefix_filename . 'file_manager.php';
  $oosFilename['forbiden'] = $prefix_filename . 'forbiden.php';
  $oosFilename['geo_zones'] = $prefix_filename . 'geo_zones.php';
  $oosFilename['gv_queue'] = $prefix_filename . 'gv_queue.php';
  $oosFilename['gv_mail'] = $prefix_filename . 'gv_mail.php';
  $oosFilename['gv_sent'] = $prefix_filename . 'gv_sent.php';
  $oosFilename['keyword_show'] = 'keyword_show.php';
  $oosFilename['lable'] = $prefix_filename . 'lable.php';
  $oosFilename['login'] = $prefix_filename . 'login.php';
  $oosFilename['login_create'] = $prefix_filename . 'login_create.php';
  $oosFilename['logoff'] = $prefix_filename . 'logoff.php';
  $oosFilename['languages'] = $prefix_filename . 'languages.php';
  $oosFilename['links'] = $prefix_filename . 'links.php';
  $oosFilename['links_categories'] = $prefix_filename . 'links_categories.php';
  $oosFilename['links_contact'] = $prefix_filename . 'links_contact.php';
  $oosFilename['listcategories'] = $prefix_filename . 'listcategories.php';
  $oosFilename['listproducts'] = $prefix_filename . 'listproducts.php';
  $oosFilename['mail'] = $prefix_filename . 'mail.php';
  $oosFilename['manual_loging'] = $prefix_filename . 'manual_loging.php';
  $oosFilename['manufacturers'] = $prefix_filename . 'manufacturers.php';
  $oosFilename['modules'] = $prefix_filename . 'modules.php';
  $oosFilename['newsletters'] = $prefix_filename . 'newsletters.php';
  $oosFilename['newsfeed_manager'] = $prefix_filename . 'newsfeed_manager.php';
  $oosFilename['newsfeed_categories'] = $prefix_filename . 'newsfeed_categories.php'; 
  $oosFilename['newsfeed_view'] = $prefix_filename . 'popup_newsfeed_view.php'; 
  $oosFilename['orders'] = $prefix_filename . 'orders.php';
  $oosFilename['invoice'] = $prefix_filename . 'invoice.php';
  $oosFilename['packingslip'] = $prefix_filename . 'packingslip.php';
  $oosFilename['password_forgotten'] = $prefix_filename . 'password_forgotten.php';
  $oosFilename['orders_status'] = $prefix_filename . 'orders_status.php';
  $oosFilename['php_info'] = $prefix_filename . 'php_info.php';
  $oosFilename['popup_image'] = $prefix_filename . 'popup_image.php';
  $oosFilename['popup_image_news'] = $prefix_filename . 'popup_image_news.php';
  $oosFilename['popup_image_product'] = $prefix_filename . 'popup_image_product.php';
  $oosFilename['popup_subimage_product'] = $prefix_filename . 'popup_subimage_product.php';
  $oosFilename['products'] = $prefix_filename . 'products.php';
  $oosFilename['products_attributes'] = $prefix_filename . 'products_attributes.php';
  $oosFilename['products_expected'] = $prefix_filename . 'products_expected.php';
  $oosFilename['products_status'] = $prefix_filename . 'products_status.php';
  $oosFilename['reviews'] = $prefix_filename . 'reviews.php';
  $oosFilename['rss_conf'] = $prefix_filename . 'rss.php';
  $oosFilename['server_info'] = $prefix_filename . 'server_info.php';
  $oosFilename['shipping_modules'] = $prefix_filename . 'shipping_modules.php';
  $oosFilename['specials'] = $prefix_filename . 'specials.php';
  $oosFilename['stats_customers'] = $prefix_filename . 'stats_customers.php';
  $oosFilename['stats_keywords'] = $prefix_filename . 'stats_keywords.php';
  $oosFilename['stats_low_stock'] = $prefix_filename . 'stats_low_stock.php';
  $oosFilename['stats_sales_report2'] = $prefix_filename . 'stats_sales_report2.php';
  $oosFilename['stats_products_purchased'] = $prefix_filename . 'stats_products_purchased.php';
  $oosFilename['stats_products_viewed'] = $prefix_filename . 'stats_products_viewed.php';
  $oosFilename['tax_classes'] = $prefix_filename . 'tax_classes.php';
  $oosFilename['tax_rates'] = $prefix_filename . 'tax_rates.php';
  $oosFilename['ticket_admin'] = $prefix_filename . 'ticket_admin.php';
  $oosFilename['ticket_department'] = $prefix_filename . 'ticket_department.php';
  $oosFilename['ticket_priority'] = $prefix_filename . 'ticket_priority.php';
  $oosFilename['ticket_reply'] = $prefix_filename . 'ticket_reply.php';
  $oosFilename['ticket_status'] = $prefix_filename . 'ticket_status.php';
  $oosFilename['ticket_view'] = $prefix_filename . 'ticket_view.php';
  $oosFilename['validcategories'] = $prefix_filename . 'validcategories.php';
  $oosFilename['validproducts'] = $prefix_filename . 'validproducts.php';
  $oosFilename['whos_online'] = $prefix_filename . 'whos_online.php';
  $oosFilename['xsell_products'] = 'xsell_products.php';
  $oosFilename['zones'] = $prefix_filename . 'zones.php';

// CAO Faktura
  $oosFilename['cao_import'] = 'cao_import.php';
  $oosFilename['xml_export'] = $prefix_filename . 'xml_export.php';

  
  //catalogLink 
  $prefix_modules = '';
  if (!$prefix_modules == '') $prefix_modules =  $prefix_modules . '_';
    
  $oosModules = array();
  $oosModules['account'] = $prefix_modules . 'account';
  $oosModules['admin'] = $prefix_modules . 'admin';
  $oosModules['affiliate'] = $prefix_modules . 'affiliate';
  $oosModules['gv'] = $prefix_modules . 'gv';
  $oosModules['main'] = $prefix_modules . 'main';
  $oosModules['products'] = $prefix_modules . 'products';
  $oosModules['search'] = $prefix_modules . 'search';
  $oosModules['ticket'] = $prefix_modules . 'ticket';
  
  $prefix_catalog_filename = '';
  if (!$prefix_catalog_filename == '') $prefix_catalog_filename =  $prefix_catalog_filename . '_';
  
  $oosCatalogFilename = array();
  $oosCatalogFilename['account_history_info'] = $prefix_catalog_filename . 'history_info';
  $oosCatalogFilename['advanced_search_result'] = $prefix_catalog_filename . 'advanced_result';
  $oosCatalogFilename['affiliate_payment'] = $prefix_catalog_filename . 'payment';
  $oosCatalogFilename['default'] = $prefix_catalog_filename . 'main';
  $oosCatalogFilename['gv_redeem'] = $prefix_catalog_filename . 'redeem';
  $oosCatalogFilename['ticket_view'] = $prefix_catalog_filename . 'view';
  $oosCatalogFilename['product_info'] = $prefix_catalog_filename . 'info'; 
  $oosCatalogFilename['login_admin'] = $prefix_catalog_filename . 'login';
  $oosCatalogFilename['create_account_admin'] = $prefix_catalog_filename . 'create_account';
  $oosCatalogFilename['wishlist'] = $prefix_catalog_filename . 'wishlist'; 

  $oosCatalogFilename['affiliate_help1'] = $prefix_catalog_filename . 'help1';
  $oosCatalogFilename['affiliate_help2'] = $prefix_catalog_filename . 'help2';
  $oosCatalogFilename['affiliate_help3'] = $prefix_catalog_filename . 'help3';
  $oosCatalogFilename['affiliate_help4'] = $prefix_catalog_filename . 'help4';
  $oosCatalogFilename['affiliate_help5'] = $prefix_catalog_filename . 'help5';
  $oosCatalogFilename['affiliate_help6'] = $prefix_catalog_filename . 'help6';
  $oosCatalogFilename['affiliate_help7'] = $prefix_catalog_filename . 'help7';
  $oosCatalogFilename['affiliate_help8'] = $prefix_catalog_filename . 'help8';

?>