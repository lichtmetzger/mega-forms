<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Text field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


class MegaForms_Choice extends MF_Field
{

  public $type               = 'choice';
  public $inputType          = 'custom';
  public $editorSettings = array(
    'general' => array(
      'field_choices'
    ),
  );
  public $editorExceptions = array(
    'field_placeholder',
    'field_default'
  );

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value;


    # Add available choices
    $field_choices      = $this->get_setting_value('field_choices');
    $available_choices  = isset($field_choices['choices']) ? $field_choices['choices'] : $this->get_placeholder_choices();
    $available__options = array();
    foreach ($available_choices as $choice) {
      $available__options[$choice['value']] = $choice['label'];
      if (isset($choice['default']) && mfget_bool_value($choice['default'])) {
        if ($this->type == 'checkboxes') {
          $args['default'][] = $choice['value'];
        } else {
          $args['default'] = $choice['value'];
        }
      }
    }

    $args['options'] = $available__options;

    # retrieve and return the input markup
    $input = mfinput($this->inputType, $args, $this->is_editor);

    return $input;
  }

  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/

  /**
   * Returns the repeatable choices option.
   *
   * @return string
   */
  protected function field_choices()
  {

    $label     = __('Choices', 'megaforms');
    $field_key = 'field_choices';
    $type     = $this->type == 'checkboxes' ? 'checkbox' : 'radio';
    $value    = $this->get_setting_value($field_key);
    $desc     = __('Add choices to this field using the repeatable inputs below.', 'megaforms');

    $options = !empty($value['choices']) && is_array($value['choices']) ? $value['choices'] : $this->get_placeholder_choices();

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);


    $repeatable_inputs = '';
    $repeatable_inputs .= sprintf('<label class="mf-choices-header-label">%s</label>', __('Label', 'megaforms'));
    $repeatable_inputs .= sprintf('<label class="mf-choices-header-value">%s</label>', __('Value', 'megaforms'));
    $repeatable_inputs .= '<ul class="mf-choices-list">';

    foreach ($options as $optkey => $optval) {
      $name = sprintf('%s[choices][%d]', $args['id'], $optkey);


      $select_default   = $this->get_js_helper_rules('none', 'select_default_choice');
      $change_label   = $this->get_js_helper_rules('none', 'change_choice_label');

      $repeatable_inputs .= sprintf('<li data-key="%d">', absint($optkey));

      $default = !empty($optval['default']) ? $optval['default'] : '';
      $default_args = array(
        'name' => $name . '[default]',
        'class' => 'default', 'value' => '1',
        'data-edit' => $select_default
      );
      if ($default === true) {
        $default_args['checked'] = 'checked';
      }
      $repeatable_inputs .= '<span class="sort"><i class="mega-icons-move"></i></span>';
      $repeatable_inputs .= get_mf_input($type, $default_args);

      $repeatable_inputs .= '<div class="mf-single-choice-input">';
      $repeatable_inputs .= get_mf_input('text', array('name' => $name . '[label]', 'class' => 'label', 'value' => $optval['label'], 'data-edit' => $change_label));
      $repeatable_inputs .= get_mf_input('text', array('name' => $name . '[value]', 'class' => 'value', 'value' => $optval['value']));
      $repeatable_inputs .= '</div>';

      $add_choice   = $this->get_js_helper_rules('none', 'add_choice');
      $remove_choice = $this->get_js_helper_rules('none', 'remove_choice');
      $repeatable_inputs .= '<button class="add" tabindex="-1" data-edit="' . esc_attr($add_choice) . '"><i class="mega-icons-plus"></i></button>';
      $repeatable_inputs .= '<button class="remove" tabindex="-1" data-edit="' . esc_attr($remove_choice) . '"><i class="mega-icons-minus"></i></button>';

      $repeatable_inputs .= '</li>';
    }
    $repeatable_inputs .= '</ul>';

    $toggle_values = $this->get_js_helper_rules('none', 'show_choices_values');

    $repeatable_inputs .= '<div class="mf-choices-list-actions">';
    # show values button
    $repeatable_inputs .= '<span class="left">';
    $repeatable_inputs .= get_mf_checkbox(array('id' => 'mf_enable_choices_values_' . $this->field_id, 'data-edit' => $toggle_values));
    $repeatable_inputs .= '<label for="mf_enable_choices_values_' . $this->field_id . '">show values</label>';
    $repeatable_inputs .= '</span>';
    # Bulk add button
    // $repeatable_inputs .= '<span class="right">';
    // $repeatable_inputs .= '<a href="#" class="mf-toggle-bulk-add-modal" data-edit="'. $bulk_add .'">Bulk add</a>';
    // $repeatable_inputs .= '</span>';

    $repeatable_inputs .= '</div>';

    $args['content'] = $repeatable_inputs;
    $input = mfinput('custom', $args, true);
    return $input;
  }

  /**
   * Returns an array of default choices to be used when none are provided.
   *
   * @return array
   */
  public function get_placeholder_choices()
  {
    return array(
      array('value' => 'One', 'label' => 'One'),
      array('value' => 'Two', 'label' => 'Two'),
      array('value' => 'Three', 'label' => 'Three')
    );
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function validate($value, $context = '')
  {

    $field_choices = $this->get_setting_value('field_choices');
    $value = is_array($value) ? $value : array($value);
    if (isset($field_choices['choices'])) {
      foreach ($value as $val) {
        $is_available = in_array($val, array_column($field_choices['choices'], 'value'));
        if (!$is_available) {
          return array(
            'notice' => __('Please select a valid option.', 'megaforms'),
            'notice_code' => 'invalid_choice',
          );
        }
      }
    }

    return true;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $choices = $this->get_setting_value('field_choices', array());

    foreach ($choices['choices'] as &$choice) {
      if (isset($choice['default'])) {
        $choice['default'] = mfget_bool_value($choice['default']);
      }

      if (isset($choice['label'])) {
        $choice['label'] = sanitize_text_field($choice['label']);
      }
      if (isset($choice['value'])) {
        $choice['value'] = wp_strip_all_tags($choice['value']);
      }
    }

    $sanitized['field_choices'] = $choices;

    return $sanitized;
  }
}
