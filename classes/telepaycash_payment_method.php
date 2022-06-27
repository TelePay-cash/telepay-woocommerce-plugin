<?php

class telepayCash_payment_method extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id                   = 'telepaycash_gateway';
        $this->icon                 = telepay_name_plugin . 'assets/img/telepay-icon.png';
        $this->has_fields           = true;
        $this->method_title         = __( "TelePay Cash", telepay_text_domain );
        $this->method_description   = __( "Receive crypto-payments with TelePay, create your merchant account, get your API key and automate your invoices. TelePay has a web dashboard, a Telegram integration and more for you to grow your business.", telepay_text_domain );
        $this->supports           = array(
            'products',
            'refunds',
        );
        
        $this->init_form_fields();
        $this->init_settings();
        
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_settings()
    {
        parent::init_settings();
        $this->enabled      = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
        $this->title        = $this->get_option('title');
        //$this->description  = $this->method_description;
    }

    public function init_form_fields()
    {
        $this->form_fields = array(

            'enabled'       => array(
                'title'         => __( 'Enable/Disable', telepay_text_domain ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable TelePay payment method', telepay_text_domain ),
                'default'       => 'yes'
            ),

            'title'         => array(
                'title'         => __( 'title', telepay_text_domain ),
                'type'          => 'text',
                'description'   => __( 'This controls the title which the user sees during checkout.', telepay_text_domain ),
                'default'       => __( 'TelePay Cash', telepay_text_domain ),
                'desc_tip'      => true,
            ),

            'description'   => array(
                'title'         => __( 'Customer Message', telepay_text_domain ),
                'type'          => 'textarea',
                'default'       => 'Receive crypto-payments with TelePay, create your merchant account, get your API key and automate your invoices. TelePay has a web dashboard, a Telegram integration and more for you to grow your business.'
            )
        );
    }

    function payment_fields()
    {
        require telepay_name_dir . '/templates/front/payment_fields.php';
    }

    function validate_fields()
    {
        if ( isset( $_POST['telepay-currency-select'] ) && !empty( $_POST['telepay-currency-select'] ) )
        {
            return true;
            
        }else{

            wc_add_notice( __('TelePay: You must select a currency', telepay_text_domain ) , 'error' );

            return false;
        }
    }

    function process_payment( $order_id )
    {
        global $woocommerce;

        $order      = new WC_Order( $order_id );

        $currency   = $_POST['telepay-currency-select'];

        $currency   = json_decode( base64_decode( $currency ) );

        $time       = telepay_get_expiration_time();

        $total_conv = $order->get_total() / get_exchange_rate( $currency->coingecko_id );

        $curl       = curl_init();

        $posfielfs = [
            'asset'         => $currency->asset,
            'blockchain'    => $currency->blockchain,
            'network'       => $currency->network,
            'amount'        => $total_conv,
            'success_url'   => $this->get_return_url( $order ),
            'cancel_url'    => $this->get_return_url( $order ),
            'metadata'      => array( 
                'orderID'   => $order_id,
            ),
            'expires_at'    => $time
        ];

        curl_setopt_array( $curl, [
            CURLOPT_URL             => 'https://api.telepay.cash/rest/createInvoice',
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
            wc_add_notice( __( 'TelePay error:',  telepay_text_domain ) . $error, 'error' );

            return;
            
        } else {
        
            $invoice    = json_decode( $response );

            if( isset( $invoice->number ) && isset( $invoice->checkout_url ) )
            {
                $note       = 'Waiting for customer to pay next TelePay bill #' . $invoice->number . ', For the amount of ' . $total_conv . ' ' . $currency->asset . '. ';
            
                // Mark as on-hold
                $order->update_status( 'on-hold', __( $note, telepay_text_domain ) );
            
                // Remove cart
                $woocommerce->cart->empty_cart();
            
                // Return thankyou redirect
                return array(
                    'result'    => 'success',
                    'redirect'  => $invoice->checkout_url
                );
                
            } else {
                wc_add_notice( __( 'TelePay error: Payment could not be made',  telepay_text_domain ) . $error, 'error' );

                return;
            }
        }
    }
}