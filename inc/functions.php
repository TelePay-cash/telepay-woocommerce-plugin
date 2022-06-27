<?php

if ( ! defined( 'ABSPATH' ) ) exit;

use Codenixsv\CoinGeckoApi\CoinGeckoClient;

/**
 * Function to get the API KEY from the database
 */
if ( ! function_exists( 'telepay_get_apikey' ) )
{
    function telepay_get_apikey()
    {
        global $wpdb;

        $table  = $wpdb->prefix . 'telepay_config';

        $query  = "SELECT apikey FROM $table";
        
        $config = $wpdb->get_results( $query );
        
        if( isset( $config[0]->apikey ) )
        {
            return $config[0]->apikey;

        }else{

            return false;
        }
    }
}

/**
 * Function to get the expiration time of the database
 */
if ( ! function_exists( 'telepay_get_expiration_time' ) )
{
    function telepay_get_expiration_time()
    {
        global $wpdb;

        $table  = $wpdb->prefix.'telepay_config';

        $query  = "SELECT expiration_time FROM $table";

        $config = $wpdb->get_results( $query );
        
        if( isset( $config[0]->expiration_time ) )
        {
            return $config[0]->expiration_time;

        }else{

            return false;
        }
    }
}

/**
 * Function to mark the coins saved by the user in the configuration page
 */
if ( ! function_exists( 'telepay_accepted_currencies' ) )
{
    function telepay_accepted_currencies( $currencies, $network )
    {
        $coins = telepay_currencies_accepted_by_the_user();

        if( $coins )
        {
            $checked = 'off';

            foreach( $coins as $coin )
            {
                if( !empty( $coin['network'] ) )
                {
                    if( $coin['asset'] === $currencies && $coin['network'] === $network ) $checked = 'on';
                
                }else{

                    if( $coin['asset'] === $currencies ) $checked = 'on';
                }
            }
            
            return $checked;

        } else {

            return false;
        }
    }
}

/**
 * Function to mark the coins saved by the user in the configuration page
 */
if ( ! function_exists( 'telepay_currencies_accepted_by_the_user' ) )
{
    function telepay_currencies_accepted_by_the_user()
    {
        global $wpdb;

        $table  = $wpdb->prefix . 'telepay_config';

        $query  = "SELECT coins FROM $table";

        $config = $wpdb->get_results( $query, ARRAY_A );

        $currencies_accepted_user  = isset( $config[0]['coins'] ) && !empty( $config[0]['coins'] ) ? json_decode( $config[0]['coins'] ) : array();

        $all_currencies  = telepay_get_assets();

        if( $all_currencies && !empty( $currencies_accepted_user ) )
        {
            $all_currencies_user = [];

            foreach( $currencies_accepted_user as $currencies_user )
            { 
                foreach( $all_currencies->assets as $currencies )
                {
                    if( !empty( $currencies->networks ) )
                    {
                        for( $i = 0; $i < count( $currencies->networks ); $i++ )
                        {
                            if( $currencies_user->asset === $currencies->asset && $currencies_user->network === $currencies->networks[$i] )
                            {
                                $all_currencies_user[] = [
                                    "asset"         => $currencies->asset, 
                                    "blockchain"    => $currencies->blockchain,
                                    "usd_price"     => $currencies->usd_price,
                                    "url"           => $currencies->url,
                                    "network"       => $currencies->networks[$i],
                                    "coingecko_id"  => $currencies->coingecko_id
                                ];
                            }
                        }

                    } else {

                        if( $currencies_user->asset === $currencies->asset )
                        {
                            $all_currencies_user[] = [
                                "asset"         => $currencies->asset, 
                                "blockchain"    => $currencies->blockchain,
                                "usd_price"     => $currencies->usd_price,
                                "url"           => $currencies->url,
                                "network"       => NULL,
                                "coingecko_id"  => $currencies->coingecko_id
                            ];
                        }
                    }
                }
            }

            return $all_currencies_user;

        } else {

            return false;
        }
    }
}

/**
 * Function to get the list of currencies available in TelePay directly from the API
 */
if ( ! function_exists( 'telepay_get_assets' ) )
{
    function telepay_get_assets()
    {
        $curl = curl_init();

        curl_setopt_array( $curl, [
            CURLOPT_URL             => 'https://api.telepay.cash/rest/getAssets',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "GET",
            CURLOPT_HTTPHEADER      => [
                "AUTHORIZATION: " . telepay_get_apikey(),
                "Accept: application/json"
            ],
        ]);
        
        $response   = curl_exec( $curl );

        $error      = curl_error( $curl );
        
        curl_close( $curl );
        
        if ( $error )
        {
            return false;

        } else {

            return json_decode( $response );
        }
    }
}

if ( ! function_exists( 'telepay_get_webhook' ) )
{
    function telepay_get_webhook()
    {
        $curl = curl_init();

        curl_setopt_array( $curl, [
            CURLOPT_URL             => "https://api.telepay.cash/rest/getWebook/webhook_id",
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "GET",
            CURLOPT_HTTPHEADER      => [
                "AUTHORIZATION: " . telepay_get_apikey(),
                "Accept: application/json"
            ],
        ]);

        $response   = curl_exec( $curl );

        $error      = curl_error( $curl );

        curl_close( $curl );
        
        if ( $error )
        {
            return false;

        } else {

            return json_decode( $response );
        }
    }
}

if ( ! function_exists( 'telepay_create_webhook' ) )
{
    function telepay_create_webhook()
    {
        $posfielfs = [
            'events'    => array( 
                'invoice.completed', 
                'invoice.cancelled',
                'invoice.expired',
                'invoice.deleted' 
            ),
            'active'    => true,
            'url'       => telepay_wc_api,
            'secret'    => telepay_encrypt()
        ];

        $curl = curl_init();

        curl_setopt_array( $curl, [
            CURLOPT_URL             => "https://api.telepay.cash/rest/createWebhook",
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "POST",
            CURLOPT_POSTFIELDS      => json_encode( $posfielfs ),
            CURLOPT_HTTPHEADER      => [
                "AUTHORIZATION: " . telepay_get_apikey(),
                "Accept: application/json",
                "Content-Type: application/json"
            ],
        ]);

        $response   = curl_exec( $curl );

        $error      = curl_error( $curl );

        curl_close( $curl );
        
        if ( $error )
        {
            return false;

        } else {

            return json_decode( $response );
        }
    }
}

if ( ! function_exists( 'telepay_update_webhook' ) )
{
    function telepay_update_webhook()
    {
        $posfielfs = [
            'events'    => array( 
                'invoice.completed', 
                'invoice.cancelled',
                'invoice.expired',
                'invoice.deleted' 
            ),
            'active'    => true,
            'url'       => telepay_wc_api,
            'secret'    => 'hola'.telepay_encrypt()
        ];

        $curl = curl_init();

        curl_setopt_array( $curl, [
            CURLOPT_URL             => "https://api.telepay.cash/rest/updateWebhook/webhook_id",
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "POST",
            CURLOPT_POSTFIELDS      => json_encode( $posfielfs ),
            CURLOPT_HTTPHEADER      => [
                "AUTHORIZATION: " . telepay_get_apikey(),
                "Accept: application/json",
                "Content-Type: application/json"
            ],
        ]);

        $response   = curl_exec( $curl );

        $error      = curl_error( $curl );

        curl_close( $curl );
        
        if ( $error )
        {
            return false;

        } else {
            
            return json_decode( $response );
        }
    }
}

if ( ! function_exists( 'telepay_encrypt' ) )
{
    function telepay_encrypt()
    {
        return hash('sha512', hash( 'sha1', base64_encode( telepay_get_apikey() . 'telepay_security' ) ) );
    }
}

function get_exchange_rate( $coingecko_id )
{
    if( !empty( $coingecko_id ) )
    {
        $client     = new CoinGeckoClient();

        $currency   = strtolower( get_woocommerce_currency() );

        $rate = $client->simple()->getPrice( $coingecko_id, $currency );

        return $rate["$coingecko_id"]["$currency"];

    } else {

        return false;
    }
}