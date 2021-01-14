<?php
namespace WebpGen;

/**
 * Automation handler file.
 *
 * @package WebpGen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automation Class.
 *
 * @class Automation
 */
class Automation {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'updated_postmeta', array( $this, 'updated_postmeta' ), 10, 4 );
		add_action( 'added_post_meta', array( $this, 'updated_postmeta' ), 10, 4 );
		add_action( 'webpgen_generate_media_webp', array( $this, 'generate_media_webp' ), 10 );
		add_action( 'webpgen_schedule_media_webp_generation', array( $this, 'schedule_media_webp_generation' ), 10 );
		add_action( 'delete_attachment', array( $this, 'delete_webp_files' ), 10 );
	}

	public function delete_webp_files( $media_id ) {
		if ( wp_attachment_is_image( $media_id ) ) {
			$media_to_webp = new Media_To_Webp( $media_id );
			$media_to_webp->delete();
		}
	}

	/**
	 * Schedule webp generation using cron
	 */
	public function schedule_media_webp_generation( $media_id ) {
		if ( ! wp_next_scheduled( 'webpgen_generate_media_webp', array( $media_id ) ) ) {
			wp_schedule_single_event( time() + 1, 'webpgen_generate_media_webp', array( $media_id ) );
		}
	}

	/**
	 * Generate webp for media
	 */
	public function generate_media_webp( $media_id ) {
		if ( wp_attachment_is_image( $media_id ) ) {
			$media_to_webp = new Media_To_Webp( $media_id );
			$media_to_webp->generate( true );

			webpgen_log(
				'WebP Files Generated For {{media_id}}',
				array(
					'media_id' => $media_id,
					'logs'     => $media_to_webp->get_logs(),
				)
			);
		}
	}

	/**
	 * Hook into post meta update/add callback and perform image generation as needed.
	 */
	public function updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		$media_id = 0;

		// When a feature image is assigned for a post, image_id would be in the meta_value.
		if ( $meta_key === '_thumbnail_id' && (int) $meta_value > 0 ) {
			$media_id = (int) $meta_value;

			// When a image metadata is updated.
		} elseif ( $meta_key === '_wp_attachment_metadata' && webpgen_is_media_featured( $object_id ) ) {
			$media_id = (int) $object_id;
		}

		// Use cache function to avoid multiple-run for same media as metadata updates too frequently
		if ( $media_id > 0 && false === wp_cache_get( $media_id, 'webpgen_generated' ) ) {
			wp_cache_set( $media_id, true, 'webpgen_generated' );

			if ( webpgen_is_cron_disabled() ) {
				webpgen_log(
					'Generating WebP For {{media_id}}',
					array(
						'media_id' => $media_id,
					)
				);
				$this->generate_media_webp( $media_id );
			} else {
				webpgen_log(
					'Scheduling Generator For {{media_id}}',
					array(
						'media_id' => $media_id,
					)
				);
				$this->schedule_media_webp_generation( $media_id );
			}
		}
	}
}
