<?php
/**
 * Plugin Name: WPML Merchant Sync
 * Description: Connects WooCommerce to Google Merchant Center via the Content API, fully supporting English and Arabic translations via WPML.
 * Version: 1.0.0
 * Author: Jules
 * Text Domain: wpml-merchant-sync
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check for dependencies.
add_action( 'plugins_loaded', 'wpml_merchant_sync_check_dependencies' );

function wpml_merchant_sync_check_dependencies() {
    $missing_dependencies = [];

    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        $missing_dependencies[] = 'WooCommerce';
    }

    if ( ! is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
        $missing_dependencies[] = 'WPML';
    }

    if ( ! empty( $missing_dependencies ) ) {
        add_action( 'admin_notices', function() use ( $missing_dependencies ) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %s: a comma-separated list of plugin names */
                        esc_htmlesc_html__( 'The WPML Merchant Sync plugin requires the following plugins to be active: %s.', 'wpml-merchant-sync' ),
                        implode( ', ', $missing_dependencies )
                    );
                    ?>
                </p>
            </div>
            <?php
        } );

        deactivate_plugins( plugin_basename( __FILE__ ) );
    } else {
        // Initialize the plugin.
        require_once __DIR__ . '/vendor/autoload.php';
        add_action( 'plugins_loaded', [ \WPMLMerchantSync\Plugin::class, 'init' ] );
    }
}

register_uninstall_hook( __FILE__, 'wpml_merchant_sync_uninstall' );

function wpml_merchant_sync_uninstall() {
    // Delete options, transients, etc.
    // This will be implemented in a later step.
}
