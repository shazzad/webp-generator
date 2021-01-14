<?php
/**
 * Generate webp file for all of the image sizes of a media.
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
class Media_To_Webp {

	public $id              = 0;
	public $basedir         = '';
	public $files_generated = 0;
	public $files           = array();
	public $logs            = array();

	public function __construct( $id ) {
		$this->id = $id;

		$uploads = wp_get_upload_dir();
		if ( $uploads['error'] === false ) {
			$this->basedir = $uploads['basedir'];
		}
	}

	public function get_files_generated() {
		return $this->files_generated;
	}

	public function get_logs() {
		return $this->logs;
	}

	public function generate( $force_generation = false ) {
		$this->gather_media_files();
		$this->generate_webp_files( $force_generation );
	}

	public function delete() {
		$metadata = wp_get_attachment_metadata( $this->id );
		$webp_files = [];

		if ( ! empty( $metadata ) ) {
			$mainfile = $metadata['file'];
			if ( 0 !== strpos( $mainfile, $this->basedir ) ) {
				$mainfile = $this->basedir . '/' . $mainfile;
			}

			$extension = pathinfo( $mainfile, PATHINFO_EXTENSION );

			$intermediate_dir = path_join( $this->basedir, dirname( $mainfile ) );

			$webp_files[] = str_replace( '.' . $extension, '.webp', $mainfile );
			foreach ( $metadata['sizes'] as $size ) {
				$webp_files[] = str_replace( '.' . $extension, '.webp', $intermediate_dir . '/' . $size['file'] );
			}

			foreach ( $webp_files as $webp_file ) {
				wp_delete_file_from_directory( $webp_file, $intermediate_dir );
			}
		}
	}

	protected function gather_media_files() {
		$this->files = array();

		$metadata = wp_get_attachment_metadata( $this->id );
		if ( ! empty( $metadata ) ) {
			$mainfile = $metadata['file'];
			if ( 0 !== strpos( $mainfile, $this->basedir ) ) {
				$mainfile = $this->basedir . '/' . $mainfile;
			}

			$this->files[] = $mainfile;
			foreach ( $metadata['sizes'] as $size ) {
				$this->files[] = dirname( $mainfile ) . '/' . $size['file'];
			}
		}
	}

	protected function generate_webp_files( $force_generation = false ) {
		foreach ( $this->files as $file ) {
			try {
				$image_to_webp = new Image_To_Webp( $file, $force_generation );

				if ( $image_to_webp->generated ) {
					++ $this->files_generated;
					$this->logs[] = __( 'GENERATED: ' ) . str_replace( $this->basedir, '', $image_to_webp->destination );

				} elseif ( $image_to_webp->exists ) {
					$this->logs[] = __( 'EXISTS:  ' ) . str_replace( $this->basedir, '', $image_to_webp->destination );
				}
			} catch ( Exception $e ) {
				$this->logs[] = __( 'ERROR: ' ) . $e->getMessage();
			}
		}
	}
}
