<?php
namespace WebpGen\Admin\Page;

use WebpGen\Admin\WP_Settings_Api;
use WebpGen\Image_To_Webp;
use WebpGen\Webp_Generator;
use Exception;

/**
 * Admin Settings Page Class.
 *
 * @package WebpGen
 * @class WebpGen_Admin_Modules_Page
 */

class Settings {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Sanitize settings option
	 */
	public function admin_menu() {
		// Access capability.
		$access_cap = apply_filters( 'webpgen_admin_page_access_cap', 'manage_options' );

		// Register menu.
		$admin_page = add_submenu_page(
			'options-general.php',
			__( 'Webp Generator', 'webpgen' ),
			__( 'Webp Generator', 'webpgen' ),
			$access_cap,
			'webpgen-settings',
			array( $this, 'render_page' )
		);

		add_action( "admin_print_styles-{$admin_page}", array( $this, 'print_scripts' ) );
		add_action( "load-{$admin_page}", array( $this, 'handle_actions' ) );
	}

	public function handle_actions() {
		// Schedule rewrite rules regeneration.
		if ( isset( $_REQUEST['action'] ) && 'clear_cache' === $_REQUEST['action'] ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'webpgen_clear_cache' ) ) {
				wp_die( __( 'Cheating huh?' ) );
			}

			webpgen_clear_all_cache();

			wp_redirect( admin_url( 'options-general.php?page=webpgen-settings&cache-cleared=true' ) );
			exit;
		}
	}

	public function render_page() {
		// $generator = new Webp_Generator();

		// if ( $generator->has_completed() ) {
		// 	echo 'Completed';
		// 	#$generator->schedule( false, 60 );

		// } elseif ( $generator->has_started() ) {
		// 	echo 'generating..';
		// 	$generator->generate();

		// } elseif ( $generator->is_scheduled() && ! $generator->has_started() ) {
		// 	echo 'not started, starting...';
		// 	$generator->start();

		// } elseif ( ! $generator->is_scheduled() ) {
		// 	echo 'not scheduled, scheduling...';
		// 	$generator->schedule();
		// }

		// echo '<pre>';
		// print_r( $generator->get_logs() );
		// echo '</pre>';

		// global $wpdb;

		// $offset = 20;
		// $limit = 10;

		// $image_mimes = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
		// $sql = "SELECT P.* FROM $wpdb->posts AS P";
		// $sql .= " INNER JOIN $wpdb->postmeta AS PM ON (PM.meta_value = P.ID)";
		// $sql .= " WHERE 1=1";
		// $sql .= " AND PM.meta_key = '_thumbnail_id'";
		// $sql .= " AND post_type='attachment'";
		// $sql .= " AND post_mime_type IN ('" . implode( "', '", $image_mimes ) . "')";
		// $sql .= " GROUP BY P.ID";
		// $sql .= " ORDER BY ID ASC";
		// $sql .= " LIMIT $offset, $limit";

		// $posts = $wpdb->get_results( $sql );

		// $metadata = wp_get_attachment_metadata( 10734 );
		// $uploads = wp_get_upload_dir();

		// $files = [];
		// if ( $uploads && false === $uploads['error'] ) {
		// 	$file = $metadata['file'];
		// 	if ( 0 !== strpos( $file, $uploads['basedir'] ) ) {
        //         $file = $uploads['basedir'] . '/' . $file;
		// 	}
			
		// 	foreach ( $metadata['sizes'] as $size ) {
		// 		$files[] = dirname( $file ) . '/' . $size['file'];
		// 	}
		// }

		// echo '<pre>';
		// print_r( $files );
		// print_r( $metadata );
		// echo '</pre>';
		// exit;

		// try {
		// 	$converter = new Image_To_Webp( WP_CONTENT_DIR . '/uploads/2020/12/Test2.jpg' );
		// 	if ($converter->webp_exists()) {
		// 		echo 'Webp Exists';
		// 	} else {
		// 		$converter->generate();
		// 	}

		// 	echo '<pre>';
		// 	print_r( $converter );
		// 	echo '</pre>';
		// } catch( Exception $e ) {
		// 	echo $e->getMessage();
		// }

		?>
		<div class="wrap webpgen-wrap">
			<h1><?php _e( 'Webp Generator', 'webpgen' ) ?></h1>
			<div class="webpgen-widget">
				<div class="webpgen-widget-title"><?php esc_html_e( 'Generate Webp Files For Featured Images', 'webpgen' ); ?></div>

				<?php if ( ! webpgen_is_gd_installed() ) : ?>
					<div class="webpgen-error"><p>PHP <code>GD Library</code> has not been installed or enabled on your server. Please contact your host.</p></div>
				<?php elseif ( ! webpgen_is_gd_support_enabled( 'WebP Support' ) ) : ?>
					<div class="webpgen-error"><p><code>WebP Support</code> is disabled on your GD Library. This plugin requires WebP Support Enabled to function properly. Please contact your host.</p></div>
				<?php else : ?>
				<div class="webpgen-action-wrap">
					<button class="button button-primary button-large webpgen-start-btn">
						<?php esc_html_e( 'Generate Now', 'webpgen' ); ?>
					</button>
					<span class="webpgen-ajax-message"></span>
				</div>
				<?php endif; ?>
			</div>

			<div class="webpgen-widget webpgen-widget-logs" style="display:none;">
				<div class="webpgen-widget-title"><?php esc_html_e( 'Logs', 'webpgen' ); ?></div>
				<div class="webpgen-generate-logs">
					<div>1. Started.</div>
				</div>
			</div>

			<div class="webpgen-widget">
				<div class="webpgen-widget-title"><?php esc_html_e( 'REST API field', 'webpgen' ); ?></div>
				<p><?php esc_html_e( 'Rest Api will display webp files data as <code>webp_media_details</code> field.', 'webpgen' ); ?></p>
				<p><?php esc_html_e( 'For post, page & custom post types, this field will display information for featured_image.', 'webpgen' ); ?></p>
				<p><?php esc_html_e( 'And for media/attachment post type, this field will be diplayed only if the media is used as featured_image with any post, page or custom post type.', 'webpgen' ); ?></p>
			</div>
			
		</div>
		<?php
	}

	public function print_scripts() {
		wp_enqueue_style( 'webpgen-admin' );
		wp_localize_script( 'webpgen-admin', 'webpgen', [
			'textNormal' => __( 'Generate Now', 'webpgen' ),
			'textScheduling' => __( 'Scheduling...', 'webpgen' ),
			'textGenerating' => __( 'Generating...', 'webpgen' ),
			'textGenerateAgain' => __( 'Generate Again...', 'webpgen' )
		] );
		wp_enqueue_script( 'webpgen-admin' );
	}
}
