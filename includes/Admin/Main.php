<?php
namespace WebpGen\Admin;

/**
 * Admin main class.
 *
 * @package WebpGen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Main Class.
 *
 * @class WebpGen_Admin_Main
 */
class Main {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 5 );
		add_action( 'plugin_action_links_' . WEBPGEN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Register admin assets.
	 */
	public function register_admin_scripts() {
		wp_register_style( 'webpgen-admin', WEBPGEN_URL . 'assets/css/admin.css', array( ) );
		wp_register_script( 'webpgen-admin', WEBPGEN_URL . 'assets/js/admin.js', array( 'jquery' ) );
	}

	/**
	 * Adds plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$new_links = array();
		$new_links['settings'] = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=webpgen-settings' ),
			__( 'Settings', 'webpgen' )
		);

		return array_merge( $new_links, $links );
	}
}
