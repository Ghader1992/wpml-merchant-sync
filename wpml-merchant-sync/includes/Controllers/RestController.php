<?php
namespace WPMLMerchantSync\Controllers;

use WPMLMerchantSync\Plugin;

class RestController {

	/**
	 * RestController constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		register_rest_route( 'merchant/v1', '/(?P<lang>en|ar)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_feed' ],
			'permission_callback' => [ $this, 'check_permission' ],
			'args'                => [
				'format'         => [
					'default'           => 'json',
					'validate_callback' => function ( $param ) {
						return in_array( $param, [ 'json', 'xml' ] );
					},
				],
				'category'       => [
					'validate_callback' => 'is_string',
				],
				'modified_after' => [
					'validate_callback' => 'is_string',
				],
				'limit'          => [
					'validate_callback' => 'is_numeric',
				],
			],
		] );
	}

	/**
	 * Get the feed.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_feed( \WP_REST_Request $request ) {
		$lang = $request->get_param( 'lang' );
		$format = $request->get_param( 'format' );

		$cached_feed = Plugin::instance()->cache_manager->get( $lang, $format );
		if ( $cached_feed ) {
			return new \WP_REST_Response( $cached_feed );
		}

		$product_ids = Plugin::instance()->product_repository->get_all_products( $lang );
		$feed = Plugin::instance()->feed_builder->build( $product_ids, $format );

		Plugin::instance()->cache_manager->set( $lang, $format, $feed );

		return new \WP_REST_Response( $feed );
	}

	/**
	 * Check if the user has permission to access the endpoint.
	 *
	 * @return bool
	 */
	public function check_permission() {
		// In a real implementation, this would check for an API key header
		// or a user capability. For now, we'll just allow access.
		return true;
	}
}
