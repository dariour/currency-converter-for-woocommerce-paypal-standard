<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option( 'wc_settings_paypal_currency_converter_supported_currencies' );
delete_option( 'wc_settings_paypal_currency_converter_exchange_rate_service' );
delete_option( 'wc_settings_paypal_currency_converter_exchange_rate_service_api_access_key' );
delete_option( 'wc_settings_paypal_currency_converter_manual_exchange_rate' );
delete_option( 'wc_settings_paypal_currency_converter_custom_currency_enable' );
delete_option( 'wc_settings_paypal_currency_converter_custom_currency' );
delete_option( 'wc_settings_paypal_currency_converter_custom_currency_symbol' );
delete_option( 'wc_settings_paypal_currency_converter_custom_currency_exchange_rate' );