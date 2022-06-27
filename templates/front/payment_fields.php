<?php

global  $woocommerce;

$currencies_accepted_user = telepay_currencies_accepted_by_the_user();

?>
    <p><?= esc_html__( "Select the currency of your transaction", telepay_text_domain ) ?></p>

    <select name="telepay-currency-select" id="telepay-currency-select">

        <option value=""><?= esc_html__( "Select the currency", telepay_text_domain ) ?></option>

        <?php
    
            if( !empty( $currencies_accepted_user ) )
            {
                foreach( $currencies_accepted_user as $currencies )
                {
                    ?>
                    <option value="<?= base64_encode( json_encode( $currencies ) ) ?>" coin="<?= $currencies['asset'] ?>" rate="<?= get_exchange_rate( $currencies[ 'coingecko_id' ] ) ?>"><?= $currencies['asset'] ?> <?= $network = $currencies['network'] ? '(' . $currencies['network'] . ')' : '' ?> </option>
                    <?php
                }
            }
        ?>
        
    </select>

    <div id="telepay-total-price"></div>

    <script>
        jQuery( document ).ready( function( $ )
        {
            $("#telepay-currency-select").change( function()
            {
                let coin, rate, total, conv;

                coin    = $( '#telepay-currency-select option:selected' ).attr( 'coin' );
                rate    = $( '#telepay-currency-select option:selected' ).attr( 'rate' );
                total   = '<?= WC()->cart->total ?>';
                conv    = total / parseFloat( rate );

                if( coin && rate )
                {
                    $( '#telepay-total-price' ).html( 'Total: ' + conv + ' ' + coin );
                } else {
                    $( '#telepay-total-price' ).html('');
                }
            });
        });
    </script>
