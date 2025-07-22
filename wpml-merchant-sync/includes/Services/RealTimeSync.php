<?php
namespace WPMLMerchantSync\Services;

class RealTimeSync {

	/**
	 * @var SyncService
	 */
	protected $sync_service;

	/**
	 * RealTimeSync constructor.
	 *
	 * @param SyncService $sync_service
	 */
	public function __construct( SyncService $sync_service ) {
		$this->sync_service = $sync_service;

		add_action( 'save_post_product', [ $this, 'schedule_sync' ], 10, 2 );
		add_action( 'woocommerce_product_set_stock', [ $this, 'schedule_sync_from_stock_change' ] );
		add_action( 'wpml_after_save_post', [ $this, 'schedule_sync_from_translation' ], 10, 2 );
	}

	/**
	 * Schedule a sync when a product is saved.
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 */
	public function schedule_sync( $post_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}

		$this->sync_service->sync_product( $post_id );
	}

	/**
	 * Schedule a sync when a product's stock is changed.
	 *
	 * @param \WC_Product $product The product object.
	 */
	public function schedule_sync_from_stock_change( $product ) {
		$this->sync_service->sync_product( $product->get_id() );
	}

	/**
	 * Schedule a sync when a translation is saved.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $data    The translation data.
	 */
	public function schedule_sync_from_translation( $post_id, $data ) {
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		$this->sync_service->sync_product( $post_id );
	}
}
