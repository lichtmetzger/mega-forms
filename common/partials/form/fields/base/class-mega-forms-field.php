<?php

/**
 * @link       https://wpali.com
 * @since      1.0.6
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Main field wrapper
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields/base
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Field
{

	/**
	 * The field key ( type )
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Get field group ( Where the button will be placed )
	 * 
	 *	@var string
	 */
	public $group = 'basic_fields';

	/**
	 * Array of all data required for the field to work ( automatically set by the caller function )
	 *
	 * @var array
	 */
	public $field = array();

	/**
	 * Current field ID ( automatically set by the caller function )
	 *
	 * @var int
	 */
	public $field_id = 0;

	/**
	 * Current form ID ( automatically set by the caller function )
	 *
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * list of custom settings that you want added to the field editor presented with the associated tab key
	 *
	 * @var array
	 */
	public $editorSettings = array();

	/**
	 * list the names of the options that you want excluded from the field editor
	 *
	 * @var array
	 */
	public $editorExceptions = array();

	/**
	 * Custom added settings ( settings added via MF_Extender )
	 *
	 * @var array
	 */
	public $editorCustomSettings = array();

	/**
	 * Does this field contain multiple inputs or display a different number of fields
	 *
	 * @var bool
	 */
	public $isCompoundField = false;

	/**
	 * Should we completely ignore this field in the submission process
	 *
	 * @var bool
	 */
	public $isStaticField = false;
	/**
	 * Should we hide the field on the frontend
	 *
	 * @var bool
	 */
	public $isHiddenField = false;

	/**
	 * Does this field require extra JS files to work properly
	 *
	 * @var bool
	 */
	public $hasJSDependency = false;
	/**
	 * Does this field require extra CSS files to work properly
	 *
	 * @var bool
	 */
	public $hasCSSDependency = false;
	/**
	 * Whether a specific task should run after a succesfull submission of this field
	 * If set to `true` the method `post_submission_task` will be triggered after a succesfull form submission
	 *
	 * @var bool
	 */
	public $hasPostSubmissionTask = false;
	/**
	 * Whether this class was called in the editor view or not
	 *
	 * @var bool
	 */
	public $is_editor = false;

	/**
	 * Constructor.
	 */
	public function __construct($params = array())
	{
		if (empty($params) || !is_array($params)) {
			return;
		}
		foreach ($params as $key => $value) {
			$this->{$key} = $value;
		}
	}

	/**
	 *  Get the field title.
	 *
	 * 	@return string
	 */
	public function get_field_title()
	{
		return $this->type;
	}
	/**
	 *  Get the field icon.
	 *
	 *	@return string
	 */
	public function get_field_icon()
	{
		return $this->type;
	}
	/**
	 *  Get field javascript dependencies.
	 *
	 *	@return array
	 */
	public function get_field_js_dependencies()
	{
		return array();
	}
	/**
	 *  Get field CSS dependencies.
	 *
	 *	@return array
	 */
	public function get_field_css_dependencies()
	{
		return array();
	}
	/**
	 * Returns tab names on the field editor.
	 * @return array
	 */
	public function get_editor_tabs()
	{
		return array(
			'general' => __('General', 'megaforms'),
			'display' => __('Display', 'megaforms'),
			'advanced' => __('Advanced', 'megaforms'),
		);
	}

	/**
	 * Returns the elements that will appear on each field editor's tab.
	 *
	 * @return array
	 */
	public function get_editor_options()
	{

		// Structure: tab_key => array( setting_callbak => priority );
		// Note: If priority is not set, default will be 100
		$settings = array(
			'general' => array(
				'field_label' => array(
					'priority' => 10,
					'size' => 'half-left'
				),
				'field_required' => array(
					'priority' => 20,
					'size' => 'half-right'
				),
			),
			'display' => array(
				'field_label_visibility' => array(
					'priority' => 10,
					'size' => 'half-left'
				),
				'field_description_position' => array(
					'priority' => 20,
					'size' => 'half-right'
				),
				'field_description' => array(
					'priority' => 30
				),
				'field_css_class' => array(
					'priority' => 40
				),
			),
			'advanced' => array(
				'field_default' => array(
					'priority' => 10
				),
				'field_placeholder' => array(
					'priority' => 20
				),
				'field_visibility' => array(
					'priority' => 30
				),
			),
		);


		/*
		 * Add custom settings to the related tab ( options added using extender ) 
		 *
		 * @see MF_Extender
		 */

		$custom_settings = MF_Extender::get_field_options();
		if (!empty($custom_settings)) {
			foreach ($custom_settings as $optionObj) {
				$option_display_logic = $optionObj->get_option_display_logic($this);
				$display = null;

				if (is_array($option_display_logic)) {
					if (isset($option_display_logic['include'])) {
						$display = false;
						if (in_array($this->type, $option_display_logic['include'])) {
							$display = true;
						}
					} else if ($option_display_logic['exclude']) {
						$display = true;
						if (in_array($this->type, $option_display_logic['exclude'])) {
							$display = false;
						}
					}
				} else {
					$display = apply_filters('mf_add_' . $optionObj->type . '_field_option', $display, $this);
				}

				if ($display) {
					$settings[$optionObj->tab][$optionObj->type] = array(
						'priority' => $optionObj->priority,
						'callback' => array($optionObj, 'get_option_display'),
						'callback_args' => array($this)
					);

					// Add to the custom settings property ( Will be used later for sanitization & adding field arguement )
					$this->editorCustomSettings[] = $optionObj->type;
				}
			}
		}

		// Combine any additional options with default options
		foreach ($settings as $sKey => $sVal) {

			// Add field-specific settings ( custom ones )
			if (isset($this->editorSettings[$sKey])) {
				$settings[$sKey] = array_merge($settings[$sKey], $this->editorSettings[$sKey]);
			}

			// Prepare the options array ( add any missing options, or unset the option if not valid )
			if (!empty($settings[$sKey])) {
				foreach ($settings[$sKey] as $key => $val) {
					if (is_numeric($key) && !is_array($val)) {
						// If the option doesn't have array options, set the key as a `callback` and 99 as `priority` by default
						$settings[$sKey][$val] = array(
							'priority' => 99,
							'callback' => $val
						);
						unset($settings[$sKey][$key]);
					} else if (!is_numeric($key) && is_array($val) && (!isset($settings[$sKey][$key]['callback']) || !isset($settings[$sKey][$key]['priority']))) {
						// If an array is provided but it's missing the main keys, add them
						if (!isset($settings[$sKey][$key]['callback'])) $settings[$sKey][$key]['callback'] = $key;
						if (!isset($settings[$sKey][$key]['priority'])) $settings[$sKey][$key]['priority'] = 99;
					} else if (!isset($settings[$sKey][$key]['callback']) || !isset($settings[$sKey][$key]['priority'])) {
						// unset this array element if it's in the wrong format
						unset($settings[$sKey][$key]);
					}
				}
			}
			// Order options by priority
			uasort($settings[$sKey], function ($a, $b) {
				return $a['priority'] <=> $b['priority'];
			});

			// Remove excluded settings
			foreach ($sVal as $svKey => $svVal) {
				if (in_array($svKey, $this->editorExceptions)) {
					unset($settings[$sKey][$svKey]);
				}
			}

			// Remove the tab index if no settings available for it.
			if (empty($settings[$sKey])) {
				unset($settings[$sKey]);
			}
		}


		return apply_filters('mf_field_editor_options', $settings, $this->field);
	}

	/**
	 * Return the field markup for backend and front end.
	 *
	 * @param   mixed   $value     input value if available.
	 * @param   bool    whether this input is effected by template overrides or not ( Always protect in back-end context ).
	 * @return  string  the field markup.
	 */
	public function get_field_display($value = '')
	{
		return '';
	}
	/**
	 * Return the field setting tabs for backend.
	 * @return string
	 */
	public function get_editor_content()
	{


		# Available tabs and settings
		$tabs    = $this->get_editor_tabs();
		$settings = $this->get_editor_options();

		$html = "";
		$html .= '<div class="field_options">';

		# Tab buttons
		$html .= '<ul id="mg-tabs" class="mg-tabs">';
		foreach ($tabs as $tabkey => $tabname) {
			if (isset($settings[$tabkey])) {
				$html .= sprintf('<li class="tabs"><a href="#%s">%s</a></li>', $tabkey, $tabname);
			}
		}
		$html .= '</ul>';

		# Tab content
		foreach ($tabs as $tabkey => $tabvalue) {

			if (isset($settings[$tabkey])) {

				$html .= sprintf('<div id="%s" class="tabs-panel">', $tabkey);

				foreach ($settings[$tabkey] as $key => $option) {
					if (!isset($option['callback'])) {
						continue;
					}

					$size = isset($option['size']) ? $option['size'] : 'full';

					$html .= sprintf('<div class="mf-inner-field mf-inner-%s mf-inner-size-%s">', $key, $size);
					// If a string is provided (only method name), then check if the method exists and call it from the current class, otherwise, keep it as is.
					if (!is_array($option['callback'])) {
						if (method_exists($this, $option['callback'])) {
							$option['callback'] = array($this, $option['callback']);
						}
					}

					if (isset($option['callback_args']) && !empty($option['callback_args'])) {
						$html .= call_user_func_array($option['callback'], $option['callback_args']);
					} else {
						$html .= call_user_func($option['callback']);
					}

					$html .= '</div>';
				}

				$html .= '</div>';
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 *  Return the field along with settings controls if in editor view.
	 *
	 * @return string
	 */
	public function get_the_field($value = '', $is_editor = false)
	{

		$this->is_editor = $is_editor;

		$field_input = $this->get_field_display($value);
		$classes = $this->get_field_container_classes();
		$styles = $this->get_field_container_inline_styles();

		# Fornt-end Only: Make sure the field is not loaded if visibility is set to administrator and current user is not admin
		if ($this->is_editor === false  && ($this->get_setting_value('field_visibility') == 'administrator' && !current_user_can('administrator'))) {
			return;
		}

		if ($this->is_editor) {
			$classes .= ' field_preview single_field';
		}


		$field_type_attr = $this->is_editor ? ' data-type="' . $this->type . '"' : '';
		$field_static_attr = $this->is_editor ? ' data-is-static="' . $this->isStaticField . '"' : '';
		$field_style_attr = !empty($styles) ? ' style="' . esc_attr($styles) . '"' : '';
		$field_hidden_attr = !$this->is_editor && $this->isHiddenField ? ' hidden="true"' : '';

		$field_id = $this->field_id === 0 ? $this->type : $this->field_id; // Usefull for dynamically added fields that do not have a stored ID ( anti-spam fields...etc )
		$field_wrapper = '';
		$field_wrapper .= sprintf('<li id="mf_%d_field_%s" data-id="%s"%s%s class="%s"%s%s>', $this->form_id, $field_id, $field_id, $field_type_attr, $field_static_attr, $classes, $field_style_attr, $field_hidden_attr);

		if ($this->is_editor) {

			$field_wrapper .= '<div class="field_controls disable-sorting">
  								<div class="controls right">
  									<a href="#" class="field_control" data-action="duplicate"><span class="mega-icons-copy"></span></a>
  									<a href="#" class="field_control" data-action="delete"><span class="mega-icons-trash-o"></span></a>
  								</div>
  								<div class="mf_clearfix"></div>
  							</div>';
		}

		$field_wrapper .= '{FIELD_INPUT}';

		if ($this->is_editor) {

			$field_wrapper .= '<div class="field_mask"></div>';
			// $field_wrapper .= '<div class="mf_hidden_elements" hidden></div>';

		}

		$field_wrapper .= '</li>';

		$field_wrapper = apply_filters('mf_field_container', $field_wrapper, $this->field);

		return str_replace('{FIELD_INPUT}', $field_input, $field_wrapper);
	}

	/**
	 *  Return field settings controls.
	 *
	 * @return string
	 */
	public function get_field_settings()
	{

		$settings = $this->get_editor_content();
		$title = $this->get_field_title();
		$icon = $this->get_field_icon();

		$html = '';
		$html .= sprintf('<li id="mf_%1$d_field_%2$d_editor" data-type="%3$s" data-is-static="%4$d" data-id="%2$d" class="field_container single_field">', $this->form_id, $this->field_id, $this->type, $this->isStaticField);
		$html .= sprintf('<div class="field_controls">
								<div class="field_title left">
									<span>
  									<span class="field-icon %s"></span>
                    %s
                  </span>
								</div>
                <span class="field_form_id right">(ID: %d)</span>
								<div class="mf_clearfix"></div>
							</div>', $icon, $title, $this->field_id);
		$html .= $settings;
		$html .= '</li>';

		return $html;
	}

	/**
	 * Add the field button to the correct container
	 *
	 * @return array
	 */
	public function add_button_to_container($fields_container)
	{

		$buttonData = array(
			'class'      => 'mg_field',
			'icon'       => $this->get_field_icon(),
			'value'      => $this->get_field_title(),
			'data-type'  => $this->type,
		);

		// Add any missing data to the pre-added fields ( fields are pre added to appear in specific order )
		foreach ($fields_container as $groupKey => $groupVal) {
			foreach ($groupVal['fields'] as $buttonKey => $button) {

				if (isset($button['data-type']) && $button['data-type'] == $this->type) {
					if (count($button) < 3) {
						$fields_container[$groupKey]['fields'][$buttonKey] = $buttonData;
						return $fields_container;
					}
				}
			}
		}
		// If the field position is not already defined, add it to the end of the list
		if (!empty($this->group) && isset($fields_container[$this->group])) {
			$fields_container[$this->group]['fields'][] = $buttonData;
		}

		return $fields_container;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/

	/**
	 * Returns the display for field main options.
	 *
	 * @return string
	 */
	protected function field_label()
	{


		$label = __('Field Label', 'megaforms');
		$desc = __('Enter the label of the form field. This is how users can identify single fields.', 'megaforms');
		$field_key = 'field_label';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('span.mf_label', 'update_label');

		$input = mfinput('text', $args, true);
		return $input;
	}

	/**
	 * Returns the display for field main options.
	 *
	 * @return string
	 */
	protected function field_required()
	{

		$label = __('Required Field', 'megaforms');
		$desc = __('Enable this option to make this field required. Required fields prevent the form from being submitted if it is not completed.', 'megaforms');
		$field_key = 'field_required';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('span.mf_required', 'update_required');

		$input = mfinput('switch', $args, true);
		return $input;
	}

	/**
	 * Returns the placeholder field markup.
	 *
	 * @return string
	 */
	protected function field_placeholder()
	{
		$label = __('Placeholder', 'megaforms');
		$desc = __('Use this to specify a short hint that describes the expected value of this field ', 'megaforms');
		$field_key = 'field_placeholder';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);;
		$args['onchange_preview'] = $this->get_js_helper_rules('input', 'update_placeholder');

		$input = mfinput('text', $args, true);
		return $input;
	}

	/**
	 * Returns the default value field markup .
	 *
	 * @return string
	 */
	protected function field_default()
	{

		$label = __('Default Value', 'megaforms');
		$desc = __('Use this to pre-populate this field value.', 'megaforms');
		$field_key = 'field_default';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('input', 'update_value');
		$args['inline_modal'] = array('form', 'wp', 'misc');

		$input = mfinput('text', $args, true);
		return $input;
	}
	/**
	 * Returns the display for field css class.
	 *
	 * @return string
	 */
	protected function field_css_class()
	{

		$label = __('CSS Classes', 'megaforms');
		$desc = __('Add extra CSS class to this field', 'megaforms');
		$field_key = 'field_class';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);

		$input = mfinput('text', $args, true);
		return $input;
	}

	/**
	 * Returns the field description option.
	 *
	 * @return string
	 */
	protected function field_description()
	{

		$label = __('Description', 'megaforms');
		$desc = __('Write a description to provide users with directions on how this field should be completed.', 'megaforms');
		$field_key = 'field_description';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['onchange_preview'] = $this->get_js_helper_rules('span.mf_description', 'update_desc');

		$input = mfinput('textarea', $args, true);
		return $input;
	}

	/**
	 * Returns the field sub label position option.
	 *
	 * @return string
	 */
	protected function field_description_position()
	{

		$label = __('Description Position', 'megaforms');
		$desc = __('Specify the position of the field description.', 'megaforms');
		$field_key = 'field_description_position';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['options'] = array(
			'top' => __('Above', 'megaforms'),
			'bottom' => __('Below', 'megaforms'),
			'hidden' => __('Hide', 'megaforms'),
		);
		$args['default'] = 'bottom';
		$args['onchange_preview'] = $this->get_js_helper_rules('.mf_description', 'update_descPosition');

		$input = mfinput('radio', $args, true);
		return $input;
	}
	/**
	 * Returns the field label visibility option.
	 *
	 * @return string
	 */
	protected function field_label_visibility()
	{

		$label = __('Field Label Visibility', 'megaforms');
		$desc = __('Choose whether this field label should be visible or hidden.', 'megaforms');
		$field_key = 'field_label_visibility';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['options'] = array(
			'visible' => __('Visible', 'megaforms'),
			'hidden' => __('Hidden', 'megaforms'),
		);
		$args['default'] = 'visible';
		$args['onchange_preview'] = $this->get_js_helper_rules('none', 'update_label_visibility');

		$input = mfinput('radio', $args, true);
		return $input;
	}

	/**
	 * Returns the field sub label position option.
	 *
	 * @return string
	 */
	protected function field_sub_label_position()
	{

		$label = __('Sub-label Position', 'megaforms');
		$desc = __('Specify the position of sub labels contained in this field.', 'megaforms');
		$field_key = 'field_sub_label_position';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);

		$args['options'] 			= array(
			'top' => __('Above', 'megaforms'),
			'bottom' => __('Below', 'megaforms'),
			'hidden' => __('Hide', 'megaforms'),
		);
		$args['default'] 			= 'bottom';
		$args['onchange_preview'] = $this->get_js_helper_rules('none', 'update_subLabelPosition');

		$input = mfinput('radio', $args, true);
		return $input;
	}
	/**
	 * Returns the markup for field size option.
	 *
	 * @return string
	 */
	protected function field_size()
	{

		$label 		= __('Field Size', 'megaforms');
		$field_key = 'field_size';
		$desc 		= __('Select your preferred size from the available options to determine width of the inputs in this field.', 'megaforms');

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['value'] = $this->get_setting_value($field_key);
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['options'] = array(
			'full' => 'Full Width',
			'half' => 'Half Width',
		);
		$args['onchange_preview']  = $this->get_js_helper_rules('.mf_sub_field', 'update_size');
		$args['default'] = 'half';

		$input = mfinput('select', $args, true);
		return $input;
	}
	/**
	 * Returns the field visibility option.
	 *
	 * @return string
	 */
	protected function field_visibility()
	{

		$label = __('Visibility', 'megaforms');
		$desc = __('Select visibility option for this field. Fields are visible to everyone by default, use "Hidden" option to hide this field from users while viewing the form and "Administrator" to only make it available and functional for website admin.', 'megaforms');
		$field_key = 'field_visibility';

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($field_key);
		$args['default'] = 'everyone';
		$args['options'] = array(
			'everyone'       => __('Everyone', 'megaforms'),
			'administrator'  => __('Administrator', 'megaforms'),
			'hidden'         => __('Hidden', 'megaforms'),
		);

		$input = mfinput('select', $args, true);
		return $input;
	}

	/**********************************************************************
	 ***************************** Helpers ********************************
	 **********************************************************************/
	/**
	 *  Return description tooltip markup.
	 *
	 * @return string
	 */
	public function get_description_tip_markup($label, $content)
	{

		$desc_tip = "";
		$desc_tip .= "<span class='mf-tooltip field-tooltip mega-icons-question-circle'>";
		$desc_tip .= sprintf("<span style='display:none;' id='tooltip-content'><h4>%s</h4>%s</span>", $label, $content);
		$desc_tip .= "</span>";

		return $desc_tip;
	}

	/**
	 * Define field container classes based on field specific options.
	 *
	 * @return array
	 */
	protected function get_field_container_classes()
	{

		$classes = array();
		$classes[] = 'mfield';
		$classes[] = 'mfield_' . $this->type;

		// Only add these classes if field is displayed on the front-end
		if (!$this->is_editor) {
			if ($this->get_setting_value('field_required') == 'yes') {
				$classes[] = 'mfield_required';
			}

			if ($this->get_setting_value('field_visibility') == 'hidden') {
				$classes[] = 'mf_hidden';
			}

			$field_custom_classes = $this->get_setting_value('field_class');
			if (!empty($field_custom_classes)) {
				$classes[] = $field_custom_classes;
			}
		}

		$classes = apply_filters('mf_field_container_classes', $classes, $this->field);

		return implode(' ', $classes);
	}
	/**
	 * Define the field container inline styles if necessary.
	 *
	 * @return string
	 */
	protected function get_field_container_inline_styles()
	{
		return '';
	}

	/**
	 * Define input container classes based on field specific options.
	 *
	 * @return array
	 */
	protected function get_input_container_classes()
	{

		$classes = array();
		$classes[] = 'mf_input_container';
		$classes[] = 'mf_input_' . $this->type;

		if ($this->isCompoundField) {
			$classes[] = 'mf_input_compound';
		}

		$classes = apply_filters('mf_input_container_classes', $classes, $this->field);

		return implode(' ', $classes);
	}

	/**
	 * Return a valid field key to be used in the name/id attribute for the HTML markup based on field type.
	 *
	 * @return string
	 */
	public function get_field_key($type = 'display', $field_key = '')
	{

		if ('options' == $type) {
			return sprintf('%s_mfield_%s', $field_key, $this->field_id);
		} else {
			return mf_api()->get_field_key($this->form_id, $this->field_id);
		}
	}
	/**
	 * Return an array of the main input arguements.
	 *
	 * @return array
	 */
	protected function build_field_display_args()
	{

		$params =  call_user_func_array('array_merge', array_values($this->get_editor_options()));

		$args = array();

		# Define field ID/name
		$args['id'] = $this->get_field_key();
		# Define field wrapper classes
		$args['wrapper_class'] = $this->get_input_container_classes();
		# Allow adding extra attributes to the input
		$args['attributes'] = array();

		// Handle custom fields arguements
		if (!empty($this->editorCustomSettings)) {
			foreach ($this->editorCustomSettings as $option_type) {
				$option_args = MF_Extender::get_single_field_option($option_type)->get_field_arguments($this);
				if ($option_args !== false && !empty($option_args)) {
					foreach ($option_args as $option_arg) {
						if (isset($option_arg['key']) && isset($option_arg['value'])) {
							$args[$option_arg['key']] = $option_arg['value'];
						}
					}
				}
			}
		}

		# Use pre-defined settings
		if (array_key_exists('field_label', $params)) {
			$args['label'] = $this->get_setting_value('field_label');
		}

		if (array_key_exists('field_description', $params)) {
			$args['desc'] = $this->get_setting_value('field_description');
		}

		if (array_key_exists('field_description_position', $params)) {
			$args['desc_position'] = $this->get_setting_value('field_description_position');
		}

		if (array_key_exists('field_label_visibility', $params)) {
			if ($this->get_setting_value('field_label_visibility') == 'hidden') {
				$args['label_hidden'] = true;
			}
		}

		if (array_key_exists('field_default', $params)) {
			$field_default = $this->get_setting_value('field_default');
			$args['default'] = $this->is_editor ? $field_default : do_shortcode(mf_merge_tags()->process($field_default));
		}

		if (array_key_exists('field_placeholder', $params)) {
			$args['placeholder'] = $this->get_setting_value('field_placeholder');
		}

		if (array_key_exists('field_required', $params)) {
			if ($this->get_setting_value('field_required') == 'yes') {
				$args['required'] = true;
			}
		}

		/*
		 * If this is a form submission, make sure to include any provided notices.
		 * Since mf_submission() submission is only declared on the frontend, we need to make sure it exists first.
		 * (Compare field form ID against submission form ID to avoid displaying errors on other megaforms on the same page )
		 */
		if (function_exists('mf_submission')) {
			if (!mf_submission()->is_empty() && isset(mf_submission()->form->ID) && mf_submission()->form->ID == $this->form_id) {

				$notice = mf_submission()->get_notice($this->field_id);
				$notice_wrapper_class = '';

				if ($notice !== false) {
					$args['notice'] = $notice;
					$notice_wrapper_class = ' mf_input_error';
				}

				if ($this->isCompoundField) {
					$compound_notices = mf_submission()->get_compound_notices($this->field_id);
					if ($compound_notices !== false) {
						$args['compound_notices'] = $compound_notices;
						$notice_wrapper_class = ' mf_compound_error';
					}
				}

				$args['wrapper_class'] .= $notice_wrapper_class;
			}
		}

		return apply_filters('mf_field_display_args', $args, $this->field);
	}

	/**
	 * Return an json string that is used as data attribute in the HTML elements
	 * to allow real time changes using JS each time an action is performed on the related element ( click, change...etc )
	 *
	 * @return array
	 */
	public function get_js_helper_rules($target, $action, $args = array())
	{

		$obj = array(
			'target' => $target,
			'action' => $action,
		);

		if (!empty($args)) {
			$obj['args'] = $args;
		}

		return json_encode($obj);
	}
	/**
	 * Return an json string that is used as data attribute in the HTML elements
	 * to allow implementing conditionl logic on the associated field
	 *
	 * @param string $action is the action type ( hide | show )
	 * @param array $rules hold the rules for this specific field, it shoud contain ( name & operator ( is|isnot|greaterthan|lessthan|contains|doesnotcontain|beginswith|doesnotbeginwith|endswith|doesnotendwith|isempty|isnotempty  ) & value )
	 * @param string $logic is the logic type ( and | or )
	 * @param string $container the conditional field parent element where you want to perform the hiding/showing action, leave empty to show/hide the field itself
	 * @return string
	 */
	public function get_js_conditional_rules($action, $rules, $logic = 'or', $container = '.mf-inner-field')
	{

		// Return the JSON string containing conditional rules
		return json_encode(array(
			'container' => $container,
			'action' => $action,
			'logic' => $logic,
			'rules' => $rules,
		));
	}

	/**
	 * Retrieve setting value from all field values ($this->field) using the key along with mfget() helper function
	 *
	 * @return mixed
	 */
	public function get_setting_value($setting_key, $default = null)
	{
		if (isset($this->field[$setting_key])) {
			$value = mfget($setting_key, $this->field);
		} else {
			$value = mfget($this->get_field_key('options', $setting_key), $this->field);
		}
		# If default is provided and returned value is empty, set value to the provided default
		if (empty($value) && $default !== null) {
			$value = $default;
		}

		return $value;
	}
	/**
	 * Retrieve boolean setting value from all field values ($this->field) using the key along with mfget() helper function
	 *
	 * @return mixed
	 */
	public function get_setting_bool_value($setting_key)
	{
		$value = $this->get_setting_value($setting_key);

		return mfget_bool_value($value);
	}

	/**
	 * Return short formatted value of this field ( to be used in the entry list view )
	 *
	 * @since    1.0.0
	 *
	 * @param array|string $value The saved value
	 * @return string
	 */
	public function get_formatted_value_short($value)
	{

		if (is_array($value)) {
			$value = array_filter($value);
			return esc_html(implode(', ', $value));
		}


		return esc_html($value);
	}

	/**
	 * Return long formatted value of this field ( to be used in the single entry view )
	 *
	 * @since    1.0.0
	 *
	 * @param array|string $value The saved value
	 * @return string
	 */
	public function get_formatted_value_long($value)
	{

		if (is_array($value)) {
			$output = '';
			$output .= '<ul class="mf_formatted_' . $this->type . '_value">';
			foreach ($value as $val) {
				$output .= !empty($val) ? '<li>' . esc_html($val) . '</li>' : '';
			}
			$output .= '</ul>';
			return $output;
		}


		return esc_html($value);
	}
	/**
	 * Process and convert merge tags into real values.
	 *
	 * @since    1.0.0
	 *
	 * @param string $value The string to be proccessed
	 * @return string
	 */
	public function process_merge_tags($value)
	{
		# Make sure the associated form is loaded in global scope ( usefull when the function is called directly )
		mfget_form($this->form_id);
		# Process existing tags
		return mf_merge_tags()->process($value, $this->field);
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	/**
	 * Make the needed check for if the field is required.
	 *
	 * @since    1.0.0
	 *
	 * @param array|string $value The submitted value
	 * @return bool|array
	 */
	public function required_check($value)
	{

		$is_required = $this->get_setting_bool_value('field_required');
		// If this is a compound field, use the dedicated method instead.
		if ($is_required && $this->isCompoundField) {
			return $this->compound_required_check($value);
		}

		// Otherwise continue
		if ($is_required && empty($value)) {
			return array(
				'notice' => mf_api()->get_validation_required_notice($this->field),
			);
		}

		return true;
	}
	/**
	 * Override this to make required check in the validation process for compound fields.
	 *
	 * @since    1.0.0
	 *
	 * @param array $value The submitted value
	 * @return bool|array
	 */
	protected function compound_required_check($value)
	{

		return array();
	}
	/**
	 * A helper method for `compound_required_check()`.
	 *
	 * @since    1.0.0
	 *
	 * @param array $components The complete array of field settings
	 * @param array $value The submitted value
	 * @return bool|array
	 */
	protected function compound_required_check_helper($components, $value)
	{

		$labels = array();
		$compound_notices = array();

		foreach ($components as $key => $val) {
			// Exclude any fields that are not enabled and also the ones that are not required.
			if (!mfget_bool_value($val['enable']) || !mfget_bool_value($val['is_required'])) {
				continue;
			}

			if (empty($value[$key])) {
				$labels[$key] = !empty($components[$key]['desc']) ? $components[$key]['desc'] : $components[$key]['label'];
			}
		}

		if (!empty($labels)) {
			$required_message = __('%s is required.', 'megaforms');
			foreach ($labels as $lkey => $lval) {
				$compound_notices[$lkey] = sprintf($required_message, ucfirst(strtolower($lval)));
			}

			// Allow the returned value to be changed
			return apply_filters('mf_' . $this->type . '_required_check_failed', array(
				# When `notice` is set, only one notice will display for compund fields instead of one per each input.
				# NOTE: make sure to unset `compound_notices` if you decided to use `notice`
				// 'notice' => mf_api()->get_validation_compound_required_notice($this->field, $labels),
				'compound_notices' => $compound_notices,
			), $this->field, $labels);
		}

		return true;
	}

	/**
	 * Override this method (if needed) for each field to make proper validation.
	 *
	 * Return 'true' if validation is completed succesfully.
	 *
	 * Return an array holding the property 'notice' and validation message as a value, and 'notice_code' so that the default message can be overriden.
	 * You can include any additional validation notices if this is a compound field in another property 'compound_notices' and pass an array with the notices.
	 *
	 * @see MFAPI::get_validation_messages() for validation messages and codes
	 * @param string|array $value The field submission value.
	 * @param string $context Where this value is being validated
	 * @return bool|array Validation result.
	 */
	public function validate($value, $context = '')
	{
		return true;
	}
	/**
	 * Override this method (if needed) for each field to do a spam check.
	 *
	 * Return 'false' if the value is not considered "spam".
	 *
	 * @param string|array $value The field submission value.
	 * @return bool spam check result.
	 */
	public function is_spam($value)
	{
		return false;
	}

	/**
	 * Override this method when needed to make proper sanitization before saving value to the database.
	 *
	 * @param string $value The field submission value.
	 *
	 * @return string
	 */
	public function sanitize($value)
	{
		// Recursion condition to make sure we handle arrays as well.
		if (is_array($value)) {
			$return = array();
			foreach ($value as $key => $val) {
				$return[$key] = $this->sanitize($val);
			}
			return $return;
		}

		$html_allowed = $this->is_html_allowed();

		if ($html_allowed === true) {
			// ( Field accepts HTML ) Sanitize text content and strips out disallowed HTML.
			$return = wp_kses_post($value);
		} else {
			// ( Field doesn't accept HTML ) Sanitize text content, Converts single < characters to entities, strip all tags, but preserve new lines (\n) and other whitespace.
			$return = sanitize_textarea_field($value);
		}
		return $return;
	}
	/**
	 * Override this method for any field that allows html to be saved to the database
	 *
	 * @return bool
	 */
	public function is_html_allowed()
	{

		return false;
	}
	/**
	 * Sanitize the field settings submitted on the form editor
	 *
	 * @return array
	 */
	public function sanitize_settings()
	{

		$sanitized = array();
		$sanitized['id'] = absint($this->field_id);
		$sanitized['formId'] = absint($this->form_id);
		$sanitized['type'] = wp_strip_all_tags($this->type);

		$options =  call_user_func_array('array_merge', array_values($this->get_editor_options()));

		# Sanitize default options
		if (array_key_exists('field_label', $options)) {
			$sanitized['field_label'] = sanitize_text_field($this->get_setting_value('field_label'));
		}
		if (array_key_exists('field_required', $options)) {
			$sanitized['field_required'] = mfget_bool_value($this->get_setting_value('field_required'));
		}
		if (array_key_exists('field_placeholder', $options)) {
			$sanitized['field_placeholder'] = sanitize_text_field($this->get_setting_value('field_placeholder'));
		}
		if (array_key_exists('field_default', $options)) {
			$sanitized['field_default'] = sanitize_text_field($this->get_setting_value('field_default'));
		}
		if (array_key_exists('field_css_class', $options)) {
			$sanitized['field_class'] = sanitize_html_class($this->get_setting_value('field_class'));
		}
		if (array_key_exists('field_description', $options)) {
			$sanitized['field_description'] = sanitize_textarea_field($this->get_setting_value('field_description'));
		}
		if (array_key_exists('field_description_position', $options)) {
			$sanitized['field_description_position'] = wp_strip_all_tags($this->get_setting_value('field_description_position'));
		}
		if (array_key_exists('field_label_visibility', $options)) {
			$sanitized['field_label_visibility'] = wp_strip_all_tags($this->get_setting_value('field_label_visibility'));
		}
		if (array_key_exists('field_sub_label_position', $options)) {
			$sanitized['field_sub_label_position'] = wp_strip_all_tags($this->get_setting_value('field_sub_label_position'));
		}
		if (array_key_exists('field_size', $options)) {
			$sanitized['field_size'] = wp_strip_all_tags($this->get_setting_value('field_size'));
		}
		if (array_key_exists('field_visibility', $options)) {
			$sanitized['field_visibility'] = wp_strip_all_tags($this->get_setting_value('field_visibility'));
		}

		// Handle custom fields attributes
		if (!empty($this->editorCustomSettings)) {
			foreach ($this->editorCustomSettings as $option_type) {
				$sanitized_custom_option = MF_Extender::get_single_field_option($option_type)->sanitize_option_value($this);
				$sanitized[$option_type] = $sanitized_custom_option;
			}
		}

		return $sanitized;
	}
	/**
	 * Perform any tasks associated with the field submission here.
	 * the property `$this->hasPostSubmissionTask` should be set to true for this to work.
	 *
	 * @return mixed
	 */
	public function post_submission_task($submission_value)
	{
		return false;
	}
}
