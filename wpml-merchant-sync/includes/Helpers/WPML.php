<?php
namespace WPMLMerchantSync\Helpers;

class WPML {

	/**
	 * Get the current language.
	 *
	 * @return string|null The current language code.
	 */
	public function get_current_language() {
		return apply_filters( 'wpml_current_language', null );
	}

	/**
	 * Get the translated post ID.
	 *
	 * @param int    $post_id The ID of the post to translate.
	 * @param string $lang    The language to translate to.
	 * @return int|null The translated post ID.
	 */
	public function get_translated_id( $post_id, $lang ) {
		return apply_filters( 'wpml_object_id', $post_id, 'product', true, $lang );
	}
}
