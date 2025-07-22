<?php
namespace WPMLMerchantSync\Services;

use WPMLMerchantSync\Plugin;

class RealTimeSync {

	/**
	 * RealTimeSync constructor.
	 */
	public function __construct() {
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

		$this->queue_sync( $post_id );
	}

	/**
	 * Schedule a sync when a product's stock is changed.
	 *
	 * @param \WC_Product $product The product object.
	 */
	public function schedule_sync_from_stock_change( $product ) {
		$this->queue_sync( $product->get_id() );
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

		$this->queue_sync( $post_id );
	}

	/**
	 * Queue a sync for a product.
	 *
	 * @param int $product_id The product ID.
	 */
	protected function queue_sync( $product_id ) {
		// In a real implementation, this would use Action Scheduler
		// to queue a background job. For now, we'll just invalidate the cache.
		Plugin::instance()->cache_manager->invalidate_product( $product_id );
	}
}
