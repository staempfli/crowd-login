<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.staempfli.com
 * @since      1.0.0
 *
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/includes
 * @author     Florian Auderset <florian.auderset@staempfli.com>
 */
class Staempfli_Crowd_Activator {

	/**
     * Actions performed during plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    // Set default option values on plugin activation
	    add_option('staempfli_crowd_login_mode', 'mode_create');
	    add_option('staempfli_crowd_login_securitymode', 'security_normal');
	    add_option('staempfli_crowd_account_type', 'administrator');
	}

}
