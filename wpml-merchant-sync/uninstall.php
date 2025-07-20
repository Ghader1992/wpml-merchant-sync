<?php
/**
 * Uninstall WPML Merchant Sync
 *
 * Deletes all plugin data.
 *
 * @package WPML_Merchant_Sync
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'wpml_merchant_sync_settings' );

// Delete transients.
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_wpml\_merchant\_sync\_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_wpml\_merchant\_sync\_%'" );

// Delete logs.
$upload_dir = wp_upload_dir();
$log_file = $upload_dir['basedir'] . '/merchant-sync.log';
if ( file_exists( $log_file ) ) {
    unlink( $log_file );
}
