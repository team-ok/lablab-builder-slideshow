<?php
/*
 * Plugin Name:       Lablab Builder Slideshow Module
 * Plugin URI:        https://github.com/team-ok/lablab-builder
 * Description:       Adds a slideshow module to lablab builder.
 * Version:           1.0.0
 * Author:            Timo Klemm
 * Author URI:        https://github.com/team-ok
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lablab-slideshow
 * Domain Path:       /languages
 */

// load the plugin textdomain
add_action( 'plugins_loaded', 'load_lablab_slideshow_module_textdomain' );
function load_lablab_slideshow_module_textdomain(){

	// Relative path to WP_PLUGIN_DIR where the .mo file resides
	$domain_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';

	load_plugin_textdomain( 'lablab-slideshow', false, $domain_path );
}

// load the module class
add_action( 'plugins_loaded', 'load_lablab_slideshow_module_class' );
function load_lablab_slideshow_module_class(){

	require_once( plugin_dir_path( __FILE__ ) . 'classes/class-lablab-builder-slideshow-module.php' );
}

// register this module with lablab builder
add_filter( 'lablab_builder_modules', array( 'Lablab_Builder_Slideshow_Module', 'register' ) );