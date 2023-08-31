<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin
 */

/**
 * Common functionality of the plugin.
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms_Common
{

	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();
	}

	/**
	 * Load the required dependencies for this class.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies()
	{
		// Load Main Classes
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-api.php'; # Database requests and helpers
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-files-api.php'; # Files API
		require_once MEGAFORMS_COMMON_PATH . 'partials/libraries/vendor/autoload.php'; # Autoload libraries ( Emogrifier library for e-mails CSS ..etc )
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-mailer.php'; # Megaforms mailer
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-tags.php'; # Handling merge tags
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-tasks.php'; # Background processing
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-crons.php'; # Cron jobs managment
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-session.php'; # User sessions using database and cookies
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-shortcodes.php'; # Load Shorcode class ( Some shortcodes will load their own dependencies )
		require_once MEGAFORMS_COMMON_PATH . 'partials/class-mega-forms-submit.php'; # Processing form submissions

		// Helper functions
		require_once MEGAFORMS_COMMON_PATH . 'partials/mega-forms-helpers.php';
		require_once MEGAFORMS_COMMON_PATH . 'partials/mega-forms-extender.php';

		// Form Related Classes ( Load all conainers, fields and field actions )
		require_once MEGAFORMS_COMMON_PATH . 'partials/form/class-mega-forms-containers.php';
		require_once MEGAFORMS_COMMON_PATH . 'partials/form/class-mega-forms-fields.php';
		require_once MEGAFORMS_COMMON_PATH . 'partials/form/class-mega-forms-actions.php';
	}

	/**
	 * Register the stylesheets shared between public-facing and admin-faceing side of the site.
	 *
	 * @since    1.0.7
	 */
	public function enqueue_styles()
	{
		# Load only in the plugin specific pages on the backend and everywhere on the front end.
		$toLoad = (is_admin() && mf_api()->get_page() !== false) || !is_admin() ? true : false;
		if ($toLoad) {
			# Enqueue the css for any available fields that has CSS dependencies.
			$field_dependencies = MF_Fields::get_field_dependencies('css');
			$enqueuedDeps = array();
			foreach ($field_dependencies as $fieldDeps) {
				foreach ($fieldDeps as $cssDependencyKey => $cssDependencyVal) {
					# If the css file is to be loaded in the footer, skip it. 
					# @see MF_Shortcodes::the_form()
					if (isset($cssDependencyVal['in_footer'])) {
						continue;
					}
					if (!in_array($cssDependencyKey, $enqueuedDeps)) {
						wp_enqueue_style($cssDependencyKey, $cssDependencyVal['src'], $cssDependencyVal['deps'], $cssDependencyVal['ver'], 'all');
						$enqueuedDeps[] = $cssDependencyKey;
					}
				}
			}
		}
	}

	/**
	 * Register the tasks that will have to run on daily basis using wp cron.
	 *
	 * @see     MF_Crons
	 * @since   1.0.0
	 */
	public function daily_cron_tasks()
	{

		global $wpdb;

		# Delete expired sessions from database
		$sessions_table = $wpdb->prefix . 'mf_sessions';
		$wpdb->query("DELETE FROM `$sessions_table` WHERE session_expiry < UNIX_TIMESTAMP()");
	}
	/**
	 * Send emails in queue at the end of the script's execution.
	 *
	 * @see     MF_Mailer
	 * @since   1.0.0
	 */
	public function send_emails_in_queue()
	{
		mf_mail()->send_deferred_notifications();
	}
	/**
	 * When a new site is created in multisite, see if we are network activated,
	 * and if so we create our plugin database tables.
	 *
	 * @since    1.2.4
	 */
	public function new_multisite_blog($new_site, $args)
	{
		if (is_plugin_active_for_network(plugin_basename(MEGAFORMS_DIR_PATH . 'mega-forms.php'))) {

			require_once MEGAFORMS_INC_PATH . 'class-mega-forms-activator.php';

			switch_to_blog($new_site->blog_id);
			Mega_Forms_Activator::create_tables();
			restore_current_blog();
		}
	}
}
