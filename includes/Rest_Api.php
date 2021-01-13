<?php
namespace WebpGen;

/**
 * Listing handler file.
 *
 * @package WebpGen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Class.
 *
 * @class Query
 */
class Rest_Api {
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	
	/**
	 * Schedule a rewrite rules regeneration after listing page updates.
	 *
	 * @param  string $post_ID Current post id.
	 */
	public function rest_api_init() {
		$types = [];
		foreach ( get_post_types() as $type ) {
			if ( post_type_supports( $type, 'thumbnail' ) ) {
				$types[] = $type;
			}
		}

		if ( empty( $types ) ) {
			return;
		}

		register_rest_field( 
			$types, 
			'webp_media_details', 
			array(
				'get_callback'    => [ $this, 'get_post_webp_media_details' ],
				'schema'          => null,
			)
		);

		register_rest_field( 
			'attachment', 
			'webp_media_details', 
			array(
				'get_callback'    => [ $this, 'get_webp_media_details' ],
				'schema'          => null,
			)
		);
	}

	public function get_post_webp_media_details( $object ) {

		if ( get_post_meta( $object['id'], '_thumbnail_id', true ) ) {
			$media_id = get_post_meta( $object['id'], '_thumbnail_id', true );
			return $this->get_webp_media_details_raw( $media_id );
		} else {
			$webp_media_details = [ 'sizes' => [] ];
		}

		return $webp_media_details;
	}

	public function get_webp_media_details( $object ) {
		if ( webpgen_is_media_featured( $object['id'] ) ) {
			return $this->get_webp_media_details_raw( $object['id'] );
		}

		return [ 'sizes' => [] ];
	}

	public function get_webp_media_details_raw( $id ) {

		$webp_media_details = [ 'sizes' => [] ];

		$metadata = wp_get_attachment_metadata( $id );
		$uploads = wp_get_upload_dir();

		if ( $uploads['error'] === false ) {
			$mainfile = $metadata['file'];
			$extension = pathinfo( $mainfile, PATHINFO_EXTENSION );

			if ( 0 !== strpos( $mainfile, $uploads['baseurl'] ) ) {
				$mainfile = $uploads['baseurl'] . '/' . $mainfile;
			}

			$dirname = dirname( $mainfile );

			foreach ( $metadata['sizes'] as $key => $size ) {
				$webp_media_details['sizes'][ $key ] = [
					'width' => $size['width'],
					'height' => $size['height'],
					'mime_type' => 'image/webp',
					'source_url' => $dirname . '/' . str_replace( '.' . $extension, '.webp', $size['file'] )
				];
			}

			$webp_media_details['sizes'][ 'full' ] = [
				'width' => $metadata['width'],
				'height' => $metadata['height'],
				'mime_type' => 'image/webp',
				'source_url' => str_replace( '.' . $extension, '.webp', $mainfile )
			];
		}

		return $webp_media_details;
	}
}
