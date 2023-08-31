<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Password field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Password extends MF_Field
{

  public $type = 'password';
  public $isCompoundField = true;
  public $editorSettings = array(
    'general' => array(
      'password_inputs'
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
    return esc_attr__('Password', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-key';
  }
  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $notices = mfget('compound_notices', $args, false);

    # Build field markup
    $emailSettings   = $this->get_setting_value('password_inputs');
    $passComponents  = $this->get_password_components($emailSettings, $value);
    $sub_labels_pos  = $this->get_setting_value('field_sub_label_position');
    $field_size      = $this->get_setting_value('field_size', 'half');

    $passwordHTML = '';
    foreach ($passComponents as $aKey => $aVal) {

      $enabled = $aVal['enable'];

      # Only load field if enabled on the front end, and always load it in the backend.
      if ($enabled || $this->is_editor) {

        $subArgs = array();
        $subArgs['id'] = sprintf('%s[%s]', $args['id'], $aKey);
        $subArgs['tag'] = 'span';
        $subArgs['desc'] = !empty($aVal['desc']) ? $aVal['desc'] : $aVal['label'];
        $subArgs['desc_position'] = $sub_labels_pos;
        $subArgs['placeholder'] = $aVal['placeholder'];
        $subArgs['notice'] = isset($notices[$aKey]) && !isset($args['notice']) ? $notices[$aKey] : false;
        $subArgs['notice_css_class'] = isset($notices[$aKey]) ? true : false;
        // Define classes
        $class = '';
        $class .= 'mf_sub_' . $aKey;
        if ($aKey == 'password') {
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
        $input_type = $this->is_editor ? 'text' : 'password';
        $inputHTML = get_mf_input($input_type, array('name' => $subArgs['id'], 'placeholder' => $subArgs['placeholder'], 'autocomplete' => 'new-password'));

        $subArgs['content'] = $inputHTML;

        $passwordHTML .= mfinput('subcustom', $subArgs, $this->is_editor);
      }
    }

    # retrieve and return the input markup
    $args['content'] = $passwordHTML;
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
  protected function password_inputs()
  {

    $label      = __('Password Inputs', 'megaforms');
    $field_key = 'password_inputs';
    $desc      = __('Select the fields you want to display and customize them as needed by entering the appropriate values in their respective inputs.', 'megaforms');
    $value     = $this->get_setting_value($field_key);

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $passComponents = $this->get_password_components($value);

    $password_inputs = '';
    $password_inputs .= '<table class="mf-field-inputs"><tbody>';

    $password_inputs .= '<tr>';

    $password_inputs .= '<td class="mf-input-header-enable">' . __('Enable', 'megaforms') . '</td>';

    $navigate_input_settings = $this->get_js_helper_rules('none', 'navigate_input_settings', true);
    $password_inputs .= '<td class="mf-input-header-customize">';
    $password_inputs .= '<button class="previous" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-back"></i></button>';
    $password_inputs .= '<span data-type="desc" class="first desc active">' . __('Sub Labels', 'megaforms') . '</span>';
    $password_inputs .= '<span data-type="placeholder" class="last placeholder">' . __('Placeholders', 'megaforms') . '</span>';
    $password_inputs .= '<button class="next" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-forward"></i></button>';
    $password_inputs .= '</td>';

    $password_inputs .= '</tr>';

    foreach ($passComponents as $pKey => $pVal) {

      $name = sprintf('%s[%s]', $args['id'], $pKey);

      $switch_args        = array(
        'id'            => $name . '[enable]',
        'label'         => $pVal['label'],
        'value'         => $pVal['enable'],
        'size'          => 'small',
        'labelRight'    => true,
        'attributes'    => array(),
        'wrapper_class' => 'mf-field-inputs-switch',
      );
      if ($pKey == 'password') {
        $switch_args['onchange_edit'] = $this->get_js_helper_rules('prevent_switch_off', true);
      } else {
        $switch_args['onchange_preview'] = $this->get_js_helper_rules('.mf_sub_' . $pKey, 'toggle_field_inputs', true);
      }
      $update_sublabel    = $this->get_js_helper_rules('.mf_sub_' . $pKey, 'update_sub_label', true);
      $update_placeholder = $this->get_js_helper_rules('.mf_sub_' . $pKey . ' :input', 'update_placeholder', true);

      $password_inputs .= sprintf('<tr data-key="%s">', $pKey);

      $password_inputs .= '<td>';
      $password_inputs .= mfinput('switch', $switch_args, true);
      $password_inputs .= '</td>';

      $password_inputs .= '<td>';
      # Sub Label Field
      $password_inputs .= get_mf_input('text', array('name' => sprintf('%s[desc]', $name), 'value' => $pVal['desc'], 'class' => 'desc active', 'placeholder' => $pVal['label'], 'data-default' => $pVal['label'], 'data-preview' => $update_sublabel));
      # Placeholder Field
      $password_inputs .= get_mf_input('text', array('name' => sprintf('%s[placeholder]', $name), 'value' => $pVal['placeholder'], 'class' => 'placeholder', 'data-preview' => $update_placeholder));
      $password_inputs .= '</td>';

      $password_inputs .= '</tr>';
    }

    $password_inputs .= '</tbody></table>';

    $args['content'] = $password_inputs;
    $input = mfinput('custom', $args, true);
    return $input;
  }

  /**********************************************************************
   **************************** Helpers *********************************
   **********************************************************************/

  public function get_password_components($settingValues, $displayValues = array())
  {

    $components = array(
      'password' => array(
        'label'         => __('Password', 'megaforms'),
        'enable'        => isset($settingValues['password']['enable']) && $settingValues['password']['enable'] ? true : false,
        'desc'          => isset($settingValues['password']['desc']) && !empty($settingValues['password']['desc'])  ? $settingValues['password']['desc'] : '',
        'placeholder'   => isset($settingValues['password']['placeholder']) && !empty($settingValues['password']['placeholder'])  ? $settingValues['password']['placeholder'] : '',
        'is_required'   => true,
      ),
      'password_confirmation' => array(
        'label'         => __('Password Confirmation', 'megaforms'),
        'enable'        => isset($settingValues['password_confirmation']['enable']) && $settingValues['password_confirmation']['enable'] ? true : false,
        'desc'          => isset($settingValues['password_confirmation']['desc']) && !empty($settingValues['password_confirmation']['desc'])  ? $settingValues['password_confirmation']['desc'] : '',
        'placeholder'   => isset($settingValues['password_confirmation']['placeholder']) && !empty($settingValues['password_confirmation']['placeholder'])  ? $settingValues['password_confirmation']['placeholder'] : '',
        'is_required'   => true,
      ),
    );

    // Include password value in entry editor and don't display password confirmation
    if (mf_api()->is_page('mf_entry_view')) {
      $components['password']['value'] = isset($displayValues['password']) ? $displayValues['password'] : '';
      unset($components['password_confirmation']);
    }
    // Make sure all sub fields are enabled by default if not already saved to database ( when adding new field to the form )
    if (empty($settingValues)) {
      foreach ($components as $key => $val) {
        $components[$key]['enable'] = true;
      }
    }

    return apply_filters('mf_password_components', $components);
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/

  protected function compound_required_check($value)
  {

    $field_settings = $this->get_setting_value('password_inputs');
    $password_components = $this->get_password_components($field_settings, $value);

    return $this->compound_required_check_helper($password_components, $value);
  }

  public function validate($value, $context = '')
  {

    $field_settings = $this->get_setting_value('password_inputs');
    $pass_components = $this->get_password_components($field_settings, $value);

    // If the validation context is the entry edit page, set confirmation field to false.
    if ($context == 'entry') {
      $is_confirmation_enabled = false;
    } else {
      $is_confirmation_enabled = $pass_components['password_confirmation']['enable'];
    }

    if (preg_match("/^(?!.*(\&\#|<[!a-z\/\?])).*$/", $value['password'])) {
      if ($is_confirmation_enabled) {
        if ($value['password'] !== $value['password_confirmation']) {
          return array(
            'notice' => __('The entered passwords do not match.', 'megaforms'),
            'notice_code' => 'invalid_password_confirmation',
          );
        }
      }
    } else {
      return array(
        'notice' => __('The entered password is not valid.', 'megaforms'),
        'notice_code' => 'invalid_password',
      );
    }

    return true;
  }
  public function sanitize($value)
  {
    return array(
      'password' => esc_attr($value['password'])
    );
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $password_inputs = $this->get_setting_value('password_inputs', array());

    foreach ($password_inputs as &$component) {
      foreach ($component as $key => &$val) {

        if ($key == 'enable') {
          $val = mfget_bool_value($val);
        }

        if ($key == 'default' || $key == 'desc' || $key == 'placeholder') {
          $val = wp_strip_all_tags($val);
        }
      }
    }

    $passComponents = $this->get_password_components($password_inputs);

    $sanitized['password_inputs'] = $passComponents;

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Password());
