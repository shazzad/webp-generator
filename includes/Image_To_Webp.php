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

	public $source;

	public $source_extension;

	const ALLOWED_EXTS = [
		'jpg',
		'jpeg',
		'gif',
		'png',
		'bmp'
	];

	/**
	 * Constructor.
	 * 
	 * @param string $source Source file absolute path.
	 */
	public function __construct( $source ) {
		if ( ! file_exists( $source ) ) {
			throw new Exception( __( 'File not exists', 'webpgen' ) );
		}

		$this->source_extension = pathinfo( $source, PATHINFO_EXTENSION );
		if ( ! in_array( $this->source_extension, static::ALLOWED_EXTS, true ) ) {
			throw new Exception( __( 'Unknown file source_extension', 'webpgen' ) );
		}

		if ( ! webpgen_is_gd_installed() || ! webpgen_is_gd_support_enabled( 'WebP Support' ) ) {
            throw new Exception( __( 'Missing GD Libary / WebP Module. Can not generate webp image.', 'webpgen' ) );
        }

		$filename = pathinfo( $source, PATHINFO_FILENAME );

		$this->source = $source;
		$this->destination = dirname( $source ) . '/' . $filename . '.webp';
	}

	public function webp_exists() {
		return file_exists( $this->destination );
	}

	public function generate() {
		if ($this->source_extension == 'jpeg' || $this->source_extension == 'jpg') {
			$image = imagecreatefromjpeg( $this->source );
		} elseif ($this->source_extension == 'gif') {
			$image = imagecreatefromgif( $this->source );
			imagepalettetotruecolor($image);
			imagealphablending($image, true);
			imagesavealpha($image, true);
		} elseif ($this->source_extension == 'png') {
			$image = imagecreatefrompng( $this->source );
			imagepalettetotruecolor($image);
			imagealphablending($image, true);
			imagesavealpha($image, true);
		} elseif ($this->source_extension == 'bmp') {
			$image = imagecreatefrombmp( $this->source );
		}

		return imagewebp( $image, $this->destination, WEBPGEN_QUALITY );
	}
}
