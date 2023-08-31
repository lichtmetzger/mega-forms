<?php

/**
 * @link       https://wpali.com
 * @since      1.0.7
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Text field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Field_Text extends MF_Field
{

  public $type = 'text';
  public $hasJSDependency = true;
  public $editorSettings = array(
    'general' => array(
      'input_mask' => array(
        'priority' => 50,
        'size' => 'half-left'
      ),
      'max_length' => array(
        'priority' => 60,
        'size' => 'half-right'
      ),
      'input_mask_type' => array(
        'priority' => 70
      ),
      'input_custom_mask' => array(
        'priority' => 80
      ),
    ),
  );

  public function get_field_title()
  {
    return esc_attr__('One Line Text', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-font';
  }

  public function get_field_js_dependencies()
  {
    return array(
      'jquery-mask' => array(
        'src'   => MEGAFORMS_COMMON_URL . 'assets/js/deps/jquery.mask.min.js',
        'deps'  => array('jquery'),
        'ver'   => '1.14.16',
      ),
    );
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value;

    $mask = $this->get_setting_value('input_mask');

    if ($mask == 'enable') {

      $data_mask = '';
      $mask_type = $this->get_setting_value('input_mask_type');
      if ($mask_type == 'custom') {
        $custom_mask = $this->get_setting_value('input_custom_mask');
        $data_mask = $custom_mask;
      } else {
        $data_mask = $mask_type;
      }

      if (!empty($data_mask)) {
        $args['attributes']['data-mfmask'] = $data_mask;
        $args['class'] = !empty($args['class']) ? $args['class'] . ' mf-input-masked' : 'mf-input-masked';
      }
    } else {
      $max_length = $this->get_setting_value('max_length');
      if (!empty($max_length)) {
        $args['attributes']['maxlength'] = $max_length;
        if (!$this->is_editor) {
          $length = empty($args['value']) && !empty($args['default']) ? strlen($args['default']) : strlen($args['value']);
          $args['after_input'] = '<span id="mf_char_num">' . $length . '/' . $max_length . '</span>';
          $args['attributes']['onkeyup'] = 'megaForms.maxLengthHandler(this)';
        }
      }
    }

    # retrieve and return the input markup
    $input = mfinput('text', $args, $this->is_editor);

    return $input;
  }

  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/

  /**
   * Returns the markup for input-mask option.
   *
   * @return string
   */
  protected function input_mask()
  {

    $label       = __('Input Mask', 'megaforms');
    $field_key  = 'input_mask';
    $desc       = __('Input mask gives the user visual guidance to easily type data in a specific format.', 'megaforms');

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['value'] = $this->get_setting_value($field_key, 'disable');
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['options'] = array(
      'enable' => __('Enable', 'megaforms'),
      'disable' => __('Disable', 'megaforms'),
    );

    $input = mfinput('radio', $args, true);

    return $input;
  }
  /**
   * Returns the markup for max-length option.
   *
   * @return string
   */
  protected function max_length()
  {

    $label     = __('Maximum Length', 'megaforms');
    $field_key = 'max_length';
    $desc     = __('Specify the maximum number of characters allowed in this field.', 'megaforms');

    $args['inputType']     = 'number';
    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);
    $args['value']         = $this->get_setting_value($field_key);

    # Conditional logic
    $conditional_rules = array(
      'name' => $this->get_field_key('options', 'input_mask'),
      'operator' => 'is',
      'value' => 'disable',
    );
    $args['conditional_rules'] = $this->get_js_conditional_rules('show', $conditional_rules);

    $input = mfinput('text', $args, true);
    return $input;
  }
  /**
   * Returns the markup for select mask option.
   *
   * @return string
   */
  protected function input_mask_type($show = false)
  {
    # Mask type arguements
    $mask_type_label   = __('Input Mask Type', 'megaforms');
    $mask_type_key     = 'input_mask_type';
    $mask_type_desc    = __('Select mask type from predefined ones, or select custom to use your own.', 'megaforms');

    $args               = array();
    $args['id']         = $this->get_field_key('options', $mask_type_key);
    $args['value']      = $this->get_setting_value($mask_type_key);
    $args['label']      = $mask_type_label;
    $args['after_label'] = $this->get_description_tip_markup($mask_type_label, $mask_type_desc);
    $args['options']    = $this->get_mask_types();
    # Conditional display
    $conditional_rules = array(array(
      'name' => $this->get_field_key('options', 'input_mask'),
      'operator' => 'is',
      'value' => 'enable',
    ));
    $args['conditional_rules'] = $this->get_js_conditional_rules('show', $conditional_rules);

    $input = mfinput('select', $args, true);
    return $input;
  }
  /**
   * Returns the markup for custom mask option.
   *
   * @return string
   */
  protected function input_custom_mask($show = false)
  {

    # Custom mask arguements
    $custom_mask_label  = __('Input Custom Mask', 'megaforms');
    $custom_mask_key    = 'input_custom_mask';
    $custom_mask_desc   = sprintf('<a target="_blank" href="%s">%s</a>', 'http://igorescobar.github.io/jQuery-Mask-Plugin/docs.html', __('See examples & documentation.', 'megaforms'));

    $args                 = array();
    $args['id']           = $this->get_field_key('options', $custom_mask_key);
    $args['label']         = $custom_mask_label;
    $args['after_label']   = $this->get_description_tip_markup($custom_mask_label, $custom_mask_desc);
    $args['value']         = $this->get_setting_value($custom_mask_key);
    # Conditional logic
    $conditional_rules = array(array(
      'name' => $this->get_field_key('options', 'input_mask_type'),
      'operator' => 'is',
      'value' => 'custom',
    ));
    $args['conditional_rules'] = $this->get_js_conditional_rules('show', $conditional_rules);

    $input = mfinput('text', $args, true);
    return $input;
  }

  /**********************************************************************
   ****************************** Helpers *******************************
   **********************************************************************/
  public function get_mask_types()
  {
    $masks = array(
      ''                       => __('Select mask type', 'megaforms'),
      '00/00/0000'             => __('Date', 'megaforms'),
      '(000) 000-0000'         => __('US Telephone', 'megaforms'),
      '000.000.000.000.000,00' => __('Money', 'megaforms'),
      'custom'                 => __('Custom', 'megaforms'),
    );

    return apply_filters('mf_text_mask_types', $masks);
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function validate($value, $context = '')
  {

    $fieldLabel = $this->get_setting_value('field_label');
    $inputMask = $this->get_setting_value('input_mask');

    if ($inputMask == 'enable') {
      $inputMaskType = $this->get_setting_value('input_mask_type');
      $maskPattern = $inputMaskType == 'custom' ? $this->get_setting_value('input_custom_mask') : $inputMaskType;
      if (strlen($maskPattern) !== strlen($value)) {
        return array(
          /* translators: field label. */
          'notice' => sprintf(__('The entered %s is not valid.', 'megaforms'), $fieldLabel),
          'notice_code' => 'invalid_format',
        );
      }
    } else {
      $maxLength = $this->get_setting_value('max_length');
      if (!empty($maxLength)) {
        if (strlen($value) > $maxLength) {
          return array(
            /* translators: field label. */
            'notice' => sprintf(__('The entered %s is too long.', 'megaforms'), $fieldLabel),
            'notice_code' => 'invalid_length',
          );
        }
      }
    }

    return true;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $sanitized['input_mask'] = sanitize_text_field($this->get_setting_value('input_mask'));
    $sanitized['input_mask_type'] = wp_strip_all_tags($this->get_setting_value('input_mask_type'));
    $sanitized['input_custom_mask'] = wp_strip_all_tags($this->get_setting_value('input_custom_mask'));

    $max_length = $this->get_setting_value('max_length');
    $sanitized['max_length'] = !empty($max_length) ? (int) $max_length : '';

    return $sanitized;
  }
}

MF_Fields::register(new MF_Field_Text());
