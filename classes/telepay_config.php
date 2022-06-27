<?php

namespace telepayCash;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'telepay_config' ) )
{
    class telepay_config
    {
        public function __construct()
        {
            register_activation_hook( telepay_name_file_plugin,     array( $this, 'telepay_activate' ) );

            register_deactivation_hook( telepay_name_file_plugin,   array( $this, 'telepay_deactivate' ) );

            add_action( 'admin_enqueue_scripts',                    array( $this, 'telepay_enqueue_cdn_for_backend' ) );

            add_action( 'admin_enqueue_scripts',                    array( $this, 'telepay_enqueue_assets_for_backend' ) );

            add_action( 'wp_enqueue_scripts',                       array( $this, 'telepay_enqueue_assets_for_frontend' ) );
            
            add_action( 'wp_ajax_telepay_save_setting',             array( $this, 'telepay_ajax_page_setting' ) );
        }

        public function telepay_activate()
        { 
            global $wpdb;
	
            $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}telepay_config` ( 
                `id` INT NOT NULL,
                `apikey` varchar(150) NOT NULL,
                `coins` varchar(1500000) NOT NULL,
                `expiration_time` INT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $wpdb->query( $sql );
        }
       
        public function telepay_deactivate()
        {
            flush_rewrite_rules();
        }

        public function telepay_enqueue_cdn_for_backend()
        {
            if( isset($_GET['page']) && $_GET['page'] === 'telepay' )
            {
                wp_enqueue_style( 'telepay-bootstrap-backcss', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css', array(), telepay_plugin_version );

                wp_enqueue_script( 'telepay-bootstrap-backjs', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js', array('jquery'), telepay_plugin_version, true );

                wp_enqueue_script( 'telepay-sweetalert-backjs', 'https://unpkg.com/sweetalert/dist/sweetalert.min.js', array('jquery'), telepay_plugin_version, true );
            }
        }

        public function telepay_enqueue_assets_for_backend()
        {
            if( isset($_GET['page']) && $_GET['page'] === 'telepay' )
            {
                wp_enqueue_style( 'telepay-backcss', telepay_name_plugin . 'assets/back/back.css', array(), telepay_plugin_version );
                wp_enqueue_script( 'telepay-backjs', telepay_name_plugin . 'assets/back/back.js', array('jquery'), telepay_plugin_version, true );

                wp_localize_script( 'telepay-backjs', 'telepay_ajax_requests', 
                    array( 
                        'url'       => admin_url( 'admin-ajax.php' ), 
                        'action'    => 'telepay_save_setting',
                        'nonce'     => wp_create_nonce( 'telepay-ajax-nonce' ) 
                    )
                );
            }
        }

        public function telepay_enqueue_assets_for_frontend()
        {
            if( is_checkout() )
            {
                wp_enqueue_style( 'telepay-frontcss', telepay_name_plugin . 'assets/front/front.css', array(), telepay_plugin_version );
            }
        }

        public function telepay_ajax_page_setting()
        {
            $r = array( "r" => false, "m" => "Acción no permitida." );

            $nonce  = isset( $_POST['nonce'] ) ? $_POST['nonce'] : false;

            $nonce  = sanitize_text_field( $nonce );

            if ( wp_verify_nonce( $nonce, 'telepay-ajax-nonce' ) )
            {
                include_once telepay_name_dir . '/ajax/save_page_setting.php';
            }

            echo json_encode($r);

            wp_die();
        }
    }
}