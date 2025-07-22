<?php
namespace WPMLMerchantSync\Services;

use WPMLMerchantSync\Helpers\WPML;

class SyncScheduler {

	const CRON_HOOK = 'wpml_merchant_sync_batch_sync';

	/**
	 * @var SyncService
	 */
	protected $sync_service;

	/**
	 * @var WPML
	 */
	protected $wpml;

	/**
	 * SyncScheduler constructor.
	 *
	 * @param SyncService $sync_service
	 * @param WPML $wpml
	 */
	public function __construct( SyncService $sync_service, WPML $wpml ) {
		$this->sync_service = $sync_service;
		$this->wpml = $wpml;

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

		foreach ( $this->wpml->get_active_languages() as $lang ) {
			$this->sync_service->sync_all_products( $lang['code'] );
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
