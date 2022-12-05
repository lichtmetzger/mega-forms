<?php

/**
 * @link       https://wpali.com
 * @since      1.0.8
 *
 */

/**
 * The core pro plugin class.
 *
 * @since      1.0.8
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms_Pro
{
	protected $loader;
	protected $plugin_name;
	protected $version;
	/**
	 * Define the core functionality of the pro plugin.
	 *
	 * @since    1.0.6
	 */
	public function __construct($version)
	{

		$this->plugin_name = 'mega-forms-pro';
		$this->version = $version;

		$this->load_dependencies();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.6
	 */
	private function load_dependencies()
	{

		require_once MEGAFORMS_DIR_PATH . 'pro/common/class-mega-forms-common-pro.php';
		require_once MEGAFORMS_DIR_PATH . 'pro/admin/class-mega-forms-admin-pro.php';
		require_once MEGAFORMS_DIR_PATH . 'pro/public/class-mega-forms-public-pro.php';

		$this->loader = new Mega_Forms_Loader();
	}


	/**
	 * Register all of the hooks for admin and public related functionality
	 * of the pro plugin.
	 *
	 * @since    1.0.8
	 */
	private function define_common_hooks()
	{

		$pro_common = new Mega_Forms_Common_Pro($this->get_plugin_name(), $this->get_version());

		# Customize frontend form attributes as needed
		$this->loader->add_filter('mf_view_form_tag_attributes', $pro_common, 'form_tag_attributes', 10, 2);

		# Add save and continue button before the submit button if enabled
		$this->loader->add_action('mf_after_hidden_inputs', $pro_common, 'after_hidden_inputs');
		# Add save and continue button before the submit button if enabled
		$this->loader->add_action('mf_footer_submit_before', $pro_common, 'save_and_continue_button');

		# Validate page submissions
		$this->loader->add_action('mf_custom_submission_validation', $pro_common, 'validate_custom_submission');

		# Customize the success response when a paged form is submitted
		$this->loader->add_filter('mf_ajax_submit_success_response', $pro_common, 'mf_submit_success_response', 10);

		# Run weekly tasks
		$this->loader->add_action(MF_Crons::$cron_daily_hook, $pro_common, 'daily_cron_tasks');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the pro plugin.
	 *
	 * @since    1.0.6
	 */
	private function define_admin_hooks()
	{

		if (is_admin()) {

			$pro_admin = new Mega_Forms_Admin_Pro($this->get_plugin_name(), $this->get_version());

			# Admin scripts and style
			$this->loader->add_action('admin_enqueue_scripts', $pro_admin, 'enqueue_styles');
			$this->loader->add_action('admin_enqueue_scripts', $pro_admin, 'enqueue_scripts');

			# global forms settings
			$this->loader->add_filter('mf_settings_options', $pro_admin, 'forms_settings', 10, 1);
			# Single form settings
			$this->loader->add_filter('mf_form_settings_options', $pro_admin, 'form_settings', 10, 2);

			# Handle AJAX file upload
			$this->loader->add_action('wp_ajax_megaforms_file_handler', $pro_admin, 'ajax_file_handler', 10);
			$this->loader->add_action('wp_ajax_nopriv_megaforms_file_handler', $pro_admin, 'ajax_file_handler', 10);
		}
	}
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the pro plugin.
	 *
	 * @since    1.0.8
	 */
	private function define_public_hooks()
	{

		if (!is_admin()) {

			$pro_public = new Mega_Forms_Public_Pro($this->get_plugin_name(), $this->get_version());

			# Public scripts and styles
			$this->loader->add_action('wp_enqueue_scripts', $pro_public, 'enqueue_styles');
			$this->loader->add_action('wp_enqueue_scripts', $pro_public, 'enqueue_scripts');

			# Handle form page submission ( validate form page )
			$this->loader->add_action('template_redirect', $pro_public, 'listen');
			# Handle actions that should run before displaying the form
			$this->loader->add_action('mf_form_view_output_before', $pro_public, 'pre_form_display');
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
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 */
	public function get_version()
	{
		return $this->version;
	}
}
