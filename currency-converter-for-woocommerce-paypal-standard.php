<?php
/*
Plugin Name: Currency Converter For WooCommerce PayPal Standard
Plugin URI: http://wordpress.org/plugins/currency-converter-for-woocommerce-paypal-standard/
Description: Currency Converter for WooCommerce PayPal Standard gateway - the plugin allows you to convert the currency used in your store into any of the ones supported by PayPal (on checkout). You can also add your own custom currency.
Author: Dario Ursulin
Version: 1.0
Requires at least: 4.6
Requires PHP: 5.6
Author URI: https://github.com/dariour
Text Domain: currency-converter-for-woocommerce-paypal-standard
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	require 'currency-converter-for-woocommerce-paypal-standard-settings.php';
	require 'currency-converter-for-woocommerce-paypal-standard-util.php';


  
	/**
     * Show admin notice on plugin activation.
     *
     */
	function admin_notice_activated () {
    ?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'Custom PayPal Currency is successfully activated! For usage instructions see the details page.', 'currency-converter-for-woocommerce-paypal-standard' ); ?></p>
	    </div>
    <?php
	}

	/**
     * Add user defined currency Currency
     *
     * @param array $currencies
     * @return string
     */
	function add_custom_currency ( $currencies ) {
		$currency = get_option( 'wc_settings_paypal_currency_converter_custom_currency' );
		$currencies[$currency] = $currency;  
		return $currencies;
	}

	/**
     * Add user defined currency symbol.
     *
     * @param string $currency_symbol
     * @param string $currency
     * @return string
     */
	function add_custom_currency_symbol ( $currency_symbol, $currency ) {  
		if ($currency == get_option( 'wc_settings_paypal_currency_converter_custom_currency' ) ) {
			$currency_symbol = get_option( 'wc_settings_paypal_currency_converter_custom_currency_symbol' );
			if( empty( $currency_symbol ) ) {
				$currency_symbol = $currency;
			}
		}
		return $currency_symbol;
	}

	/**
     * Add currently active Woocommerce currency to PayPal supported currencies.
     *
     * @param array $currencies
     * @return array
     */
	function add_paypal_valid_currency ( $currencies ) {  
		array_push ( $currencies , get_woocommerce_currency() );  
		return $currencies;    
	}

	/**
     * Set currency conversion rate.
     *
     * @return int
     */
	function set_exchange_rate () {
		$exchange_rate = '';

		if ( get_option( 'wc_settings_paypal_currency_converter_custom_currency_enable' ) == 'yes' ) {
			if ( !empty( get_option( 'wc_settings_paypal_currency_converter_custom_currency_exchange_rate' ) ) ) {
				$exchange_rate = get_option('wc_settings_paypal_currency_converter_custom_currency_exchange_rate');
			}
			else {
				$exchange_rate = 1;
			}
			return $exchange_rate;
		}

		if ( !empty( get_option( 'wc_settings_paypal_currency_converter_manual_exchange_rate' ) ) ) {
			$exchange_rate = get_option( 'wc_settings_paypal_currency_converter_manual_exchange_rate' );
		}
		else {
			$exchange_rate = get_exchange_rate (
				get_option( 'wc_settings_paypal_currency_converter_exchange_rate_service' ),
				get_woocommerce_currency (),
				get_option( 'wc_settings_paypal_currency_converter_supported_currencies' ),
				get_option( 'wc_settings_paypal_currency_converter_exchange_rate_service_api_access_key' )
			);
		}
		return $exchange_rate;
	}

	/**
     * Convert shop price to set PayPal currency
     *
     * @param array $paypal_args
     * @return array
     */
	function convert_paypal_currency ( $paypal_args ) {
		$paypal_currency = get_option( 'wc_settings_paypal_currency_converter_supported_currencies' );
		$exchange_rate = '';

		if ( $paypal_currency == get_woocommerce_currency () ) {
			$exchange_rate =  1;
		}
		else {
			$exchange_rate = set_exchange_rate ();
		}

		if ( $paypal_args['currency_code'] == get_woocommerce_currency () ) {  
			$paypal_args['currency_code'] = $paypal_currency; 
			$i = 1;  
			while ( isset( $paypal_args['amount_' . $i] ) ) {  
				$paypal_args['amount_' . $i] = round( $paypal_args['amount_' . $i] * $exchange_rate, 2);
				++$i;  
			}  
			if ( $paypal_args['shipping_1'] > 0 ) {
				$paypal_args['shipping_1'] = round( $paypal_args['shipping_1'] * $exchange_rate, 2);
			}
			if ( $paypal_args['discount_amount_cart'] > 0 ) {
				$paypal_args['discount_amount_cart'] = round( $paypal_args['discount_amount_cart'] * $exchange_rate, 2);
			}
			if ( $paypal_args['tax_cart'] > 0 ) {
				$paypal_args['tax_cart'] = round( $paypal_args['tax_cart'] * $exchange_rate, 2);
			}
		}
		return $paypal_args;  
	}

	/**
	* Check if user defined currency usage is enabled and add it to supported currencies
	*/
	if ( get_option( 'wc_settings_paypal_currency_converter_custom_currency_enable' ) == 'yes' ) {
		if ( empty( get_option( 'wc_settings_paypal_currency_converter_custom_currency' ) ) ) return;

		add_filter ( 'woocommerce_currencies', 'add_custom_currency' );
		add_filter ( 'woocommerce_currency_symbol', 'add_custom_currency_symbol', 10, 2 );  
	}

	add_action ( 'admin_notices', 'admin_notice_activated' );
	add_filter ( 'woocommerce_paypal_args', 'convert_paypal_currency', 11 );
	add_filter ( 'woocommerce_paypal_supported_currencies', 'add_paypal_valid_currency' );
}