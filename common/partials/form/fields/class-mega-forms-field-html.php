<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * HTML field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Html extends MF_Field
{

  public $type = 'html';
  public $isStaticField = true;
  public $editorSettings = array(
    'general' => array(
      'html_content',
      'process_shortcodes'
    ),
  );
  public $editorExceptions = array(
    'field_required',
    'field_placeholder',
    'field_label_visibility',
    'field_description_position',
    'field_description',
    'field_default'
  );

  public function get_field_title()
  {
    return esc_attr__('HTML', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-code';
  }
  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args   = $this->build_field_display_args();

    if ($this->is_editor) {
      # Load a placeholder on the backend
      $html_content = '';
      $html_content .= '<div class="mf-html-placeholder">';
      $html_content .= '<span><i class="mega-icons-code"></i>' . __('HTML Code is only displayed when viewing the form on the frontend.', 'megaforms') . '</span>';
      $html_content .= '</div>';
    } else {
      # Load HTML on the frontend
      unset($args['label']);
      $html_content = mf_merge_tags()->process($this->get_setting_value('html_content'));
      if( $this->get_setting_bool_value('process_shortcodes') ){
        $html_content = do_shortcode($html_content);
      }
    }

    $args['content']  = $html_content;
    # retrieve and return the input markup
    $input = mfinput('custom', $args, $this->is_editor);

    return $input;
  }
  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/
  /**
   * Returns the markup for html content field.
   *
   * @return string
   */
  protected function html_content()
  {

    $label       = __('HTML Content', 'megaforms');
    $field_key  = 'html_content';
    $desc       = __('Enter the HTML code you want displayed on your form.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['value']         = $this->get_setting_value($field_key);
    $args['rows']         = 5;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);
    $args['inline_modal']   = array('form', 'wp', 'misc');

    $input = mfinput('textarea', $args, true);
    return $input;
  }
  /**
   * Returns the markup for process shortcodes field.
   *
   * @return string
   */
  protected function process_shortcodes()
  {

    $label      = __('Process Shortcode', 'megaforms');
    $field_key  = 'process_shortcodes';
    $desc       = __('Enable or disable WordPress shortcode processing for the HTML content.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['value']         = $this->get_setting_bool_value($field_key);
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $input = mfinput('switch', $args, true);
    return $input;
  }
  /**********************************************************************
   ************************* Helpers ******************************
   **********************************************************************/
  public function allowed_html()
  {
    return apply_filters('mf_html_field_allowed_tags', 'post', $this);
  }
  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $sanitized['html_content'] = wp_kses($this->get_setting_value('html_content'), $this->allowed_html());
    $sanitized['process_shortcodes'] = $this->get_setting_bool_value('process_shortcodes');

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Html());
