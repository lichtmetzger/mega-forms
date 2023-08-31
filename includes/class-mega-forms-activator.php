<?php

/**
 * @link       https://wpali.com
 * @since      1.0.7
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 */

/**
 * Fired during plugin activation.
 *
 * @since      1.0.7
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms_Activator
{

	/**
	 * Create the associated database tables and directories.
	 * Run any updates or actions related to the activation of this plugin
	 *
	 * @since    1.0.7
	 */
	public static function activate($network_wide = false)
	{

		// Check if we are on multisite and network activating.
		if (is_multisite() && $network_wide) {

			// If this is a multisite, loop through each subsite and run the necessary processes.
			$sites = get_sites(
				[
					'fields' => 'ids',
					'number' => 0,
				]
			);

			foreach ($sites as $blog_id) {
				switch_to_blog($blog_id);
				self::create_tables();
				restore_current_blog();
			}
		} else {
			// Normal single site.
			self::create_tables();
		}


		/**
		 * Setup cron jobs
		 * 
		 */

		MF_Crons::setup_daily_cron();

		/**
		 * Create the necessary directories
		 *
		 */
		mf_files()->create_upload_dir();
	}
	/**
	 * Create the associated database tables and directories.
	 * Run any updates or actions related to the activation of this plugin
	 *
	 * @since    1.2.4
	 */
	public static function create_tables()
	{

		/**
		 * Create database tables
		 *
		 */

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;


		$charset_collate = $wpdb->get_charset_collate();
		$mf_forms = $wpdb->prefix . "mf_forms";
		$mf_formsmeta = $wpdb->prefix . "mf_formsmeta";
		$mf_entries = $wpdb->prefix . "mf_entries";
		$mf_entriesmeta = $wpdb->prefix . "mf_entriesmeta";
		$mf_sessions = $wpdb->prefix . "mf_sessions";

		if ($wpdb->get_var("SHOW TABLES LIKE '$mf_forms'") != $mf_forms) {
			$sql1 = "CREATE TABLE $mf_forms (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				type varchar(255) NOT NULL,
				title varchar(255) NOT NULL,
				form_created varchar(96) NOT NULL,
				form_modified varchar(96) NOT NULL,
				view_count int NOT NULL,
				lead_count int NOT NULL,
        		is_active int NOT NULL,
				is_trash int NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta($sql1);

			// If the forms table is created for the first time, store database version
			if (is_multisite()) {
				update_site_option('megaforms_db_version', MEGAFORMS);
			} else {
				update_option('megaforms_db_version', MEGAFORMS);
			}
		}

		if ($wpdb->get_var("SHOW TABLES LIKE '$mf_formsmeta'") != $mf_formsmeta) {
			$sql2 = "CREATE TABLE $mf_formsmeta (
				meta_id mediumint(9) NOT NULL AUTO_INCREMENT,
				form_id mediumint(9) NOT NULL,
				meta_key MEDIUMTEXT NOT NULL,
				meta_value MEDIUMTEXT NOT NULL,
				UNIQUE KEY id (meta_id)
			) $charset_collate;";
			dbDelta($sql2);
		}

		if ($wpdb->get_var("SHOW TABLES LIKE '$mf_entries'") != $mf_entries) {
			$sql3 = "CREATE TABLE $mf_entries (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				form_id mediumint(9) NOT NULL,
				user_id varchar(96) NOT NULL,
				date_created varchar(96) NOT NULL,
				referrer MEDIUMTEXT NOT NULL,
				user_ip varchar(100) NOT NULL,
				user_agent varchar(100) NOT NULL,
				is_starred TINYINT NOT NULL,
				is_read TINYINT NOT NULL,
				is_trash TINYINT NOT NULL,
				is_spam TINYINT NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta($sql3);
		}

		if ($wpdb->get_var("SHOW TABLES LIKE '$mf_entriesmeta'") != $mf_entriesmeta) {
			$sql4 = "CREATE TABLE $mf_entriesmeta (
				meta_id mediumint(9) NOT NULL AUTO_INCREMENT,
        		entry_id mediumint(9) NOT NULL,
				form_id mediumint(9) NOT NULL,
				meta_key varchar(255) NOT NULL,
				meta_value MEDIUMTEXT NOT NULL,
				UNIQUE KEY id (meta_id)
			) $charset_collate;";
			dbDelta($sql4);
		}

		if ($wpdb->get_var("SHOW TABLES LIKE '$mf_sessions'") != $mf_sessions) {
			$sql5 = "CREATE TABLE $mf_sessions (
				session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				session_key char(32) NOT NULL,
				session_value MEDIUMTEXT NOT NULL,
				session_expiry INT NOT NULL,
				PRIMARY KEY  (session_id),
				UNIQUE KEY session_key (session_key)
			) $charset_collate;";
			dbDelta($sql5);
		}
	}
}
