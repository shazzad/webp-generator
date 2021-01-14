<?php
namespace WebpGen;

/**
 * Main Plugin File.
 *
 * @package WebpGen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class.
 *
 * @class WebpGen
 */
final class Plugin {

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	public $name = 'WebpGen';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.2';

	/**
	 * Singleton The reference the *Singleton* instance of this class.
	 *
	 * @var WebpGen
	 */
	protected static $instance = null;

	/**
	 * Private clone method to prevent cloning of the instance of the
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Private unserialize method to prevent unserializing.
	 *
	 * @return void
	 */
	private function __wakeup() {}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	private function __construct() {
		$this->define_constants();
		$this->initialize();

		add_action( 'init', array( $this, 'load_plugin_translations' ) );
	}

	/**
	 * Define constants
	 */
	private function define_constants() {
		define( 'WEBPGEN_DIR', plugin_dir_path( WEBPGEN_PLUGIN_FILE ) );
		define( 'WEBPGEN_URL', plugin_dir_url( WEBPGEN_PLUGIN_FILE ) );
		define( 'WEBPGEN_BASENAME', plugin_basename( WEBPGEN_PLUGIN_FILE ) );
		define( 'WEBPGEN_VERSION', $this->version );
		define( 'WEBPGEN_NAME', $this->name );
	}

	/**
	 * Initialize the plugin
	 */
	private function initialize() {
		new Rest_Api();
		new Automation();

		if ( is_admin() ) {
			new Admin\Main();
			new Admin\Ajax_Handlers();
			new Admin\Settings_Page();
		}
	}

	/**
	 * Load plugin translation file
	 */
	public function load_plugin_translations() {
		load_plugin_textdomain(
			'webpgen',
			false,
			basename( dirname( WEBPGEN_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
