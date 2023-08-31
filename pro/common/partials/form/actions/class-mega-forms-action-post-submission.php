<?php

/**
 * @link       https://wpali.com
 * @since      1.3.3
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

class MF_Action_Post_Submission extends MF_Action
{
	public $type = 'post_submission';
	public $options = array(
		'post_type',
		'post_status',
		'post_title',
		'post_content',
		'post_excerpt',
		'featured_image',
		'custom_fields',
	);
	public $priority = 'high';

	public function get_action_title()
	{
		return esc_attr__('Post Submission', 'megaforms');
	}
	public function get_action_icon()
	{
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEyOCAxMjgiIGhlaWdodD0iMTI4cHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiB3aWR0aD0iMTI4cHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnPjxnPjxwYXRoIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTk2LDBIMTZDNy4xNjQsMCwwLDcuMTY0LDAsMTZ2OTZjMCw4LjgzNiw3LjE2NCwxNiwxNiwxNmg0NHYtOCAgICBIMTZjLTQuNDA2LDAtOC0zLjU4Ni04LThWMTZjMC00LjQxNCwzLjU5NC04LDgtOGg4MGM0LjQxNCwwLDgsMy41ODYsOCw4djQ3LjAzMWw4LThWMTZDMTEyLDcuMTY0LDEwNC44MzYsMCw5NiwweiIgZmlsbD0iIzU0NkU3QSIgZmlsbC1ydWxlPSJldmVub2RkIi8+PC9nPjwvZz48cGF0aCBkPSJNMTI1LjY1Niw3MkwxMjAsNjYuMzQ0Yy0xLjU2My0xLjU2My0zLjYwOS0yLjM0NC01LjY1Ni0yLjM0NHMtNC4wOTQsMC43ODEtNS42NTYsMi4zNDRsLTM0LjM0NCwzNC4zNDQgIGMtMS41NjMsMS41NjMtNi4zNDIsNy42MDUtNi4zNDQsOS42NTJMNjQsMTI4bDE3LjY1Ni00YzAsMCw4LjA5NC00Ljc4MSw5LjY1Ni02LjM0NGwzNC4zNDQtMzQuMzQ0ICBDMTI4Ljc4MSw4MC4xODgsMTI4Ljc4MSw3NS4xMjEsMTI1LjY1Niw3MnogTTg4LjQ5MiwxMTQuODJjLTAuNDUzLDAuNDMtMi4wMiwxLjQ4OC0zLjkzNCwyLjcwN2wtMTAuMzYzLTEwLjM2MyAgYzEuMDYzLTEuNDU3LDIuMjQ2LTIuOTIyLDIuOTc3LTMuNjQ4bDI1Ljg1OS0yNS44NTlsMTEuMzEzLDExLjMxM0w4OC40OTIsMTE0LjgyeiIgZmlsbD0iIzAzQTlGNCIvPjxwYXRoIGQ9Ik04OCwzMkgyNHYtOGg2NFYzMnoiIGZpbGw9IiNCMEJFQzUiLz48cGF0aCBkPSJNODgsNDhIMjR2LThoNjRWNDh6IiBmaWxsPSIjQjBCRUM1Ii8+PHBhdGggZD0iTTg4LDY0SDI0di04aDY0VjY0eiIgZmlsbD0iI0IwQkVDNSIvPjxwYXRoIGQ9Ik01Niw4MEgyNHYtOGgzMlY4MHoiIGZpbGw9IiNCMEJFQzUiLz48L3N2Zz4=';
	}
	public function pre_process_action($postedFields = array())
	{

		$post_type = $this->get_setting_value('post_type', 'post');
		$post_status = $this->get_setting_value('post_status', 'pending');
		$post_title = $this->get_setting_value('post_title');
		$post_content = $this->get_setting_value('post_content');
		$post_excerpt = $this->get_setting_value('post_excerpt');
		$featured_image = $this->get_setting_value('featured_image');
		$custom_fields = $this->get_setting_value('custom_fields');
		$data = array();

		// Resolve any merge tags available
		$post_title = $this->process_merge_tags($post_title, $postedFields);
		$post_content = $this->process_merge_tags($post_content, $postedFields);
		$post_excerpt = $this->process_merge_tags($post_excerpt, $postedFields);

		// Special logic to resolve existing merge tags for featured image
		$post_thumbnail = '';
		if (substr($featured_image, 0, 4) === "http") {
			$post_thumbnail = $featured_image;
		} else {
			$featured_image_tags = mf_merge_tags()->extract_tags($featured_image);
			if (!empty($featured_image_tags)  && isset($featured_image_tags['fields'][0]['tag'])) {
				$featured_image_field_id = (int)$featured_image_tags['fields'][0]['tag'];
				if (isset($postedFields[$featured_image_field_id])) {
					$post_thumbnail = $postedFields[$featured_image_field_id]['values']['raw'][0]['path'] ?? '';
				}
			}
		}
		// Special logic to resolve existing merge tags for featured image
		$post_meta = array();
		if (!empty($custom_fields['fields'])) {
			foreach ($custom_fields['fields'] as $field) {
				if (empty($field['meta_key']) || empty($field['meta_value']) || empty($field['meta_type'])) {
					continue;
				}
				$meta_value = $this->process_merge_tags($field['meta_value'], $postedFields);
				$post_meta[$field['meta_key']] = mf_sanitize($meta_value, $field['meta_type']);
			}
		}

		// Append all of the fields to the `$data` array
		$data['post_type'] = $post_type;
		$data['post_status'] = $post_status;
		$data['post_title'] = $post_title;
		$data['post_content'] = $post_content;
		$data['post_excerpt'] = $post_excerpt;
		$data['featured_image'] = $post_thumbnail;
		$data['custom_fields'] = $post_meta;

		return $data;
	}
	public function process_action()
	{

		if (is_array($this->prepared_data) && !empty($this->prepared_data)) {

			$post_type = $this->prepared_data['post_type'];
			$post_status = $this->prepared_data['post_status'];
			$post_title = $this->prepared_data['post_title'];
			$post_content = $this->prepared_data['post_content'];
			$post_excerpt = $this->prepared_data['post_excerpt'];
			$featured_image = $this->prepared_data['featured_image'];
			$custom_fields = $this->prepared_data['custom_fields'];

			// Validation
			if (empty($post_title)) {
				throw new Exception(__('A title is required.', 'megaforms'));
			}

			// Create the destination post
			$post_id = wp_insert_post(array(
				'post_status'  => $post_status,
				'post_type'    => $post_type,
				'post_title'   => $post_title,
				'post_content'   => $post_content,
				'post_excerpt'   => $post_excerpt,
			));

			if (!is_wp_error($post_id)) {
				// Save featured image
				if (!empty($featured_image)) {
					if (substr($featured_image, 0, 4) === "http") {
						// Handle by URL ( if a URL to the image is given )
						$image_id = media_sideload_image($featured_image, $post_id, null, 'id');
						set_post_thumbnail($post_id, $image_id);
					} else {
						// Handle by Path ( if a path to the image is given )
						$filename = wp_basename($featured_image);
						$upload_file = wp_upload_bits($filename, null, @file_get_contents($featured_image));
						if (!$upload_file['error']) {
							// if succesfull insert the new file into the media library (create a new attachment post type).
							$wp_filetype = wp_check_filetype($filename, null);

							$attachment = array(
								'post_mime_type' => $wp_filetype['type'],
								'post_parent'    => $post_id,
								'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
								'post_content'   => '',
								'post_status'    => 'inherit'
							);

							$attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $post_id);
							if (!is_wp_error($attachment_id)) {
								// if attachment post was successfully created, insert it as a thumbnail to the post $post_id.
								require_once(ABSPATH . "wp-admin" . '/includes/image.php');
								$attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
								wp_update_attachment_metadata($attachment_id,  $attachment_data);
								set_post_thumbnail($post_id, $attachment_id);
							}
						}
					}
				}
				// Save custom fields
				if (!empty($custom_fields)) {
					foreach ($custom_fields as $key => $val) {
						update_post_meta($post_id, $key, $val);
					}
				}
				// Return true
				return true;
			} else {
				throw new Exception($post_id->get_error_message());
			}
		}

		return false;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/
	public function post_type()
	{
		$label = __('Post Type', 'megaforms');
		$desc = __('Enter the default post type where the submission will go.', 'megaforms');
		$action_key = 'post_type';

		// Prepare roles list
		$types = get_post_types(array(
			'public' => true,
			'show_ui' => true
		));
		$types_list = array();
		foreach ($types as $key => $val) {
			if (in_array($key, array('attachment'))) {
				continue;
			}
			$types_list[$key] = ucfirst($val);
		}
		// Prepare the field
		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['options'] = $types_list;
		$args['default'] = 'post';

		return mfinput('select', $args, true);
	}
	public function post_status()
	{
		$label = __('Post Status', 'megaforms');
		$desc = __('Enter the default post status for the created post.', 'megaforms');
		$action_key = 'post_status';

		// Prepare roles list
		$statuses = get_post_statuses();
		// Prepare the field
		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['options'] = $statuses;
		$args['default'] = 'pending';

		return mfinput('select', $args, true);
	}
	public function post_title()
	{
		$label = __('Post Title', 'megaforms');
		$desc = __('Enter the title that will be assignned to the post.', 'megaforms');
		$action_key = 'post_title';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('text', $args, true);
	}
	public function post_content()
	{
		$label = __('Post Content', 'megaforms');
		$desc = __('Enter the content that will be assignned to the post.', 'megaforms');
		$action_key = 'post_content';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('textarea', $args, true);
	}
	public function post_excerpt()
	{
		$label = __('Post Excerpt', 'megaforms');
		$desc = __('Enter the post excerpt.', 'megaforms');
		$action_key = 'post_excerpt';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('textarea', $args, true);
	}
	public function featured_image()
	{
		$label = __('Featured Image', 'megaforms');
		$desc = __('Enter a link to the post featured image.', 'megaforms');
		$action_key = 'featured_image';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';
		$args['inline_modal'] = array('fields');

		return mfinput('text', $args, true);
	}
	public function custom_fields()
	{
		$label = __('Custom Fields', 'megaforms');
		$desc = __('Add any additional custom fields or the post meta that should be saved to the post.', 'megaforms');
		$action_key = 'custom_fields';

		$args['id'] = $this->get_action_field_key($action_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);
		$args['value'] = $this->get_setting_value($action_key);
		$args['default'] = '';


		// Get the existing custom fields and their values
		$is_enabled = mfget_bool_value(mfget('enable', $args['value']));
		$fields = mfget('fields', $args['value']);
		$fields_count = 0;

		// If there are no saved custom fields, create a default empty field
		if (!is_array($fields) || empty($fields)) {
			$fields = array(
				array(
					'meta_key' => '',
					'meta_value' => '',
					'meta_type' => 'string',
				)
			);
		}

		// Build the custom field markup
		$cf_inputs = '';
		$cf_inputs .= mfinput('switch', array(
			'id'            => $args['id'] . '[enable]',
			'label'         => __('Enable', 'megaforms'),
			'value'         => $is_enabled,
			'labelRight'    => true,
			'attributes'    => array(),
			'wrapper_class' => 'mf-field-inputs-switch',
			'class' => 'mf-action-field-switch',
			'onchange_edit' => $this->get_js_helper_rules('none', 'handle_custom_fields_swtich')
		), true);

		$hidden_attr = $is_enabled ? "" : " hidden='1'";
		$cf_inputs .= '<div class="mf-custom-fields-inputs"' . esc_attr($hidden_attr) . '>';
		$cf_inputs .= '<table class="mf-custom-meta-table"><tbody>';
		$cf_inputs .= '<tr><td>' . __('Meta key', 'megaforms') . '</td><td>' . __('Meta value', 'megaforms') . '</td><td>' . __('Type', 'megaforms') . '</td></tr>';

		$input_key = $args['id'] . '[fields]';
		foreach ($fields as $key => $field) {

			$cf_inputs .= '<tr class="mf-custom-meta-field"' . mf_esc_attr('data-key', $key) . '>';
			// Meta key
			$cf_inputs .= '<td>';
			$cf_inputs .= mfinput('text', array(
				'id'            => $input_key . '[' . $key . '][meta_key]',
				'value'         => $field['meta_key'],
				'label_hidden'  => true,
				'attributes' 	=> $is_enabled ? array() : array('disabled' => 'disabled'), // set to disabled by default
			), true);
			$cf_inputs .= '</td>';
			// Meta value
			$cf_inputs .= '<td>';
			$cf_inputs .= mfinput('text', array(
				'id'            => $input_key . '[' . $key . '][meta_value]',
				'value'         => $field['meta_value'],
				'label_hidden'  => true,
				'attributes' 	=> $is_enabled ? array() : array('disabled' => 'disabled'), // set to disabled by default
				'inline_modal' 	=> array('fields'),
			), true);
			$cf_inputs .= '</td>';
			// Meta Type
			$cf_inputs .= '<td>';
			$cf_inputs .= mfinput('select', array(
				'id'            => $input_key . '[' . $key . '][meta_type]',
				'value'         => $field['meta_type'],
				'label_hidden'  => true,
				'attributes' 	=> $is_enabled ? array() : array('disabled' => 'disabled'), // set to disabled by default
				'default' 		=> 'string',
				'options' 		=> array(
					'string' 		=> 'Text',
					'boolean' 		=> 'True/False',
					'html' 			=> 'HTML',
					'integer' 		=> 'Integer',
					'float' 		=> 'Float',
					'email' 		=> 'Email',
					'url' 			=> 'URL',
				),
			), true);
			$cf_inputs .= '</td>';

			// Buttons
			$cf_inputs .= '<td class="mf-condition-rule-buttons">';
			$cf_inputs .= '<button class="add_custom_field_btn button" tabindex="-1">+</button>';
			$cf_inputs .= '<button class="remove_custom_field_btn button" tabindex="-1">-</button>';
			$cf_inputs .= '</td>';

			$cf_inputs .= '</tr>';

			$fields_count++;
		}

		$cf_inputs .= '</tbody></table>'; // close .mf-custom-meta-table
		$cf_inputs .= '</div>'; // close .mf-custom-fields-inputs

		$args['content'] = $cf_inputs;
		$input = mfinput('custom', $args, true);

		return $input;
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	public function sanitize_settings()
	{

		$sanitized = parent::sanitize_settings();

		$sanitized['post_type'] = esc_attr($this->get_setting_value('post_type'));
		$sanitized['post_title'] = sanitize_text_field($this->get_setting_value('post_title'));
		$sanitized['post_content'] = wp_kses_post($this->get_setting_value('post_content'));
		$sanitized['post_excerpt'] = wp_kses_post($this->get_setting_value('post_excerpt'));
		$sanitized['featured_image'] = sanitize_text_field($this->get_setting_value('featured_image'));
		$sanitized['custom_fields'] = !empty($this->get_setting_value('custom_fields')) ? map_deep($this->get_setting_value('custom_fields'), 'sanitize_text_field') : '';

		return $sanitized;
	}
}

MF_Extender::register_action(new MF_Action_Post_Submission());
