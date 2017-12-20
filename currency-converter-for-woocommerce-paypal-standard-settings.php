<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Settings_PayPal_Currency_Converter', false ) ) :

/**
 * WC_Admin_Settings_General.
 */
class WC_Settings_PayPal_Currency_Converter {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'converter';
        $this->label = __( 'PayPal Currency Converter', 'currency-converter-for-woocommerce-paypal-standard' );

        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings() {

        $settings = apply_filters( 'woocommerce_general_settings', array(

            'section_title' => array(
                'title'     => __( 'PayPal Currency Converter', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_paypal_currency_converter_section_title'
            ),

            'supported_currencies' => array(
                'name'    => __( 'PayPal Supported Currencies', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'    => 'select',
                'options' => array(
                            'AUD' => __( 'Australian Dollar (AUD)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'BRL' => __( 'Brazilian Real (BRL)', 'currency-converter-for-woocommerce-paypal-standard' ), // in-country payments only
                            'CAD' => __( 'Canadian Dollar (CAD)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'CZK' => __( 'Czech Koruna (CZK)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'DKK' => __( 'Danish Krone (DKK)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'EUR' => __( 'Euro (EUR)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'HKD' => __( 'Hong Kong Dollar (HKD)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'HUF' => __( 'Hungarian Forint (HUF)', 'currency-converter-for-woocommerce-paypal-standard' ), // does not support decimals
                            'ILS' => __( 'Israeli New Sheqel (ILS)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'JPY' => __( 'Japanese Yen (JPY)', 'currency-converter-for-woocommerce-paypal-standard' ), // does not support decimals
                            'MYR' => __( 'Malaysian Ringgit (MYR)', 'currency-converter-for-woocommerce-paypal-standard' ), // in-country payments only
                            'MXN' => __( 'Mexican Peso (MXN)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'NOK' => __( 'Norwegian Krone (NOK)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'NZD' => __( 'New Zealand Dollar (NZD)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'PHP' => __( 'Philippine Peso (PHP)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'PLN' => __( 'Polish Zloty (PLN)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'GBP' => __( 'Pound Sterling (GBP)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'RUB' => __( 'Russian Ruble (RUB)', 'currency-converter-for-woocommerce-paypal-standard' ), // in-country payments only
                            'SGD' => __( 'Singapore Dollar (SGD)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'SEK' => __( 'Swedish Krona (SEK)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'CHF' => __( 'Swiss Franc (CHF)', 'currency-converter-for-woocommerce-paypal-standard' ), // does not support decimals
                            'TWD' => __( 'Taiwan New Dollar (TWD)', 'currency-converter-for-woocommerce-paypal-standard' ), // does not support decimals
                            'THB' => __( 'Thai Baht (THB)', 'currency-converter-for-woocommerce-paypal-standard' ),
                            'USD' => __( 'U.S Dollar (USD)', 'currency-converter-for-woocommerce-paypal-standard' )
                ),
                'default'  => 'EUR',
                'desc_tip' =>  true,
                'desc'     => __( 'Currencies supported by PayPal. Choose which currency to convert to on checkout. *WARNING: BRL, MYR, RUB support in-country payments only', 'currency-converter-for-woocommerce-paypal-standard' ),
                'id'       => 'wc_settings_paypal_currency_converter_supported_currencies'
            ),

            'currency_exchange_rate_service' => array(
                'name'    => __( 'Currency Exchange Rate Service', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'    => 'select',
                'options' => array(
                    'currencylayer'        => 'currencylayer.com *recommended',
                    'fixer'                => 'fixer.io',
                    'currencyconvertedapi' => 'free.currencyconverterapi.com',
                    'xignite'              => 'www.xignite.com',

                ),
                'desc'     => __( 'Choose which online currency exchange rate service will be used. *WARNING: currencylayer.com and xignite.com require API Access Keys to work. Using currencylayer.com is recommended. Other services may not support every possible currency.', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_exchange_rate_service'
            ),

            'api_access_key' => array(
                'name'     => __( 'Currency API Access Key', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'password',
                'desc'     => __( 'API access key for online exchange rate service. Add if needed.', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_exchange_rate_service_api_access_key'
            ),

            'manual_currency_exchange_rate' => array(
                'name'     => __( 'Manual Currency Exchange Rate', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'text',
                'desc'     => __( 'Add your own currency exchange rate. To use online services leave this empty', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_manual_exchange_rate'
            ),

            'section_end' => array(
                 'type' => 'sectionend',
                 'id'   => 'wc_settings_paypal_currency_converter_section_title'
            ),

            'section_title_custom_currency' => array(
                'title'     => __( 'Add Custom Currency', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'title',
                'desc'     => __( 'Here you can add your own currency to be used in your store. When enabled, set the currency in WooCommerce general settings', 'currency-converter-for-woocommerce-paypal-standard' ),
                'id'       => 'wc_settings_paypal_currency_converter_section_title_custom_currency'
            ),

            'custom_currency_enable' => array(
                'name'     => __( 'Enable Custom Currency', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'checkbox',
                'desc'     => __( 'Check to enable custom currency', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_custom_currency_enable'
            ),

            'custom_currency_' => array(
                'name'     => __( 'Custom Currency', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'text',
                'desc'     => __( 'Add custom currency', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_custom_currency'
            ),

            'custom_currency_symbol' => array(
                'name'     => __( 'Custom Currency Symbol', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'text',
                'desc'     => __( 'Add custom currency symbol to be used in your store', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_custom_currency_symbol'
            ),

            'custom_currency_exchange_rate' => array(
                'name'     => __( 'Custom Currency Exchange Rate', 'currency-converter-for-woocommerce-paypal-standard' ),
                'type'     => 'text',
                'desc'     => __( 'Add your custom currency exchange rate. If empty, conversion rate is 1.', 'currency-converter-for-woocommerce-paypal-standard' ),
                'desc_tip' =>  true,
                'id'       => 'wc_settings_paypal_currency_converter_custom_currency_exchange_rate'
            ),

            'section_end_custom_currency' => array(
                 'type' => 'sectionend',
                 'id'   => 'wc_settings_paypal_currency_converter_section_title_custom_currency'
            )

        ) );

        return apply_filters( 'woocommerce_get_settings' . $this->id, $settings );
    }

    /**
     * Add this page to settings.
     *
     * @param array $pages
     *
     * @return mixed
     */
    public function add_settings_page( $pages ) {
        $pages[ $this->id ] = $this->label;

        return $pages;
    }

    /**
     * Get settings page ID.
     * @since 3.0.0
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get settings page label.
     * @since 3.0.0
     * @return string
     */
    public function get_label() {
        return $this->label;
    }

    /**
     * Output the settings.
     */
    public function output() {
        $settings = $this->get_settings();

        WC_Admin_Settings::output_fields( $settings );
    }

    /**
     * Save settings.
     */
    public function save() {
        $settings = $this->get_settings();

        WC_Admin_Settings::save_fields( $settings );
    }
}

endif;

return new WC_Settings_PayPal_Currency_Converter();
