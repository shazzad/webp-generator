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

	public $id = 0;
	public $files = [];
	public $basedir = '';
	public $files_generated = 0;
	public $logs = [];

	public function __construct( $id ) {
		$this->id = $id;

		$uploads = wp_get_upload_dir();
        if ( $uploads['error'] === false ) {
			$this->basedir = $uploads['basedir'];
        }
	}

	public function generate( $force_generation = false ) {
		$this->gather_media_files();
		$this->generate_webp_files( $force_generation );
	}

	public function gather_media_files() {
        $this->files = [];

		$metadata = wp_get_attachment_metadata( $this->id );

		$mainfile = $metadata['file'];
        if ( 0 !== strpos( $mainfile, $this->basedir ) ) {
            $mainfile = $this->basedir . '/' . $mainfile;
        }

        $this->files[] = $mainfile;
        foreach ( $metadata['sizes'] as $size ) {
            $this->files[] = dirname( $mainfile ) . '/' . $size['file'];
        }
	}

	public function generate_webp_files( $force_generation = false ) {
		foreach ( $this->files as $file ) {
            try {
                $converter = new Image_To_Webp( $file );

				if ( ! $converter->webp_exists() || $force_generation ) {
                    $converter->generate();
                    ++ $this->files_generated;
                    $this->logs[] = __( 'GENERATED: ' ) . str_replace( $this->basedir, '', $converter->destination );
                } else {
                    $this->logs[] = __( 'EXISTS:  ' ) . str_replace( $this->basedir, '', $converter->destination );
                }

			} catch( Exception $e ) {
				$this->logs[] = __( 'ERROR: ' ) . $e->getMessage();
            }
		}
    }
}
