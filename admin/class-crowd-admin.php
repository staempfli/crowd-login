<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.auderset.dev
 * @since      1.0.0
 *
 * @package    Crowd
 * @subpackage Crowd/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * Retrieve values with:
 * $crowd_login_options = get_option( 'crowd_login_option_name' ); // Array of All Options
 * $crowd_url = $crowd_login_options['crowd_url']; // Crowd URL
 * $crowd_application_name = $crowd_login_options['crowd_application_name']; // Application Name
 * $crowd_application_password = $crowd_login_options['crowd_application_password']; // Application Password
 * $crowd_login_mode = $crowd_login_options['crowd_login_mode']; // Login Mode
 * $crowd_account_type = $crowd_login_options['crowd_account_type']; // Account Type
 *
 * @package    Crowd
 * @subpackage Crowd/admin
 * @author     Florian Auderset <florian@auderset.dev>
 */
class Crowd_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Callback to add options page
     *
     * @since    1.0.0
     */
    public function crowd_login_add_plugin_page() {

        add_options_page(
            'Crowd Login', // page_title
            'Crowd Login', // menu_title
            'manage_options', // capability
            'crowd-login', // menu_slug
            [$this, 'crowd_login_create_admin_page'] // function
        );

    }

    /**
     * Callback to add option fields
     *
     * @since    1.0.0
     */
    public function crowd_login_page_init() {
        register_setting(
            'crowd_login_option_group', // option_group
            'crowd_login_option_name', // option_name
            [$this, 'crowd_login_sanitize'] // sanitize_callback
        );

        add_settings_section(
            'crowd_login_setting_section', // id
            'Settings', // title
            [$this, 'crowd_login_section_info'], // callback
            'crowd-login-admin' // page
        );

        add_test_section(
            'crowd_login_test_section', // id
            'Test Settings', // title
            [$this, 'crowd_login_section_info'], // callback
            'crowd-login-admin' // page
        );

        add_settings_field(
            'crowd_url', // id
            'Crowd URL', // title
            [$this, 'crowd_url_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'application_name', // id
            'Application Name', // title
            [$this, 'application_name_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'application_password', // id
            'Application Password', // title
            [$this, 'application_password_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_login_mode', // id
            'Login Mode', // title
            [$this, 'login_mode_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_account_type', // id
            'Account Type', // title
            [$this, 'account_type_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_test_username', // id
            'Username', // title
            [$this, 'crowd_test_username_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_test_section' // section
        );

        add_settings_field(
            'crowd_test_password', // id
            'Password', // title
            [$this, 'crowd_test_password_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_test_section' // section
        );
    }


    /**
     * Sanitize text input fields
     *
     * @param $input
     * @return array
     *
     * @since    1.0.0
     */
    public function crowd_login_sanitize($input) {
        $sanitary_values = [];
        if ( isset( $input['crowd_url'] ) ) {
            $sanitary_values['crowd_url'] = sanitize_text_field( $input['crowd_url'] );
        }

        if ( isset( $input['crowd_application_name'] ) ) {
            $sanitary_values['crowd_application_name'] = sanitize_text_field( $input['crowd_application_name'] );
        }

        if ( isset( $input['crowd_application_password'] ) ) {
            $sanitary_values['crowd_application_password'] = sanitize_text_field( $input['crowd_application_password'] );
        }

        if ( isset( $input['crowd_login_mode'] ) ) {
            $sanitary_values['crowd_login_mode'] = $input['crowd_login_mode'];
        }

        if ( isset( $input['crowd_account_type'] ) ) {
            $sanitary_values['crowd_account_type'] = $input['crowd_account_type'];
        }

        if ( isset( $input['crowd_test_username'] ) ) {
            $sanitary_values['crowd_test_username'] = $input['crowd_test_username'];
        }

        if ( isset( $input['crowd_test_password'] ) ) {
            $sanitary_values['crowd_test_password'] = $input['crowd_test_password'];
        }

        return $sanitary_values;
    }

    /**
     *
     */
    public function crowd_login_section_info() {

    }

    /**
     * Callback for input field crowd_url
     *
     * @since    1.0.0
     */
    public function crowd_url_callback() {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_url]" id="crowd_url" value="%s">',
            isset( $this->crowd_login_options['crowd_url'] ) ? esc_attr( $this->crowd_login_options['crowd_url']) : ''
        );
    }

    /**
     * Callback for input field crowd_application_name
     *
     * @since    1.0.0
     */
    public function crowd_application_name_callback() {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_application_name]" id="crowd_application_name" value="%s">',
            isset( $this->crowd_login_options['crowd_application_name'] ) ? esc_attr( $this->crowd_login_options['crowd_application_name']) : ''
        );
    }

    /**
     * Callback for input field crowd_application_password
     *
     * @since    1.0.0
     */
    public function crowd_application_password_callback() {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_application_password]" id="crowd_application_password" value="%s">',
            isset( $this->crowd_login_options['crowd_application_password'] ) ? esc_attr( $this->crowd_login_options['crowd_application_password']) : ''
        );
    }

    /**
     * Callback for input field crowd_login_mode
     *
     * @since    1.0.0
     */
    public function crowd_login_mode_callback() {
        ?> <fieldset><?php $checked = ( isset( $this->crowd_login_options['crowd_login_mode'] ) && $this->crowd_login_options['crowd_login_mode'] === '0' ) ? 'checked' : '' ; ?>
            <label for="crowd_login_mode-0"><input type="radio" name="crowd_login_option_name[crowd_login_mode]" id="crowd_login_mode-0" value="0" <?php echo $checked; ?>> Default</label><br>
            <?php $checked = ( isset( $this->crowd_login_options['crowd_login_mode'] ) && $this->crowd_login_options['crowd_login_mode'] === '1' ) ? 'checked' : '' ; ?>
            <label for="crowd_login_mode-1"><input type="radio" name="crowd_login_option_name[crowd_login_mode]" id="crowd_login_mode-1" value="1" <?php echo $checked; ?>> Create Account</label><br>
            <?php $checked = ( isset( $this->crowd_login_options['crowd_login_mode'] ) && $this->crowd_login_options['crowd_login_mode'] === '2' ) ? 'checked' : '' ; ?>
            <label for="crowd_login_mode-2"><input type="radio" name="crowd_login_option_name[crowd_login_mode]" id="crowd_login_mode-2" value="2" <?php echo $checked; ?>> Create Account when in Group</label><br>
            <?php $checked = ( isset( $this->crowd_login_options['crowd_login_mode'] ) && $this->crowd_login_options['crowd_login_mode'] === '' ) ? 'checked' : '' ; ?>
            <label for="crowd_login_mode-3"><input type="radio" name="crowd_login_option_name[crowd_login_mode]" id="crowd_login_mode-3" value="" <?php echo $checked; ?>> </label></fieldset> <?php
    }

    /**
     * Callback for input field crowd_account_type
     *
     * @since    1.0.0
     */
    public function crowd_account_type_callback() {
        ?> <select name="crowd_login_option_name[crowd_account_type]" id="crowd_account_type">
            <?php $selected = (isset( $this->crowd_login_options['crowd_account_type'] ) && $this->crowd_login_options['crowd_account_type'] === 'Administrator') ? 'selected' : '' ; ?>
            <option value="Administrator" <?php echo $selected; ?>>Administrator</option>
            <?php $selected = (isset( $this->crowd_login_options['crowd_account_type'] ) && $this->crowd_login_options['crowd_account_type'] === 'Editor') ? 'selected' : '' ; ?>
            <option value="Editor" <?php echo $selected; ?>>Editor</option>
            <?php $selected = (isset( $this->crowd_login_options['crowd_account_type'] ) && $this->crowd_login_options['crowd_account_type'] === 'Author') ? 'selected' : '' ; ?>
            <option value="Author" <?php echo $selected; ?>>Author</option>
            <?php $selected = (isset( $this->crowd_login_options['crowd_account_type'] ) && $this->crowd_login_options['crowd_account_type'] === 'Contributor') ? 'selected' : '' ; ?>
            <option value="Contributor" <?php echo $selected; ?>>Contributor</option>
            <?php $selected = (isset( $this->crowd_login_options['crowd_account_type'] ) && $this->crowd_login_options['crowd_account_type'] === 'Subscriber') ? 'selected' : '' ; ?>
            <option value="Subscriber" <?php echo $selected; ?>>Subscriber</option>
        </select> <?php
    }

    /**
     * Callback for input field crowd_test_username
     *
     * @since    1.0.0
     */
    public function crowd_test_username_callback() {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_test_username]" id="crowd_test_username" value="%s">',
            isset( $this->crowd_login_options['crowd_test_username'] ) ? esc_attr( $this->crowd_login_options['crowd_test_username']) : ''
        );
    }

    /**
     * Callback for input field crowd_test_password
     *
     * @since    1.0.0
     */
    public function crowd_test_password_callback() {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_test_password]" id="crowd_test_password" value="%s">',
            isset( $this->crowd_login_options['crowd_test_password'] ) ? esc_attr( $this->crowd_login_options['crowd_test_password']) : ''
        );
    }
}
