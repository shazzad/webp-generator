<?php
namespace WebpGen\Admin;

use WebpGen\Image_To_Webp;
use WebpGen\Webp_Generator;
use Exception;

/**
 * Admin Settings Page Class.
 *
 * @package WebpGen
 * @class WebpGen_Admin_Modules_Page
 */
class Settings_Page {

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
		$admin_page = add_submenu_page(
			'options-general.php',
			__( 'Webp Generator', 'webpgen' ),
			__( 'Webp Generator', 'webpgen' ),
			'manage_options',
			'webpgen-settings',
			array( $this, 'render_page' )
		);

		add_action( "admin_print_styles-{$admin_page}", array( $this, 'print_scripts' ) );
	}

	/**
	 * Render Admin Page
	 */
	public function render_page() {
		?>
		<div class="wrap webpgen-wrap">
			<h1><?php _e( 'Webp Generator', 'webpgen' ) ?></h1>
			<div class="webpgen-widget">
				<div class="webpgen-widget-title"><?php esc_html_e( 'Generate Webp Files For Featured Images', 'webpgen' ); ?></div>

				<?php if ( ! webpgen_is_gd_installed() ) : ?>
					<div class="webpgen-error">
						<p><?php _e( 'PHP <code>GD Library</code> has not been installed or enabled on your server. Please contact your host.', 'webpgen' ); ?></p>
					</div>
				<?php elseif ( ! webpgen_is_gd_support_enabled( 'WebP Support' ) ) : ?>
					<div class="webpgen-error">
						<p><?php _e( '<code>WebP Support</code> is disabled on your GD Library. This plugin requires WebP Support Enabled to function properly. Please contact your host.', 'webpgen' ); ?></p>
					</div>
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

	/**
	 * Enqueue Js/Css
	 */
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
