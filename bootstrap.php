<?php
/*
Plugin Name: Clipchamp
Plugin URI: https://clipchamp.com
Description: Brings the Clipchamp API to your WordPress site.
Version: 1.6.7
Author: Clipchamp Pty Ltd
Author URI: https://clipchamp.com
License: GPLv2
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'CCB_NAME', 'Clipchamp' );
define( 'CCB_REQUIRED_PHP_VERSION', '5.3' );                          // because of get_called_class()
define( 'CCB_REQUIRED_WP_VERSION', '4.0' );                          // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function ccb_requirements_met() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, CCB_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, CCB_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function ccb_requirements_error() {
	global $wp_version;

	require_once dirname( __FILE__ ) . '/views/requirements-error.php';
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( ccb_requirements_met() ) {
	require_once __DIR__ . '/classes/ccb-module.php';
	require_once __DIR__ . '/classes/clipchamp.php';
	require_once __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php';
	require_once __DIR__ . '/classes/ccb-welcome.php';
	require_once __DIR__ . '/classes/ccb-settings.php';
	require_once __DIR__ . '/classes/ccb-custom-post-type.php';
	require_once __DIR__ . '/classes/ccb-video-post-type.php';
	require_once __DIR__ . '/classes/ccb-shortcode.php';
	require_once __DIR__ . '/classes/ccb-block.php';
	require_once __DIR__ . '/classes/ccb-uploader.php';

	if ( class_exists( 'Clipchamp' ) ) {
		$GLOBALS['ccb'] = Clipchamp::get_instance();
		register_activation_hook( __FILE__, array( $GLOBALS['ccb'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['ccb'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'ccb_requirements_error' );
}
