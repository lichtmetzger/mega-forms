<?php

/**
 * @link       https://wpali.com
 * @since      1.0.5
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Phone field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Phone extends MF_Field
{

  public $type = 'phone';
  public $hasJSDependency = true;

  public $editorSettings = array(
    'general' => array(
      'phone_format'
    ),
  );
  public $editorExceptions = array(
    'field_default',
  );

  public function get_field_title()
  {
    return esc_attr__('Phone', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-phone';
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
    $args          = $this->build_field_display_args();
    $args['value'] = $value;
    $args['inputType']  = 'tel';

    $mask = $this->get_setting_value('phone_format');

    $data_mask = '';

    if (!empty($mask)) {
      $data_mask = $mask;
    }

    if (!empty($data_mask)) {
      $args['attributes']['data-mfmask'] = $data_mask;
      $args['class'] = 'mf-input-masked';
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
  protected function phone_format()
  {

    $label       = __('Phone Format', 'megaforms');
    $field_key  = 'phone_format';
    $desc       = __('Input mask gives the user visual guidance to easily type data in a specific format.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']        = $label;
    $args['after_label']  = $this->get_description_tip_markup($label, $desc);
    $args['value']        = $this->get_setting_value($field_key);
    $args['options']      = $this->get_countries_phone_formats();

    $input = mfinput('select', $args, true);

    return $input;
  }
  /**********************************************************************
   ************************* Helpers ******************************
   **********************************************************************/
  public function get_countries_phone_formats()
  {
    $formats = array(
      ''               => __('International', 'megaforms'),
      '999 9999-9999'  => __('Argentina', 'megaforms'),
      '(99) 9999 9999' => __('Australia', 'megaforms'),
      '9999 999999'    => __('Austria', 'megaforms'),
      '99 999 99 99'   => __('Belgium', 'megaforms'),
      '(99) 9999-9999' => __('Brazil', 'megaforms'),
      '99 999 9999'    => __('Bulgaria', 'megaforms'),
      '(999) 999-9999' => __('Canada', 'megaforms'),
      '(9) 9999999'    => __('Colombia', 'megaforms'),
      '99 9999 999'    => __('Croatia', 'megaforms'),
      '99 999999'      => __('Cyprus', 'megaforms'),
      '999 999 999'    => __('Czech Republic', 'megaforms'),
      '999 999 999'    => __('Czech Republic', 'megaforms'),
      '99 99 99 99'    => __('Denmark', 'megaforms'),
      '(999) 999-9999' => __('Dominican Republic', 'megaforms'),
      '9999 9999'      => __('El Salvador', 'megaforms'),
      '999 9999'       => __('Estonia', 'megaforms'),
      '99 99999999'    => __('Finland', 'megaforms'),
      '99 99 99 99 99' => __('France', 'megaforms'),
      '999 99999999'   => __('Germany', 'megaforms'),
      '99 9999 9999'   => __('Greece', 'megaforms'),
      '9999 9999'      => __('Hong Kong', 'megaforms'),
      '(9) 999 9999'   => __('Hungary', 'megaforms'),
      '(99) 999 9999'  => __('Ireland', 'megaforms'),
      '999-999-9999'   => __('Israel', 'megaforms'),
      '99 9999 9999'   => __('Italy', 'megaforms'),
      '99-9999-9999'   => __('Japan', 'megaforms'),
      '99 999 999'     => __('Latvia', 'megaforms'),
      '(9-9) 999 9999' => __('Lithuania', 'megaforms'),
      '99 99 99 99'    => __('Luxembourg', 'megaforms'),
      '99-9999 9999'   => __('Malaysia', 'megaforms'),
      '9999 9999'      => __('Malta', 'megaforms'),
      '99 99 9999 9999' => __('Mexico', 'megaforms'),
      '999 999 9999'   => __('Netherlands', 'megaforms'),
      '99-999 9999'    => __('New Zealand', 'megaforms'),
      '99 99 99 99'    => __('Norway', 'megaforms'),
      '999-9999'       => __('Panama', 'megaforms'),
      '(99) 9999999'   => __('Peru', 'megaforms'),
      '99 999 99 99'   => __('Poland', 'megaforms'),
      '999 999 999'    => __('Portugal', 'megaforms'),
      '(999) 999-9999' => __('Puerto Rico', 'megaforms'),
      '999 999 9999'   => __('Romania', 'megaforms'),
      '9999 9999'      => __('Singapore', 'megaforms'),
      '99/999 999 99'  => __('Slovakia', 'megaforms'),
      '(99) 999 99 99' => __('Slovenia', 'megaforms'),
      '999 999 9999'   => __('South Africa', 'megaforms'),
      '99-9999-9999'   => __('South Korea', 'megaforms'),
      '999 99 99 99'   => __('Spain', 'megaforms'),
      '99-999 999 99'  => __('Sweden', 'megaforms'),
      '999 999 99 99'  => __('Switzerland', 'megaforms'),
      '99 9999 9999'   => __('Taiwan, Province of China', 'megaforms'),
      '999 9999 9999'  => __('United Kingdom', 'megaforms'),
      '(999) 999-999'  => __('United States', 'megaforms'),
    );

    return apply_filters('mf_countries_phone_formats', $formats);
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function validate($value, $context = '')
  {

    $fieldLabel = $this->get_setting_value('field_label');
    $phoneFormat = $this->get_setting_bool_value('phone_format');

    if (!empty($phoneFormat)) {
      if (strlen($phoneFormat) !== strlen($value)) {
        return array(
          /* translators: field label. */
          'notice' => sprintf(__('The entered %s is not valid.', 'megaforms'), $fieldLabel),
          'notice_code' => 'invalid_format',
        );
      }
    }

    return true;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Phone());
