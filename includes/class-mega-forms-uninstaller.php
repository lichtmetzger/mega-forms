<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 */

/**
 * Fired during plugin uninstallation.
 *
 * @since      1.0.0
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms_Uninstaller
{

	/**
	 * Handle all actions that must happen after plugin uninstallation.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall()
	{

		global $wpdb;

		// clear meagforms cron jobs.
		require_once plugin_dir_path(__DIR__) . 'common/partials/class-mega-forms-crons.php';
		MF_Crons::clear_all();


		// Delete megaforms background tasks
		$megaforms_tasks = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wp_megaforms_tasks_%'");
		foreach ($megaforms_tasks as $option) {
			delete_option($option->option_name);
		}

		// Delete any transients related to megaforms
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_wp_megaforms\_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_wp_megaforms\_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_wp_megaforms\_%'");
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_wp_megaforms\_%'");

		$megaforms_uninstall = get_option('megaforms_uninstall', false);
		if ($megaforms_uninstall === true || $megaforms_uninstall === "true") {

			// Delete forms table.
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mf_forms');
			// Delete forms meta table.
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mf_formsmeta');
			// Delete entries table.
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mf_entries');
			// Delete entries meta table.
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mf_entriesmeta');
			// Delete sessions table.
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mf_sessions');

			// Delete megaforms save_settings
			$megaforms_options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'megaforms_%'");
			foreach ($megaforms_options as $option) {
				delete_option($option->option_name);
			}

			// Delete megaforms upload folder and any uploaded files
			$uploads_directory = wp_upload_dir();
			if ( empty( $uploads_directory['error'] ) ) {
				global $wp_filesystem;
				$wp_filesystem->rmdir( $uploads_directory['basedir'] . '/mega-forms/', true );
			}

			// Removes all cache items.
			wp_cache_flush();
		}
	}
}
