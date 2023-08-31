<?php

/**
 * @link       https://wpali.com
 * @since      1.3.1
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/actions
 */

/**
 * Main action option wrapper
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/action/base
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Action_Option
{

	/**
	 * Unique key to identify the option
	 *
	 * @var string
	 */
	public $type;


	/**
	 * Constructor
	 * 
	 *	@var string
	 */
	function __construct()
	{
		if (!is_array($this->get_option_display_logic())) {
			add_filter('mf_add_' . $this->type . '_action_option', array($this, 'handle_display_logic'), 10, 2);
		}
	}
	/**
	 * Return the option markup.
	 *
	 * @param   object	$action	the associated action object.
	 * @return  string  the option markup.
	 */
	public function get_option_display($action)
	{
		return '';
	}
	/**
	 * Return the display logic for this option.
	 *
	 * The returned array must include a list of supported actions, or excluded actions.
	 * If a string is returned, then it's assumed that the display logic will be implemented manually.
	 * If an array is returned, it must be in a specific format, it can hold only 1 of the following keys ('support', 'exclude').
	 * Both of the keys ('support', 'exclude') can hold an array of action types to support or exclude from support.
	 * When 'exclude' is set, all action types will be supported by default, except the ones provided in the 'exclude' list.
	 * 
	 * @return  array|string
	 */
	public function get_option_display_logic($action = array())
	{
		return '';
	}
	/**
	 * Decide whether a custom option should display in the passed action on not
	 * Note: this is only applicable when 'get_option_display_logic' is returning a string
	 *
	 */
	public function handle_display_logic($display, $action)
	{
		return false;
	}
	/**
	 * Returns `true` or `false` depending on whether the rules were passed on not.
	 *
	 */
	public function evaluate_rules($rules, $posted_values)
	{
		return false;
	}
	/**
	 * Sanitize the option value before saving it to the database
	 *
	 */
	public function sanitize_option_value($action)
	{
		$value = $action->get_setting_value($this->type);
		if (is_array($value)) {
			return map_deep($value, 'sanitize_text_field');
		} else {
			return wp_strip_all_tags($value);
		}
	}
}
