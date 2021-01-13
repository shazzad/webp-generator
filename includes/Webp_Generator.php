<?php
/**
 * Generate Webp File
 *
 * @package WebpGen
 */

namespace WebpGen;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Webp_Generator {

    protected $logs = [];
    protected $limit = 20;
    protected $offset = 0;
    protected $files_generated = 0;
    protected $force_generation = false;
    protected $basedir = '';

    public function __construct() {
        $this->init();
    }

    protected function init() {
        $data = get_option( 'webpgen_generate_data', [] );
        foreach ( $data as $key => $val ) {
            $this->{$key} = $val;
        }

        $progress = get_option('webpgen_generate_progress', []);
        foreach ($progress as $key => $val) {
            $this->{$key} = $val;
        }
    }

    protected function save_data() {
        update_option( 'webpgen_generate_data', [
            'files_generated'  => $this->files_generated,
            'force_generation' => $this->force_generation
        ]);
    }

    protected function save_progress() {
        update_option( 'webpgen_generate_progress', [
            'offset' => $this->offset,
            'limit'  => $this->limit,
            'logs'   => $this->logs
        ]);
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

        $images = $this->get_images( $this->offset, $this->limit );
        foreach ( $images as $image ) {
            $this->generate_image( $image->ID );
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

        $this->files_generated += $media_to_webp->files_generated;

        foreach ( $media_to_webp->logs as $log ) {
            $this->logs[] = $log;
        }
    }

    public function get_images( $offset, $limit ) {
        global $wpdb;

		$image_mimes = array( 
            'image/jpeg',
            'image/gif',
            'image/png',
            'image/bmp'
        );

		$sql = "SELECT P.* FROM $wpdb->posts AS P";
		$sql .= " INNER JOIN $wpdb->postmeta AS PM ON (PM.meta_value = P.ID)";
		$sql .= " WHERE 1=1";
		$sql .= " AND PM.meta_key = '_thumbnail_id'";
		$sql .= " AND post_type='attachment'";
		$sql .= " AND post_mime_type IN ('" . implode( "', '", $image_mimes ) . "')";
		$sql .= " GROUP BY P.ID";
		$sql .= " ORDER BY ID ASC";
        $sql .= " LIMIT $offset, $limit";
        
        $this->logs[] = 'OFFSET: ' . $offset . ', LIMIT: ' . $limit;

        return $wpdb->get_results( $sql );
    }

    public function schedule( $force_generation = false ) {
        if ( ! webpgen_is_gd_installed() || ! webpgen_is_gd_support_enabled( 'WebP Support' ) ) {
            throw new Exception( __( 'Missing GD Libary / WebP Module. Can not run webp generator.', 'webpgen' ) );
        }

        $this->offset = 0;
        $this->logs = [];

        $this->files_generated = 0;
        $this->force_generation = $force_generation;

        update_option('webpgen_generate_scheduled', time());
        delete_option('webpgen_generate_progress');
        delete_option('webpgen_generate_started');
        delete_option('webpgen_generate_completed');

        $this->save_data();
        $this->save_progress();

        return true;
    }

    public function unschedule() {
        delete_option('webpgen_generate_scheduled');
        delete_option('webpgen_generate_progress');
        delete_option('webpgen_generate_started');
        delete_option('webpgen_generate_completed');
    }

    public function is_scheduled() {
        return (bool) get_option('webpgen_generate_scheduled');
    }

    public function has_started() {
        return (bool) get_option('webpgen_generate_started');
    }

    public function has_completed() {
        return (bool) get_option('webpgen_generate_completed');
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
        delete_option('webpgen_generate_scheduled');
        update_option('webpgen_generate_started', time());
        $this->save_progress();
    }

    public function reset() {
        update_option('webpgen_generate_started', time());
        delete_option('webpgen_generate_scheduled');
        delete_option('webpgen_generate_completed');

        $this->offset = 0;
        $this->logs = [];

        $this->save_progress();
    }

    public function end() {
        update_option('webpgen_generate_completed', time());
    }

    public function set_limit($limit) {
        $this->limit = $limit;
    }

    public function set_offset($offset) {
        $this->offset = $offset;
    }

    public function clear_logs() {
        $this->logs = [];
        $this->save_progress();
    }
}
