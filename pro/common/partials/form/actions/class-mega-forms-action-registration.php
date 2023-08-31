<?php

/**
 * @link       https://wpali.com
 * @since      1.3.0
 *
 */

/**
 * Form action class
 *
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Action_Registeration extends MF_Action
{
	public $type = 'registration';
	public $options = array(
		'username',
		'email',
		'first_name',
		'last_name',
		'role',
	);
	public $priority = 'high';

	public function get_action_title()
	{
		return esc_attr__('User Registration', 'megaforms');
	}
	public function get_action_icon()
	{
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pjxzdmcgdmlld0JveD0iMCAwIDI1NiAyNTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3QgZmlsbD0ibm9uZSIgaGVpZ2h0PSIyNTYiIHdpZHRoPSIyNTYiLz48cGF0aCBmaWxsPSIjMDNhOWY0IiBkPSJNMjMxLjksMjEyYTEyMC43LDEyMC43LDAsMCwwLTY3LjEtNTQuMiw3Miw3MiwwLDEsMC03My42LDBBMTIwLjcsMTIwLjcsMCwwLDAsMjQuMSwyMTJhNy43LDcuNywwLDAsMCwwLDgsNy44LDcuOCwwLDAsMCw2LjksNEgyMjVhNy44LDcuOCwwLDAsMCw2LjktNEE3LjcsNy43LDAsMCwwLDIzMS45LDIxMloiLz48L3N2Zz4=';
	}
	public function pre_process_action($postedFields = array())
	{

		$username = $this->get_setting_value('username');
		$email = $this->get_setting_value('email');
		$first_name = $this->get_setting_value('first_name');
		$last_name = $this->get_setting_value('last_name');
		$role = $this->get_setting_value('role', 'subscriber');

		$data = array();
		if (!empty($email) && !empty($role) && (!empty($first_name) || !empty($last_name))) {
			// Resolve any merge tags available
			$username = $this->process_merge_tags($username, $postedFields);
			$email = $this->process_merge_tags($email, $postedFields);
			$first_name = $this->process_merge_tags($first_name, $postedFields);
			$last_name = $this->process_merge_tags($last_name, $postedFields);
			$role = $role;

			$data['username'] = $username;
			$data['email'] = $email;
			$data['first_name'] = $first_name;
			$data['last_name'] = $last_name;
			$data['role'] = $role;
		}

		return $data;
	}
	public function process_action()
	{

		if (is_array($this->prepared_data) && !empty($this->prepared_data)) {

			$username = $this->prepared_data['username'];
			$email = $this->prepared_data['email'];
			$first_name = $this->prepared_data['first_name'];
			$last_name = $this->prepared_data['last_name'];
			$role = $this->prepared_data['role'];

			// Validation
			if (empty($email) || !is_email($email)) {
				throw new Exception(__('Please use a valid email.', 'megaforms'));
			}

			if (email_exists($email)) {
				throw new Exception(__('The email you entered already exists.', 'megaforms'));
			}

			if (empty($username)) {
				$username = $email;
			}

			$username = sanitize_user($username);

			if (empty($username) || !validate_username($username)) {
				throw new Exception(__('Your username is not valid.', 'megaforms'));
			}

			if (username_exists($username)) {
				throw new Exception(__('The username you entered already exists.', 'megaforms'));
			}

			// User creation
			$user_id = wp_insert_user(array(
				'user_login' => $username,
				'user_pass' => wp_generate_password(),
				'user_email' => $email,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'role' => $role,
			));


			if (!is_wp_error($user_id)) {
				// Send the new user a notification
				wp_send_new_user_notifications($user_id, 'user');
				return true;
			} else {
				throw new Exception($user_id->get_error_message());
			}
		}

		return false;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/
	public function username()
	{
		$label = __('Username', 'megaforms');
		$desc = __('Enter the username that will be assignned to the user.', 'megaforms');
		$action_key = 'username';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('text', $args, true);
	}
	public function email()
	{
		$label = __('Email', 'megaforms');
		$desc = __('Enter the email that will be assignned to the user.', 'megaforms');
		$action_key = 'email';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('text', $args, true);
	}
	public function first_name()
	{
		$label = __('First name', 'megaforms');
		$desc = __('Enter the user first name.', 'megaforms');
		$action_key = 'first_name';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('text', $args, true);
	}
	public function last_name()
	{
		$label = __('Last name', 'megaforms');
		$desc = __('Enter the user last name.', 'megaforms');
		$action_key = 'last_name';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('text', $args, true);
	}
	public function role()
	{
		$label = __('Role', 'megaforms');
		$desc = __('Enter the default role that will be assigned to the user.', 'megaforms');
		$action_key = 'role';

		// Prepare roles list
		$roles = get_editable_roles();
		$roles_list = array();
		foreach ($roles as $key => $val) {
			if ($key == 'administrator') {
				continue;
			}
			$roles_list[$key] = $val['name'];
		}
		// Prepare the field
		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['options'] = $roles_list;
		$args['default'] = 'subscriber';

		return mfinput('select', $args, true);
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	public function sanitize_settings()
	{

		$sanitized = parent::sanitize_settings();

		$sanitized['username'] = esc_attr($this->get_setting_value('username'));
		$sanitized['email'] = esc_attr($this->get_setting_value('email'));
		$sanitized['first_name'] = esc_attr($this->get_setting_value('first_name'));
		$sanitized['last_name'] = esc_attr($this->get_setting_value('last_name'));
		$sanitized['role'] = esc_attr($this->get_setting_value('role'));

		return $sanitized;
	}
}

MF_Extender::register_action(new MF_Action_Registeration());
