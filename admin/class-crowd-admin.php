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
class Crowd_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The configured login options.
     *
     * @since    1.0.0
     * @access   private
     * @var      mixed $crowd_login_options The current login options.
     */
    private $crowd_login_options;

    /**
     * The crowd client instance
     *
     * @since    1.0.0
     * @access   private
     * @var      Crowd_Client $crowd_client The crowd client instance.
     */
    private $crowd_client = null;

    /**
     * The principal token recieved from the Atlassian Crowd server.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $crowd_login_principal_token The current principal token..
     */
    private $crowd_login_principal_token;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->crowd_login_options = get_option('crowd_login_option_name');
    }

    /**
     * Callback to add options page
     *
     * @since    1.0.0
     */
    public function crowd_login_add_plugin_page()
    {
        add_options_page(
            'Crowd Login', // page_title
            'Crowd Login', // menu_title
            'manage_options', // capability
            'crowd-login', // menu_slug
            [$this, 'crowd_login_create_admin_page'] // function
        );
    }

    /**
     * Callback to add content to the options page
     *
     * @since    1.0.0
     */
    public function crowd_login_create_admin_page()
    {
        ?>
        <div class="wrap">
            <h2>Crowd Login</h2>
            <?php settings_errors(); ?>

            <form id="crowd-login-options" method="post" action="options.php">
                <?php
                settings_fields('crowd_login_option_group');
                do_settings_sections('crowd-login-admin');
                submit_button();
                ?>
            </form>

            <hr/>

            <form id="crowd-login-test-options" method="post" action="options.php">
                <?php
                settings_fields('crowd_login_test_option_group');
                do_settings_sections('crowd-login-admin-test');
                submit_button(__('Test settings', 'crowd'));
                ?>
            </form>
        </div>
    <?php }

    /**
     * Callback to add option fields
     *
     * @since    1.0.0
     */
    public function crowd_login_page_init()
    {
        register_setting(
            'crowd_login_option_group', // option_group
            'crowd_login_option_name', // option_name
            [$this, 'crowd_login_sanitize_options'] // sanitize_callback
        );

        register_setting(
            'crowd_login_test_option_group', // option_group
            'crowd_login_test_option_name', // option_name
            [$this, 'crowd_login_sanitize_test_options'] // sanitize_callback
        );

        add_settings_section(
            'crowd_login_setting_section', // id
            'Settings', // title
            [$this, 'crowd_login_section_info'], // callback
            'crowd-login-admin' // page
        );

        add_settings_section(
            'crowd_login_test_section', // id
            'Test Settings', // title
            [$this, 'crowd_login_test_section_info'], // callback
            'crowd-login-admin-test' // page
        );

        add_settings_field(
            'crowd_url', // id
            'Crowd URL', // title
            [$this, 'crowd_url_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_application_name', // id
            'Application Name', // title
            [$this, 'crowd_application_name_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_application_password', // id
            'Application Password', // title
            [$this, 'crowd_application_password_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_login_mode', // id
            'Login Mode', // title
            [$this, 'crowd_login_mode_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_group', // id
            'Group', // title
            [$this, 'crowd_group_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_login_securitymode', // id
            'Security Mode', // title
            [$this, 'crowd_login_securitymode_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_account_type', // id
            'Account Type', // title
            [$this, 'crowd_account_type_callback'], // callback
            'crowd-login-admin', // page
            'crowd_login_setting_section' // section
        );

        add_settings_field(
            'crowd_test_username', // id
            'Username', // title
            [$this, 'crowd_test_username_callback'], // callback
            'crowd-login-admin-test', // page
            'crowd_login_test_section' // section
        );

        add_settings_field(
            'crowd_test_password', // id
            'Password', // title
            [$this, 'crowd_test_password_callback'], // callback
            'crowd-login-admin-test', // page
            'crowd_login_test_section' // section
        );
    }

    /**
     * Sanitize text input fields for options
     *
     * @param $input
     * @return array
     *
     * @since    1.0.0
     */
    public function crowd_login_sanitize_options($input)
    {
        $sanitary_values = [];

        if (isset($input['crowd_url'])) {
            $sanitary_values['crowd_url'] = sanitize_text_field($input['crowd_url']);
        }

        if (isset($input['crowd_application_name'])) {
            $sanitary_values['crowd_application_name'] = sanitize_text_field($input['crowd_application_name']);
        }

        if (isset($input['crowd_application_password'])) {
            $sanitary_values['crowd_application_password'] = sanitize_text_field($input['crowd_application_password']);
        }

        if (isset($input['crowd_login_mode'])) {
            $sanitary_values['crowd_login_mode'] = $input['crowd_login_mode'];
        }

        if (isset($input['crowd_group'])) {
            $sanitary_values['crowd_group'] = $input['crowd_group'];
        }

        if (isset($input['crowd_login_securitymode'])) {
            $sanitary_values['crowd_login_securitymode'] = $input['crowd_login_securitymode'];
        }

        if (isset($input['crowd_account_type'])) {
            $sanitary_values['crowd_account_type'] = $input['crowd_account_type'];
        }

        return $sanitary_values;
    }

    /**
     * Sanitize text input fields for test options
     *
     * @param $input
     * @return array
     *
     * @since    1.0.0
     */
    public function crowd_login_sanitize_test_options($input)
    {
        $sanitary_values = [];

        if (isset($input['crowd_test_username'])) {
            $sanitary_values['crowd_test_username'] = $input['crowd_test_username'];
        }

        if (isset($input['crowd_test_password'])) {
            $sanitary_values['crowd_test_password'] = $input['crowd_test_password'];
        }

        return $sanitary_values;
    }

    /**
     * Callback for options section information
     *
     * @since    1.0.0
     */
    public function crowd_login_section_info()
    {

    }

    /**
     * Callback for test section information
     *
     * @since    1.0.0
     */
    public function crowd_login_test_section_info()
    {

    }

    /**
     * Callback for input field crowd_url
     *
     * @since    1.0.0
     */
    public function crowd_url_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_url]" id="crowd_url" value="%s">',
            isset($this->crowd_login_options['crowd_url']) ? esc_attr($this->crowd_login_options['crowd_url']) : ''
        );
        echo "<p class='description'>" . __('Example: https://crowd.server.com:8080/crowd', 'crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_application_name
     *
     * @since    1.0.0
     */
    public function crowd_application_name_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_application_name]" id="crowd_application_name" value="%s">',
            isset($this->crowd_login_options['crowd_application_name']) ? esc_attr($this->crowd_login_options['crowd_application_name']) : ''
        );
        echo "<p class='description'>" . __('The application name specified in the Atlassian Crowd backend.', 'crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_application_password
     *
     * @since    1.0.0
     */
    public function crowd_application_password_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_application_password]" id="crowd_application_password" value="%s">',
            isset($this->crowd_login_options['crowd_application_password']) ? esc_attr($this->crowd_login_options['crowd_application_password']) : ''
        );
        echo "<p class='description'>" . __('The application password specified in the Atlassian Crowd backend.', 'crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_login_mode
     *
     * @since    1.0.0
     */
    public function crowd_login_mode_callback()
    {
        ?>
        <fieldset>
            <?php $checked = (isset($this->crowd_login_options['crowd_login_mode']) && $this->crowd_login_options['crowd_login_mode'] === 'mode_auth') ? 'checked' : ''; ?>
            <label for="crowd_login_mode_auth"><input type="radio" name="crowd_login_option_name[crowd_login_mode]"
                                                      id="crowd_login_mode_auth"
                                                      value="mode_auth" <?php echo $checked; ?>>
                <?php echo __('Authenticate only', 'crowd'); ?>
            </label>
            <p class="description"><?php echo __('Authenticate user against Atlassian Crowd. No user is created.', 'crowd'); ?></p><br>

            <?php $checked = (isset($this->crowd_login_options['crowd_login_mode']) && $this->crowd_login_options['crowd_login_mode'] === 'mode_create') ? 'checked' : ''; ?>
            <label for="crowd_login_mode_create"><input type="radio" name="crowd_login_option_name[crowd_login_mode]"
                                                        id="crowd_login_mode_create"
                                                        value="mode_create" <?php echo $checked; ?>>
                <?php echo __('Create user', 'crowd'); ?>
            </label>
            <p class="description"><?php echo __('Create Wordpress user when successfully authenticated against Atlassian Crowd.', 'crowd'); ?></p><br>

            <?php $checked = (isset($this->crowd_login_options['crowd_login_mode']) && $this->crowd_login_options['crowd_login_mode'] === 'mode_create_group') ? 'checked' : ''; ?>
            <label for="crowd_login_mode_create_group"><input type="radio"
                                                              name="crowd_login_option_name[crowd_login_mode]"
                                                              id="crowd_login_mode_create_group"
                                                              value="mode_create_group" <?php echo $checked; ?>>
                <?php echo __('Create user when groupmember', 'crowd'); ?>
            </label>
            <p class="description"><?php echo __('Create Wordpress user only when successfully authenticated against Atlassian Crowd and user is a member of a specified group.', 'crowd'); ?></p>
        </fieldset>
        <?php
    }

    /**
     * Callback for input field crowd_group
     *
     * @since    1.0.0
     */
    public function crowd_group_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_group]" id="crowd_group" value="%s">',
            isset($this->crowd_login_options['crowd_group']) ? esc_attr($this->crowd_login_options['crowd_group']) : ''
        );
        echo "<p class='description'>" . __('A Wordpress user is only created if the user is a member of this group.', 'crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_login_securitymode
     *
     * @since    1.0.0
     */
    public function crowd_login_securitymode_callback()
    {
        ?>
        <fieldset>
            <?php $checked = (isset($this->crowd_login_options['crowd_login_securitymode']) && $this->crowd_login_options['crowd_login_securitymode'] === 'security_normal') ? 'checked' : ''; ?>
            <label for="crowd_login_securitymode-normal">
                <input type="radio" name="crowd_login_option_name[crowd_login_securitymode]"
                       id="crowd_login_securitymode-normal" value="security_normal" <?php echo $checked; ?>>
                <?php echo __('Normal', 'crowd'); ?>
            </label>
            <p class="description"><?php echo __('First attempt to login with Atlassian Crowd user, when failing attempt login using the local Wordpress users. This can be used to provide a mixed login mode with Atlassian Crowd and Wordpress default login.', 'crowd'); ?></p><br>

            <?php $checked = (isset($this->crowd_login_options['crowd_login_securitymode']) && $this->crowd_login_options['crowd_login_securitymode'] === 'security_strict') ? 'checked' : ''; ?>
            <label for="crowd_login_securitymode-strict">
                <input type="radio" name="crowd_login_option_name[crowd_login_securitymode]"
                       id="crowd_login_securitymode-strict" value="security_strict" <?php echo $checked; ?>>
                <?php echo __('Strict', 'crowd'); ?>
            </label>
            <p class="description"><?php echo __('Restrict logins to Atlassian Crowd only. The default Wordpress authentication is deactivated.', 'crowd'); ?></p><br>
        </fieldset>
        <?php
    }

    /**
     * Callback for input field crowd_account_type
     *
     * @since    1.0.0
     */
    public function crowd_account_type_callback()
    {
        $user_roles = get_editable_roles();
        ?>

        <select name="crowd_login_option_name[crowd_account_type]" id="crowd_account_type">
            <?php
            foreach ($user_roles as $role => $details) {
                $role = esc_attr($role);
                $role_name = translate_user_role($details['name']);

                $selected = (isset($this->crowd_login_options['crowd_account_type']) && $this->crowd_login_options['crowd_account_type'] === $role) ? 'selected' : '';
                echo "<option value='$role' $selected>$role_name</option>";
            }
            ?>
        </select>
        <p class="description"><?php echo __('Create Wordpress user with this role when login mode "create" is selected.', 'crowd'); ?></p><br>

        <?php
    }

    /**
     * Callback for input field crowd_test_username
     *
     * @since    1.0.0
     */
    public function crowd_test_username_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_test_username]" id="crowd_test_username" value="%s">',
            isset($this->crowd_login_options['crowd_test_username']) ? esc_attr($this->crowd_login_options['crowd_test_username']) : ''
        );
    }

    /**
     * Callback for input field crowd_test_password
     *
     * @since    1.0.0
     */
    public function crowd_test_password_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="crowd_login_option_name[crowd_test_password]" id="crowd_test_password" value="%s">',
            isset($this->crowd_login_options['crowd_test_password']) ? esc_attr($this->crowd_login_options['crowd_test_password']) : ''
        );
    }

    /**
     * Custom user authentication using Atlassian Crowd server
     *
     * Authenticate against Atlassian Crowd server. Create user or log in according plugin configuration.
     * Return Wordpress user if successful, return a Wordpress error if not.
     *
     * @since    1.0.0
     * @return WP_Error|WP_User
     */
    public function crowd_login_authenticate($user, $username, $password)
    {
        try {
            $this->crowd_client = new Crowd_Client();
        } catch (Crowd_Connection_Exception $e) {
            $error = new WP_Error();
            $error->add('crowd_login_connection_error', $e->getMessage());
            return $error;
        }

        try {
            $crowd_login_app_token = $this->crowd_client->authenticateApplication();
        } catch (Crowd_Login_Exception $e) {
            $this->crowd_client = null;
            echo $e->getMessage();
        }

        if (is_a($user, 'WP_User')) {
            return $user;
        }

        // Remove default authentication if security mode is set to strict.
        if ($this->crowd_login_options['crowd_login_securitymode'] == 'security_strict') {
            remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
        }

        // Check if credentials have been set.
        if (empty($username) || empty($password)) {
            $error = new WP_Error();

            if (empty($username)) {
                $error->add('empty_username', __('<strong>Error</strong>: The username field is empty.'));
            }

            if (empty($password)) {
                $error->add('empty_password', __('<strong>Error</strong>: The password field is empty.'));
            }

            return $error;
        }

        // Authenticate against Atlassian Crowd server, login or create user according to plugin configuration.
        $auth_result = $this->crowd_login_can_authenticate($username, $password);
        if ($auth_result == true && !is_a($auth_result, 'WP_Error')) {
            $user = get_user_by('login', $username);

            if (!$user || (strtolower($user->user_login) != strtolower($username))) {
                switch ($this->crowd_login_options['crowd_login_mode']) {
                    case 'mode_create':
                        $new_user_id = $this->crowd_login_create_user($username);
                        if (!is_a($new_user_id, 'WP_Error')) {
                            return new WP_User($new_user_id);
                        } else {
                            do_action('wp_login_failed', $username);
                            return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: An error occurred creating the user in Wordpress. <br><br>' . $new_user_id->get_error_message()));
                        }
                        break;

                    case 'mode_create_group':
                        if ($this->crowd_login_is_in_group($username)) {
                            $new_user_id = $this->crowd_login_create_user($username);
                            if (!is_a($new_user_id, 'WP_Error')) {
                                return new WP_User($new_user_id);
                            } else {
                                do_action('wp_login_failed', $username);
                                return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: An error occurred creating the user in Wordpress. <br><br>' . $new_user_id->get_error_message()));
                            }
                        } else {
                            do_action('wp_login_failed', $username);
                            return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: You are not allowed to log in. Please contact your administrator.'));
                        }
                        break;

                    default:
                        do_action('wp_login_failed', $username);
                        return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: Crowd Login mode does not permit account creation.'));
                }
            } else {
                if ($this->crowd_login_options['crowd_login_mode'] == 'mode_create_group') {
                    if ($this->crowd_login_is_in_group($username)) {
                        return new WP_User($user->ID);
                    } else {
                        do_action('wp_login_failed', $username);
                        return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: You are not allowed to log in. Please contact your administrator.'));
                    }
                } else {
                    return new WP_User($user->ID);
                }
            }
        } else {
            if (is_a($auth_result, 'WP_Error')) {
                return $auth_result;
            } else {
                return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: Authentication failed.'));
            }
        }
    }

    /**
     * Authenticates against Atlassian Crowd server and returns principal token if successful.
     *
     * @param $username
     * @param $password
     * @since    1.0.0
     * @return string|WP_Error
     */
    public function crowd_login_can_authenticate($username, $password)
    {
        if ($this->crowd_client == null) {
            return new WP_Error('crowd_client_error', __('<strong>Crowd Login Error</strong>: No Crowd_Client instance available.'));
        }

        $this->crowd_login_principal_token = $this->crowd_client->authenticatePrincipal($username, $password, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);

        if ($this->crowd_login_principal_token == null) {
            return new WP_Error('crowd_principal_error', __('<strong>Crowd Login Error</strong>: Could not retrieve principal from Atlassian Crowd server.'));
        }

        return $this->crowd_login_principal_token;
    }

    /**
     * Check if user is allowed to login according his assigned groups.
     *
     * @param $username
     * @since    1.0.0
     * @return bool
     */
    public function crowd_login_is_in_group($username)
    {
        if ($this->crowd_client == null) {
            return false;
        }

        $groups = $this->crowd_client->findGroupMemberships($username);
        $crowd_group = $this->crowd_login_options['crowd_group'];

        return in_array($crowd_group, $groups);
    }

    /**
     * Create Wordpress user from userdata recieved from Atlassian Crowd authentication.
     *
     * @param $username
     * @since    1.0.0
     * @return int|WP_Error
     */
    public function crowd_login_create_user($username)
    {
        $result = 0;

        if ($this->crowd_client == null || $this->crowd_login_principal_token == null) {
            return $result;
        }

        $user = $this->crowd_login_get_user_info($this->crowd_login_principal_token);

        $userData = [
            'user_pass' => microtime(),
            'user_login' => $username,
            'user_nicename' => sanitize_title($user['givenName'] . ' ' . $user['sn']),
            'user_email' => $user['mail'],
            'display_name' => $user['givenName'] . ' ' . $user['sn'],
            'first_name' => $user['givenName'],
            'last_name' => $user['sn'],
            'role' => $this->crowd_login_options['crowd_account_type']
        ];

        $result = wp_insert_user($userData);

        return $result;
    }

    /**
     * Returns userdata from SOAP response as a user array.
     *
     * @param $principal_token
     * @since    1.0.0
     * @return array
     */
    protected function crowd_login_get_user_info($principal_token)
    {
        $user = [];

        $response = $this->crowd_client->findPrincipalByToken($principal_token);
        if ($response) {
            // Get userdata from response.
            for ($i = 0; $i < count($response->attributes->SOAPAttribute); $i++) {
                $user[$response->attributes->SOAPAttribute[$i]->name] = $response->attributes->SOAPAttribute[$i]->values->string;
            }
        }

        return $user;
    }

}
