<?php
namespace WPMLMerchantSync;

use WPMLMerchantSync\Helpers\WPML;
use WPMLMerchantSync\Repositories\ProductRepository;
use WPMLMerchantSync\Services\MerchantApiClient;
use WPMLMerchantSync\Services\FeedBuilder;
use WPMLMerchantSync\Services\CacheManager;
use WPMLMerchantSync\Services\SyncScheduler;
use WPMLMerchantSync\Services\RealTimeSync;
use WPMLMerchantSync\Controllers\RestController;
use WPMLMerchantSync\Admin\SettingsPage;

class Plugin {

	/**
	 * @var Plugin The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * @var WPML
	 */
	public $wpml;

	/**
	 * @var ProductRepository
	 */
	public $product_repository;

	/**
	 * @var MerchantApiClient
	 */
	public $merchant_api_client;

	/**
	 * @var FeedBuilder
	 */
	public $feed_builder;

	/**
	 * @var CacheManager
	 */
	public $cache_manager;

	/**
	 * @var SyncScheduler
	 */
	public $sync_scheduler;

	/**
	 * @var RealTimeSync
	 */
	public $real_time_sync;

	/**
	 * @var RestController
	 */
	public $rest_controller;

	/**
	 * @var SettingsPage
	 */
	public $settings_page;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of the plugin is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Initialize the plugin
	 */
	public static function init() {
		$instance = self::instance();
		$instance->load_textdomain();
		$instance->wpml = new WPML();
		$instance->product_repository = new ProductRepository( $instance->wpml );
		$instance->merchant_api_client = new MerchantApiClient();
		$instance->feed_builder = new FeedBuilder();
		$instance->cache_manager = new CacheManager();
		$instance->sync_scheduler = new SyncScheduler();
		$instance->real_time_sync = new RealTimeSync();
		$instance->rest_controller = new RestController();
		$instance->settings_page = new SettingsPage();
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wpml-merchant-sync', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
