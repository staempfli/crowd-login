<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.staempfli.com
 * @since             1.0.0
 * @package           Staempfli_Crowd_Login
 *
 * @wordpress-plugin
 * Plugin Name:       Stämpfli Crowd Login
 * Description:       This plugin provides a login provider for atlassian crowd.
 * Version:           1.0.5
 * Author:            Florian Auderset
 * Author URI:        https://www.staempfli.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       staempfli-crowd
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'STAEMPFLI_CROWD_VERSION', '1.0.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-staempfli-crowd-activator.php
 */
function activate_staempfli_crowd() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-staempfli-crowd-activator.php';
	Staempfli_Crowd_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-staempfli-crowd-deactivator.php
 */
function deactivate_staempfli_crowd() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-staempfli-crowd-deactivator.php';
	Staempfli_Crowd_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_staempfli_crowd' );
register_deactivation_hook( __FILE__, 'deactivate_staempfli_crowd' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-staempfli-crowd.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_staempfli_crowd() {

	$plugin = new Staempfli_Crowd();
	$plugin->run();

}
run_staempfli_crowd();
