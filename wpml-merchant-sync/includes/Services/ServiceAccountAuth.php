<?php
namespace WPMLMerchantSync\Services;

class ServiceAccountAuth {

	/**
	 * @var array
	 */
	protected $credentials;

	/**
	 * ServiceAccountAuth constructor.
	 *
	 * @param string $credentials_json The Google Service Account JSON.
	 */
	public function __construct( $credentials_json ) {
		$this->credentials = json_decode( $credentials_json, true );
	}

	/**
	 * Get the access token.
	 *
	 * @return string|\WP_Error The access token or an error.
	 */
	public function get_access_token() {
		$jwt = $this->create_jwt();
		$response = $this->request_token( $jwt );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['access_token'] ) ) {
			return $body['access_token'];
		}

		return new \WP_Error( 'token_error', 'Could not retrieve access token.' );
	}

	/**
	 * Create the JWT.
	 *
	 * @return string The JWT.
	 */
	protected function create_jwt() {
		$header = [
			'alg' => 'RS256',
			'typ' => 'JWT',
		];

		$now = time();
		$payload = [
			'iss'   => $this->credentials['client_email'],
			'scope' => 'https://www.googleapis.com/auth/content',
			'aud'   => 'https://oauth2.googleapis.com/token',
			'exp'   => $now + 3600,
			'iat'   => $now,
		];

		$header_encoded = $this->base64url_encode( json_encode( $header ) );
		$payload_encoded = $this->base64url_encode( json_encode( $payload ) );
		$signature_input = "$header_encoded.$payload_encoded";
		$signature = '';
		openssl_sign( $signature_input, $signature, $this->credentials['private_key'], 'sha256' );
		$signature_encoded = $this->base64url_encode( $signature );

		return "$signature_input.$signature_encoded";
	}

	/**
	 * Request the access token.
	 *
	 * @param string $jwt The JWT.
	 * @return array|\WP_Error The response or an error.
	 */
	protected function request_token( $jwt ) {
		$url = 'https://oauth2.googleapis.com/token';
		$args = [
			'method' => 'POST',
			'body'   => [
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion'  => $jwt,
			],
		];

		return wp_remote_post( $url, $args );
	}

	/**
	 * Base64 URL encode a string.
	 *
	 * @param string $data The string to encode.
	 * @return string The encoded string.
	 */
	protected function base64url_encode( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}
}
