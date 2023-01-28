<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$assets = telepay_get_assets();

?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 p-5">

            <div class="row my-5">
                <img src="<?= telepay_name_plugin . 'assets/img/telepay-logo.svg' ?>" width="200pt" alt="TelePay">
            </div>

            <div class="row mb-5">
                <div class="input-group input-group-lg">
                    <input type="text" id="telepay-api-key" class="form-control" placeholder="<?= esc_html__( "Enter the API KEY", telepay_text_domain ) ?>" value="<?= telepay_get_apikey() ?>">
                </div>
                <label for="telepay-api-key"><?= esc_html__( "Add your Merchant API Key found in your TelePay account.", telepay_text_domain ) ?></label>
            </div>

            <div class="row mb-5">
                <div class="input-group input-group-lg">
                    <input type="number" id="telepay-time-exp" class="form-control" placeholder="<?= esc_html__( "Enter the expiration time in minutes", telepay_text_domain ) ?>" value="<?= telepay_get_expiration_time() ?>">
                </div>
                <label for="telepay-time-exp"><?= esc_html__( "Add the maximum expiration time of an invoice in case the client does not make the payment.", telepay_text_domain ) ?></label>
            </div>

            <div class="row mb-5" id="telepay-coins">
                <h4 class="text-center mb-3"><?= esc_html__( "Coins to accept", telepay_text_domain ) ?></h4>

                <?php

                if( is_object( $assets ) && isset($assets->assets) )
                {
                    foreach( $assets->assets as $asset )
                    {
                        
                        if( !empty( $asset->networks ) )
                        {
                            for( $i = 0; $i < count( $asset->networks ); $i++ )
                            {
                                ?>
                                <div class="form-check">
                                    <input type="checkbox" coin="<?= $asset->asset ?>" network="<?= $asset->networks[$i] ?>" <?php checked( telepay_accepted_currencies( $asset->asset, $asset->networks[$i] ), 'on' ); ?>>
                                    <label class="form-check-label" for="<?= $asset->asset ?>"><?= $asset->asset ?> (<?= $asset->networks[$i] ?>)</label>
                                </div>
                                <?php
                            }
                        }else{
                            ?>
                            <div class="form-check">
                                <input type="checkbox" coin="<?= $asset->asset ?>" network="" <?php checked( telepay_accepted_currencies( $asset->asset, '' ), 'on' ); ?>>
                                <label class="form-check-label" for="<?= $asset->asset ?>"><?= $asset->asset ?></label>
                            </div>
                            <?php
                        }
                    } 
                }else{
                    ?>
                    <p><?= esc_html__( "Enter a correct API KEY so that the list of currencies appears and you can select those with which you want to receive your payments", telepay_text_domain ) ?></p>
                    <?php
                }
                ?>

            </div>

            <button type="button" id="telepay-btn-save" class="btn btn-primary btn-lg w-100"><?= esc_html__( "Save", telepay_text_domain ) ?></button>
            
        </div>
    </div>
</div>