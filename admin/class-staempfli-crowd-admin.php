<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.staempfli.com
 * @since      1.0.0
 *
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * Retrieve values with:
 * $crowd_login_options = get_option( 'staempfli_crowd_login_option_name' ); // Array of All Options
 * $crowd_url = $crowd_login_options['staempfli_crowd_url']; // Crowd URL
 * $crowd_application_name = $crowd_login_options['staempfli_crowd_application_name']; // Application Name
 * $crowd_application_password = $crowd_login_options['staempfli_crowd_application_password']; // Application Password
 * $crowd_login_mode = $crowd_login_options['staempfli_crowd_login_mode']; // Login Mode
 * $crowd_account_type = $crowd_login_options['staempfli_crowd_account_type']; // Account Type
 * ...
 *
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/admin
 * @author     Florian Auderset <florian.auderset@staempfli.com>
 */
class Staempfli_Crowd_Admin
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
     * @var      mixed $staempfli_crowd_login_options The current login options.
     */
    private $staempfli_crowd_login_options;

    /**
     * The crowd client instance
     *
     * @since    1.0.0
     * @access   private
     * @var      Staempfli_Crowd_Client $staempfli_crowd_client The crowd client instance.
     */
    private $staempfli_crowd_client = null;

    /**
     * The principal token recieved from the Atlassian Crowd server.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $staempfli_crowd_login_principal_token The current principal token..
     */
    private $staempfli_crowd_login_principal_token;

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
        $this->staempfli_crowd_login_options = get_option('staempfli_crowd_login_option_name');
    }

    /**
     * Callback to add options page
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_add_plugin_page()
    {
        add_options_page(
            'Crowd Login',
            'Crowd Login',
            'manage_options',
            'staempfli-crowd-login',
            [$this, 'staempfli_crowd_login_create_admin_page']
        );
    }

    /**
     * Callback to add content to the options page
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_create_admin_page()
    {
        ?>
        <div class="wrap">
            <h2>Crowd Login</h2>

            <form id="crowd-login-options" method="post" action="options.php">
                <?php
                settings_fields('staempfli_crowd_login_option_group');
                do_settings_sections('staempfli-crowd-login-admin');
                submit_button();
                ?>
            </form>

            <hr/>

            <form id="crowd-login-test-options" method="post" action="options.php">
                <?php
                settings_fields('staempfli_crowd_login_test_option_group');
                do_settings_sections('staempfli-crowd-login-admin-test');
                submit_button(__('Test settings', 'staempfli-crowd'));
                ?>
            </form>
        </div>
    <?php }

    /**
     * Callback to add option fields
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_page_init()
    {
        register_setting(
            'staempfli_crowd_login_option_group',
            'staempfli_crowd_login_option_name',
            [$this, 'staempfli_crowd_login_sanitize_options']
        );

        register_setting(
            'staempfli_crowd_login_test_option_group',
            'staempfli_crowd_login_test_option_name',
            [$this, 'staempfli_crowd_login_test_connection']
        );

        add_settings_section(
            'staempfli_crowd_login_setting_section',
            __('Settings', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_login_section_info'],
            'staempfli-crowd-login-admin'
        );

        add_settings_section(
            'staempfli_crowd_login_test_section',
            __('Test settings', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_login_test_section_info'],
            'staempfli-crowd-login-admin-test'
        );

        add_settings_field(
            'staempfli_crowd_url',
            __('Crowd URL', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_url_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_application_name',
            __('Application Name', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_application_name_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_application_password',
            __('Application Password', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_application_password_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_login_mode',
            __('Login Mode', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_login_mode_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_group',
            __('Group', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_group_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_login_securitymode',
            __('Security Mode', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_login_securitymode_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_account_type',
            __('Account Type', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_account_type_callback'],
            'staempfli-crowd-login-admin',
            'staempfli_crowd_login_setting_section'
        );

        add_settings_field(
            'staempfli_crowd_test_username',
            __('Username', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_test_username_callback'],
            'staempfli-crowd-login-admin-test',
            'staempfli_crowd_login_test_section'
        );

        add_settings_field(
            'staempfli_crowd_test_password',
            __('Password', 'staempfli-crowd'),
            [$this, 'staempfli_crowd_test_password_callback'],
            'staempfli-crowd-login-admin-test',
            'staempfli_crowd_login_test_section'
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
    public function staempfli_crowd_login_sanitize_options($input)
    {
        $sanitary_values = [];

        if (isset($input['staempfli_crowd_url'])) {
            $sanitary_values['staempfli_crowd_url'] = sanitize_text_field($input['staempfli_crowd_url']);
        }

        if (isset($input['staempfli_crowd_application_name'])) {
            $sanitary_values['staempfli_crowd_application_name'] = sanitize_text_field($input['staempfli_crowd_application_name']);
        }

        if (isset($input['staempfli_crowd_application_password'])) {
            $sanitary_values['staempfli_crowd_application_password'] = sanitize_text_field($input['staempfli_crowd_application_password']);
        }

        if (isset($input['staempfli_crowd_login_mode'])) {
            $sanitary_values['staempfli_crowd_login_mode'] = $input['staempfli_crowd_login_mode'];
        }

        if (isset($input['staempfli_crowd_group'])) {
            $sanitary_values['staempfli_crowd_group'] = $input['staempfli_crowd_group'];
        }

        if (isset($input['staempfli_crowd_login_securitymode'])) {
            $sanitary_values['staempfli_crowd_login_securitymode'] = $input['staempfli_crowd_login_securitymode'];
        }

        if (isset($input['staempfli_crowd_account_type'])) {
            $sanitary_values['staempfli_crowd_account_type'] = $input['staempfli_crowd_account_type'];
        }

        return $sanitary_values;
    }

    /**
     * Check if connection can be established using the current configuration.
     *
     * @param $input
     * @return array
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_test_connection($input)
    {
        if (isset($input['staempfli_crowd_test_username']) && isset($input['staempfli_crowd_test_password'])) {
            $crowd_login_app_token = $this->staempfli_crowd_login_initialize_client();
            $auth_result = $this->staempfli_crowd_login_can_authenticate($input['staempfli_crowd_test_username'], $input['staempfli_crowd_test_password']);

            if ($auth_result == true && !is_a($auth_result, 'WP_Error')) {
                add_settings_error('staempfli_crowd_login_connection_test', 'staempfli-crowd-login-connection-test-successful', __('User successfully authenticated with given configuration.', 'staempfli-crowd'), 'success');
            } else {
                add_settings_error('staempfli_crowd_login_connection_test', 'staempfli-crowd-login-connection-test-successful', __('User authenticated failed. Please check your configuration.', 'staempfli-crowd'));
            }
        }

        return [];
    }

    /**
     * Callback for options section information
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_section_info()
    {

    }

    /**
     * Callback for test section information
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_test_section_info()
    {

    }

    /**
     * Callback for input field crowd_url
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_url_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="staempfli_crowd_login_option_name[staempfli_crowd_url]" id="staempfli_crowd_url" value="%s">',
            isset($this->staempfli_crowd_login_options['staempfli_crowd_url']) ? esc_attr($this->staempfli_crowd_login_options['staempfli_crowd_url']) : ''
        );
        echo "<p class='description'>" . __('Example: https://crowd.server.com:8080/crowd', 'staempfli-crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_application_name
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_application_name_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="staempfli_crowd_login_option_name[staempfli_crowd_application_name]" id="staempfli_crowd_application_name" value="%s">',
            isset($this->staempfli_crowd_login_options['staempfli_crowd_application_name']) ? esc_attr($this->staempfli_crowd_login_options['staempfli_crowd_application_name']) : ''
        );
        echo "<p class='description'>" . __('The application name specified in the Atlassian Crowd backend.', 'staempfli-crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_application_password
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_application_password_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="staempfli_crowd_login_option_name[staempfli_crowd_application_password]" id="staempfli_crowd_application_password" value="%s">',
            isset($this->staempfli_crowd_login_options['staempfli_crowd_application_password']) ? esc_attr($this->staempfli_crowd_login_options['staempfli_crowd_application_password']) : ''
        );
        echo "<p class='description'>" . __('The application password specified in the Atlassian Crowd backend.', 'staempfli-crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_login_mode
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_mode_callback()
    {
        ?>
        <fieldset>
            <?php $checked = (isset($this->staempfli_crowd_login_options['staempfli_crowd_login_mode']) && $this->staempfli_crowd_login_options['staempfli_crowd_login_mode'] === 'mode_auth') ? 'checked' : ''; ?>
            <label for="staempfli_crowd_login_mode_auth"><input type="radio" name="staempfli_crowd_login_option_name[staempfli_crowd_login_mode]"
                                                      id="staempfli_crowd_login_mode_auth"
                                                      value="mode_auth" <?php echo $checked; ?>>
                <?php echo __('Authenticate only', 'staempfli-crowd'); ?>
            </label>
            <p class="description"><?php echo __('Authenticate user against Atlassian Crowd. No user is created.', 'staempfli-crowd'); ?></p><br>

            <?php $checked = (isset($this->staempfli_crowd_login_options['staempfli_crowd_login_mode']) && $this->staempfli_crowd_login_options['staempfli_crowd_login_mode'] === 'mode_create') ? 'checked' : ''; ?>
            <label for="staempfli_crowd_login_mode_create"><input type="radio" name="staempfli_crowd_login_option_name[staempfli_crowd_login_mode]"
                                                        id="staempfli_crowd_login_mode_create"
                                                        value="mode_create" <?php echo $checked; ?>>
                <?php echo __('Create user', 'staempfli-crowd'); ?>
            </label>
            <p class="description"><?php echo __('Create WordPress user when successfully authenticated against Atlassian Crowd.', 'staempfli-crowd'); ?></p><br>

            <?php $checked = (isset($this->staempfli_crowd_login_options['staempfli_crowd_login_mode']) && $this->staempfli_crowd_login_options['staempfli_crowd_login_mode'] === 'mode_create_group') ? 'checked' : ''; ?>
            <label for="staempfli_crowd_login_mode_create_group"><input type="radio"
                                                              name="staempfli_crowd_login_option_name[staempfli_crowd_login_mode]"
                                                              id="staempfli_crowd_login_mode_create_group"
                                                              value="mode_create_group" <?php echo $checked; ?>>
                <?php echo __('Create user when groupmember', 'staempfli-crowd'); ?>
            </label>
            <p class="description"><?php echo __('Create WordPress user only when successfully authenticated against Atlassian Crowd and user is a member of a specified group.', 'staempfli-crowd'); ?></p>
        </fieldset>
        <?php
    }

    /**
     * Callback for input field crowd_group
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_group_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="staempfli_crowd_login_option_name[staempfli_crowd_group]" id="staempfli_crowd_group" value="%s">',
            isset($this->staempfli_crowd_login_options['staempfli_crowd_group']) ? esc_attr($this->staempfli_crowd_login_options['staempfli_crowd_group']) : ''
        );
        echo "<p class='description'>" . __('A WordPress user is only created if the user is a member of this group.', 'staempfli-crowd') . "</p>";
    }

    /**
     * Callback for input field crowd_login_securitymode
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_login_securitymode_callback()
    {
        ?>
        <fieldset>
            <?php $checked = (isset($this->staempfli_crowd_login_options['staempfli_crowd_login_securitymode']) && $this->staempfli_crowd_login_options['staempfli_crowd_login_securitymode'] === 'security_normal') ? 'checked' : ''; ?>
            <label for="staempfli_crowd_login_securitymode-normal">
                <input type="radio" name="staempfli_crowd_login_option_name[staempfli_crowd_login_securitymode]"
                       id="staempfli_crowd_login_securitymode-normal" value="security_normal" <?php echo $checked; ?>>
                <?php echo __('Normal', 'staempfli-crowd'); ?>
            </label>
            <p class="description"><?php echo __('First attempt to login with Atlassian Crowd user, when failing attempt login using the local WordPress users. This can be used to provide a mixed login mode with Atlassian Crowd and WordPress default login.', 'staempfli-crowd'); ?></p><br>

            <?php $checked = (isset($this->staempfli_crowd_login_options['staempfli_crowd_login_securitymode']) && $this->staempfli_crowd_login_options['staempfli_crowd_login_securitymode'] === 'security_strict') ? 'checked' : ''; ?>
            <label for="staempfli_crowd_login_securitymode-strict">
                <input type="radio" name="staempfli_crowd_login_option_name[staempfli_crowd_login_securitymode]"
                       id="staempfli_crowd_login_securitymode-strict" value="security_strict" <?php echo $checked; ?>>
                <?php echo __('Strict', 'staempfli-crowd'); ?>
            </label>
            <p class="description"><?php echo __('Restrict logins to Atlassian Crowd only. The default WordPress authentication is deactivated.', 'staempfli-crowd'); ?></p><br>
        </fieldset>
        <?php
    }

    /**
     * Callback for input field crowd_account_type
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_account_type_callback()
    {
        $user_roles = get_editable_roles();
        ?>

        <select name="staempfli_crowd_login_option_name[staempfli_crowd_account_type]" id="staempfli_crowd_account_type">
            <?php
            foreach ($user_roles as $role => $details) {
                $role = esc_attr($role);
                $role_name = translate_user_role($details['name']);

                $selected = (isset($this->staempfli_crowd_login_options['staempfli_crowd_account_type']) && $this->staempfli_crowd_login_options['staempfli_crowd_account_type'] === $role) ? 'selected' : '';
                echo "<option value='$role' $selected>$role_name</option>";
            }
            ?>
        </select>
        <p class="description"><?php echo __('Create WordPress user with this role when login mode "create" is selected.', 'staempfli-crowd'); ?></p><br>

        <?php
    }

    /**
     * Callback for input field crowd_test_username
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_test_username_callback()
    {
        echo '<input class="regular-text" type="text" name="staempfli_crowd_login_test_option_name[staempfli_crowd_test_username]" id="staempfli_crowd_test_username">';
    }

    /**
     * Callback for input field crowd_test_password
     *
     * @since    1.0.0
     */
    public function staempfli_crowd_test_password_callback()
    {
        echo '<input class="regular-text" type="password" name="staempfli_crowd_login_test_option_name[staempfli_crowd_test_password]" id="staempfli_crowd_test_password">';
    }

    /**
     * Create an instance of the SOAP Client
     *
     * @since    1.0.0
     * @return string|WP_Error
     */
    public function staempfli_crowd_login_initialize_client()
    {
        try {
            $this->staempfli_crowd_client = new Staempfli_Crowd_Client();
        } catch (Staempfli_Crowd_Connection_Exception $e) {
            $error = new WP_Error();
            $error->add('staempfli_crowd_login_connection_error', $e->getMessage());
            return $error;
        }

        try {
            $crowd_login_app_token = $this->staempfli_crowd_client->authenticateApplication();
        } catch (Staempfli_Crowd_Login_Exception $e) {
            $this->staempfli_crowd_client = null;
            $error = new WP_Error();
            $error->add('staempfli_crowd_login_error', $e->getMessage());
            return $error;
        }

        return $crowd_login_app_token;
    }

    /**
     * Custom user authentication using Atlassian Crowd server
     *
     * Authenticate against Atlassian Crowd server. Create user or log in according plugin configuration.
     * Return WordPress user if successful, return a WordPress error if not.
     *
     * @since    1.0.0
     * @return WP_Error|WP_User
     */
    public function staempfli_crowd_login_authenticate($user, $username, $password)
    {
        $crowd_login_app_token = $this->staempfli_crowd_login_initialize_client();

        if (is_a($user, 'WP_User')) {
            return $user;
        }

        // Remove default authentication if security mode is set to strict.
        if ($this->staempfli_crowd_login_options['staempfli_crowd_login_securitymode'] == 'security_strict') {
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
        $auth_result = $this->staempfli_crowd_login_can_authenticate($username, $password);
        if ($auth_result == true && !is_a($auth_result, 'WP_Error')) {
            $user = get_user_by('login', $username);

            if (!$user || (strtolower($user->user_login) != strtolower($username))) {
                switch ($this->staempfli_crowd_login_options['staempfli_crowd_login_mode']) {
                    case 'mode_create':
                        $new_user_id = $this->staempfli_crowd_login_create_user($username);
                        if (!is_a($new_user_id, 'WP_Error')) {
                            return new WP_User($new_user_id);
                        } else {
                            do_action('wp_login_failed', $username);
                            return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: An error occurred creating the user in WordPress. <br><br>' . $new_user_id->get_error_message()));
                        }
                        break;

                    case 'mode_create_group':
                        if ($this->staempfli_crowd_login_is_in_group($username)) {
                            $new_user_id = $this->staempfli_crowd_login_create_user($username);
                            if (!is_a($new_user_id, 'WP_Error')) {
                                return new WP_User($new_user_id);
                            } else {
                                do_action('wp_login_failed', $username);
                                return new WP_Error('invalid_username', __('<strong>Crowd Login Error</strong>: An error occurred creating the user in WordPress. <br><br>' . $new_user_id->get_error_message()));
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
                if ($this->staempfli_crowd_login_options['staempfli_crowd_login_mode'] == 'mode_create_group') {
                    if ($this->staempfli_crowd_login_is_in_group($username)) {
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
    public function staempfli_crowd_login_can_authenticate($username, $password)
    {
        if ($this->staempfli_crowd_client == null) {
            return new WP_Error('staempfli_crowd_client_error', __('<strong>Crowd Login Error</strong>: No Crowd_Client instance available.'));
        }

        $this->staempfli_crowd_login_principal_token = $this->staempfli_crowd_client->authenticatePrincipal($username, $password, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);

        if ($this->staempfli_crowd_login_principal_token == null) {
            return new WP_Error('staempfli_crowd_principal_error', __('<strong>Crowd Login Error</strong>: Could not retrieve principal from Atlassian Crowd server.'));
        }

        return $this->staempfli_crowd_login_principal_token;
    }

    /**
     * Check if user is allowed to login according his assigned groups.
     *
     * @param $username
     * @since    1.0.0
     * @return bool
     */
    public function staempfli_crowd_login_is_in_group($username)
    {
        if ($this->staempfli_crowd_client == null) {
            return false;
        }

        $groups = $this->staempfli_crowd_client->findGroupMemberships($username);
        $crowd_group = $this->staempfli_crowd_login_options['staempfli_crowd_group'];

        return in_array($crowd_group, $groups);
    }

    /**
     * Create WordPress user from userdata recieved from Atlassian Crowd authentication.
     *
     * @param $username
     * @since    1.0.0
     * @return int|WP_Error
     */
    public function staempfli_crowd_login_create_user($username)
    {
        $result = 0;

        if ($this->staempfli_crowd_client == null || $this->staempfli_crowd_login_principal_token == null) {
            return $result;
        }

        $user = $this->staempfli_crowd_login_get_user_info($this->staempfli_crowd_login_principal_token);

        $userData = [
            'user_pass' => microtime(),
            'user_login' => $username,
            'user_nicename' => sanitize_title($user['givenName'] . ' ' . $user['sn']),
            'user_email' => $user['mail'],
            'display_name' => $user['givenName'] . ' ' . $user['sn'],
            'first_name' => $user['givenName'],
            'last_name' => $user['sn'],
            'role' => $this->staempfli_crowd_login_options['staempfli_crowd_account_type']
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
    protected function staempfli_crowd_login_get_user_info($principal_token)
    {
        $user = [];

        $response = $this->staempfli_crowd_client->findPrincipalByToken($principal_token);
        if ($response) {
            // Get userdata from response.
            for ($i = 0; $i < count($response->attributes->SOAPAttribute); $i++) {
                $user[$response->attributes->SOAPAttribute[$i]->name] = $response->attributes->SOAPAttribute[$i]->values->string;
            }
        }

        return $user;
    }

}
