<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Website field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MegaForms_Website extends MF_Field
{

	public $type = 'website';

	public function get_field_title()
	{
		return esc_attr__('Website', 'megaforms');
	}

	public function get_field_icon()
	{
		return 'mega-icons-globe';
	}


	public function get_field_display($value = null)
	{

		# Define arguements array and pass required arguements
		$args = $this->build_field_display_args();
		$args['default'] = isset($this->field['field_default']) ? $this->get_setting_value('field_default') : 'https://';
		$args['value']   = $value;

		# retrieve and return the input markup
		$input = mfinput('text', $args, $this->is_editor);

		return $input;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/
	/**
	 * Returns the default value field markup .
	 *
	 * @return string
	 */
	protected function field_default()
	{

		$label = __('Default Value', 'megaforms');
		$desc = __('Use this to pre-populate this field value.', 'megaforms');
		$field_key = 'field_default';

		$args['id'] 					= $this->get_field_key('options', $field_key);
		$args['label'] 				= $label;
		$args['after_label'] 	= $this->get_description_tip_markup($label, $desc);
		$args['value'] 				= isset($this->field[$field_key]) ? $this->get_setting_value($field_key) : 'https://';
		$args['onchange_preview'] 	= $this->get_js_helper_rules('input', 'update_value');

		$input = mfinput('text', $args, true);
		return $input;
	}

	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/
	public function validate($value, $context = '')
	{

		if (filter_var($value, FILTER_VALIDATE_URL) === false) {
			return array(
				'notice' => __('Please enter a valid url.', 'megaforms'),
				'notice_code' => 'invalid_url',
			);
		}

		return true;
	}

	public function sanitize($value)
	{
		$return = filter_var($value, FILTER_SANITIZE_URL);
		return $return;
	}
}

MF_Fields::register(new MegaForms_Website());
