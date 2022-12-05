<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Textarea field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MegaForms_textarea extends MF_Field
{

	public $type = 'textarea';
	public $editorSettings = array(
		'general' => array(
			'field_max_length',
		),
	);

	public function get_field_title()
	{
		return esc_attr__('Paragraph Text', 'megaforms');
	}

	public function get_field_icon()
	{
		return 'mega-icons-paragraph';
	}

	public function get_field_display($value = null)
	{

		# Define arguements array and pass required arguements
		$args = $this->build_field_display_args();
		$args['value'] = $value;

		$max_length = $this->get_setting_value('max_length');
		if (!empty($max_length)) {
			$args['attributes']['maxlength'] = $max_length;
			if (!$this->is_editor) {
				$length = empty($args['value']) && !empty($args['default']) ? strlen($args['default']) : strlen($args['value']);
				$args['after_input'] = '<span id="mf_char_num">' . $length . '/' . $max_length . '</span>';
				$args['attributes']['onkeyup'] = 'megaForms.maxLengthHandler(this)';
			}
		}

		$rows = $this->get_setting_value('rows');
		if (!empty($rows)) {
			$args['rows'] = $rows;
		}

		# retrieve and return the input markup
		$input = mfinput('textarea', $args, $this->is_editor);

		return $input;
	}
	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/

	/**
	 * Returns the display for field placeholder.
	 *
	 * @return string
	 */
	protected function field_placeholder()
	{
		$label = __('Placeholder', 'megaforms');
		$desc = __('Use this to specify a short hint that describes the expected value of this field ', 'megaforms');
		$field_key = 'field_placeholder';

		$args['name'] = $field_key;
		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('textarea', 'update_placeholder');

		$input = mfinput('text', $args, true);
		return $input;
	}

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

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('textarea', 'update_value');

		$input = mfinput('text', $args, true);
		return $input;
	}
	/**
	 * Returns the markup for max-length option.
	 *
	 * @return string
	 */
	protected function field_max_length()
	{

		$label = __('Maximum Length', 'megaforms');
		$field_key = 'max_length';
		$desc = __('Specify the maximum number of characters allowed in this field.', 'megaforms');

		$args['inputType'] = 'number';
		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);

		$input = mfinput('text', $args, true);
		return $input;
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/
	public function validate($value, $context = '')
	{

		$fieldLabel = $this->get_setting_value('field_label');
		$maxLength = $this->get_setting_value('max_length');

		if (!empty($maxLength)) {
			if (strlen($value) > $maxLength) {
				/* translators: field label. */
				return array(
					'notice' => sprintf(__('The entered %s is too long.', 'megaforms'), $fieldLabel),
					'notice_code' => 'invalid_length',
				);
			}
		}

		return true;
	}
	public function is_spam($value)
	{
		// BB code check
		if ((bool) preg_match('/\[url[=\]].*\[\/url\]/is', $value)) {
			return true;
		}
		// Russian Language spam check
		if (
			!in_array(get_locale(), array('ru_RU', 'uk', 'bel', 'bg_BG', 'tt_RU', 'mk_MK', 'sah', 'sr_RS', 'mn')) &&
			preg_match('/[А-Яа-яЁё]/u', $value)

		) {
			return true;
		}
		// Chinese Language spam check
		if (
			!in_array(get_locale(), array('zh_CN', 'zh_TW', 'zh_HK')) &&
			preg_match('/[\p{Han}]/simu', $value)
		) {
			return true;
		}

		// Regex check
		$value = (function_exists('iconv') ? iconv('utf-8', 'utf-8//TRANSLIT', $value) : $value);
		$patterns = array(
			'gay|sexy|porn',
			'50% off|money back guarantee|get it now|click here|buy here|buy now|make dollars|earn ([\S]+) money|earn money online|purchase amazing|buy amazing|luxurybrandsale',
			'target[t]?ed (visitors|traffic)|viagra|cialis|increas(e|ing) your (sales|leads|conversion)',
			'forex (course|trading)|financial robot',
			'fiverr\.com|clickbank\.(net|com)',
			'\b[a-z]{30}\b',
		);
		if (!empty($value)) {
			foreach ($patterns as $regexp) {
				if (preg_match('/' . $regexp . '/isu', $value)) {
					return true;
				}
			}
		}
		return false;
	}
	public function sanitize_settings()
	{

		$sanitized = parent::sanitize_settings();

		$max_length = $this->get_setting_value('max_length');

		$sanitized['max_length'] = !empty($max_length) ? (int) $max_length : '';

		return $sanitized;
	}
}

MF_Fields::register(new MegaForms_textarea());
