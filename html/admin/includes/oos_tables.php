<?php
/* ----------------------------------------------------------------------
   $Id: oos_tables.php,v 1.2 2005/05/26 22:16:13 r23 Exp $
   ----------------------------------------------------------------------
   Contribution based on:  

   File: oos_tables.php,v 1.22 2004/08/19 09:35:09 vexoid 
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

  $prefix_table = OOS_DB_PREFIX;

  if (!$prefix_table == '') $prefix_table = $prefix_table . '_';

  $oosDBTable = array();
  $oosDBTable['admin'] = $prefix_table . 'admin';
  $oosDBTable['admin_files'] = $prefix_table . 'admin_files';
  $oosDBTable['admin_groups'] = $prefix_table . 'admin_groups';
  $oosDBTable['address_book'] = $prefix_table . 'address_book';
  $oosDBTable['address_format'] = $prefix_table . 'address_format';
  $oosDBTable['adodb_logsql'] = $prefix_table . 'logsql';
  $oosDBTable['affiliate'] = $prefix_table . 'affiliate_affiliate';
  $oosDBTable['affiliate_banners'] = $prefix_table . 'affiliate_banners';
  $oosDBTable['affiliate_banners_history'] = $prefix_table . 'affiliate_banners_history';
  $oosDBTable['affiliate_clickthroughs'] = $prefix_table . 'affiliate_clickthroughs';
  $oosDBTable['affiliate_payment'] = $prefix_table . 'affiliate_payment';
  $oosDBTable['affiliate_payment_status'] = $prefix_table . 'affiliate_payment_status';
  $oosDBTable['affiliate_payment_status_history'] = $prefix_table . 'affiliate_payment_status_history';
  $oosDBTable['banktransfer'] = $prefix_table . 'banktransfer';
  $oosDBTable['banktransfer_blz'] = $prefix_table . 'banktransfer_blz';
  $oosDBTable['affiliate_sales'] = $prefix_table . 'affiliate_sales'; 
  $oosDBTable['banners'] = $prefix_table . 'banners';
  $oosDBTable['banners_history'] = $prefix_table . 'banners_history';
  $oosDBTable['block'] = $prefix_table . 'block';
  $oosDBTable['block_info'] = $prefix_table . 'block_info';
  $oosDBTable['block_to_page_type'] = $prefix_table . 'block_to_page_type';
  $oosDBTable['categories'] = $prefix_table . 'categories';
  $oosDBTable['categories_description'] = $prefix_table . 'categories_description';
  $oosDBTable['configuration'] = $prefix_table . 'configuration';
  $oosDBTable['configuration_group'] = $prefix_table . 'configuration_group';
  $oosDBTable['countries'] = $prefix_table . 'countries';
  $oosDBTable['currencies'] = $prefix_table . 'currencies';
  $oosDBTable['coupons'] = $prefix_table . 'coupons';
  $oosDBTable['coupon_gv_queue'] = $prefix_table . 'coupon_gv_queue';
  $oosDBTable['coupon_gv_customer'] = $prefix_table . 'coupon_gv_customer';
  $oosDBTable['coupon_email_track'] = $prefix_table . 'coupon_email_track';
  $oosDBTable['coupon_redeem_track'] = $prefix_table . 'coupon_redeem_track';
  $oosDBTable['coupons_description'] = $prefix_table . 'coupons_description';  
  $oosDBTable['customers'] = $prefix_table . 'customers';
  $oosDBTable['customers_basket'] = $prefix_table . 'customers_basket';
  $oosDBTable['customers_basket_attributes'] = $prefix_table . 'customers_basket_attributes';
  $oosDBTable['customers_info'] = $prefix_table . 'customers_info';
  $oosDBTable['customers_status'] = $prefix_table . 'customers_status';
  $oosDBTable['customers_status_history'] = $prefix_table . 'customers_status_history';
  $oosDBTable['customers_wishlist'] = $prefix_table . 'customers_wishlist'; 
  $oosDBTable['customers_wishlist_attributes'] = $prefix_table . 'customers_wishlist_attributes';
  $oosDBTable['featured'] = $prefix_table . 'featured';
  $oosDBTable['information'] = $prefix_table . 'information';
  $oosDBTable['information_description'] = $prefix_table . 'information_description';  
  $oosDBTable['languages'] = $prefix_table . 'languages';
  $oosDBTable['link_categories'] = $prefix_table . 'link_categories';
  $oosDBTable['link_categories_description'] = $prefix_table . 'link_categories_description';
  $oosDBTable['links'] = $prefix_table . 'links';
  $oosDBTable['links_description'] = $prefix_table . 'links_description';
  $oosDBTable['links_status'] = $prefix_table . 'links_status';
  $oosDBTable['links_to_link_categories'] = $prefix_table . 'links_to_link_categories';
  $oosDBTable['maillist'] = $prefix_table . 'maillist';
  $oosDBTable['manual_info'] = $prefix_table . 'manual_info';
  $oosDBTable['manufacturers'] = $prefix_table . 'manufacturers';
  $oosDBTable['manufacturers_info'] = $prefix_table . 'manufacturers_info';
  $oosDBTable['newsletters'] = $prefix_table . 'newsletters';
  $oosDBTable['newsfeed'] = $prefix_table . 'newsfeed';
  $oosDBTable['newsfeed_categories'] = $prefix_table . 'newsfeed_categories';
  $oosDBTable['newsfeed_info'] = $prefix_table . 'newsfeed_info';
  $oosDBTable['newsfeed_manager'] = $prefix_table . 'newsfeed_manager';
  $oosDBTable['news_categories'] = $prefix_table . 'news_categories';
  $oosDBTable['news_categories_description'] = $prefix_table . 'news_categories_description';
  $oosDBTable['news'] = $prefix_table . 'news';
  $oosDBTable['news_description'] = $prefix_table . 'news_description';
  $oosDBTable['news_to_categories'] = $prefix_table . 'news_to_categories';
  $oosDBTable['news_reviews'] = $prefix_table . 'news_reviews';
  $oosDBTable['news_reviews_description'] = $prefix_table . 'news_reviews_description';
  $oosDBTable['orders'] = $prefix_table . 'orders';
  $oosDBTable['orders_products'] = $prefix_table . 'orders_products';
  $oosDBTable['orders_products_attributes'] = $prefix_table . 'orders_products_attributes';
  $oosDBTable['orders_products_download'] = $prefix_table . 'orders_products_download';
  $oosDBTable['orders_status'] = $prefix_table . 'orders_status';
  $oosDBTable['orders_status_history'] = $prefix_table . 'orders_status_history';
  $oosDBTable['orders_total'] = $prefix_table . 'orders_total';
  $oosDBTable['page_type'] = $prefix_table . 'page_type';
  $oosDBTable['products'] = $prefix_table . 'products';
  $oosDBTable['products_attributes'] = $prefix_table . 'products_attributes';
  $oosDBTable['products_attributes_download'] = $prefix_table . 'products_attributes_download';
  $oosDBTable['products_description'] = $prefix_table . 'products_description';
  $oosDBTable['products_notifications'] = $prefix_table . 'products_notifications';
  $oosDBTable['products_options'] = $prefix_table . 'products_options';
  $oosDBTable['products_options_types'] = $prefix_table . 'products_options_types';
  $oosDBTable['products_options_values'] = $prefix_table . 'products_options_values';
  $oosDBTable['products_options_values_to_products_options'] = $prefix_table . 'products_options_values_to_products_options';
  $oosDBTable['products_status'] = $prefix_table . 'products_status';
  $oosDBTable['products_to_categories'] = $prefix_table . 'products_to_categories';
  $oosDBTable['products_to_master'] = $prefix_table . 'products_to_master';
  $oosDBTable['products_xsell'] = $prefix_table . 'products_xsell';
  $oosDBTable['reviews'] = $prefix_table . 'reviews';
  $oosDBTable['reviews_description'] = $prefix_table . 'reviews_description';
  $oosDBTable['search_queries'] = $prefix_table . 'search_queries';
  $oosDBTable['search_queries_sorted'] = $prefix_table . 'search_queries_sorted';
  $oosDBTable['searchword_swap'] = $prefix_table . 'searchword_swap';
  $oosDBTable['sessions'] = $prefix_table . 'sessions';
  $oosDBTable['specials'] = $prefix_table . 'specials';
  $oosDBTable['tax_class'] = $prefix_table . 'tax_class';
  $oosDBTable['tax_rates'] = $prefix_table . 'tax_rates';
  $oosDBTable['ticket_admins'] = $prefix_table . 'ticket_admins';
  $oosDBTable['ticket_department'] = $prefix_table . 'ticket_department';
  $oosDBTable['ticket_priority'] = $prefix_table . 'ticket_priority';
  $oosDBTable['ticket_reply'] = $prefix_table . 'ticket_reply';
  $oosDBTable['ticket_status'] = $prefix_table . 'ticket_status';
  $oosDBTable['ticket_status_history'] = $prefix_table . 'ticket_status_history';
  $oosDBTable['ticket_ticket'] = $prefix_table . 'ticket_ticket';
  $oosDBTable['geo_zones'] = $prefix_table . 'geo_zones';
  $oosDBTable['zones_to_geo_zones'] = $prefix_table . 'zones_to_geo_zones';
  $oosDBTable['whos_online'] = $prefix_table . 'whos_online';
  $oosDBTable['zones'] = $prefix_table . 'zones';
  
  // CAO Faktura
  $oosDBTable['cao_log'] = $prefix_table . 'cao_log';
  
?>