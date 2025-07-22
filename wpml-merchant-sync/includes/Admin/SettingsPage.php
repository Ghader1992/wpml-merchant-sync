<?php
namespace WPMLMerchantSync\Admin;

use WPMLMerchantSync\Services\SyncService;

class SettingsPage {

	/**
	 * SettingsPage constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_post_wpml_merchant_sync_manual_sync', [ $this, 'manual_sync' ] );
	}

	/**
	 * Add the settings page to the WooCommerce menu.
	 */
	public function add_settings_page() {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Merchant Sync', 'wpml-merchant-sync' ),
			esc_html__( 'Merchant Sync', 'wpml-merchant-sync' ),
			'manage_woocommerce',
			'wpml-merchant-sync',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wpml_merchant_sync' );
				do_settings_sections( 'wpml_merchant_sync' );
				submit_button();
				?>
			</form>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="wpml_merchant_sync_manual_sync">
				<?php submit_button( esc_html__( 'Manual Sync', 'wpml-merchant-sync' ), 'secondary' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the settings.
	 */
	public function register_settings() {
		register_setting( 'wpml_merchant_sync', 'wpml_merchant_sync_settings', [ $this, 'sanitize_settings' ] );

		add_settings_section(
			'google_api',
			esc_html__( 'Google API Settings', 'wpml-merchant-sync' ),
			'__return_false',
			'wpml_merchant_sync'
		);

		add_settings_field(
			'google_service_account',
			esc_html__( 'Google Service Account JSON', 'wpml-merchant-sync' ),
			[ $this, 'render_google_service_account_field' ],
			'wpml_merchant_sync',
			'google_api'
		);

		add_settings_section(
			'feed_settings',
			esc_html__( 'Feed Settings', 'wpml-merchant-sync' ),
			'__return_false',
			'wpml_merchant_sync'
		);

		add_settings_field(
			'enabled_languages',
			esc_html__( 'Enabled Languages', 'wpml-merchant-sync' ),
			[ $this, 'render_enabled_languages_field' ],
			'wpml_merchant_sync',
			'feed_settings'
		);

		add_settings_field(
			'default_feed_format',
			esc_html__( 'Default Feed Format', 'wpml-merchant-sync' ),
			[ $this, 'render_default_feed_format_field' ],
			'wpml_merchant_sync',
			'feed_settings'
		);

		add_settings_section(
			'sync_settings',
			esc_html__( 'Sync Settings', 'wpml-merchant-sync' ),
			'__return_false',
			'wpml_merchant_sync'
		);

		add_settings_field(
			'sync_schedule',
			esc_html__( 'Sync Schedule', 'wpml-merchant-sync' ),
			[ $this, 'render_sync_schedule_field' ],
			'wpml_merchant_sync',
			'sync_settings'
		);

		add_settings_field(
			'cache_ttl',
			esc_html__( 'Cache TTL', 'wpml-merchant-sync' ),
			[ $this, 'render_cache_ttl_field' ],
			'wpml_merchant_sync',
			'sync_settings'
		);
	}

	/**
	 * Render the Google Service Account JSON field.
	 */
	public function render_google_service_account_field() {
		$options = get_option( 'wpml_merchant_sync_settings' );
		?>
		<textarea name="wpml_merchant_sync_settings[google_service_account]" rows="10" cols="50" class="large-text"><?php echo esc_textarea( $options['google_service_account'] ?? '' ); ?></textarea>
		<?php
	}

	/**
	 * Render the enabled languages field.
	 */
	public function render_enabled_languages_field() {
		$options = get_option( 'wpml_merchant_sync_settings' );
		$languages = function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=0' ) : [];
		?>
		<?php foreach ( $languages as $lang ) : ?>
			<label>
				<input type="checkbox" name="wpml_merchant_sync_settings[enabled_languages][]" value="<?php echo esc_attr( $lang['language_code'] ); ?>" <?php checked( in_array( $lang['language_code'], $options['enabled_languages'] ?? [] ) ); ?>>
				<?php echo esc_html( $lang['translated_name'] ); ?>
			</label>
			<br>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Render the default feed format field.
	 */
	public function render_default_feed_format_field() {
		$options = get_option( 'wpml_merchant_sync_settings' );
		?>
		<select name="wpml_merchant_sync_settings[default_feed_format]">
			<option value="json" <?php selected( 'json', $options['default_feed_format'] ?? 'json' ); ?>>JSON</option>
			<option value="xml" <?php selected( 'xml', $options['default_feed_format'] ?? '' ); ?>>XML</option>
		</select>
		<?php
	}

	/**
	 * Render the sync schedule field.
	 */
	public function render_sync_schedule_field() {
		$options = get_option( 'wpml_merchant_sync_settings' );
		?>
		<select name="wpml_merchant_sync_settings[sync_schedule]">
			<option value="hourly" <?php selected( 'hourly', $options['sync_schedule'] ?? 'hourly' ); ?>>Hourly</option>
			<option value="daily" <?php selected( 'daily', $options['sync_schedule'] ?? '' ); ?>>Daily</option>
		</select>
		<?php
	}

	/**
	 * Render the cache TTL field.
	 */
	public function render_cache_ttl_field() {
		$options = get_option( 'wpml_merchant_sync_settings' );
		?>
		<input type="number" name="wpml_merchant_sync_settings[cache_ttl]" value="<?php echo esc_attr( $options['cache_ttl'] ?? 3600 ); ?>">
		<?php
	}

	/**
	 * Sanitize the settings.
	 *
	 * @param array $input The input to sanitize.
	 * @return array The sanitized input.
	 */
	public function sanitize_settings( $input ) {
		$output = [];

		if ( isset( $input['google_service_account'] ) ) {
			$output['google_service_account'] = sanitize_textarea_field( $input['google_service_account'] );
		}

		if ( isset( $input['enabled_languages'] ) ) {
			$output['enabled_languages'] = array_map( 'sanitize_text_field', $input['enabled_languages'] );
		}

		if ( isset( $input['default_feed_format'] ) ) {
			$output['default_feed_format'] = sanitize_text_field( $input['default_feed_format'] );
		}

		if ( isset( $input['sync_schedule'] ) ) {
			$output['sync_schedule'] = sanitize_text_field( $input['sync_schedule'] );
		}

		if ( isset( $input['cache_ttl'] ) ) {
			$output['cache_ttl'] = absint( $input['cache_ttl'] );
		}

		return $output;
	}

	/**
	 * Trigger a manual sync.
	 */
	public function manual_sync() {
		$sync_service = new SyncService();
		$product_ids = \WPMLMerchantSync\Plugin::instance()->product_repository->get_all_products( 'en' ); // Assuming 'en' is the default language
		foreach ( $product_ids as $product_id ) {
			$sync_service->sync_product( $product_id );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=wpml-merchant-sync&synced=true' ) );
		exit;
	}
}
