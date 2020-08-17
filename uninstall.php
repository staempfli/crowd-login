<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link       https://www.staempfli.com
 * @since      1.0.0
 *
 * @package    Staempfli_Crowd_Login
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove all option values from database since no longer in use.
delete_option('staempfli_crowd_url');
delete_option('staempfli_crowd_application_name');
delete_option('staempfli_crowd_application_password');
delete_option('staempfli_crowd_login_mode');
delete_option('staempfli_crowd_group');
delete_option('staempfli_crowd_login_securitymode');
delete_option('staempfli_crowd_account_type');
delete_option('staempfli_crowd_test_username');
delete_option('staempfli_crowd_test_password');