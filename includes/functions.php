<?php
/**
 * @see https://github.com/shazzad/w4-loggable
 */
function webpgen_log( $message, $context = array() ) {
	do_action(
		'w4_loggable_log',
		'WebP Generator',
		$message,
		$context
	);
}

function webpgen_is_media_featured( $media_id ) {
	$result = wp_cache_get( $media_id, 'webpgen_featured' );

	if ( false === $result ) {
		global $wpdb;

		$res = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT
				P.ID
			FROM
				{$wpdb->posts} AS P
			INNER JOIN 
				{$wpdb->postmeta} AS PM ON (PM.post_id = P.ID)
			WHERE
				P.post_status = 'publish'
				AND PM.meta_key = '_thumbnail_id'
				AND PM.meta_value = %d
			",
			$media_id
		));

		if ( $res ) {
			$result = 'yes';
		} else {
			$result = 'no';
		}

		wp_cache_set( $media_id, $result, 'webpgen_featured' );
	}

	if ( 'yes' === $result ) {
		return true;
	}

	return false;
}

function webpgen_featured_image_count() {
	if ( get_transient( 'webpgen_featured_image_count' ) ) {
		return get_transient( 'webpgen_featured_image_count' );
	}

	global $wpdb;
	$image_mimes = array(
		'image/jpeg',
		'image/gif',
		'image/png',
		'image/bmp',
	);

	$result = $wpdb->get_var(
		"
		SELECT
			COUNT(DISTINCT meta_value)
		FROM
			{$wpdb->postmeta}
		WHERE
			meta_key = '_thumbnail_id'
			AND meta_value <> ''
		"
	);
	set_transient( 'webpgen_featured_image_count', $result, 60 );

	return $result;
}

function webpgen_is_cron_disabled() {
	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		return true;
	}

	return false;
}

function webpgen_get_all_image_sizes() {
    global $_wp_additional_image_sizes;

    $default_image_sizes = get_intermediate_image_sizes();

    foreach ( $default_image_sizes as $size ) {
        $image_sizes[ $size ][ 'width' ] = intval( get_option( "{$size}_size_w" ) );
        $image_sizes[ $size ][ 'height' ] = intval( get_option( "{$size}_size_h" ) );
        $image_sizes[ $size ][ 'crop' ] = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
    }

    if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
        $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
    }

    return $image_sizes;
}


function webpgen_is_imagick_available() {
	require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
	require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';

	return WP_Image_Editor_Imagick::test();
}

function webpgen_is_imagick_support_webp() {
	if ( ! class_exists( 'Imagick' ) ) {
		return false;
	}

	try {
		return (bool) Imagick::queryformats( 'WEBP' );
	} catch ( Exception $e ) {
		return false;
	}
}

function webpgen_is_gd_available() {
	require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
	require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';

	return WP_Image_Editor_GD::test();
}

function webpgen_is_gd_support_webp() {
	if ( ! function_exists( 'gd_info' ) ) {
		return false;
	}

	$info = gd_info();

	if ( isset( $info['WebP Support'] ) && $info['WebP Support'] ) {
		return true;
	}
}