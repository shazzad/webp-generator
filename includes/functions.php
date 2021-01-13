<?php
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

		$sql = "SELECT P.ID FROM $wpdb->posts AS P";
		$sql .= " INNER JOIN $wpdb->postmeta AS PM ON (PM.post_id = P.ID)";
		$sql .= " WHERE 1=1";
		$sql .= " AND P.post_status = 'publish'";
		$sql .= " AND PM.meta_key = '_thumbnail_id'";
		$sql .= " AND PM.meta_value = '". $media_id ."'";
		$sql .= " LIMIT 1";
	
		if ( $wpdb->get_var( $sql ) ) {
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

function webpgen_is_cron_disabled() {
	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		return true;
	}

	return false;
}

function webpgen_is_gd_installed() {
	return extension_loaded('gd');
}

function webpgen_is_gd_support_enabled( $module ) {
	if ( ! function_exists( 'gd_info' ) ) {
		return false;
	}

	$info = gd_info();
	if ( $info[ $module ] && $info[ $module ] === true ) {
		return true;
	}

	return false;
}