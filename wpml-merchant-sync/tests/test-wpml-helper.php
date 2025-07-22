<?php

use WPMLMerchantSync\Helpers\WPML;

class WPML_Helper_Test extends WP_UnitTestCase {

	public function test_get_current_language() {
		// Mock the 'wpml_current_language' filter.
		add_filter( 'wpml_current_language', function() {
			return 'en';
		} );

		$wpml_helper = new WPML();
		$this->assertEquals( 'en', $wpml_helper->get_current_language() );
	}

	public function test_get_translated_id() {
		// Mock the 'wpml_object_id' filter.
		add_filter( 'wpml_object_id', function( $post_id, $post_type, $return_original_if_missing, $lang ) {
			if ( $post_id === 1 && $lang === 'ar' ) {
				return 2;
			}
			return $post_id;
		}, 10, 4 );

		$wpml_helper = new WPML();
		$this->assertEquals( 2, $wpml_helper->get_translated_id( 1, 'ar' ) );
		$this->assertEquals( 1, $wpml_helper->get_translated_id( 1, 'en' ) );
	}
}
