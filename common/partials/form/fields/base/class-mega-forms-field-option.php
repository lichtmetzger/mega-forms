<?php

/**
 * @link       https://wpali.com
 * @since      1.0.6
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Main field wrapper
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields/base
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Field_Option
{

	/**
	 * Unique key to identify the option
	 *
	 * @var string
	 */
	public $type;
	/**
	 * Option display priority
	 *
	 * @var int
	 */
	public $priority;

	/**
	 * The tab key where this option will be displayed
	 * 
	 *	@var string
	 */
	public $tab = 'general';

	/**
	 * Constructor
	 * 
	 *	@var string
	 */
	function __construct()
	{
		if (!is_array($this->get_option_display_logic())) {
			add_filter('mf_add_' . $this->type . '_field_option', array($this, 'handle_display_logic'), 10, 2);
		}
	}
	/**
	 * Return the option markup.
	 *
	 * @param   object	$field	the associated field object.
	 * @return  string  the option markup.
	 */
	public function get_option_display($field)
	{
		return '';
	}
	/**
	 * Return the option markup.
	 *
	 * The returned array must include a list of supported fields, or/and excluded fields.
	 * If a string is returned, then it's assumed that the display logic will be implemented manually.
	 * If an array is returned, it must be in a specific format, it can hold only 1 of the main kays ('support', 'exclude').
	 * Both of the keys ('support', 'exclude') can hold an array of field types to support or exclude from support.
	 * When 'exclude' is set, all field types will be supported by default, except the ones provided in the 'exclude' list.
	 * 
	 * @return  array|string
	 */
	public function get_option_display_logic($field = array())
	{
		return '';
	}
	/**
	 * Decide whether a custom option should display in the passed field on not
	 * Note: this is only needed when 'get_option_display_logic' is returning a string
	 *
	 */
	public function handle_display_logic($display, $field)
	{
		return false;
	}
	/**
	 * If this option requires adding special attributes to the fields display
	 * you can supply them using this method in the form of an array|list of arrays
	 * containing mainly 2 properties `key` & `value`
	 *
	 */
	public function get_field_arguments($field)
	{
		return false;
	}
	/**
	 * Sanitize the option value before saving it to the database
	 *
	 */
	public function sanitize_option_value($field)
	{
		$value = $field->get_setting_value($this->type);
		if (is_array($value)) {
			return map_deep($value, 'sanitize_text_field');
		} else {
			return wp_strip_all_tags($value);
		}
	}
}
