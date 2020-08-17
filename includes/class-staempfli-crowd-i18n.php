<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.staempfli.com
 * @since      1.0.0
 *
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/includes
 * @author     Florian Auderset <florian.auderset@staempfli.com>
 */
class Staempfli_Crowd_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'staempfli-crowd',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
