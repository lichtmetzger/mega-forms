<?php

/**
 * @link       https://wpali.com
 * @since      1.0.8
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/containers
 */

/**
 * Main action wrapper
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/containers
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Container
{
	/**
	 * The container type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Current form object ( automatically set by the caller function )
	 *
	 * @var object
	 */
	public $form = null;

	/**
	 * Current form ID ( automatically set by the caller function )
	 *
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * Array of all data required for the container to work ( automatically set by the caller function )
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Holds the global settings values for the container
	 *
	 * @var boolean|array
	 */
	public $settings = false;

	/**
	 * Whether this class was called in the editor view or not
	 *
	 * @var bool
	 */
	public $is_editor = false;
	/**
	 * Whether this form has element that should appear outside the fields container
	 *
	 * @var bool
	 */
	public $is_fluid = false;
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Define properties
		if (mf_api()->is_page('mf_form_editor')) {
			$this->is_editor = true;
		}
	}

	/**
	 *  Return the markup for this container and any available fields.
	 *
	 * 	@return string
	 */
	public function get_container_display()
	{
		return '';
	}

	/**
	 *  Get the container title.
	 *
	 * 	@return string
	 */
	public function get_container_title()
	{
		return $this->type;
	}
	/**
	 *  Get the container icon.
	 *  Image URLs, Megaform-Icons, Dashicons and base64-encoded data:image/svg_xml URIs are all accepted
	 *
	 *	@return string
	 */
	public function get_container_icon()
	{
		return $this->type;
	}
	/**
	 *  Get the container global settings.
	 *  If an array is returned, it should hold two main properties:
	 *  `tabs` => holding an array of available tabs
	 *  `options` => holding an array of available options 
	 *
	 *  Global settings means that these options will treat all
	 *  the containers with the same type in the same way regardless
	 *  of the container specific options (if they exist)
	 * 
	 *	@return boolean|array
	 */
	public function get_container_settings()
	{
		return false;
	}
	/**
	 *  Get the container options.
	 *
	 *  This is different from global settings as it only 
	 *  treats the single container that is provided to
	 *  this class via the property ( $this->data )
	 * 
	 *	@return boolean|array
	 */
	public function get_container_options()
	{
		return false;
	}

	/**
	 *  Get the container css classes.
	 *
	 * @since    1.0.7
	 * @param string $additional_class
	 * @return string
	 */
	public function get_container_classes($additional_class = '')
	{

		$classes     = array(
			'mf-container',
			'mf-' . $this->type . '-container',
		);

		if ($this->is_editor) {
			$classes[]   = 'mf_container_preview'; # Preview CSS Class added only on the editor view
		}

		if (!empty($additional_class)) {
			if (is_array($additional_class)) {
				$classes = array_merge($classes, $additional_class);
			} else {
				$classes[]   = $additional_class;
			}
		}

		return implode(' ', $classes);
	}
	/**
	 *  Get container editor inline JS.
	 *
	 *	@return string
	 */
	public function get_editor_inline_JS()
	{
		return '';
	}

	/**
	 *  Get the markup to prepend to add outside the fields container.
	 * Accepts the following keys: 
	 * `below_header`: This is the header content to appear after form header area ( public and admin )
	 * `below_body`: This is the body content to appear after fields ( public only )
	 * `below_footer`: This is the footer content to appear in the form footer area ( public and admin )
	 * `args`: Arguements to pass to the form template on MF_Form_View ( public only ) 
	 * `header_args: Arguements to pass to the form header template on MF_Form_View ( public only )
	 * `footer_args: Arguements to pass to the form footer template on MF_Form_View ( public only )
	 * 
	 * @see MegaForms_Form_Fields->render() (admin)
	 * @see MF_Form_View->form_display() (public)
	 * @since    1.0.7
	 * @return array|false
	 */
	public function get_fluid_data()
	{
		return false;
	}
	/**
	 * Get the markup to the container global settings.
	 * 
	 * The container settings allow controlling and modifying the 
	 * container global options via JS & is going to be converted
	 * to an array and saved with with form meta data when the form is saved.
	 * 
	 * @see MF_Admin_Ajax ::update_form()
	 * @since    1.0.8
	 * @return string
	 */
	public function get_settings_content($title = '', $desc = '')
	{
		$html = '';
		if ($this->is_editor) {
			$settings = $this->get_container_settings();
			if (!empty($settings) && isset($settings['tabs']) && isset($settings['options'])) {
				$title = !empty($title) ? $title : $this->get_container_title();
				$desc = !empty($desc) ? $desc : '';
				$html .= '<form method="post" class="mf_container_global_settings mf_tabs" data-type="' . $this->type . '" data-title="' . $title . '" data-desc="' . $desc . '" style="display:none;">';
				if (count($settings['tabs']) > 1) {
					$html .= '<div class="mf_tab_links">';
					$n = 1;
					foreach ($settings['tabs'] as $key => $label) {
						// Prefix the key to avoid duplicate IDs
						$new_key = $this->type . '_container_' . $key;
						if (isset($settings['options'][$key])) {
							$settings['options'][$new_key] = $settings['options'][$key];
							unset($settings['options'][$key]);
						}
						// Build the tab button markup
						$is_active = $n === 1 ? ' active' : '';
						$html .= '<a href="#" title="' . $label . '" data-panel="' . $new_key . '_settings" data-disable="mfsetting_container" class="mf-panel-toggler' . $is_active . '" target="_self"><span class="app-menu-text">' . $label . '</span></a>';
						$n++;
					}
					$html .= '</div>';
				} else {
					// Prefix the key to avoid duplicate IDs
					foreach ($settings['options'] as $tab => $options) {
						$new_key = $this->type . '_container_' . $tab;
						$settings['options'][$new_key] = $settings['options'][$tab];
						unset($settings['options'][$tab]);
					}
				}

				$html .= '<div class="mf_tab_panels">';
				$html .= mfsettings($settings['options'], 'mfcontainer_settings', 'mfcontainer_option', true);
				$html .= '</div>';
				$html .= '</form>';

				return $html;
			}
		}


		return $html;
	}
	/**
	 * Get the markup to the container options.
	 * 
	 * @since    1.0.7
	 * @return string
	 */
	public function get_options_content($title = '', $desc = '')
	{
		$html = "";
		if ($this->is_editor) {
			$options = $this->get_container_options();
			if (!empty($options)) {
				$id = md5(mt_rand(1, 3) . microtime() . mt_rand(1, 3));
				$title = !empty($title) ? $title : $this->get_container_title();
				$desc = !empty($desc) ? $desc : '';
				$html .= '<form method="post" class="mf_container_options" data-title="' . $title . '" data-desc="' . $desc . '" style="display:none;">';
				$html .= '<div class="mfoptions_container">';
				foreach ($options as $key => $option) {

					if (!isset($option['type'])) {
						continue;
					}

					// Add tooltip if description is available
					if (isset($option['desc']) && $option['label']) {
						$option['after_label'] = get_mfsettings_tooltip($option['label'], $option['desc']);
						unset($option['desc']);
					}

					$args = wp_parse_args($option, array(
						'name' => $key,
						'id' => $this->type . '_' . $key . '_' . $id,
						'wrapper_class' => 'mf_input_container',
					));

					$html .= '<div class="mf-inner-field">';
					$html .= mfinput($args['type'], $args);
					$html .= '</div>';
				}
				$html .= '</div>';
				$html .= '</form>';
			}
		}

		return $html;
	}
	/**
	 * Get the markup to the container controls.
	 * 
	 * @since    1.0.7
	 * @return string
	 */
	public function get_controls_content($controls, $custom = array(), $class = 'container_controls')
	{
		$html = '';
		if ($this->is_editor) {

			if (!is_array($controls)) {
				$controls = (array)$controls;
			}

			$html .= '<div class="' . $class . ' disable-sorting"><div class="controls right">';

			if (in_array('trash', $controls)) {
				$html .= '<a href="#" class="container_control" data-action="delete"' . mf_esc_attr('title', __('Delete', 'megaforms')) . '><span class="dashicons dashicons-trash"></span></a>';
			}

			if (in_array('edit', $controls)) {
				$html .= '<a href="#" class="container_control" data-action="edit"' . mf_esc_attr('title', __('Edit', 'megaforms')) . '><span class="dashicons dashicons-edit"></span></a>';
			}

			if (!empty($custom)) {
				foreach ($custom as $key => $control) {
					$html .= '<a href="#" class="container_control"' . mf_esc_attr('data-action', $key) . mf_esc_attr('title', $control['label']) . '><span class="' . $control['icon'] . '"></span></a>';
				}
			}

			$html .= '</div></div>';
		}


		return $html;
	}
	/**
	 * Sanitize container global settings.
	 * 
	 * @since    1.0.7
	 * @return array
	 */
	public function sanitize_settings()
	{
		$container_settings = $this->get_container_settings();
		if ($container_settings && isset($container_settings['options'])) {

			$settings = is_array($container_settings['options']) && !empty($container_settings['options']) ? call_user_func_array('array_merge', $container_settings['options']) : array();
			$sanitized = array();
			foreach ($settings as $key => $val) {
				// Make sure switch/checkbox field values are also included
				$value = mfget('sanitization', $val) == 'boolean' ? mfget($key, $this->settings, false) : mfget($key, $this->settings);
				$sanitized[$key] = mf_sanitize($value, mfget('sanitization', $val));
			}

			return $sanitized;
		}
		return false;
	}
	/**
	 * Sanitize container data.
	 * 
	 * @since    1.0.7
	 * @return array
	 */
	public function sanitize_data()
	{
		$container_options = $this->get_container_options();
		if ($container_options) {
			$sanitized = array();
			$sanitized['type'] = $this->type;
			foreach ($container_options as $key => $val) {
				// Make sure switch/checkbox field values are also included
				$value = $val['sanitization'] == 'boolean' ? mfget($key, $this->data, false) : mfget($key, $this->data);
				$sanitized[$key] = mf_sanitize($value, $val['sanitization']);
			}

			return $sanitized;
		}

		return false;
	}
}
