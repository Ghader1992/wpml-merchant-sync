<?php
namespace WPMLMerchantSync\Repositories;

use WPMLMerchantSync\Helpers\WPML;

class ProductRepository {

	/**
	 * @var WPML
	 */
	protected $wpml;

	/**
	 * ProductRepository constructor.
	 *
	 * @param WPML $wpml
	 */
	public function __construct( WPML $wpml ) {
		$this->wpml = $wpml;
	}

	/**
	 * Get all published products.
	 *
	 * @param string $lang The language to get products for.
	 * @return int[]
	 */
	public function get_all_products( $lang ) {
		$args = [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'lang'           => $lang,
		];

		$query = new \WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Get products filtered by category.
	 *
	 * @param string $lang The language to get products for.
	 * @param string $category The category to filter by.
	 * @return int[]
	 */
	public function get_products_by_category( $lang, $category ) {
		$args = [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'lang'           => $lang,
			'tax_query'      => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $category,
				],
			],
		];

		$query = new \WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Get products modified after a certain date.
	 *
	 * @param string $lang The language to get products for.
	 * @param string $date The date to filter by (Y-m-d H:i:s).
	 * @return int[]
	 */
	public function get_products_modified_after( $lang, $date ) {
		$args = [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'lang'           => $lang,
			'date_query'     => [
				[
					'column' => 'post_modified_gmt',
					'after'  => $date,
				],
			],
		];

		$query = new \WP_Query( $args );

		return $query->posts;
	}
}
