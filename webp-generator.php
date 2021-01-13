<?php
/**
 * Plugin Name: WebP Generator
 * Plugin URI: https://shazzad.me
 * Description: Media image to WebP Generator
 * Version: 1.0.1
 * Author: Shazzad Hossain Khan
 * Author URI: https://shazzad.me
 * Requires at least: 5.2
 * Tested up to: 5.6
 * Text Domain: webpgen
 * Domain Path: /languages
 *
 * @package WebpGen
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define base file.
if ( ! defined( 'WEBPGEN_PLUGIN_FILE' ) ) {
	define( 'WEBPGEN_PLUGIN_FILE', __FILE__ );
}

// Define base file.
if ( ! defined( 'WEBPGEN_QUALITY' ) ) {
	define( 'WEBPGEN_QUALITY', 90 );
}

/**
 * Notice if vendor folder is missing.
 */
function webpgen_notice_missing_composer_file() {
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php _e( '<strong>WebP Generator</strong> plugin was not installed properly. Please run <code>composer install</code> in webp-generator plugin folder to complete plugin setup.', 'webpgen' ); ?></p>
	</div>
	<?php
}

if (! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	add_action( 'admin_notices', 'webpgen_notice_missing_composer_file' );
	return;
}


// Load dependencies.
require_once __DIR__ . '/vendor/autoload.php';


/**
 * Intialize everything after plugins_loaded action.
 *
 * @return void
 */
function webpgen_init() {
	webpgen();
}
add_action( 'plugins_loaded', 'webpgen_init', 5 );


/**
 * Get an instance of plugin main class.
 *
 * @return WebpGen Instance of main class.
 */
function webpgen() {
	return \WebpGen\Plugin::get_instance();
}