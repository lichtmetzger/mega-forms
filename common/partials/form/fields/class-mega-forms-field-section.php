<?php

/**
 * @link       https://wpali.com
 * @since      1.0.2
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Section field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Section extends MF_Field
{

  public $type = 'section';
  public $isStaticField = true;
  public $editorSettings = array(
    'general' => array(
      'section_description',
    ),
  );
  public $editorExceptions = array(
    'field_required',
    'field_placeholder',
    'field_label_visibility',
    'field_description',
    'field_description_position',
    'field_default'
  );

  public function get_field_title()
  {
    return esc_attr__('Section', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-document-text';
  }
  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args   = $this->build_field_display_args();
    $title = isset($args['label']) ? $args['label'] : '';
    unset($args['label']);

    $desc = $this->get_setting_value('section_description');

    $content = '';
    $content .= '<h2 class="mf-section-title">';
    $content .= $title;
    $content .= '</h2>';

    if (!empty($desc)) {
      $content .= '<p class="mf-section-desc">' . $desc . '</p>';
    }

    $args['content']  = $content;
    # retrieve and return the input markup
    $input = mfinput('custom', $args, $this->is_editor);

    return $input;
  }
  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/

  protected function field_label()
  {


    $label = __('Section Title', 'megaforms');
    $desc = __('Enter the section title.', 'megaforms');
    $field_key = 'field_label';

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['value'] = $this->get_setting_value($field_key);
    $args['onchange_preview'] = $this->get_js_helper_rules('.mf-section-title', 'update_label');

    $input = mfinput('text', $args, true);
    return $input;
  }
  protected function section_description()
  {

    $label = __('Description', 'megaforms');
    $desc = __('Write a description to provide users with more information about this section.', 'megaforms');
    $field_key = 'section_description';

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['value'] = $this->get_setting_value($field_key);
    $args['onchange_preview'] = $this->get_js_helper_rules('.mf-section-desc', 'update_desc');

    $input = mfinput('textarea', $args, true);
    return $input;
  }
  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();
    $sanitized['section_description'] = sanitize_textarea_field($this->get_setting_value('section_description'));

    return $sanitized;

  }
}

MF_Fields::register(new MegaForms_Section());
