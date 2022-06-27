<?php

if ( !class_exists( 'telepay_wc_api' ) )
{
	class telepay_wc_api extends WC_Payment_Gateway
	{
		public function __construct()
		{
			$this->telepay_handler();
		}
		
		public function telepay_handler()
		{
			$headers = apache_request_headers();
			
			if( isset( $headers['User-Agent'] ) && $headers['User-Agent'] === 'TelePay' && isset( $headers['Webhook-Signature'] )  )
			{
				global $woocommerce;

				$raw_post 	= file_get_contents( 'php://input' );

				$data  		= json_decode( $raw_post );
				
				$order      = new WC_Order( $data->invoice->metadata->orderID );

				switch ( $data->event )
				{
					case 'invoice.completed':

						$order->update_status( 'completed' );

						$order->payment_complete();
					
					break;

					case 'invoice.cancelled':

						$order->update_status( 'cancelled' );
						
					break;

					case 'invoice.expired':

						$order->update_status( 'failed' );
						
					break;

					case 'invoice.deleted':

					break;
				}
			}
			
			die();
		}
	}

	( new telepay_wc_api );
}