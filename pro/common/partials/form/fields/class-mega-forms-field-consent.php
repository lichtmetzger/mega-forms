<?php


/**
 * @link       https://wpali.com
 * @since      1.0.8
 *
 */

/**
 * consent field type class
 *
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Field_Consent extends MF_Field
{

  public $type = 'consent';
  public $group = 'misc_fields';

  public $editorSettings = array(
    'general' => array(
      'checkbox_label',
    ),
  );

  public $editorExceptions = array(
    'field_default',
    'field_placeholder',
  );

  public function get_field_title()
  {
    return esc_attr__('Consent', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-document-list';
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $checkbox_label = $this->get_setting_value('checkbox_label', $this->get_default_checkbox_label());

    # Create checkbox attributes
    $attributes = array();
    $attributes['id'] = $args['id'];
    $attributes['name'] = $args['id'];
    $attributes['value'] = 'yes';
    if (!empty($value) && $value == 'yes') {
      $attributes['checked'] = 'checked';
    }

    # Add available choices
    $content = "";
    $content .= '<label>';
    $content .= get_mf_checkbox($attributes);
    $content .= '<span class="mf-consent-desc mf-checkbox-desc">' . $checkbox_label . '</span>';
    $content .= '</label>';
    $args['content'] = $content;

    # retrieve and return the input markup
    $input = mfinput('custom', $args, $this->is_editor);
    return $input;
  }

  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/
  protected function field_description()
  {

    $label = __('Consent agreement', 'megaforms');
    $desc = __('Enter consent agreement text here.', 'megaforms');
    $field_key = 'field_description';

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['value'] = $this->get_setting_value($field_key);
    $args['onchange_preview'] = $this->get_js_helper_rules('span.mf_description', 'update_desc');

    $input = mfinput('textarea', $args, true);
    return $input;
  }

  protected function field_description_position()
  {

    $label = __('Consent Agreement Position', 'megaforms');
    $desc = __('Specify the position of consent agreement.', 'megaforms');
    $field_key = 'field_description_position';

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['value'] = $this->get_setting_value($field_key);
    $args['options'] = array(
      'top' => __('Above', 'megaforms'),
      'bottom' => __('Below', 'megaforms'),
      'hidden' => __('Hide', 'megaforms'),
    );
    $args['default'] = 'top';
    $args['onchange_preview'] = $this->get_js_helper_rules('.mf_description', 'update_descPosition');

    $input = mfinput('radio', $args, true);
    return $input;
  }

  /**
   * Returns the markup for checkbox label option.
   *
   * @return string
   */
  protected function checkbox_label()
  {

    $label = __('Checkbox Label', 'megaforms');
    $field_key = 'checkbox_label';
    $desc = __('Text of the consent checkbox.', 'megaforms');

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['default'] = $this->get_default_checkbox_label();
    $args['value'] = $this->get_setting_value($field_key);
    $args['onchange_preview'] = $this->get_js_helper_rules('span.mf-consent-desc', 'update_desc');

    $input = mfinput('text', $args, true);
    return $input;
  }

  /**********************************************************************
   **************************** Helpers *********************************
   **********************************************************************/
  public function get_formatted_value_short($value)
  {
    if ($value == 'yes') {
      return '<span class="mega-icons-check-square-o" style="color:green; vertical-align: middle;"></span> ' . $this->get_setting_value('checkbox_label');
    }

    return '';
  }

  public function get_formatted_value_long($value)
  {
    return $this->get_formatted_value_short($value);
  }
  public function get_default_checkbox_label()
  {
    return __('I agree to the privacy policy.', 'megaforms');
  }
  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();
    // Make field always required
    $sanitized['field_required'] = true;

    // Sanitize the answer text
    $sanitized['checkbox_label'] = sanitize_text_field($this->get_setting_value('checkbox_label'));

    return $sanitized;
  }
}

MF_Fields::register(new MF_Field_Consent());
