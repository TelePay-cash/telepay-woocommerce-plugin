jQuery( document ).ready( function( $ )
{
    $("#telepay-btn-save").on( 'click', function()
    {
        let i = 0, coins = [], xhr, coin, network, checked, api_key, time_exp;

        /**
         * We get the API KEY
         */
        api_key = $("#telepay-api-key").val();

        /**
         * We get the expiration time in milliseconds
         */
        time_exp = $("#telepay-time-exp").val();

        /**
         * We get the coins selected by the user
         */
        $("#telepay-coins input[type='checkbox']").each(function()
        {
            checked  = $( this ).prop('checked');
            
            if( checked )
            {
                coin    = $( this ).attr( 'coin' );
                network = $( this ).attr( 'network' );

                coins[i] = { coin, network };
                i++;
            }
        });

        /**
         * If there is more than one request, we abort it.
         */
        if( xhr && xhr.readystate != 1 ){ 
            xhr.abort(); 
        }

        xhr = $.ajax({

            type: "POST",
            dataType: 'json',
            url: telepay_ajax_requests.url,
            data: {
                action:             telepay_ajax_requests.action,
                nonce:              telepay_ajax_requests.nonce,
                save_page_setting:  true,
                coins:              coins,
                api_key:            api_key,
                time_exp:           time_exp
                
            },
            success:  function ( obj ){    

                if( obj.r ){

                    /**
                     * Success message
                     */
                    swal({
                        text: obj.m,
                        icon: "success",
                        button: "ok",
                    });

                    location.reload(true);
                }else{

                    /**
                     * Error message
                     */
                    swal({
                        text: obj.m,
                        icon: "error",
                        button: "ok",
                    })
                }
            },
        });
    });
});