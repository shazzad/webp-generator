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

	public $allowe_extensions = [
		'jpg',
		'jpeg',
		'gif',
		'png',
		'bmp'
	];

	public $source;
	public $destination;
	public $filedir;
	public $filename;
	public $extension;

	public function __construct( $source ) {
		if ( ! file_exists( $source ) ) {
			throw new Exception( __( 'File not exists', 'webpgen' ) );
		}

		$this->extension = pathinfo( $source, PATHINFO_EXTENSION );
		if ( ! in_array( $this->extension, $this->allowe_extensions, true ) ) {
			throw new Exception( __( 'Unknown file extension', 'webpgen' ) );
		}

		if ( ! webpgen_is_gd_installed() || ! webpgen_is_gd_support_enabled( 'WebP Support' ) ) {
            throw new Exception( __( 'Missing GD Libary / WebP Module. Can not generate webp image.', 'webpgen' ) );
        }

		$this->source = $source;
		$this->filedir = dirname( $source );
		$this->filename = pathinfo( $source, PATHINFO_FILENAME );
		$this->destination = $this->filedir . '/' . $this->filename . '.webp';
	}

	public function webp_exists() {
		return file_exists( $this->destination );
	}

	public function generate() {
		if ($this->extension == 'jpeg' || $this->extension == 'jpg') {
			$image = imagecreatefromjpeg( $this->source );
		} elseif ($this->extension == 'gif') {
			$image = imagecreatefromgif( $this->source );
		} elseif ($this->extension == 'png') {
			$image = imagecreatefrompng( $this->source );
		} elseif ($this->extension == 'bmp') {
			$image = imagecreatefrombmp( $this->source );
		}

		return \imagewebp( $image, $this->destination, WEBPGEN_QUALITY );
	}
}
