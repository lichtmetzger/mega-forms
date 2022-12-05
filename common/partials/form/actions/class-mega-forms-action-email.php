<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/actions
 */

/**
 * Text action type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/actions
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Action_Email extends MF_Action
{
	public $type = 'email';
	public $options = array(
		'from_name',
		'from_email',
		'to',
		'subject',
		'message',
		'more_options',
		'cc',
		'bcc',
		'replyto',
	);

	public function get_action_title()
	{
		return esc_attr__('Email', 'megaforms');
	}
	public function get_action_icon()
	{
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMC8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvVFIvMjAwMS9SRUMtU1ZHLTIwMDEwOTA0L0RURC9zdmcxMC5kdGQnPjxzdmcgaGVpZ2h0PSI5NHB4IiBzdHlsZT0ic2hhcGUtcmVuZGVyaW5nOmdlb21ldHJpY1ByZWNpc2lvbjsgdGV4dC1yZW5kZXJpbmc6Z2VvbWV0cmljUHJlY2lzaW9uOyBpbWFnZS1yZW5kZXJpbmc6b3B0aW1pemVRdWFsaXR5OyBmaWxsLXJ1bGU6ZXZlbm9kZDsgY2xpcC1ydWxlOmV2ZW5vZGQiIHZlcnNpb249IjEuMCIgdmlld0JveD0iMCAwIDE4NjAzIDEzNjYxIiB3aWR0aD0iMTI4cHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+CiAgIDwhW0NEQVRBWwogICAgLmZpbDAge2ZpbGw6I0U2QzEzMX0KICAgXV0+CiAgPC9zdHlsZT48L2RlZnM+PGcgaWQ9IkxheWVyX3gwMDIwXzEiPjxwYXRoIGNsYXNzPSJmaWwwIiBkPSJNMTg2MDMgMjE2NGwwIDk4MjZjMCw5MTkgLTc1MiwxNjcxIC0xNjcyLDE2NzFsLTE1MjYwIDBjLTkxOSwwIC0xNjcxLC03NTIgLTE2NzEsLTE2NzFsMCAtOTgyNiA5MzAxIDUxNjAgOTMwMiAtNTE2MHptLTcyOTAgODY4NWwyODA1IDAgMCA3MjYgLTI4MDUgMCAwIC03MjZ6bTAgLTE3ODFsNTAwMSAwIDAgNzI2IC01MDAxIDAgMCAtNzI2eiIvPjxwYXRoIGNsYXNzPSJmaWwwIiBkPSJNMTY3MSAwbDE1MjYwIDBjNzQzLDAgMTM3Nyw0OTEgMTU5MywxMTY1bC05MjIzIDUxMTYgLTkyMjIgLTUxMTZjMjE2LC02NzQgODQ5LC0xMTY1IDE1OTIsLTExNjV6Ii8+PC9nPjwvc3ZnPg==';
	}
	public function pre_process_action($postedFields = array())
	{

		$from_name = $this->get_setting_value('from_name');
		$from_email = $this->get_setting_value('from_email');
		$to = $this->get_setting_value('to');
		$subject = $this->get_setting_value('subject');
		$message = $this->get_setting_value('message');
		$more_options = $this->get_setting_value('more_options');
		$headers = array();
		if (!empty($more_options)) {
			foreach ($more_options as $val) {
				switch ($val) {
					case '_cc':
						$headers['cc'] = $this->get_setting_value('cc');
						break;
					case '_bcc':
						$headers['bcc'] = $this->get_setting_value('bcc');
						break;
					case '_replyto':
						$headers['replyto'] = $this->get_setting_value('replyto');
						break;
				}
			}
		}

		if (empty($from_name)) {
			$from_name = mfget_option('email_from_name', get_bloginfo('name'));
		}
		if (empty($from_email) || !is_email($from_email)) {
			$from_email =  mfget_option('email_from_address', get_bloginfo('admin_email'));
		}

		$data = array();
		if (!empty($to) && !empty($subject) && !empty($message)) {
			// Resolve any merge tags available
			$to = $this->process_merge_tags($to, $postedFields);
			$subject = $this->process_merge_tags($subject, $postedFields);
			$message = $this->process_merge_tags($message, $postedFields);

			$headers['from'] = htmlspecialchars_decode($from_name . ' <' . $from_email . '>');

			foreach ($headers as &$header) {
				$header = $this->process_merge_tags($header, $postedFields);
			}

			$data['to'] = $to;
			$data['subject'] = $subject;
			$data['message'] = $message;
			$data['headers'] = $headers;
		}

		return $data;
	}
	public function process_action()
	{

		if (is_array($this->prepared_data) && !empty($this->prepared_data)) {
			return mf_mail()->send(
				$this->prepared_data['to'],
				$this->prepared_data['subject'],
				$this->prepared_data['message'],
				$this->prepared_data['headers']
			);
		}

		return false;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/
	public function from_name()
	{
		$label = __('From Name', 'megaforms');
		$desc = __('Enter the name of the sender, or leave empty to use the default name.', 'megaforms');
		$action_key = 'from_name';

		$default_from_name = mfget_option('email_from_name', get_bloginfo('name'));
		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['placeholder'] = htmlspecialchars_decode($default_from_name);
		$args['inline_modal'] = array('fields', 'form', 'wp', 'misc');

		return mfinput('text', $args, true);
	}
	public function from_email()
	{
		$label = __('From Email', 'megaforms');
		$desc = __('Enter the email address of the sender, or leave empty to use the default email address.', 'megaforms');
		$action_key = 'from_email';

		$default_from_address =  mfget_option('email_from_address', get_bloginfo('admin_email'));

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['placeholder'] = htmlspecialchars_decode($default_from_address);
		$args['inline_modal'] = array('fields', 'form', 'wp', 'misc');

		return mfinput('text', $args, true);
	}
	public function to()
	{
		$label = __('To', 'megaforms');
		$desc = __('Enter the recipient address ( you can enter multiple addresses separated by comma ",").', 'megaforms');
		$action_key = 'to';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '{mf:wp admin_email}';
		$args['inline_modal'] = array('fields', 'wp');

		return mfinput('text', $args, true);
	}
	public function subject()
	{
		$label = __('Subject', 'megaforms');
		$desc = __('Enter the email subject ( If left empty, it will default to the form title ).', 'megaforms');
		$action_key = 'subject';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = __('New Submission: {mf:form title}', 'megaforms');
		$args['inline_modal'] = array('fields', 'form', 'wp', 'misc');

		return mfinput('text', $args, true);
	}
	public function message()
	{
		$label = __('Message', 'megaforms');
		$action_key = 'message';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '{mf:fields all_fields}';
		$args['inline_modal'] = array('fields', 'form', 'wp', 'misc');

		return mfinput('textarea', $args, true);
	}
	public function more_options()
	{

		$label = __('More Options', 'megaforms');
		$desc = __('Enable additional options for this email notification by highlighting the ones you would like to activate.', 'megaforms');
		$key = 'more_options';

		# Build the filed arguements
		$args = array();
		$args['label'] = $label;
		$args['id'] = $this->get_action_field_key($key);
		$args['value'] = $this->get_setting_value($key);
		$args['after_label'] 	= $this->get_description_tip_markup($label, $desc);
		$args['options'] = array(
			'_cc' => __('CC', 'megaforms'),
			'_bcc' => __('BCC', 'megaforms'),
			'_replyto' => __('Reply To', 'megaforms'),
		);

		return mfinput('checkbox', $args, true);
	}
	public function cc()
	{
		$label = __('CC', 'megaforms');
		$key = 'cc';

		# Build the filed arguements
		$args = array();
		$args['label'] = $label;
		$args['id'] = $this->get_action_field_key($key);
		$args['value'] = $this->get_setting_value($key);
		$args['conditional_rules'] = $this->get_js_conditional_rules('show', array(
			'name' => $this->get_action_field_key('more_options') . '[]', # adding [] to make sure identical key to multi-checkbox field name
			'operator' => 'contains',
			'value' => '_cc',
		));
		$args['inline_modal'] = array('fields', 'wp');

		return mfinput('text', $args, true);
	}
	public function bcc()
	{
		$label = __('BCC', 'megaforms');
		$key = 'bcc';

		# Build the filed arguements
		$args = array();
		$args['label'] = $label;
		$args['id'] = $this->get_action_field_key($key);
		$args['value'] = $this->get_setting_value($key);
		$args['conditional_rules'] = $this->get_js_conditional_rules('show', array(
			'name' => $this->get_action_field_key('more_options') . '[]', # adding [] to make sure identical key to multi-checkbox field name
			'operator' => 'contains',
			'value' => '_bcc',
		));
		$args['inline_modal'] = array('fields', 'wp');

		return mfinput('text', $args, true);
	}
	public function replyto()
	{
		$label = __('Reply To', 'megaforms');
		$key = 'replyto';

		# Build the filed arguements
		$args = array();
		$args['label'] = $label;
		$args['id'] = $this->get_action_field_key($key);
		$args['value'] = $this->get_setting_value($key);
		$args['conditional_rules'] = $this->get_js_conditional_rules('show', array(
			'name' => $this->get_action_field_key('more_options') . '[]', # adding [] to make sure identical key to multi-checkbox field name
			'operator' => 'contains',
			'value' => '_replyto',
		));
		$args['inline_modal'] = array('fields', 'wp');

		return mfinput('text', $args, true);
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	public function sanitize_settings()
	{

		$sanitized = parent::sanitize_settings();

		$sanitized['from_name'] = esc_attr($this->get_setting_value('from_name'));
		$sanitized['from_email'] = sanitize_email($this->get_setting_value('from_email'));
		$sanitized['to'] = wp_strip_all_tags($this->get_setting_value('to'));
		$sanitized['subject'] = sanitize_text_field($this->get_setting_value('subject'));
		$sanitized['message'] = wp_kses_post($this->get_setting_value('message'));
		$sanitized['more_options'] = $this->get_setting_value('more_options', array());

		if (in_array('_cc', $sanitized['more_options'])) {
			$sanitized['cc'] = wp_strip_all_tags($this->get_setting_value('cc'));
		}
		if (in_array('_bcc', $sanitized['more_options'])) {
			$sanitized['bcc'] = wp_strip_all_tags($this->get_setting_value('bcc'));
		}
		if (in_array('_replyto', $sanitized['more_options'])) {
			$sanitized['replyto'] = wp_strip_all_tags($this->get_setting_value('replyto'));
		}

		return $sanitized;
	}
}

MF_Actions::register(new MF_Action_Email());
