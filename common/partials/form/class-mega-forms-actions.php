<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/actions
 */

/**
 * Load actions and handle registration
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/actions
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Actions
{

	private static $actions = array();
	/**
	 * Register a new action.
	 *
	 */
	public static function register($action)
	{

		self::$actions[$action->type] = $action;
	}
	/**
	 * Get a single form action.
	 *
	 * @param $args an `action` arguement is required for some
	 * methods in the requested object, failing to provide will
	 * trigger an error. Pay attention to the methods being called
	 * in the context of the returned single field object
	 */
	public static function get_single($type, $args = array())
	{

		if (isset(self::$actions[$type])) {

			if (empty($args)) {
				// If nothing should be assigned, return the original object
				$action_object = self::$actions[$type];
			} else {
				// Clone the object before assigning property values 
				$action_object = clone self::$actions[$type];

				// Assign properties if provided
				foreach ($args as $key => $val) {
					if ('action' == $key) {
						// Make sure the field is an array
						if (!is_array($val)) {
							$val = array();
						}
						// Make sure extract field id and form id and assign them to the object
						$action_object->form_id = absint(mfget('formId', $val, 0));
						$action_object->action_id = absint(mfget('id', $val, 0));
						if (isset($val['formId'])) {
							unset($val['formId']);
						}
						if (isset($val['id'])) {
							unset($val['id']);
						}
					}
					$action_object->$key = $val;
				}
			}

			return $action_object;
		}

		return false;
	}
	/**
	 * Get a single action object.
	 *
	 */
	public static function get($type, $action = array())
	{
		return self::get_single($type, $action);
	}

	/**
	 * Get all Megaforms actions.
	 *
	 */
	public static function get_actions()
	{
		return self::$actions;
	}
}

require_once(MEGAFORMS_COMMON_PATH . 'partials/form/actions/class-mega-forms-action.php');
foreach (glob(MEGAFORMS_COMMON_PATH . 'partials/form/actions/class-mega-forms-action-*.php') as $action_filename) {
	require_once($action_filename);
}
