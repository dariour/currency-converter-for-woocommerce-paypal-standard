<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
* Get currency conversion rate from an online source
*
* @param string $service
* @param string $store_currency
* @param string $paypal_currency
* @param string $api_access_key
* @return int
*/
function get_exchange_rate ( $service, $store_currency, $paypal_currency, $api_access_key ) {
	$exchange_rate = '';

	switch ($service) {
		case 'fixer' :
			$url = 'https://api.fixer.io/latest?base=' . $store_currency;
			break;
		case 'currencyconvertedapi' :
			$url = 'https://free.currencyconverterapi.com/api/v5/convert?q=' . $store_currency . '_' . $paypal_currency . '&compact=y';
		case 'currencylayer' :
			$url = 'http://www.apilayer.net/api/live?access_key=' . $api_access_key . '&currencies=' . $paypal_currency . ',' . $store_currency;
			break;
		case 'xignite' :
			$url = 'http://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRate?Symbol=' . $store_currency . $paypal_currency . '&_token=' . $api_access_key;
			break;
		default:
			$url = 'https://api.fixer.io/latest?base=' . $store_currency;
			break;
	}

	$result = wp_remote_get( $url );

	if( is_wp_error( $result ) ) {
		return false;
	}

	$body = wp_remote_retrieve_body( $result );
	$data = json_decode ( $body, true );

	if ( !empty( $data ) ) {
		switch ( $service ) {
			case 'fixer' :
				$exchange_rate = $data['rates'][$paypal_currency];
				break;
			case 'currencyconvertedapi' :
				$exchange_rate = $data[$store_currency . '_' . $paypal_currency]['val'];
				break;
			case 'currencylayer' :
				$exchange_rate = $data['quotes']['USD' . $paypal_currency] / $data['quotes']['USD' . $store_currency];
				break;
			case 'xignite' :
				$exchange_rate = $data['Mid'];
				break;
			default:
				$exchange_rate = $data['rates'][$paypal_currency];
				break;
		}
	}

	return $exchange_rate;
}