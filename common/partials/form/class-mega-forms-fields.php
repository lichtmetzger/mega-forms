<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/fields
 */

/**
 * Load all fields and handle their registration
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Fields
{

	private static $fields = array();
	private static $jsDependencies = array();
	private static $cssDependencies = array();

	/**
	 * Register a new field.
	 *
	 */
	public static function register($field)
	{

		self::$fields[$field->type] = $field;

		if ($field->hasJSDependency) {
			self::$jsDependencies[$field->type] = $field->get_field_js_dependencies();
		}

		if ($field->hasCSSDependency) {
			self::$cssDependencies[$field->type] = $field->get_field_css_dependencies();
		}
	}
	/**
	 * Get a single form field.
	 *
	 * @param $args a `field` arguement is required for some
	 * methods in the requested object, failing to provide will
	 * trigger an error. Pay attention to the methods being called
	 * in the context of the returned single field object
	 */
	public static function get_single($type, $args = array())
	{
		if (isset(self::$fields[$type])) {

			if (empty($args)) {
				// If nothing should be assigned, return the original object
				$field_object = self::$fields[$type];
			} else {
				// Clone the object before assigning property values 
				$field_object = clone self::$fields[$type];

				// Assign properties if provided
				foreach ($args as $key => $val) {
					if ('field' == $key) {
						// Make sure the field is an array
						if (!is_array($val)) {
							$val = array();
						}
						// Make sure extract field id and form id and assign them to the object
						$field_object->field_id = absint(mfget('id', $val, 0));
						$field_object->form_id = absint(mfget('formId', $val, 0));
						if(isset($val['formId'])) unset($val['formId']);
						if(isset($val['id']))unset($val['id']);
					}
					$field_object->$key = $val;
				}
			}

			return $field_object;
		}

		return false;
	}
	/**
	 * Get a single field object.
	 *
	 */
	public static function get($type, $field = array())
	{

		return self::get_single($type, $field);
	}

	/**
	 * Get all Mega Forms fields.
	 *
	 */
	public static function get_fields()
	{

		return self::$fields;
	}

	/**
	 * Get field dependencies ( JS | CSS ) .
	 *
	 */
	public static function get_field_dependencies($depType, $fieldType = '')
	{

		$Deps = array();

		if ($depType == 'js') {
			$availableDependecies = self::$jsDependencies;
		} elseif ($depType == 'css') {
			$availableDependecies = self::$cssDependencies;
		}else{
			return false;
		}

		# If field type is defined, return the dependencies only for that field. Otherwise, return all dependencies.
		if ($fieldType !== '') {

			if (isset($availableDependecies[$fieldType])) {
				$Deps = $availableDependecies[$fieldType];
			}
		} else {

			foreach ($availableDependecies as $field) {
				$Deps[] = $field;
			}
		}

		return $Deps;
	}
}

/**
 * Require the base classes
 *
 * @since    1.0.0
 */
require_once(MEGAFORMS_COMMON_PATH . 'partials/form/fields/base/class-mega-forms-field.php');
foreach (glob(MEGAFORMS_COMMON_PATH . 'partials/form/fields/base/class-mega-forms-field-*.php') as $field_filename) {
	require_once($field_filename);
}
/**
 * Require available fields
 *
 * @since    1.0.0
 */
foreach (glob(MEGAFORMS_COMMON_PATH . 'partials/form/fields/class-mega-forms-field-*.php') as $field_filename) {
	require_once($field_filename);
}
