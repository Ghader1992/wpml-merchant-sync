<?php
namespace WPMLMerchantSync\Services;

use Google_Client;
use Google_Service_ShoppingContent;

class ServiceAccountAuth {

	/**
	 * Get the Google Client.
	 *
	 * @return Google_Client
	 * @throws \Google\Exception
	 */
	public function getClient() {
		$client = new Google_Client();
		$client->setApplicationName( 'WPML Merchant Sync' );
		$client->setScopes( [ Google_Service_ShoppingContent::CONTENT ] );
		$client->setAuthConfig( $this->getServiceAccountJson() );

		return $client;
	}

	/**
	 * Get the service account JSON from the settings.
	 *
	 * @return array
	 */
	private function getServiceAccountJson() {
		$options = get_option( 'wpml_merchant_sync_settings' );

		return json_decode( $options['google_service_account'] ?? '', true );
	}
}
