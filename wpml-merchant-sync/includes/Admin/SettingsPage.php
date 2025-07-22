<?php
namespace WPMLMerchantSync\Admin;

class SettingsPage {

	/**
	 * SettingsPage constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'handle_manual_sync' ] );
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
			'sync',
			esc_html__( 'Sync Settings', 'wpml-merchant-sync' ),
			'__return_false',
			'wpml_merchant_sync'
		);

		add_settings_field(
			'sync_schedule',
			esc_html__( 'Sync Schedule', 'wpml-merchant-sync' ),
			[ $this, 'render_sync_schedule_field' ],
			'wpml_merchant_sync',
			'sync'
		);

		add_settings_field(
			'manual_sync',
			esc_html__( 'Manual Sync', 'wpml-merchant-sync' ),
			[ $this, 'render_manual_sync_field' ],
			'wpml_merchant_sync',
			'sync'
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
	 * Render the Sync Schedule field.
	 */
	public function render_sync_schedule_field() {
		$options = get_option( 'wpml_merchant_sync_settings' );
		$schedules = wp_get_schedules();
		?>
		<select name="wpml_merchant_sync_settings[sync_schedule]">
			<?php foreach ( $schedules as $name => $schedule ) : ?>
				<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $options['sync_schedule'] ?? 'hourly', $name ); ?>>
					<?php echo esc_html( $schedule['display'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the Manual Sync field.
	 */
	public function render_manual_sync_field() {
		?>
		<a href="<?php echo esc_url( add_query_arg( 'manual_sync', '1' ) ); ?>" class="button">
			<?php esc_html_e( 'Sync All Products', 'wpml-merchant-sync' ); ?>
		</a>
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

		if ( isset( $input['sync_schedule'] ) ) {
			$output['sync_schedule'] = sanitize_text_field( $input['sync_schedule'] );
		}

		return $output;
	}

	/**
	 * Handle the manual sync button.
	 */
	public function handle_manual_sync() {
		if ( ! empty( $_GET['manual_sync'] ) ) {
			wp_schedule_single_event( time(), SyncScheduler::CRON_HOOK );
			wp_safe_redirect( remove_query_arg( 'manual_sync' ) );
			exit;
		}
	}
}
