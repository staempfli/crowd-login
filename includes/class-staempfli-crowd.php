<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.staempfli.com
 * @since      1.0.0
 *
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Staempfli_Crowd_Login
 * @subpackage Staempfli_Crowd_Login/includes
 * @author     Florian Auderset <florian.auderset@staempfli.com>
 */
class Staempfli_Crowd {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Staempfli_Crowd_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'STAEMPFLI_CROWD_VERSION' ) ) {
			$this->version = STAEMPFLI_CROWD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'staempfli-crowd-login';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Crowd_Loader. Orchestrates the hooks of the plugin.
	 * - Crowd_i18n. Defines internationalization functionality.
	 * - Crowd_Admin. Defines all hooks for the admin area.
	 * - Crowd_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-staempfli-crowd-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-staempfli-crowd-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-staempfli-crowd-admin.php';

        /**
         * The class providing the soap client to authenticate against Atlassian Crowd.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-staempfli-crowd-client.php';

        /**
         * Exceptions
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/exceptions/class-connection-exception.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/exceptions/class-login-exception.php';

		$this->loader = new Staempfli_Crowd_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Crowd_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Staempfli_Crowd_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Staempfli_Crowd_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'staempfli_crowd_login_add_plugin_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'staempfli_crowd_login_page_init' );

		$this->loader->add_filter('authenticate', $plugin_admin, 'staempfli_crowd_login_authenticate', 1, 3);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Staempfli_Crowd_Loader    Orchestrates the hooks of the plugin.
	 *@since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
