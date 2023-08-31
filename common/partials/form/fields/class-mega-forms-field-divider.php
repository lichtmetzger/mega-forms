<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Divider field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Divider extends MF_Field
{

  public $type = 'divider';
  public $isStaticField = true;

  public function get_field_title()
  {
    return esc_attr__('Divider', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-arrows-h';
  }
  public $editorSettings = array(
    'general' => array(
      'divider_options',
      'is_transparent'
    ),
  );
  public $editorExceptions = array(
    'field_label',
    'field_label_visibility',
    'field_required',
    'field_placeholder',
    'field_css_class',
    'field_description_position',
    'field_description',
    'field_default',
    'field_visibility'
  );

  public function get_field_display($value = null)
  {

    $dividerOptions = $this->get_setting_value('divider_options');
    $is_transparent = $this->get_setting_bool_value('is_transparent');

    $styles = '';

    if (isset($dividerOptions['height'])) {
      $styles .= 'height: ' . $dividerOptions['height'] . 'px;';
    }
    if (isset($dividerOptions['width'])) {
      $styles .= 'width: ' . $dividerOptions['width'] . '%;';
    }
    if (isset($dividerOptions['margin-top'])) {
      $styles .= 'margin-top: ' . $dividerOptions['margin-top'] . 'px;';
    }
    if (isset($dividerOptions['margin-bottom'])) {
      $styles .= 'margin-bottom: ' . $dividerOptions['margin-bottom'] . 'px;';
    }

    if ($is_transparent) {
      $styles .= 'border-color: transparent';
    }

    $divider = '<div class="mf-form-divider" style="' . $styles . '"></div>';

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    unset($args['label']);
    $args['content']  = $divider;
    # retrieve and return the input markup
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
  protected function divider_options()
  {

    $label       = __('Divider Options', 'megaforms');
    $field_key  = 'divider_options';
    $desc       = __('Customize the divider.', 'megaforms');
    $value    = $this->get_setting_value($field_key);

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $width  = isset($value['width']) ? $value['width'] : 100;
    $height  = isset($value['height']) ? $value['height'] : 1;
    $marginTop  = isset($value['margin-top']) ? $value['margin-top'] : 20;
    $marginBottom = isset($value['margin-bottom']) ? $value['margin-bottom'] : 20;

    $update_styles = $this->get_js_helper_rules('.mf-form-divider', 'update_divider_styles', true);

    $divider_options = '';
    $divider_options .= '<table class="mf-divider-options"><tbody>';

    $divider_options .= '<tr>';
    $divider_options .= '<td class="mf-divider-options-header">' . __('Height', 'megaforms') . '</td>';
    $divider_options .= '<td class="mf-divider-options-header">' . __('Width', 'megaforms') . '</td>';
    $divider_options .= '</tr>';

    $divider_options .= '<tr>';
    $divider_options .= '<td>';
    $divider_options .= get_mf_input('number', array('name' => sprintf('%s[height]', $args['id']), 'value' => $height, ' data-style' => 'height', 'data-preview' => $update_styles));
    $divider_options .= '<span class="mf-divider-px-txt">px</span>';
    $divider_options .= '</td>';

    $divider_options .= '<td>';
    $divider_options .= get_mf_input('number', array('name' => sprintf('%s[width]', $args['id']), 'value' => $width, ' min' => '50', ' max' => '100', ' data-style' => 'width', 'data-preview' => $update_styles));
    $divider_options .= '<span class="mf-divider-px-txt">%</span>';
    $divider_options .= '</td>';
    $divider_options .= '</tr>';


    $divider_options .= '<tr>';
    $divider_options .= '<td class="mf-divider-options-header">' . __('Top Margin', 'megaforms') . '</td>';
    $divider_options .= '<td class="mf-divider-options-header">' . __('Bottom Margin', 'megaforms') . '</td>';
    $divider_options .= '</tr>';

    $divider_options .= '<tr>';
    $divider_options .= '<td>';
    $divider_options .= get_mf_input('number', array('name' => sprintf('%s[margin-top]', $args['id']), 'value' => $marginTop, ' min' => '20', ' data-style' => 'margin-top', 'data-preview' => $update_styles));
    $divider_options .= '<span class="mf-divider-px-txt">px</span>';
    $divider_options .= '</td>';

    $divider_options .= '<td>';
    $divider_options .= get_mf_input('number', array('name' => sprintf('%s[margin-bottom]', $args['id']), 'value' => $marginBottom, ' min' => '20', ' data-style' => 'margin-bottom', 'data-preview' => $update_styles));
    $divider_options .= '<span class="mf-divider-px-txt">px</span>';
    $divider_options .= '</td>';

    $divider_options .= '</tr>';

    $divider_options .= '</tbody></table>';

    $args['content'] = $divider_options;
    $input = mfinput('custom', $args, true);
    return $input;
  }


	/**
	 * Returns the display for field main options.
	 *
	 * @return string
	 */
	protected function is_transparent()
	{

		$label = __('Transparent', 'megaforms');
		$desc = __('Enable this option to make the divider invisible, this will maintain the empty space without showing the actual divider.', 'megaforms');
		$field_key = 'is_transparent';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_bool_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('.mf-form-divider', 'change_border_color', array('color' => 'transparent'));

		$input = mfinput('switch', $args, true);
		return $input;
	}
  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $divider_options = $this->get_setting_value('divider_options');
    if (!empty($divider_options) && is_array($divider_options)) {
      $sanitized['divider_options'] = array();
      foreach ($divider_options as $key => &$val) {
        $val = filter_var($val, FILTER_SANITIZE_NUMBER_INT);
      }
    }
    $sanitized['divider_options'] = $divider_options;
    $sanitized['is_transparent'] = $this->get_setting_bool_value('is_transparent');

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Divider());
