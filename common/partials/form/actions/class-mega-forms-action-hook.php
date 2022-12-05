<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/actions
 */

/**
 * hook action type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/actions
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Action_Hook extends MF_Action
{

	public $type = 'hook';
	public $options = array(
		'hook_tag',
	);
	public function get_action_title()
	{
		return esc_attr__('WP Hook', 'megaforms');
	}
	public function get_action_icon()
	{
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUwIDUwIiBpZD0iTGF5ZXJfMSIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNTAgNTAiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnIGlkPSJXXzJfIj48cGF0aCBkPSJNMTAsMjVjMCw1LjksMy41LDExLjEsOC41LDEzLjVsLTcuMi0xOS42QzEwLjUsMjAuOCwxMCwyMi44LDEwLDI1eiIgZmlsbD0iIzEyQTVENyIvPjxwYXRoIGQ9Ik0zNS4xLDI0LjJjMC0xLjktMC43LTMuMS0xLjItNC4xYy0wLjgtMS4yLTEuNS0yLjMtMS41LTMuNWMwLTEuNCwxLTIuNywyLjUtMi43YzAuMSwwLDAuMSwwLDAuMiwwICAgQzMyLjUsMTEuNSwyOC45LDEwLDI1LDEwYy01LjIsMC05LjksMi43LTEyLjUsNi44YzAuNCwwLDAuNywwLDEsMGMxLjYsMCw0LTAuMiw0LTAuMmMwLjgsMCwwLjksMS4xLDAuMSwxLjJjMCwwLTAuOCwwLjEtMS43LDAuMSAgIGw1LjUsMTYuMmwzLjMtOS44TDIyLjIsMThjLTAuOCwwLTEuNi0wLjEtMS42LTAuMWMtMC44LDAtMC43LTEuMywwLjEtMS4yYzAsMCwyLjUsMC4yLDQsMC4yYzEuNiwwLDQtMC4yLDQtMC4yICAgYzAuOCwwLDAuOSwxLjEsMC4xLDEuMmMwLDAtMC44LDAuMS0xLjcsMC4xbDUuNCwxNi4xbDEuNS01QzM0LjYsMjcsMzUuMSwyNS41LDM1LjEsMjQuMnoiIGZpbGw9IiMxMkE1RDciLz48cGF0aCBkPSJNMjUuMywyNi4zbC00LjUsMTMuMWMxLjMsMC40LDIuOCwwLjYsNC4yLDAuNmMxLjcsMCwzLjQtMC4zLDUtMC45YzAtMC4xLTAuMS0wLjEtMC4xLTAuMkwyNS4zLDI2LjN6IiBmaWxsPSIjMTJBNUQ3Ii8+PHBhdGggZD0iTTM4LjIsMTcuOGMwLjEsMC41LDAuMSwxLDAuMSwxLjVjMCwxLjUtMC4zLDMuMi0xLjEsNS40TDMyLjUsMzhDMzcsMzUuNCw0MCwzMC41LDQwLDI1ICAgQzQwLDIyLjQsMzkuMywxOS45LDM4LjIsMTcuOHoiIGZpbGw9IiMxMkE1RDciLz48L2c+PHBhdGggZD0iTTI1LDFDMTEuNywxLDEsMTEuNywxLDI1czEwLjcsMjQsMjQsMjRzMjQtMTAuNywyNC0yNFMzOC4zLDEsMjUsMXogTTI1LDQ0QzE0LjUsNDQsNiwzNS41LDYsMjVTMTQuNSw2LDI1LDYgIHMxOSw4LjUsMTksMTlTMzUuNSw0NCwyNSw0NHoiIGZpbGw9IiMxMkE1RDciLz48L3N2Zz4=';
	}
	public function pre_process_action($postedFields = array())
	{
		$hook = $this->get_setting_value('hook_tag');
		
		$data = array();
		$data['hook'] = $hook;
		$data['postedFields'] = $postedFields;
		
		return $data;
	}
	public function process_action()
	{
		
		if(!empty($this->prepared_data['hook'])){
			do_action( $this->prepared_data['hook'], $this->prepared_data['postedFields'] );
		}
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/
	public function hook_tag()
	{
		$label = __('Hook Tag', 'megaforms');
		$desc = __('Enter the name of the action hook you’re hooking to.', 'megaforms');
		$action_key = 'hook_tag';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);

		return mfinput('text', $args, true);
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	public function sanitize_settings()
	{

		$sanitized = parent::sanitize_settings();

		$sanitized['hook_tag'] = str_replace('-', '_', sanitize_title($this->get_setting_value('hook_tag')));

		return $sanitized;
	}
}

MF_Actions::register(new MF_Action_Hook());
