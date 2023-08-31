<?php

/**
 * @link       https://wpali.com
 * @since      1.0.7
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 */

/**
 * Fired on plugin update.
 *
 * @since      1.0.7
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms_Updater
{
	/**
	 * DB updates and callbacks that need to be run per version.
	 * 
	 * Note: the version number must be the previous version.
	 * Example: if database column is to be added in version 1.0.1,
	 * 			the version number should state 1.0.0.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.0.6' => array(
			'update_106_containers_structure',
		),
		'1.1.2' => array(
			'update_112_entries_referrer_type',
		),
		'1.2.6' => array(
			'update_126_entries_spam_column',
		)
	);
	/**
	 * Check if an update has been performed.
	 *
	 * @since    1.0.7
	 */
	public function __construct()
	{
		if (is_multisite()) {
			$current_vers = get_site_option('megaforms_db_version');
		} else {
			$current_vers = get_option('megaforms_db_version');
		}

		if (version_compare(MEGAFORMS, $current_vers, '>')) {
			self::update_db($current_vers);
		}
	}
	/**
	 * Run the necessary database updates.
	 *
	 * @since    1.0.8
	 */
	public static function update_db($current_db_version = '')
	{
		$network = is_multisite() ? true : false;

		if (empty($current_db_version)) {
			if ($network) {
				$current_db_version = get_site_option('megaforms_db_version');
			} else {
				$current_db_version = get_option('megaforms_db_version');
			}
		}

		/**
		 * Run database updates
		 *
		 */

		# Make sure the main database table exists before running any updates
		global $wpdb;
		$mf_forms = $wpdb->prefix . "mf_forms";
		if ($wpdb->get_var("SHOW TABLES LIKE '$mf_forms'") != $mf_forms) {
			return false;
		}

		# Run the updates
		$loop = 0;
		foreach (self::$db_updates as $version => $update_callbacks) {

			if (version_compare($current_db_version, $version, '<=') && version_compare(MEGAFORMS, $version, '>')) {
				foreach ($update_callbacks as $update_callback) {
					if (method_exists(__CLASS__, $update_callback)) {
						self::$update_callback();
						$loop++;
					}
				}
			}
		}

		/**
		 * Store latest database version
		 *
		 */
		if ($network) {
			update_site_option('megaforms_db_version', MEGAFORMS);
		} else {
			update_option('megaforms_db_version', MEGAFORMS);
		}

		return $loop;
	}

	/**********************************************************************
	 ********************** Database Update Callbacks *********************
	 **********************************************************************/

	/**
	 * The containers meta data structure was updated in version 1.0.7
	 * We need to update any existing records and make sure they use the new structure
	 *
	 */
	public static function update_106_containers_structure()
	{
		$forms = mf_api()->get_forms();
		if ($forms) {
			foreach ($forms as $form) {
				$containers = mf_api()->get_form_meta($form->id, 'containers');
				if (!isset($containers['data'])) {
					mf_api()->update_form_meta($form->id, 'containers', array(
						'settings' => false,
						'data' => is_array($containers) && count($containers) > 0 ? $containers : array()
					));
				}
			}
		}
	}
	/**
	 * A bug with the `referrer` column in the entries database table was found on 1.1.1
	 * We need to change the `referrer` type to accept longer values.
	 *
	 */
	public static function update_112_entries_referrer_type()
	{
		global $wpdb;
		if ($wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mf_entries` LIKE 'referrer';")) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}mf_entries CHANGE `referrer` `referrer` MEDIUMTEXT NOT NULL;");
		}
	}
	/**
	 * A new spam features were introduced in version 1.2.7 which requires a new column
	 * in the entries database table.
	 *
	 */
	public static function update_126_entries_spam_column()
	{
		global $wpdb;
		if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mf_entries` LIKE 'is_spam';")) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}mf_entries ADD `is_spam` TINYINT NOT NULL;");
		}
	}
}
