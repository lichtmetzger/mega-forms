<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Name field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Name extends MF_Field
{

  public $type  = 'name';
  public $editorSettings = array(
    'general' => array(
      'name_inputs'
    ),
    'display' => array(
      'field_sub_label_position'
    ),
  );
  public $editorExceptions = array(
    'field_placeholder',
    'field_default',
  );
  public $isCompoundField = true;
  public function get_field_title()
  {
    return esc_attr__('Name', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-user';
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value !== null ? $value : array();
    $notices = mfget('compound_notices', $args, false);

    # Build field markup
    $nameSettings  = $this->get_setting_value('name_inputs');
    $nameComponents = $this->get_name_components($nameSettings, $args['value']);
    $sub_labels_pos = $this->get_setting_value('field_sub_label_position');

    $nameHTML = '';
    $nameHTML .= '<table><tr>';
    foreach ($nameComponents as $aKey => $aVal) {

      $enabled = $aVal['enable'];

      # Only load field if enabled on the front end, and always load it in the backend.
      if ($enabled || $this->is_editor) {

        $subArgs = array();
        $subArgs['id'] = sprintf('%s[%s]', $args['id'], $aKey);
        $subArgs['wrapper_tag'] = 'td';
        $subArgs['desc'] = !empty($aVal['desc']) ? $aVal['desc'] : $aVal['label'];
        $subArgs['desc_position'] = $sub_labels_pos;
        $subArgs['value'] = $aVal['value'] !== null ? $aVal['value'] : $aVal['default'];
        $subArgs['placeholder'] = $aVal['placeholder'];
        $subArgs['notice'] = isset($notices[$aKey]) && !isset($args['notice']) ? $notices[$aKey] : false;
        $subArgs['notice_css_class'] = isset($notices[$aKey]) ? true : false;

        $class = '';
        $class .= ' mf_sub_' . $aKey;
        // Hide the field on the preview screen if not enabled
        if (!$enabled) {
          $class .= ' mf_hidden';
        }
        $subArgs['wrapper_class'] = $class;

        // Define input
        $inputHTML = '';
        if ('prefix' == $aKey) {
          $inputHTML .= get_mf_select(array('name' => $subArgs['id'], 'value' => $subArgs['value'], 'placeholder' => $subArgs['placeholder']), $this->get_name_prefixes());
        } else {
          $inputHTML .= get_mf_input('text', array('name' => $subArgs['id'], 'value' => $subArgs['value'], 'placeholder' => $subArgs['placeholder']));
        }

        $subArgs['content'] = $inputHTML;

        $nameHTML .= mfinput('subcustom', $subArgs, $this->is_editor);
      }
    }
    $nameHTML .= '</tr></table>';

    # retrieve and return the input markup
    $args['content'] = $nameHTML;
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
  protected function name_inputs()
  {

    $label     = __('Name Inputs', 'megaforms');
    $field_key = 'name_inputs';
    $desc     = __('Select the fields you want to display and customize them as needed by entering the appropriate values in their respective inputs.', 'megaforms');

    $value                = $this->get_setting_value($field_key);
    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $nameComponents = $this->get_name_components($value);

    $name_inputs = '';
    $name_inputs .= '<table class="mf-field-inputs"><tbody>';

    $name_inputs .= '<tr>';

    $name_inputs .= '<td class="mf-input-header-enable">' . __('Enable', 'megaforms') . '</td>';

    $navigate_input_settings = $this->get_js_helper_rules('none', 'navigate_input_settings');
    $name_inputs .= '<td class="mf-input-header-customize">';
    $name_inputs .= '<button class="previous" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '"><i class="mega-icons-arrow-back"></i></button>';
    $name_inputs .= '<span data-type="desc" class="first desc active">' . __('Sub Labels', 'megaforms') . '</span>';
    $name_inputs .= '<span data-type="default" class="default">' . __('Default Values', 'megaforms') . '</span>';
    $name_inputs .= '<span data-type="placeholder" class="last placeholder">' . __('Placeholders', 'megaforms') . '</span>';
    $name_inputs .= '<button class="next" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-forward"></i></button>';
    $name_inputs .= '</td>';

    $name_inputs .= '</tr>';

    foreach ($nameComponents as $aKey => $aVal) {

      $name = sprintf('%s[%s]', $args['id'], $aKey);
      $toggle_fields      = $this->get_js_helper_rules('.mf_sub_' . $aKey, 'toggle_field_inputs');
      $update_sublabel    = $this->get_js_helper_rules('.mf_sub_' . $aKey, 'update_sub_label', true);
      $update_value       = $this->get_js_helper_rules('.mf_sub_' . $aKey . ' :input', 'update_value', true);
      $update_placeholder = $this->get_js_helper_rules('.mf_sub_' . $aKey . ' :input', 'update_placeholder', true);

      $name_inputs .= sprintf('<tr data-key="%s">', $aKey);
      $name_inputs .= '<td>';
      $name_inputs .= mfinput('switch', array(
        'id'            => $name . '[enable]',
        'label'         => $aVal['label'],
        'value'         => $aVal['enable'],
        'size'          => 'small',
        'labelRight'    => true,
        'onchange_preview'    => $toggle_fields,
        'wrapper_class' => 'mf-field-inputs-switch',
      ), true);
      $name_inputs .= '</td>';

      $name_inputs .= '<td>';

      # Sub Label Field
      $name_inputs .= get_mf_input('text', array('name' => sprintf('%s[desc]', $name), 'value' => $aVal['desc'], 'class' => 'desc active', 'placeholder' => $aVal['label'], 'data-default' => $aVal['label'], 'data-preview' => $update_sublabel));
      # Default Value Field
      if ('prefix' == $aKey) {
        $name_inputs .= get_mf_select(array('name' => sprintf('%s[default]', $name), 'value' => $aVal['default'], 'placeholder' => '', 'class' => 'default', 'data-preview' => $update_value), $this->get_name_prefixes());
      } else {
        $name_inputs .= get_mf_input('text', array('name' => sprintf('%s[default]', $name), 'value' => $aVal['default'], 'class' => 'default', 'data-preview' => $update_value));
      }
      # Placeholder Field
      $name_inputs .= get_mf_input('text', array('name' => sprintf('%s[placeholder]', $name), 'value' => $aVal['placeholder'], 'class' => 'placeholder', 'data-preview' => $update_placeholder));

      $name_inputs .= '</td>';

      $name_inputs .= '</tr>';
    }

    $name_inputs .= '</tbody></table>';

    $args['content'] = $name_inputs;
    $input = mfinput('custom', $args, true);
    return $input;
  }

  /**********************************************************************
   ************************* Helpers ******************************
   **********************************************************************/
  public function get_name_prefixes()
  {
    $prefixes = array(
      'mr' => __('Mr.', 'megaforms'),
      'mrs' => __('Mrs.', 'megaforms'),
      'ms' => __('Ms.', 'megaforms'),
      'miss' => __('Miss', 'megaforms'),
      'dr' => __('Dr.', 'megaforms'),
      'prof' => __('Prof.', 'megaforms'),
    );

    return apply_filters('mf_name_prefixes', $prefixes);
  }
  public function get_name_components($settingValues, $displayValues = array())
  {

    $components = array(
      'prefix'  => array(
        'label'         => __('Prefix', 'megaforms'),
        'enable'        => isset($settingValues['prefix']['enable']) && $settingValues['prefix']['enable'] ? true : false,
        'default'       => isset($settingValues['prefix']['default']) && !empty($settingValues['prefix']['default'])  ? $settingValues['prefix']['default'] : '',
        'desc'          => isset($settingValues['prefix']['desc']) && !empty($settingValues['prefix']['desc'])  ? $settingValues['prefix']['desc'] : '',
        'placeholder'   => isset($settingValues['prefix']['placeholder']) && !empty($settingValues['prefix']['placeholder'])  ? $settingValues['prefix']['placeholder'] : '',
        'value'         => isset($displayValues['prefix']) && !empty($displayValues['prefix'])  ? $displayValues['prefix'] : null,
        'is_required'   => false,
      ),
      'first_name' => array(
        'label'         => __('First Name', 'megaforms'),
        'enable'        => isset($settingValues['first_name']['enable']) && $settingValues['first_name']['enable'] ? true : false,
        'default'       => isset($settingValues['first_name']['default']) && !empty($settingValues['first_name']['default'])  ? $settingValues['first_name']['default'] : '',
        'desc'          => isset($settingValues['first_name']['desc']) && !empty($settingValues['first_name']['desc'])  ? $settingValues['first_name']['desc'] : '',
        'placeholder'   => isset($settingValues['first_name']['placeholder']) && !empty($settingValues['first_name']['placeholder'])  ? $settingValues['first_name']['placeholder'] : '',
        'value'         => isset($displayValues['first_name']) && !empty($displayValues['first_name'])  ? $displayValues['first_name'] : null,
        'is_required'   => true,
      ),
      'middle_name' => array(
        'label'         => __('Middle Name', 'megaforms'),
        'enable'        => isset($settingValues['middle_name']['enable']) && $settingValues['middle_name']['enable'] ? true : false,
        'default'       => isset($settingValues['middle_name']['default']) && !empty($settingValues['middle_name']['default'])  ? $settingValues['middle_name']['default'] : '',
        'desc'          => isset($settingValues['middle_name']['desc']) && !empty($settingValues['middle_name']['desc'])  ? $settingValues['middle_name']['desc'] : '',
        'placeholder'   => isset($settingValues['middle_name']['placeholder']) && !empty($settingValues['middle_name']['placeholder'])  ? $settingValues['middle_name']['placeholder'] : '',
        'value'         => isset($displayValues['middle_name']) && !empty($displayValues['middle_name'])  ? $displayValues['middle_name'] : null,
        'is_required'   => true,
      ),
      'last_name' => array(
        'label'         => __('Last Name', 'megaforms'),
        'enable'        => isset($settingValues['last_name']['enable']) && $settingValues['last_name']['enable'] ? true : false,
        'default'       => isset($settingValues['last_name']['default']) && !empty($settingValues['last_name']['default'])  ? $settingValues['last_name']['default'] : '',
        'desc'          => isset($settingValues['last_name']['desc']) && !empty($settingValues['last_name']['desc'])  ? $settingValues['last_name']['desc'] : '',
        'placeholder'   => isset($settingValues['last_name']['placeholder']) && !empty($settingValues['last_name']['placeholder'])  ? $settingValues['last_name']['placeholder'] : '',
        'value'         => isset($displayValues['last_name']) && !empty($displayValues['last_name'])  ? $displayValues['last_name'] : null,
        'is_required'   => true,
      ),
    );

    // Make sure all sub fields are enabled by default if not already saved to database ( when adding new field to the form )
    if (empty($settingValues)) {
      foreach ($components as $key => $val) {
        if ($key !== 'middle_name') {
          $components[$key]['enable'] = true;
        } else {
          $components[$key]['enable'] = false;
        }
      }
    }

    return apply_filters('mf_name_components', $components);
  }

  public function get_formatted_value_short($value)
  {

    return esc_html(implode(' ', $value));
  }

  public function get_formatted_value_long($value)
  {

    return esc_html(implode(' ', $value));
  }
  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/

  public function is_spam($value)
  {
    // Regex check
    if (is_array($value)) {
      foreach ($value as $name_part) {
        if (empty($name_part)) {
          continue;
        }
        if (preg_match('/Henrynal|Crytonal|eric jones|moncler|north face|vuitton|handbag|burberry|outlet|prada|cialis|viagra|maillot|oakley|ralph lauren|ray ban|iphone|プラダ/isu', $name_part)) {
          return true;
        }
      }
    }
    return false;
  }

  protected function compound_required_check($value)
  {

    $field_settings = $this->get_setting_value('name_inputs');
    $name_components = $this->get_name_components($field_settings, $value);

    return $this->compound_required_check_helper($name_components, $value);
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $name_inputs = $this->get_setting_value('name_inputs', array());

    foreach ($name_inputs as &$component) {
      foreach ($component as $key => &$val) {

        if ($key == 'enable') {
          $val = mfget_bool_value($val);
        }

        if ($key == 'default' || $key == 'desc' || $key == 'placeholder') {
          $val = wp_strip_all_tags($val);
        }
      }
    }

    $nameComponents = $this->get_name_components($name_inputs);

    $sanitized['name_inputs'] = $nameComponents;

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Name());
