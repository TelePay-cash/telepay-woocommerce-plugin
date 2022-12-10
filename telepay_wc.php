<?php
/**
 * Plugin Name:       TelePay Woocommerce Plugin
 * Plugin URI:        https://telepay.cash/
 * Description:       Receive crypto-payments with TelePay, create your merchant account, get your API key and automate your invoices. TelePay has a web dashboard, a Telegram integration and more for you to grow your business.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Byweb Developer
 * Author URI:        https://bywebdeveloper.com/
 * License:           GNU GPL v3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Update URI:        
 * Text Domain:       telepaywc
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Name of this file with its URL
 */
if ( ! defined( 'telepay_name_file_plugin' ) ) define( 'telepay_name_file_plugin', __FILE__ );

/**
 * Name of this plugin folder and its URL
 */
if ( ! defined( 'telepay_name_plugin' ) ) define( 'telepay_name_plugin', plugins_url( '/', __FILE__ ) );

/**
 * Name of this plugin folder and its DIR
 */
if ( ! defined( 'telepay_name_dir' ) ) define( 'telepay_name_dir', dirname( __FILE__ ) );

if ( ! defined( 'telepay_path_dir' ) ) define( 'telepay_path_dir', realpath( dirname(__DIR__) ) );

/**
 * Plugin version
 */
if ( ! defined( 'telepay_plugin_version' ) ) define( 'telepay_plugin_version', '1.0.0' );

/**
 * Text Domain
 */
if ( ! defined( 'telepay_text_domain' ) ) define( 'telepay_text_domain', 'telepaywc' ); 

/**
 * url Webhook
 */
if ( ! defined( 'telepay_wc_api' ) ) define( 'telepay_wc_api', site_url( '/wc-api/telepay_payment/' ) );


$loader = require 'lib/vendor/autoload.php';

$loader->addPsr4( 'telepayCash\\', telepay_name_dir . '/classes' );

require 'inc/functions.php';

use telepayCash\telepay_config;

use telepayCash\telepay_page_setting;

if ( !class_exists( 'telepay_config' ) ) ( new telepay_config );

if ( !class_exists( 'telepay_page_setting' ) ) ( new telepay_page_setting );

if ( ! function_exists( 'telepay_woocommerce_gateway_init' ) )
{
    function telepay_woocommerce_gateway_init()
    {
        if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

        /**
         * Gateway class telepayCash_payment_method
        */
        require 'classes/telepaycash_payment_method.php';

        /**
         * Add the Gateway to WooCommerce
        */ 
        function telepay_woocommerce_add_gateway( $methods )
        {
            $methods[] = 'telepayCash_payment_method';
            
            return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'telepay_woocommerce_add_gateway' );
    }
    add_action('plugins_loaded', 'telepay_woocommerce_gateway_init' );
}

/**
 * class telepay_wc_api
 */
if ( ! function_exists( 'telepay_wc_api' ) )
{
    function telepay_wc_api()
    { 
        require 'classes/telepay_wc_api.php';
    }
    add_action('woocommerce_api_telepay_payment', 'telepay_wc_api' );
}
    

