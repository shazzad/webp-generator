<?php
/**
 * Generate Webp File For a Single Image File.
 *
 * @package WebpGen
 */

namespace WebpGen;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Class.
 *
 * @class Query
 */
class Image_To_Webp {

	public $extension;

	public $destination = '';

	public $generated = false;

	const ALLOWED_EXTS = array(
		'jpg',
		'jpeg',
		'gif',
		'png',
		'bmp',
	);

	/**
	 * Constructor.
	 *
	 * @param string $source Source file absolute path.
	 */
	public function __construct( $source, $force = true ) {
		if ( ! file_exists( $source ) ) {
			throw new Exception( __( 'File not exists', 'webpgen' ) );
		}

		$extension = pathinfo( $source, PATHINFO_EXTENSION );
		if ( ! in_array( $extension, static::ALLOWED_EXTS, true ) ) {
			throw new Exception( __( 'Unknown file extension', 'webpgen' ) );
		}

		$editor = wp_get_image_editor( $source );
		if ( is_wp_error( $editor ) ) {
			throw new Exception( $editor->get_error_message() );
		}

		$this->destination = dirname( $source ) . '/' . pathinfo( $source, PATHINFO_FILENAME ) . '.webp';
		$this->exists      = file_exists( $this->destination );

		if ( $force || ! $this->exists ) {

			$saved = $editor->save( $this->destination, 'image/webp' );

			if ( is_wp_error( $saved ) ) {
				throw new Exception( $saved->get_error_message() );
			} else {
				$this->generated = true;
			}
		}
	}
}
