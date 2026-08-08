<?php
/**
 * Plugin Name: ITSE Features & Navbar
 * Plugin URI:  https://github.com/nazim613/ITSE-navbar-plugin
 * Description: Replaces any WordPress header with a modern Dynamic Island floating ITSE navbar, offer bar, WooCommerce/FunnelKit cart icon, profile icon, separate desktop/mobile menus, mobile accordion submenus, and GitHub automatic update checker.
 * Version:     1.0.8
 * Author:      ITSE
 * Text Domain: itse-navbar
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'ITSE_NAVBAR_VERSION', '1.0.8' );
define( 'ITSE_NAVBAR_DIR', plugin_dir_path( __FILE__ ) );
define( 'ITSE_NAVBAR_URL', plugin_dir_url( __FILE__ ) );

// Legacy aliases for compatibility
define( 'DIN_VERSION', ITSE_NAVBAR_VERSION );
define( 'DIN_PLUGIN_DIR', ITSE_NAVBAR_DIR );
define( 'DIN_PLUGIN_URL', ITSE_NAVBAR_URL );

require_autoload_files();

function require_autoload_files() {
	require_once ITSE_NAVBAR_DIR . 'includes/class-din-admin.php';
	require_once ITSE_NAVBAR_DIR . 'includes/class-din-frontend.php';
	require_once ITSE_NAVBAR_DIR . 'includes/class-din-updater.php';
}

function run_itse_navbar() {
	$admin    = new DIN_Admin();
	$frontend = new DIN_Frontend();
	$updater  = new ITSE_Navbar_Updater( __FILE__ );

	$admin->init();
	$frontend->init();
	$updater->init();
}

add_action( 'plugins_loaded', 'run_itse_navbar' );
