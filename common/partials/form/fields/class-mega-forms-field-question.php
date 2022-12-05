<?php

/**
 * @link       https://wpali.com
 * @since      1.0.2
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Question field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Field_Question extends MF_Field
{

  public $type = 'question';

  public $editorSettings = array(
    'general' => array(
      'answer',
    ),
  );

  public $editorExceptions = array(
    'field_default',
  );

  public function get_field_title()
  {
    return esc_attr__('Question', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-question-circle';
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value;
    $args['required'] = true;

    # retrieve and return the input markup
    $input = mfinput('text', $args, $this->is_editor);

    return $input;
  }

  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/
	/**
	 * Returns the display for field main options.
	 *
	 * @return string
	 */
	protected function field_label()
	{

		$label = __('Question', 'megaforms');
		$desc = __('The question you want answered.', 'megaforms');
		$field_key = 'field_label';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('span.mf_label', 'update_label');

		$input = mfinput('text', $args, true);
		return $input;
  }
	/**
	 * Returns the display for field main options.
	 *
	 * @return string
	 */
	protected function field_required()
	{

		$label = __('Required Field', 'megaforms');
		$desc = __('Enable this option to make this field required. Required fields prevent the form from being submitted if it is not completed.', 'megaforms');
		$field_key = 'field_required';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = true;
		$args['onchange_edit'] = $this->get_js_helper_rules('none', 'prevent_switch_off', array( 'message' => __('Question field must be required.', 'megaforms') ));

		$input = mfinput('switch', $args, true);
		return $input;
	}
  /**
   * Returns the markup for answer option.
   *
   * @return string
   */
  protected function answer()
  {

    $label     = __('Answer', 'megaforms');
    $field_key = 'answer';
    $desc     = __('A case sensitive answer. The form will not be submitted if user entry does not match this asnwer.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);
    $args['value']         = $this->get_setting_value($field_key);

    $input = mfinput('text', $args, true);
    return $input;
  }


  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function validate($value, $context = '')
  {

    $answer = $this->get_setting_value('answer');
    if (strcmp($value, $answer) !== 0) {
      return array(
        'notice' => __('The entered answer is not valid.', 'megaforms'),
        'notice_code' => 'invalid_answer',
      );
    }

    return true;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();
    // Make field always required
    $sanitized['field_required'] = true;

    // Sanitize the answer text
    $sanitized['answer'] = sanitize_text_field($this->get_setting_value('answer'));

    return $sanitized;
  }
}

MF_Fields::register(new MF_Field_Question());
