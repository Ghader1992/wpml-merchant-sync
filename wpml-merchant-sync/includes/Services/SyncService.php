<?php
namespace WPMLMerchantSync\Services;

use WPMLMerchantSync\Repositories\ProductRepository;
use WPMLMerchantSync\Helpers\WPML;

class SyncService {

	/**
	 * @var ProductRepository
	 */
	protected $product_repository;

	/**
	 * @var MerchantApiClient
	 */
	protected $merchant_api_client;

	/**
	 * @var WPML
	 */
	protected $wpml;

	/**
	 * SyncService constructor.
	 *
	 * @param ProductRepository   $product_repository
	 * @param MerchantApiClient   $merchant_api_client
	 * @param WPML                $wpml
	 */
	public function __construct(
		ProductRepository $product_repository,
		MerchantApiClient $merchant_api_client,
		WPML $wpml
	) {
		$this->product_repository   = $product_repository;
		$this->merchant_api_client  = $merchant_api_client;
		$this->wpml                 = $wpml;
	}

	/**
	 * Sync all products for a given language.
	 *
	 * @param string $lang
	 */
	public function sync_all_products( $lang ) {
		$product_ids = $this->product_repository->get_all_products( $lang );

		foreach ( $product_ids as $product_id ) {
			$this->sync_product( $product_id );
		}
	}

	/**
	 * Sync a single product.
	 *
	 * @param int $product_id
	 */
	public function sync_product( $product_id ) {
		$this->log( "Syncing product {$product_id}..." );
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			$this->log( "Product {$product_id} not found." );
			return;
		}

		$product_data = $this->get_product_data( $product );
		$merchant_id  = get_post_meta( $product_id, '_merchant_id', true );

		if ( $merchant_id ) {
			$this->log( "Updating product {$product_id} (merchant ID: {$merchant_id})." );
			$response = $this->merchant_api_client->update_product( $merchant_id, $product_data );
		} else {
			$this->log( "Inserting product {$product_id}." );
			$response = $this->merchant_api_client->insert_product( $product_data );
			if ( ! is_wp_error( $response ) ) {
				update_post_meta( $product_id, '_merchant_id', $response['id'] );
				$this->log( "Product {$product_id} inserted (merchant ID: {$response['id']})." );
			}
		}

		if ( is_wp_error( $response ) ) {
			$this->log( "Error syncing product {$product_id}: " . $response->get_error_message() );
		}
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

	/**
	 * Get the product data in the format required by the Google Content API.
	 *
	 * @param \WC_Product $product
	 * @return array
	 */
	private function get_product_data( $product ) {
		$lang = $this->wpml->get_product_language( $product->get_id() );

		return [
			'offerId'         => $product->get_id(),
			'title'           => $product->get_name(),
			'description'     => $product->get_description(),
			'link'            => $product->get_permalink(),
			'imageLink'       => wp_get_attachment_url( $product->get_image_id() ),
			'contentLanguage' => $lang,
			'targetCountry'   => $this->wpml->get_country_for_language( $lang ),
			'channel'         => 'online',
			'availability'    => $product->is_in_stock() ? 'in stock' : 'out of stock',
			'price'           => [
				'value'    => $product->get_price(),
				'currency' => get_woocommerce_currency(),
			],
		];
	}
}
