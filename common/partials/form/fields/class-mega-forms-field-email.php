<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Email field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Email extends MF_Field
{

  public $type = 'email';
  public $isCompoundField = true;
  public $editorSettings = array(
    'general' => array(
      'email_inputs'
    ),
    'display' => array(
      'field_sub_label_position',
      'field_size'
    ),
  );
  public $editorExceptions = array(
    'field_placeholder',
    'field_default',
  );

  public function get_field_title()
  {
    return esc_attr__('Email', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-envelope-o';
  }
  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value !== null ? $value : array();
    $notices = mfget('compound_notices', $args, false);

    # Build field markup
    $emailSettings    = $this->get_setting_value('email_inputs');
    $emailComponents  = $this->get_email_components($emailSettings, $args['value']);
    $sub_labels_pos   = $this->get_setting_value('field_sub_label_position');
    $field_size       = $this->get_setting_value('field_size', 'half');

    $emailHTML = '';
    foreach ($emailComponents as $aKey => $aVal) {

      $enabled = $aVal['enable'];

      # Only load field if enabled on the front end, and always load it in the backend.
      if ($enabled || $this->is_editor) {

        $subArgs = array();
        $subArgs['id'] = sprintf('%s[%s]', $args['id'], $aKey);
        $subArgs['tag'] = 'span';
        $subArgs['desc'] = !empty($aVal['desc']) ? $aVal['desc'] : $aVal['label'];
        $subArgs['desc_position'] = $sub_labels_pos;
        $subArgs['value'] = $aVal['value'] !== null ? $aVal['value'] : $aVal['default'];
        $subArgs['placeholder'] = $aVal['placeholder'];
        $subArgs['notice'] = isset($notices[$aKey]) && !isset($args['notice']) ? $notices[$aKey] : false;
        $subArgs['notice_css_class'] = isset($notices[$aKey]) ? true : false;
        // Define classes
        $class = '';
        $class .= 'mf_sub_' . $aKey;
        if ($aKey == 'email') {
          $class .= $field_size == 'full' ? ' mf_full' : ' mf_half';
          $class .= ' mf_left';
        } else {
          $class .= $field_size == 'full' ? ' mf_full' : ' mf_half';
          $class .= ' mf_right';
        }
        // Hide the field on the preview screen if not enabled
        if (!$enabled) {
          $class .= ' mf_hidden';
        }
        $subArgs['wrapper_class'] = $class;

        // Define input
        $inputHTML = get_mf_input('text', array('name' => $subArgs['id'], 'value' => $subArgs['value'], 'placeholder' => $subArgs['placeholder']));

        $subArgs['content'] = $inputHTML;

        $emailHTML .= mfinput('subcustom', $subArgs, $this->is_editor);
      }
    }

    # retrieve and return the input markup
    $args['content'] = $emailHTML;
    $input = mfinput('custom', $args, $this->is_editor);
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
  protected function email_inputs()
  {

    $label     = __('Email Inputs', 'megaforms');
    $field_key = 'email_inputs';
    $desc     = __('Select the fields you want to display and customize them as needed by entering the appropriate values in their respective inputs.', 'megaforms');
    $value    = $this->get_setting_value($field_key);

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $emailComponents = $this->get_email_components($value);


    $email_inputs = '';
    $email_inputs .= '<table class="mf-field-inputs"><tbody>';

    $email_inputs .= '<tr>';

    $email_inputs .= '<td class="mf-input-header-enable">' . __('Enable', 'megaforms') . '</td>';

    $navigate_input_settings = $this->get_js_helper_rules('none', 'navigate_input_settings');
    $email_inputs .= '<td class="mf-input-header-customize">';
    $email_inputs .= '<button class="previous" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-back"></i></button>';
    $email_inputs .= '<span data-type="desc" class="first desc active">' . __('Sub Labels', 'megaforms') . '</span>';
    $email_inputs .= '<span data-type="default" class="default">' . __('Default Values', 'megaforms') . '</span>';
    $email_inputs .= '<span data-type="placeholder" class="last placeholder">' . __('Placeholders', 'megaforms') . '</span>';
    $email_inputs .= '<button class="next" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-forward"></i></button>';
    $email_inputs .= '</td>';

    $email_inputs .= '</tr>';

    foreach ($emailComponents as $eKey => $eVal) {

      $name = sprintf('%s[%s]', $args['id'], $eKey);

      $switch_args        = array(
        'id'            => $name . '[enable]',
        'label'         => $eVal['label'],
        'value'         => $eVal['enable'],
        'size'          => 'small',
        'labelRight'    => true,
        'attributes'    => array(),
        'wrapper_class' => 'mf-field-inputs-switch',
      );
      if ($eKey == 'email') {
        $switch_args['onchange_edit'] = $this->get_js_helper_rules('none', 'prevent_switch_off');
      } else {
        $switch_args['onchange_preview'] = $this->get_js_helper_rules('.mf_sub_' . $eKey, 'toggle_field_inputs', true);
      }
      $update_sublabel    = $this->get_js_helper_rules('.mf_sub_' . $eKey, 'update_sub_label', true);
      $update_value       = $this->get_js_helper_rules('.mf_sub_' . $eKey . ' :input', 'update_value', true);
      $update_placeholder = $this->get_js_helper_rules('.mf_sub_' . $eKey . ' :input', 'update_placeholder', true);

      $email_inputs .= sprintf('<tr data-key="%s">', $eKey);

      $email_inputs .= '<td>';
      $email_inputs .= mfinput('switch', $switch_args, true);
      $email_inputs .= '</td>';

      $email_inputs .= '<td>';
      # Sub Label Field
      $email_inputs .= get_mf_input('text', array('name' => sprintf('%s[desc]', $name), 'value' => $eVal['desc'], 'class' => 'desc active', 'placeholder' => $eVal['label'], 'data-default' => $eVal['label'], 'data-preview' => $update_sublabel));
      # Default Value Field
      $email_inputs .= get_mf_input('text', array('name' => sprintf('%s[default]', $name), 'value' => $eVal['default'], 'class' => 'default', 'data-preview' => $update_value));
      # Placeholder Field
      $email_inputs .= get_mf_input('text', array('name' => sprintf('%s[placeholder]', $name), 'value' => $eVal['placeholder'], 'class' => 'placeholder', 'data-preview' => $update_placeholder));
      $email_inputs .= '</td>';

      $email_inputs .= '</tr>';
    }

    $email_inputs .= '</tbody></table>';

    $args['content'] = $email_inputs;
    $input = mfinput('custom', $args, true);
    return $input;
  }

  /**********************************************************************
   ************************* Helpers ******************************
   **********************************************************************/

  public function get_email_components($settingsValues, $displayValues = array())
  {

    $components = array(
      'email' => array(
        'label'         => __('Email', 'megaforms'),
        'enable'        => isset($settingsValues['email']['enable']) && $settingsValues['email']['enable'] ? true : false,
        'default'       => isset($settingsValues['email']['default']) && !empty($settingsValues['email']['default'])  ? $settingsValues['email']['default'] : '',
        'desc'          => isset($settingsValues['email']['desc']) && !empty($settingsValues['email']['desc'])  ? $settingsValues['email']['desc'] : '',
        'placeholder'   => isset($settingsValues['email']['placeholder']) && !empty($settingsValues['email']['placeholder'])  ? $settingsValues['email']['placeholder'] : '',
        'value'         => isset($displayValues['email']) && !empty($displayValues['email'])  ? $displayValues['email'] : null,
        'is_required'   => true,
      ),
      'email_confirmation' => array(
        'label'         => __('Email Confirmation', 'megaforms'),
        'enable'        => isset($settingsValues['email_confirmation']['enable']) && $settingsValues['email_confirmation']['enable']  ? true : false,
        'default'       => isset($settingsValues['email_confirmation']['default']) && !empty($settingsValues['email_confirmation']['default'])  ? $settingsValues['email_confirmation']['default'] : '',
        'desc'          => isset($settingsValues['email_confirmation']['desc']) && !empty($settingsValues['email_confirmation']['desc'])  ? $settingsValues['email_confirmation']['desc'] : '',
        'placeholder'   => isset($settingsValues['email_confirmation']['placeholder']) && !empty($settingsValues['email_confirmation']['placeholder'])  ? $settingsValues['email_confirmation']['placeholder'] : '',
        'value'         => isset($displayValues['email_confirmation']) && !empty($displayValues['email_confirmation'])  ? $displayValues['email_confirmation'] : null,
        'is_required'   => true,
      ),
    );

    // Include remove email confirmation from entry view
    if (mf_api()->is_page('mf_entry_view')) {
      unset($components['email_confirmation']);
    }
    // Make sure all sub fields are enabled by default if not already saved to database ( when adding new field to the form )
    if (empty($settingsValues)) {
      foreach ($components as $key => $val) {
        $components[$key]['enable'] = true;
      }
    }

    return apply_filters('mf_email_components', $components);
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/

  protected function compound_required_check($value)
  {

    $field_settings = $this->get_setting_value('email_inputs');
    $email_components = $this->get_email_components($field_settings, $value);

    return $this->compound_required_check_helper($email_components, $value);
  }

  public function validate($value, $context = '')
  {

    $field_settings = $this->get_setting_value('email_inputs');
    $email_components = $this->get_email_components($field_settings, $value);

    // If the validation context is the entry edit page, set confirmation field to false.
    if ($context == 'entry') {
      $is_confirmation_enabled = false;
    } else {
      $is_confirmation_enabled = $email_components['email_confirmation']['enable'];
    }

    if (!empty($value['email'])) {

      if (is_email($value['email'])) {
        if ($is_confirmation_enabled) {
          if ($value['email'] !== $value['email_confirmation']) {
            return array(
              'notice' => __('The entered values do not match.', 'megaforms'),
              'notice_code' => 'invalid_email_confirmation',
            );
          }
        }
      } else {
        return array(
          'notice' => __('Please enter a valid email.', 'megaforms'),
          'notice_code' => 'invalid_email',
        );
      }
    }

    return true;
  }

  public function is_spam($value)
  {
    // Regex check
    $email = $value['email'] ?? '';
    if (!empty($email)) {
      if (preg_match('/@mail\.ru|@yandex\.|course-fitness\.com|wowzilla\.ru/isu', $email)) {
        return true;
      }
    }
    return false;
  }

  public function sanitize($value)
  {
    return array(
      'email' => sanitize_email($value['email'])
    );
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $email_inputs = $this->get_setting_value('email_inputs', array());

    foreach ($email_inputs as &$component) {
      foreach ($component as $key => &$val) {

        if ($key == 'enable') {
          $val = mfget_bool_value($val);
        }

        if ($key == 'default' || $key == 'desc' || $key == 'placeholder') {
          $val = wp_strip_all_tags($val);
        }
      }
    }

    $emailComponents = $this->get_email_components($email_inputs);

    $sanitized['email_inputs'] = $emailComponents;

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Email());
