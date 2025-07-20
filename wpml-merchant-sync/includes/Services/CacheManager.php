<?php
namespace WPMLMerchantSync\Services;

class CacheManager {

	const CACHE_GROUP = 'wpml_merchant_sync';
	const CACHE_PREFIX = 'wpml_merchant_sync_feed_';

	/**
	 * Get the cache key for a feed.
	 *
	 * @param string $lang   The language of the feed.
	 * @param string $format The format of the feed.
	 * @return string The cache key.
	 */
	protected function get_cache_key( $lang, $format ) {
		return self::CACHE_PREFIX . "_{$lang}_{$format}";
	}

	/**
	 * Get a cached feed.
	 *
	 * @param string $lang   The language of the feed.
	 * @param string $format The format of the feed.
	 * @return false|mixed The cached feed or false if not found.
	 */
	public function get( $lang, $format ) {
		return wp_cache_get( $this->get_cache_key( $lang, $format ), self::CACHE_GROUP );
	}

	/**
	 * Cache a feed.
	 *
	 * @param string $lang   The language of the feed.
	 * @param string $format The format of the feed.
	 * @param mixed  $data   The data to cache.
	 * @param int    $ttl    The cache TTL in seconds.
	 */
	public function set( $lang, $format, $data, $ttl = 3600 ) {
		wp_cache_set( $this->get_cache_key( $lang, $format ), $data, self::CACHE_GROUP, $ttl );
	}

	/**
	 * Invalidate the cache for a specific feed.
	 *
	 * @param string $lang   The language of the feed.
	 * @param string $format The format of the feed.
	 */
	public function invalidate( $lang, $format ) {
		wp_cache_delete( $this->get_cache_key( $lang, $format ), self::CACHE_GROUP );
	}

	/**
	 * Invalidate the cache for a specific product.
	 *
	 * @param int $product_id The ID of the product to invalidate.
	 */
	public function invalidate_product( $product_id ) {
		// Invalidate all language and format combinations for this product.
		// This is a simplified approach. A more sophisticated approach would be
		// to track which feeds a product belongs to.
		$settings = get_option( 'wpml_merchant_sync_settings', [] );
		$languages = ! empty( $settings['enabled_languages'] ) ? $settings['enabled_languages'] : [];
		$formats = [ 'json', 'xml' ];

		foreach ( $languages as $lang ) {
			foreach ( $formats as $format ) {
				$this->invalidate( $lang, $format );
			}
		}
	}
}
