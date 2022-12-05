<?php

/**
 * @link       https://wpali.com
 * @since      1.0.4
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Text field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields/base
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


class MegaForms_Honeypot extends MF_Field
{

  public $type = 'hp';
  public $isStaticField = true;

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args['id'] = mf_api()->get_field_key($this->form_id, 'hp');
    $args['label'] = __('Do not fill this field.', 'megaforms');
    $args['value'] = $value;
    $args['attributes'] = array(
      'autocomplete' => 'false'
    );

    # retrieve and return the input markup
    $input = mfinput('text', $args, $this->is_editor);

    return $input;
  }

  /**********************************************************************
   ***************************** Helpers ********************************
   **********************************************************************/

  public function get_field_container_inline_styles()
  {
    if (!mfget_option('load_form_styling', true)) {
      return 'opacity: 0; position: absolute; top: 0; left: 0; height: 0; width: 0; z-index: -1; visibility: hidden;';
    }

    return '';
  }
}
