<?php
/**
 * Generate Webp File
 *
 * @package WebpGen
 */

namespace WebpGen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Webp_Generator {

	protected $logs             = array();
	protected $limit            = 20;
	protected $offset           = 0;
	protected $files_generated  = 0;
	protected $force_generation = false;
	protected $basedir          = '';

	public function __construct() {
		$this->init();
	}

	protected function init() {
		$data = get_option( 'webpgen_generate_data', array() );
		foreach ( $data as $key => $val ) {
			$this->{$key} = $val;
		}

		$progress = get_option( 'webpgen_generate_progress', array() );
		foreach ( $progress as $key => $val ) {
			$this->{$key} = $val;
		}
	}

	protected function save_data() {
		update_option(
			'webpgen_generate_data',
			array(
				'files_generated'  => $this->files_generated,
				'force_generation' => $this->force_generation,
			)
		);
	}

	protected function save_progress() {
		update_option(
			'webpgen_generate_progress',
			array(
				'offset' => $this->offset,
				'limit'  => $this->limit,
				'logs'   => $this->logs,
			)
		);
	}

	public function generate() {
		if ( ! $this->has_started() ) {
			$this->logs[] = __( 'Process has not started', 'webpgen' );
			return;
		} elseif ( $this->has_completed() ) {
			$this->logs[] = __( 'Process completed', 'webpgen' );
			return;
		}

		$uploads = wp_get_upload_dir();
		if ( $uploads['error'] ) {
			$this->logs[] = __( 'Upload dir error', 'webpgen' );
			return;
		}

		$this->basedir = $uploads['basedir'];

		$this->logs[] = 'OFFSET: ' . $this->offset . ', LIMIT: ' . $this->limit;

		$images = $this->get_images( $this->offset, $this->limit );

		foreach ( $images as $image_id ) {
			$this->generate_image( $image_id );
		}

		$this->offset = $this->offset + $this->limit;

		$this->save_progress();
		$this->save_data();

		if ( count( $images ) < $this->limit ) {
			$this->end();
		}
	}

	public function generate_image( $id ) {
		$media_to_webp = new Media_To_Webp( $id );
		$media_to_webp->generate( $this->force_generation );

		$this->files_generated += $media_to_webp->get_files_generated();

		foreach ( $media_to_webp->get_logs() as $log ) {
			$this->logs[] = $log;
		}
	}

	public function get_images( $offset, $limit ) {
		global $wpdb;

		$image_mimes = array(
			'image/jpeg',
			'image/gif',
			'image/png',
			'image/bmp',
		);

		return $wpdb->get_col( $wpdb->prepare(
			"
			SELECT
				P.ID
			FROM
				{$wpdb->posts} AS P
			INNER JOIN 
				{$wpdb->postmeta} AS PM ON (PM.meta_value = P.ID)
			WHERE
				P.post_type = 'attachment' 
				AND P.post_mime_type IN ('" . implode( "', '", $image_mimes ) . "') 
                AND PM.meta_key = '_thumbnail_id' 
            GROUP BY P.ID 
            ORDER BY ID ASC 
            LIMIT 
                %d, %d
			",
			$offset,
			$limit
		));
	}

	public function schedule( $force_generation = false ) {
		// Don't let one schedule if not supported.
		if ( ! wp_image_editor_supports( array( 'methods' => array( 'save' ) ) ) ) {
			throw new \Exception( __( 'Your can\'t use this tool because your server doesn\'t support image conversion which means that WordPress can\'t convert new image. Please ask your host to install the Imagick or GD PHP extensions.', 'webpgen' ) );
		}

		$this->offset = 0;
		$this->logs   = array();

		$this->files_generated  = 0;
		$this->force_generation = $force_generation;

		update_option( 'webpgen_generate_scheduled', time() );
		delete_option( 'webpgen_generate_progress' );
		delete_option( 'webpgen_generate_started' );
		delete_option( 'webpgen_generate_completed' );

		$this->save_data();
		$this->save_progress();

		return true;
	}

	public function unschedule() {
		delete_option( 'webpgen_generate_scheduled' );
		delete_option( 'webpgen_generate_progress' );
		delete_option( 'webpgen_generate_started' );
		delete_option( 'webpgen_generate_completed' );
	}

	public function is_scheduled() {
		return (bool) get_option( 'webpgen_generate_scheduled' );
	}

	public function has_started() {
		return (bool) get_option( 'webpgen_generate_started' );
	}

	public function has_completed() {
		return (bool) get_option( 'webpgen_generate_completed' );
	}

	public function get_logs() {
		return $this->logs;
	}

	public function get_offset() {
		return $this->offset;
	}

	public function get_limit() {
		return $this->limit;
	}

	public function start() {
		delete_option( 'webpgen_generate_scheduled' );
		update_option( 'webpgen_generate_started', time() );
		$this->save_progress();
	}

	public function reset() {
		update_option( 'webpgen_generate_started', time() );
		delete_option( 'webpgen_generate_scheduled' );
		delete_option( 'webpgen_generate_completed' );

		$this->offset = 0;
		$this->logs   = array();

		$this->save_progress();
	}

	public function end() {
		update_option( 'webpgen_generate_completed', time() );
	}

	public function set_limit( $limit ) {
		$this->limit = $limit;
	}

	public function set_offset( $offset ) {
		$this->offset = $offset;
	}

	public function clear_logs() {
		$this->logs = array();
		$this->save_progress();
	}
}
