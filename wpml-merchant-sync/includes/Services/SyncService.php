<?php
namespace WPMLMerchantSync\Services;

use WPMLMerchantSync\Plugin;

class SyncService {

	/**
	 * @var MerchantApiClient
	 */
	protected $api_client;

	/**
	 * @var FeedBuilder
	 */
	protected $feed_builder;

	/**
	 * SyncService constructor.
	 */
	public function __construct() {
		$this->api_client = new MerchantApiClient();
		$this->feed_builder = new FeedBuilder();
	}

	/**
	 * Sync a product.
	 *
	 * @param int $product_id The ID of the product to sync.
	 */
	public function sync_product( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$settings = get_option( 'wpml_merchant_sync_settings', [] );
		$languages = ! empty( $settings['enabled_languages'] ) ? $settings['enabled_languages'] : [];

		foreach ( $languages as $lang ) {
			$translated_id = Plugin::instance()->wpml->get_translated_id( $product_id, $lang );
			if ( ! $translated_id ) {
				continue;
			}

			$translated_product = wc_get_product( $translated_id );
			if ( ! $translated_product ) {
				continue;
			}

			$payload = $this->feed_builder->format_product( $translated_product );
			$merchant_id = get_post_meta( $translated_id, '_google_merchant_id', true );

			if ( $merchant_id ) {
				$this->api_client->update_product( $merchant_id, $payload );
			} else {
				$response = $this->api_client->insert_product( $payload );
				if ( ! is_wp_error( $response ) && isset( $response['id'] ) ) {
					update_post_meta( $translated_id, '_google_merchant_id', $response['id'] );
				}
			}
		}
	}
}
