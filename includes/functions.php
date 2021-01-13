<?php

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