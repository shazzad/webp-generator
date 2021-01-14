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

		$this->extension = pathinfo( $source, PATHINFO_EXTENSION );
		if ( ! in_array( $this->extension, static::ALLOWED_EXTS, true ) ) {
			throw new Exception( __( 'Unknown file extension', 'webpgen' ) );
		}

		$this->source = $source;
		$this->destination = dirname( $source ) . '/' . pathinfo( $source, PATHINFO_FILENAME ) . '.webp';
		$this->exists      = file_exists( $this->destination );

		if ( $force || ! $this->exists ) {

			if ( webpgen_is_imagick_available() && webpgen_is_imagick_support_webp() ) {
				$this->imagick_generate();

			} elseif ( webpgen_is_gd_available() && webpgen_is_gd_support_webp() ) {
				$this->gd_generate();

			} else {
				throw new Exception( __( 'Unable to convert. No editor support webp generation.' ) );
			}
		}
	}

	public function gd_generate() {
		if ($this->extension == 'jpeg' || $this->extension == 'jpg') {
			$image = imagecreatefromjpeg( $this->source );

		} elseif ($this->extension == 'gif') {
			$image = imagecreatefromgif( $this->source );
			imagepalettetotruecolor($image);
			imagealphablending($image, true);
			imagesavealpha($image, true);

		} elseif ($this->extension == 'png') {
			$image = imagecreatefrompng( $this->source );
			imagepalettetotruecolor($image);
			imagealphablending($image, true);
			imagesavealpha($image, true);

		} elseif ($this->extension == 'bmp') {
			$image = imagecreatefrombmp( $this->source );
		}

		if ( imagewebp( $image, $this->destination, WEBPGEN_QUALITY ) ) {
			$this->generated = true;
		} else {
			throw new Exception( __( 'Unable to convert. imagewebp failed to create the file.' ) );
		}
	}

	public function imagick_generate() {
		$editor = wp_get_image_editor( $this->source );
		if ( is_wp_error( $editor ) ) {
			throw new Exception( $editor->get_error_message() );
		}

		$saved = $editor->save( $this->destination, 'image/webp' );

		if ( is_wp_error( $saved ) ) {
			throw new Exception( $saved->get_error_message() );
		} else {
			$this->generated = true;
		}
	}
}
