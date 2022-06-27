<?php

namespace telepayCash;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'telepay_page_setting' ) )
{
    class telepay_page_setting
    {
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'telepay_add_menu_in_admin_area' ) );
        }

        public function telepay_add_menu_in_admin_area()
        {
            add_menu_page(
                'TelePay',
                'TelePay',
                'manage_options',
                'telepay',
                array( $this, 'telepay_content_page_menu' ), 
                telepay_name_plugin . 'assets/img/telepay-icon.png', 
                '5'
            );
        }

        public function telepay_content_page_menu()
        {
            include_once telepay_name_dir . '/templates/back/content_page_menu.php';
        }
    }
}