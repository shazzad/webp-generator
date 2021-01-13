<?php
namespace WebpGen\Admin;

use WebpGen\Webp_Generator;
/**
 * Admin ajax handler class.
 *
 * @package WebpGen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax handler.
 *
 * @class WebpGen_Ajax_Handlers
 */

class Ajax_Handlers {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_webpgen_schedule', array( $this, 'schedule_ajax' ) );
		add_action( 'wp_ajax_webpgen_generate', array( $this, 'generate_ajax' ) );
	}

	/**
	 * Adds plugin action links.
	 */
	public function schedule_ajax() {
		// Debug Delay
		// sleep(3);

		$force_regeneration = true;

		$generator = new Webp_Generator();

		if ( $generator->has_started() && ! $generator->has_completed() ) {
			wp_send_json_success( array(
				'message' => __( 'Webp File Generation Resuming..', 'webpgen' ),
				'buttonText' => __( 'Generating...', 'webpgen' ),
			));

		} else {
			try {
				if ( ! $generator->is_scheduled() ) {
					$generator->set_limit( 5 );
					$generator->schedule( $force_regeneration );
				}
				if ( ! $generator->has_started() ) {
					$generator->start();
				}
	
				wp_send_json_success( array(
					'message' => __( 'Webp File Generation Started', 'webpgen' ),
					'buttonText' => __( 'Generating...', 'webpgen' ),
				));
			} catch ( Exception $e ) {
				wp_send_json_error( array(
					'message' => $e->getMessage()
				));
			}
		}
	}

	/**
	 * Adds plugin action links.
	 */
	public function generate_ajax() {
		// Debug Delay
		// sleep(5);
		// wp_send_json_error( array(
		// 	'message' => __( 'Unable to run generator.' )
		// ));

		$generator = new Webp_Generator();

		if ( $generator->has_completed() ) {
			wp_send_json_success( array(
				'message' => __( 'Webp File Generation Completed', 'webpgen' ),
				'buttonText' => __( 'Done !!', 'webpgen' ),
				'logs' => $generator->get_logs()
			));

		} elseif ( $generator->has_started() ) {
			try {
				$generator->generate();
				$logs = $generator->get_logs();
				$generator->clear_logs();

				wp_send_json_success( array(
					'resend' => true,
					'message' => __( 'Webp File Generation Ongoing..', 'webpgen' ),
					'logs' => $logs
				));
			} catch ( Exception $e ) {
				wp_send_json_error( array(
					'message' => $e->getMessage()
				));
			}
		}
	}
}
