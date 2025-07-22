<?php
namespace WPMLMerchantSync\Services;

class MerchantApiClient {

	const API_BASE_URL = 'https://www.googleapis.com/content/v2.1/';

	/**
	 * @var ServiceAccountAuth
	 */
	protected $auth;

	/**
	 * MerchantApiClient constructor.
	 *
	 * @param ServiceAccountAuth $auth
	 */
	public function __construct( ServiceAccountAuth $auth ) {
		$this->auth = $auth;
	}

	/**
	 * Insert a product.
	 *
	 * @param array $payload The product data.
	 * @return array|\WP_Error The API response or an error.
	 */
	public function insert_product( $payload ) {
		return $this->request( 'products', 'POST', $payload );
	}

	/**
	 * Update a product.
	 *
	 * @param string $id      The product ID.
	 * @param array  $payload The product data.
	 * @return array|\WP_Error The API response or an error.
	 */
	public function update_product( $id, $payload ) {
		return $this->request( "products/{$id}", 'PATCH', $payload );
	}

	/**
	 * Make a request to the Google Content API.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param string $method   The HTTP method.
	 * @param array  $payload  The request payload.
	 * @param int    $retries  The number of retries.
	 * @return array|\WP_Error The API response or an error.
	 */
	protected function request( $endpoint, $method = 'GET', $payload = [], $retries = 3 ) {
		$url = self::API_BASE_URL . $endpoint;
		$args = [
			'method'  => $method,
			'headers' => [
				'Authorization' => 'Bearer ' . $this->get_access_token(),
				'Content-Type'  => 'application/json',
			],
			'body'    => json_encode( $payload ),
		];

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$http_code = wp_remote_retrieve_response_code( $response );

		if ( $http_code >= 400 ) {
			if ( ( $http_code === 429 || $http_code >= 500 ) && $retries > 0 ) {
				sleep( ( 4 - $retries ) * 2 ); // Exponential backoff.
				return $this->request( $endpoint, $method, $payload, $retries - 1 );
			} else {
				return new \WP_Error( 'api_error', 'Google Merchant API Error', [ 'status' => $http_code, 'response' => $body ] );
			}
		}

		return $body;
	}

	/**
	 * Get the access token.
	 *
	 * @return string The access token.
	 * @throws \Google\Exception
	 */
	protected function get_access_token() {
		$client = $this->auth->getClient();
		$token  = $client->fetchAccessTokenWithAssertion();

		return $token['access_token'];
	}
}
