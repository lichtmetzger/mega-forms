<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/actions
 */

/**
 * Main action wrapper
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/actions
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Action
{

	/**
	 * The action key ( type )
	 *
	 * @var string
	 */
	public $type;

	/**
	 * The action group
	 *
	 * @var string
	 */
	public $group = 'form_actions';

	/**
	 * Array of all action data ( automatically set by the caller function )
	 *
	 * @var array
	 */
	public $action = array();

	/**
	 * Current action ID ( automatically set by the caller function )
	 *
	 * @var int
	 */
	public $action_id = 0;

	/**
	 * Current form ID ( automatically set by the caller function )
	 *
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * Action available options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Custom action available options
	 *
	 * @var array
	 */
	public $custom_options = array();

	/**
	 * Prepared data
	 *
	 * @var mixed
	 */
	public $prepared_data = null;


	/**
	 * Whether this action should run early in the submission process or later
	 *
	 * @var string
	 */
	public $priority = 'normal';

	public function __construct($data = array())
	{
		if (empty($data)) {
			return;
		}
		foreach ($data as $key => $value) {
			$this->{$key} = $value;
		}
	}

	/**
	 *  Get the action title.
	 *
	 * 	@return string
	 */
	public function get_action_title()
	{
		return $this->type;
	}
	/**
	 *  Get the action icon.
	 *  Image URLs, Megaform-Icons, Dashicons and base64-encoded data:image/svg_xml URIs are all accepted
	 *
	 *	@return string
	 */
	public function get_action_icon()
	{
		return $this->type;
	}
	/**
	 * Returns an array of option callbacks.
	 *
	 * @return array
	 */
	public function get_action_options()
	{

		/*
		 * Prepare default options
		 *
		 */

		$default_options = array(
			'action_label',
		);


		/*
		 * Prepare custom options ( options added using extender ) 
		 *
		 * @see MF_Extender
		 */
		$custom_options = MF_Extender::get_action_options();
		if (!empty($custom_options)) {
			foreach ($custom_options as $option_obj) {
				$option_display_logic = $option_obj->get_option_display_logic($this);
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
					$display = apply_filters('mf_add_' . $option_obj->type . '_action_option', $display, $this);
				}

				if ($display) {
					// Add to the custom options property ( Will be used later for sanitization & adding arguements )
					$this->custom_options[] = $option_obj->type;
				}
			}
		}

		$options = array_merge($default_options, $this->options, $this->custom_options);

		return apply_filters('mf_action_options', $options, $this->action);
	}
	/**
	 *  Returns action settings complete markup.
	 *
	 *	@return string
	 */
	public function get_action_settings()
	{

		$action_display = $this->get_action_display();
		$title = $this->get_action_title();
		$subtitle = '';
		$icon = mf_api()->get_custom_icon('span', 'mgaction_icon mfaction_' . $this->type . '_icon', $this->get_action_icon());
		$classes = $this->get_action_container_classes();
		$is_enabled = $this->get_setting_value('enabled', false) ? 'checked="checked"' : '';
		$switch_key = $this->get_action_field_key('enabled');
		$action_label = $this->get_setting_value('action_label');
		if ($action_label !== $title) {
			$subtitle = '( ' . $action_label . ' )';
		}

		$action_wrapper = '';
		$action_wrapper .= sprintf('<li id="%s" data-id="%d" data-type="%s" class="%s">', $this->get_action_key(), $this->action_id, $this->type, $classes);
		$action_wrapper .= sprintf('<div class="action_controls noselect">
							<div class="controls mf_left">
								%4$s
								<span class="mgaction_name">%5$s<bdi>%6$s</bdi></span>
							</div>
							<div class="controls mf_right disable-sorting">
								<span class="mf_action_handles"><a href="#" class="action_control" data-action="delete"><span class="mega-icons-trash-o"></span></a></span>
								<span class="mf_enable_action"><label class="mfswitch mfswitch-size-small action_control"><input type="checkbox" name="%2$s" id="%2$s" value="yes"%3$s><span class="mfswitch-slider round"></span></label></span>
								<span class="mf_action_arrow dashicons dashicons-arrow-right-alt2"></span>
							</div>
							<div class="mf_clearfix"></div>
						</div>', $this->action_id, $switch_key, $is_enabled, $icon, $title, $subtitle);
		$action_wrapper .= '<div class="single-action_panel">';
		$action_wrapper .= '{ACTION_DISPLAY}';
		$action_wrapper .= '</div>';

		$action_wrapper .= '</li>';

		$action_wrapper = apply_filters('mf_action_container', $action_wrapper, $this->action);

		return str_replace('{ACTION_DISPLAY}', $action_display, $action_wrapper);
	}

	/**
	 *  Returns action settings inner markup.
	 *
	 *	@return string
	 */
	public function get_action_display()
	{

		$options = $this->get_action_options();
		$html = "";

		/*
		 * Build the markup for options
		 */
		foreach ($options as $option) {
			$html .= sprintf('<div class="mf-inner-field mf-inner-%s">', $option);
			// Use the option key to call the display method to pull the markup
			if (!is_array($option)) {
				if (!in_array($option, $this->custom_options)) {
					if (method_exists($this, $option)) {
						$html .= call_user_func(array($this, $option));
					}
				} else {
					$custom_option = MF_Extender::get_single_action_option($option);
					if ($custom_option) {
						$html .= call_user_func(array($custom_option, 'get_option_display'), $this);
					}
				}
			}
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 *  Execute the action processes
	 *
	 *	@param mixed $data
	 *	@return bool
	 */
	public function exec($postedFields = array())
	{
		if ($this->get_setting_value('enabled', false)) {

			// Prepare data for processing
			if ($this->prepared_data === null) {
				$this->prepared_data = $this->pre_process_action($postedFields);
			}

			return $this->process_action();
		}

		return false;
	}

	/**
	 *  Returns the prepared data before processing it
	 *
	 *	@param array $postedFields
	 *	@return mixed
	 */
	public function pre_process_action($postedFields = array())
	{
		/**
		 * Here goes the code responsible for preparing data and assigning it to the class property $this->prepared_data
		 * $this->prepared_data can be then used inside method to complete the action execution
		 */

		/**
		 * This is usefull for background tasks, and cron jobs
		 * when you need to prepare data immediately ( eg; merge-tags, current user, current page..etc ) and execute the action later.
		 */

		return null;
	}

	/**
	 * Run and process any tasks related to this action
	 * It's not adviced to process the action directly without preparing data first 
	 * ( Must assign and use the property $this->prepared_data )
	 *
	 *	@return bool
	 */
	public function process_action()
	{
		return false;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/

	/**
	 * Returns action label field markup.
	 *
	 * @return string
	 */
	public function action_label()
	{

		$label = __('Action Label', 'megaforms');
		$desc = __('Enter the label for the action. This is how you can identify this action.', 'megaforms');
		$action_key = 'action_label';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		// $args['attributes'] 	= $this->get_js_helper_rules('preview', 'span.mf_label', 'update_label');

		return mfinput('text', $args, true);
	}

	/**********************************************************************
	 ******************************* Helpers ******************************
	 **********************************************************************/
	/**
	 * Return a valid action key to be used as the wrapper id.
	 *
	 * @return string
	 */
	protected function get_action_key()
	{

		return sprintf('mf_%d_action_%d', $this->form_id, $this->action_id);
	}
	/**
	 * Return a valid key for action fields to be used in the name/id attribute for the HTML markup.
	 *
	 * @return string
	 */
	public function get_action_field_key($action_key = '')
	{

		return sprintf('mfaction_%d_%s', $this->action_id, $action_key);
	}
	/**
	 *  Return description tooltip markup.
	 *
	 * @return string
	 */
	public function get_description_tip_markup($label, $content)
	{

		$desc_tip = "";
		$desc_tip .= "<span class='mf-tooltip action-tooltip mega-icons-question-circle'>";
		$desc_tip .= sprintf("<span style='display:none;' id='tooltip-content'><h4>%s</h4>%s</span>", $label, $content);
		$desc_tip .= "</span>";

		return $desc_tip;
	}

	/**
	 * Define action container classes based.
	 *
	 * @return array
	 */
	protected function get_action_container_classes()
	{

		$classes = array();
		$classes[] = 'mfaction';
		$classes[] = 'single_action';
		$classes[] = 'field_container';

		$classes = apply_filters('mf_action_container_classes', $classes, $this->action);

		return implode(' ', $classes);
	}

	/**
	 * Add the field button to the correct container
	 *
	 * @return array
	 */
	public function add_button_to_container($actions_container)
	{

		$buttonData = array(
			'class'      => 'mg_action',
			'icon'       => $this->get_action_icon(),
			'value'      => $this->get_action_title(),
			'data-type'  => $this->type,
		);

		// Add any missing data to the pre-added actions ( actions are pre added to appear in specific order )
		// @see MegaForms_Form_Actions
		foreach ($actions_container as $groupKey => $groupVal) {
			foreach ($groupVal['actions'] as $buttonKey => $button) {

				if (isset($button['data-type']) && $button['data-type'] == $this->type) {
					if (count($button) < 3) {
						$actions_container[$groupKey]['actions'][$buttonKey] = $buttonData;
						return $actions_container;
					}
				}
			}
		}
		// If the action position is not already defined, add it to the end of the list
		if (!empty($this->group) && isset($actions_container[$this->group])) {
			$actions_container[$this->group]['actions'][] = $buttonData;
		}

		return $actions_container;
	}

	/**
	 * Retrieve setting value from all action values ($this->action) using the key along with mfget() helper function
	 *
	 * @return mixed
	 */
	public function get_setting_value($setting_key, $default = null)
	{

		if (isset($this->action[$setting_key])) {
			$value = mfget($setting_key, $this->action);
		} else {
			$value = mfget($this->get_action_field_key($setting_key), $this->action);
		}

		# If default is provided and returned value is empty, set value to the provided default
		if (empty($value) && $default !== null) {
			$value = $default;
		}

		return $value;
	}
	/**
	 * Returns action priority
	 *
	 * @return integer
	 */
	public function get_priority()
	{

		$priority = array(
			'low' => 3,
			'normal' => 2,
			'high' => 1
		);

		return intval($priority[$this->priority]);
	}
	/**
	 * Return an json string that is used as data attribute in the HTML elements
	 * to allow real time changes using JS each time an action is performed on the related element ( click, change...etc )
	 *
	 * @return array
	 */
	public function get_js_helper_rules($target, $action)
	{

		return json_encode(array(
			'target' => $target,
			'action' => $action,
		));
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
	 * Process and convert merge tags into real values.
	 *
	 * @since    1.0.0
	 *
	 * @param string $value The string to be proccessed
	 * @return string
	 */
	public function process_merge_tags($value, $data)
	{
		# Make sure the associated form is loaded in global scope ( usefull when the function is called directly )
		mfget_form($this->form_id);
		# Process existing tags
		return mf_merge_tags()->process($value, $data);
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	/**
	 * Sanitize the action settings submitted on the form editor
	 *
	 * @return array
	 */
	public function sanitize_settings()
	{

		$sanitized = array();
		$sanitized['id'] = absint($this->action_id);
		$sanitized['formId'] = absint($this->form_id);
		$sanitized['type'] = wp_strip_all_tags($this->type);
		$sanitized['priority'] = $this->get_priority();

		$options =  $this->get_action_options();

		// Sanitize default options
		$sanitized['enabled'] = mfget_bool_value($this->get_setting_value('enabled'));

		if (in_array('action_label', $options)) {
			$sanitized['action_label'] = sanitize_text_field($this->get_setting_value('action_label'));
		}

		// Sanitize custom options, if available.
		if (!empty($this->custom_options)) {
			foreach ($this->custom_options as $option_type) {
				$sanitized_custom_option = MF_Extender::get_single_action_option($option_type)->sanitize_option_value($this);
				$sanitized[$option_type] = $sanitized_custom_option;
			}
		}

		return $sanitized;
	}
}
