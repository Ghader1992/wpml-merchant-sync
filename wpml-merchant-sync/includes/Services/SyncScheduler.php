<?php
namespace WPMLMerchantSync\Services;

class SyncScheduler {

	const CRON_HOOK = 'wpml_merchant_sync_batch_sync';

	/**
	 * SyncScheduler constructor.
	 */
	public function __construct() {
		add_action( self::CRON_HOOK, [ $this, 'run_batch_sync' ] );

		$settings = get_option( 'wpml_merchant_sync_settings', [] );
		$schedule = ! empty( $settings['sync_schedule'] ) ? $settings['sync_schedule'] : 'hourly';

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), $schedule, self::CRON_HOOK );
		}
	}

	/**
	 * Run the batch sync.
	 */
	public function run_batch_sync() {
		$this->log( 'Running batch sync...' );
		$product_ids = Plugin::instance()->product_repository->get_all_products( 'en' ); // Assuming 'en' is the default language
		$sync_service = new SyncService();
		foreach ( $product_ids as $product_id ) {
			$sync_service->sync_product( $product_id );
		}
		$this->log( 'Batch sync complete.' );
	}

	/**
	 * Log a message to the log file.
	 *
	 * @param string $message The message to log.
	 */
	protected function log( $message ) {
		$upload_dir = wp_upload_dir();
		$log_file = $upload_dir['basedir'] . '/merchant-sync.log';
		$timestamp = date( 'Y-m-d H:i:s' );
		file_put_contents( $log_file, "[$timestamp] $message\n", FILE_APPEND );
	}
}
