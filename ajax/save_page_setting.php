<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( isset( $_REQUEST['save_page_setting'] ) )
{
    global $wpdb;

    $table      = $wpdb->prefix . 'telepay_config';

    /**
     * We receive the data by AJAX
     */
    $api_key    = isset( $_REQUEST['api_key'] ) ? $_REQUEST['api_key'] : '';

    $coins      = isset( $_REQUEST['coins'] ) ? $_REQUEST['coins'] : array();
    
    $time_exp   = isset( $_REQUEST['time_exp'] ) ? $_REQUEST['time_exp'] : '';

    /**
     * We sanitize the data to save it later in the database
     */
    $time_exp   = sanitize_text_field( $time_exp );

    $api_key    = sanitize_text_field( $api_key );

    /**
     * We save in an array all the data of the currencies selected by the user
     */
    $currencies_accepted_user = [];

    /**
     * We get all coins from the API
     */
    $assets     = telepay_get_assets();
    
    if( !empty( $coins ) )
    {
        /**
         * We compare the selected currencies with the currencies in the t API and filter the data
         */
        foreach( $coins as $coin )
        {
            foreach( $assets->assets as $asset )
            {
                if( !empty( $asset->networks ) )
                {
                    foreach( $asset->networks as $network )
                    {
                        if( $coin['coin'] === $asset->asset && $coin['network'] === $network )
                        {
                            $currencies_accepted_user[] = [
                                'asset'         => $asset->asset,
                                'network'      => $network,
                            ];
                        }
                    }
                }else{

                    if( $coin['coin'] === $asset->asset )
                    {
                        $currencies_accepted_user[] = [
                            'asset'         => $asset->asset,
                            'network'      => NULL,
                        ];
                    }
                }
            } 
        }
    }

    /**
     * We encode the selected currencies to save them in JSON format
     */
    $coins      = json_encode( $currencies_accepted_user );

    /**
     * Error messages
     */
    if( empty( $api_key ) )
    {
        $r['m'] = esc_html__( "The API KEY must not be empty.", telepay_text_domain );

    }elseif( empty( $time_exp ) ){

        $r['m'] = esc_html__( "It must indicate the expiration time in milliseconds.", telepay_text_domain );

    }elseif( !is_numeric( $time_exp ) ){

        $r['m'] = esc_html__( "The expiration time must be an integer represented in milliseconds.", telepay_text_domain );

    }else{

        /**
         * If there are no error messages we save the data in the database
         */
        
        /**
         * Check if there are records in the database
         */
        $query  = "SELECT id FROM $table";

        $conf   = $wpdb->get_results( $query, ARRAY_A );

        /**
         * If there are no records we insert a new row, otherwise we update the existing row
         */
        if( empty( $conf[0] ) )
        {
            $wpdb->insert( $table, array( 'id' => 1, 'apikey' => $api_key, 'coins' => $coins, 'expiration_time' => intval( $time_exp ) ) );

            telepay_create_webhook();

        }else{

            $wpdb->update( $table, array( 'apikey' => $api_key, 'coins' => $coins, 'expiration_time' => intval( $time_exp ) ), array( 'id' => 1 ) );

            $webhook = telepay_update_webhook();

            if( isset( $webhook->error ) )
            {
                telepay_create_webhook();
            }
        }

        /**
         * Success message to send by AJAX
         */
        $r['m'] = esc_html__( "The data has been saved successfully", telepay_text_domain );
        $r['r'] = true;
    }
}