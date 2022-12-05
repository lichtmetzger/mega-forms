<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.3
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms
{


	protected $loader;
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($version)
	{
		$this->version = $version;
		$this->plugin_name = 'mega-forms';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies()
	{

		require_once MEGAFORMS_INC_PATH . 'class-mega-forms-loader.php';

		require_once MEGAFORMS_INC_PATH . 'class-mega-forms-i18n.php';

		require_once MEGAFORMS_ADMIN_PATH . 'class-mega-forms-admin.php';

		require_once MEGAFORMS_PUBLIC_PATH . 'class-mega-forms-public.php';

		require_once MEGAFORMS_COMMON_PATH . 'class-mega-forms-common.php';

		$this->loader = new Mega_Forms_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 */
	private function set_locale()
	{

		$plugin_i18n = new Mega_Forms_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks for admin and public related functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_common_hooks()
	{

		$plugin_common = new Mega_Forms_Common($this->get_plugin_name(), $this->get_version());
		$plugin_shortcodes  = new MF_Shortcodes();

		# Handle db tables creation on multisite for new subsites
		if (is_multisite()) {
			$this->loader->add_action('wp_initialize_site', $plugin_common, 'new_multisite_blog', 10, 2);
		}

		# Admin and common scripts and style
		$this->loader->add_action('admin_enqueue_scripts', $plugin_common, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_common, 'enqueue_styles');

		# Send any emails in the queue
		$this->loader->add_action('shutdown', $plugin_common, 'send_emails_in_queue');

		# Hook the class responsible for background processes to `plugins_loaded`
		$this->loader->add_action('plugins_loaded', false, 'mf_tasks');

		# Run daily tasks
		$this->loader->add_action(MF_Crons::$cron_daily_hook, $plugin_common, 'daily_cron_tasks');

		# Define Shortcodes
		$this->loader->add_action('init', $plugin_shortcodes, 'init');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.3
	 */
	private function define_admin_hooks()
	{

		if (is_admin()) {

			$plugin_admin = new Mega_Forms_Admin($this->get_plugin_name(), $this->get_version());

			# Admin scripts and style
			$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
			$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

			# MegaFroms Admin settings page
			$this->loader->add_action('admin_menu', $plugin_admin, 'settings_pages');

			# MegaFroms body css classes
			$this->loader->add_action('admin_body_class', $plugin_admin, 'body_classes');

			# Handle Mega Forms admin header actions ( downloads ..etc )
			$this->loader->add_action('admin_init', $plugin_admin, 'header_actions');

			/*
			* Since all ajax requests are sent to the admin_url(), they should be hooked here
			*/

			$plugin_admin_ajax = new MF_Admin_Ajax();
			# Define Mega Forms AJAX handler on admin side
			$this->loader->add_action('wp_ajax_megaforms_admin_request', $plugin_admin_ajax, 'handler');

			$plugin_public_ajax = new MF_Public_Ajax();
			# Define Mega Forms AJAX handler on public side
			$this->loader->add_action('wp_ajax_megaforms_public_request', $plugin_public_ajax, 'handler');
			$this->loader->add_action('wp_ajax_nopriv_megaforms_public_request', $plugin_public_ajax, 'handler');
		}
	}
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.3
	 */
	private function define_public_hooks()
	{

		if (!is_admin()) {
			$plugin_public      = new Mega_Forms_Public($this->get_plugin_name(), $this->get_version());

			# Public scripts and styles
			$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
			$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
			$this->loader->add_action('template_redirect', $plugin_public, 'listen');
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 */
	public function get_version()
	{
		return $this->version;
	}
}
