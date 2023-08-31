<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Mega_Forms_Deactivator
{

	/**
	 * Handle all actions that must happen after plugin deactivation.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{

		# Clear Mega Forms Cron Jobs
		MF_Crons::clear_all();
	}
}
