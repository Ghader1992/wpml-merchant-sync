<?php
namespace WPMLMerchantSync\Services;

class FeedBuilder {

	/**
	 * Build the feed for a given set of products.
	 *
	 * @param int[]  $product_ids The IDs of the products to include in the feed.
	 * @param string $format      The format of the feed (json or xml).
	 * @return string The feed content.
	 */
	public function build( $product_ids, $format = 'json' ) {
		$products = [];
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}
			$products[] = $this->format_product( $product );
		}

		if ( 'json' === $format ) {
			return json_encode( $products, JSON_PRETTY_PRINT );
		} else {
			return $this->build_xml( $products );
		}
	}

	/**
	 * Format a single product for the feed.
	 *
	 * @param \WC_Product $product The product to format.
	 * @return array The formatted product data.
	 */
	protected function format_product( \WC_Product $product ) {
		$data = [
			'id'           => $product->get_id(),
			'title'        => $product->get_name(),
			'description'  => $product->get_description(),
			'link'         => $product->get_permalink(),
			'image_link'   => wp_get_attachment_url( $product->get_image_id() ),
			'price'        => [
				'value'    => $product->get_price(),
				'currency' => get_woocommerce_currency(),
			],
			'availability' => $product->is_in_stock() ? 'in stock' : 'out of stock',
			'gtin'         => $product->get_sku(),
			'brand'        => '', // Needs to be mapped in settings.
		];

		return $data;
	}

	/**
	 * Build the XML feed.
	 *
	 * @param array $products The products to include in the feed.
	 * @return string The XML feed content.
	 */
	protected function build_xml( $products ) {
		$xml = new \SimpleXMLElement( '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>' );
		$channel = $xml->addChild( 'channel' );
		$channel->addChild( 'title', 'Google Merchant Feed' );
		$channel->addChild( 'link', get_site_url() );
		$channel->addChild( 'description', 'Product feed for Google Merchant Center' );

		foreach ( $products as $product_data ) {
			$item = $channel->addChild( 'item' );
			foreach ( $product_data as $key => $value ) {
				if ( is_array( $value ) ) {
					$node = $item->addChild( "g:{$key}" );
					foreach ( $value as $sub_key => $sub_value ) {
						$node->addChild( "g:{$sub_key}", $sub_value );
					}
				} else {
					$item->addChild( "g:{$key}", htmlspecialchars( $value ) );
				}
			}
		}

		return $xml->asXML();
	}
}
