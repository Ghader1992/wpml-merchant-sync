<?php

use WPMLMerchantSync\Services\SyncService;
use WPMLMerchantSync\Repositories\ProductRepository;
use WPMLMerchantSync\Services\MerchantApiClient;
use WPMLMerchantSync\Helpers\WPML;

class Test_Sync_Service extends WP_UnitTestCase {

	public function test_sync_product() {
		$product_repository = $this->createMock( ProductRepository::class );
		$merchant_api_client = $this->createMock( MerchantApiClient::class );
		$wpml = $this->createMock( WPML::class );

		$sync_service = new SyncService( $product_repository, $merchant_api_client, $wpml );

		$product_id = $this->factory->post->create( [ 'post_type' => 'product' ] );
		$product = wc_get_product( $product_id );

		$wpml->method( 'get_product_language' )->willReturn( 'en' );
		$wpml->method( 'get_country_for_language' )->willReturn( 'US' );

		$merchant_api_client->expects( $this->once() )->method( 'insert_product' )->willReturn( [ 'id' => '123' ] );

		$sync_service->sync_product( $product_id );

		$this->assertEquals( '123', get_post_meta( $product_id, '_merchant_id', true ) );
	}
}
