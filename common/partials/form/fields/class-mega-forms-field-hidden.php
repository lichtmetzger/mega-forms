<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
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

class MegaForms_Hidden extends MF_Field
{

  public $type = 'hidden';
  public $editorSettings = array(
    'general' => array(
      'field_default',
      'field_visibility'
    )
  );
  public $editorExceptions = array(
    'field_required',
    'field_label_visibility',
    'field_description',
    'field_description_position',
    'field_css_class',
    'field_placeholder',
    'field_default',
    'field_visibility'
  );
  public $isHiddenField = true;

  public function get_field_title()
  {
    return esc_attr__('Hidden', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-eye-slash';
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value;

    # retrieve and return the input markup
    if ($this->is_editor) {
      $input = mfinput('text', $args, $this->is_editor);
    } else {
      $input = mfinput('hidden', $args, $this->is_editor);
    }

    return $input;
  }
}

MF_Fields::register(new MegaForms_Hidden());
