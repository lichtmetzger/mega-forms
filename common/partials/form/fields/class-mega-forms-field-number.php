<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Number field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Number extends MF_Field
{

  public $type = 'number';
  public $editorSettings = array(
    'general' => array(
      'number_options'
    ),
  );
  public function get_field_title()
  {
    return esc_attr__('Number', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-hashtag';
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args               = $this->build_field_display_args();
    $args['inputType']  = 'number';
    $args['value']      = $value;
    $args['attributes']['pattern'] = "[0-9]*";
    $args['attributes']['inputmode'] = "numeric";

    $numberOptions = $this->get_setting_value('number_options');
    if (!empty($numberOptions) && is_array($numberOptions)) {
      foreach ($numberOptions as $oKey => $oVal) {
        if(!empty($oVal)){
          $args['attributes'][$oKey] = $oVal;
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
   * Returns the markup for max-length option.
   *
   * @return string
   */
  protected function number_options()
  {

    $label       = __('Number Options', 'megaforms');
    $field_key  = 'number_options';
    $desc       = __('Use number options to force a minimum and maximum value for this field. You can use step input to specify the legal number intervals for the field.', 'megaforms');

    $value    = $this->get_setting_value($field_key);

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $min  = isset($value['min']) ? $value['min'] : '';
    $max  = isset($value['max']) ? $value['max'] : '';
    $step = isset($value['step']) ? $value['step'] : '';

    $numberOptions = '';
    $numberOptions .= '<table class="mf-number-options"><tbody>';

    $numberOptions .= '<tr>';

    $numberOptions .= '<td class="mf-number-options-header">' . __('Min', 'megaforms') . '</td>';
    $numberOptions .= '<td class="mf-number-options-header">' . __('Max', 'megaforms') . '</td>';
    $numberOptions .= '<td class="mf-number-options-header">' . __('Step', 'megaforms') . '</td>';

    $numberOptions .= '</tr>';

    $numberOptions .= '<tr>';


    $numberOptions .= '<td>';
    $numberOptions .= get_mf_input('number', array('name' => $args['id'] . '[min]', 'value' => $min));
    $numberOptions .= '</td>';

    $numberOptions .= '<td>';
    $numberOptions .= get_mf_input('number', array('name' => $args['id'] . '[max]', 'value' => $max));
    $numberOptions .= '</td>';

    $numberOptions .= '<td>';
    $numberOptions .= get_mf_input('number', array('name' => $args['id'] . '[step]', 'value' => $step));
    $numberOptions .= '</td>';

    $numberOptions .= '</tr>';

    $numberOptions .= '</tbody></table>';

    $args['content'] = $numberOptions;
    $input = mfinput('custom', $args, true);
    return $input;
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/

  public function validate($value, $context = '')
  {

    if (!is_numeric($value)) {
      return array(
        'notice' => __('Please enter a valid number.', 'megaforms'),
        'notice_code' => 'invalid_number',
      );
    }
    return true;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $numberOptions = $this->get_setting_value('number_options');
    if (!empty($numberOptions) && is_array($numberOptions)) {
      $sanitized['number_options'] = array();
      foreach ($numberOptions as $oKey => &$oVal) {
        $oVal = filter_var($oVal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
      }
    }
    $sanitized['number_options'] = $numberOptions;

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Number());
