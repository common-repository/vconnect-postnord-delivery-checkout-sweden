<?php

/*
  Plugin Name: vConnect PostNord Delivery Checkout - Sweden
  Plugin URI: https://vconnect.se/moduler/postnord-delivery-check-out
  Description: PostNord Delivery checkout integration for WooCommerce
  Version: 2.6.2.1.0
  Author: vConnect
  Author URI: http://vconnect.dk
 */

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    define('AINO_PLUGIN_VERSION', '2.6.2.1.0');
    
    define('AINO_PLUGIN_PATH', plugin_dir_path(__FILE__));
    define('AINO_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('AINO_WIDGET_LANG', 'sv');

    require_once 'core/vc-aio-core.php';
    require_once 'spec/vc-aino-shipping-methods.php';
}