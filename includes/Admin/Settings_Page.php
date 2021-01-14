<?php
/**
 * Admin main class.
 *
 * @package WebpGen
 */

namespace WebpGen\Admin;

use WebpGen\Utils;

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
		add_filter( 'mime_types', array( $this, 'mime_types' ) );
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

	public function mime_types( $types ) {
		$types['webp'] = 'image/webp';
		return $types;
	}

	/**
	 * Render Admin Page
	 */
	public function render_page() {
		?>
		<div class="wrap webpgen-wrap">
			<h1><?php _e( 'Webp Generator', 'webpgen' ); ?></h1>
			<div class="webpgen-widget">
				<div class="webpgen-widget-title"><?php esc_html_e( 'Generate Webp Files For Featured Images', 'webpgen' ); ?></div>

				<?php if ( ! wp_image_editor_supports( array( 'methods' => array( 'save' ) ) ) ) : ?>
					<div class="webpgen-error"><p><?php _e( 'Your can\'t use this tool because your server doesn\'t support image conversion which means that WordPress can\'t convert new image. Please ask your host to install the Imagick or GD PHP extensions.', 'webpgeb' ); ?></p></div>
				<?php else : ?>
				<div class="webpgen-action-wrap">
					<button class="button button-primary button-large" id="webpgen-start-btn">
						<?php esc_html_e( 'Generate Now', 'webpgen' ); ?>
					</button>
					<span class="webpgen-ajax-message">
						<?php 
						printf( 
							__( '%d Featured Images Approximately.', 'webpgen' ),
							webpgen_featured_image_count()
						);
						?>
					</span>
				</div>
				<?php endif; ?>
			</div>

			<div class="webpgen-widget webpgen-widget-logs" style="display:none;">
				<div class="webpgen-widget-title"><?php esc_html_e( 'Logs', 'webpgen' ); ?></div>
				<div class="webpgen-generate-logs"></div>
			</div>

			<div class="webpgen-widget">
				<div class="webpgen-widget-title"><?php esc_html_e( 'Registered Image Sizes', 'webpgen' ); ?></div>
				<table class="widefat striped">
					<thead>
					<tr>
						<th><?php _e( 'Size', 'webpgen' ); ?></th>
						<th><?php _e( 'Width', 'webpgen' ); ?></th>
						<th><?php _e( 'Height', 'webpgen' ); ?></th>
						<th><?php _e( 'Crop', 'webpgen' ); ?></th>
					</tr>
					</thead>
					<?php  foreach ( webpgen_get_all_image_sizes() as $key => $data ) : ?>
					<tr>
						<td><?php echo $key; ?></td>
						<td><?php echo $data['width']; ?></td>
						<td><?php echo $data['height']; ?></td>
						<td><?php echo isset( $data['crop'] ) && $data['crop'] ? __( 'Yes' ) : __( 'No' ); ?></td>
					</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="webpgen-widget">
				<div class="webpgen-widget-title"><?php esc_html_e( 'REST API field', 'webpgen' ); ?></div>
				<p><?php _e( 'Rest Api will display webp files data as <code>webp_media_details</code> field.', 'webpgen' ); ?></p>
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
		wp_localize_script(
			'webpgen-admin',
			'webpgen',
			array(
				'textNormal'        => __( 'Generate Now', 'webpgen' ),
				'textScheduling'    => __( 'Scheduling...', 'webpgen' ),
				'textGenerating'    => __( 'Generating...', 'webpgen' ),
				'textGenerateAgain' => __( 'Generate Again ?', 'webpgen' ),
			)
		);
		wp_enqueue_script( 'webpgen-admin' );
	}
}
