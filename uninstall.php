<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) die;

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}telepay_config");