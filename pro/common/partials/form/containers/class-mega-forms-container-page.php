<?php

/**
 * @link       https://wpali.com
 * @since      1.0.7
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/containers
 */

/**
 * Text action type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/containers
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Container_Page extends MF_Container
{

	public  $type = 'page';
	public  $is_fluid = true;
	public 	$loaded_page = null;

	public function get_container_title()
	{
		return __('Page', 'megaforms');
	}

	public function get_container_icon()
	{
		return 'dashicons-excerpt-view';
	}

	public function get_container_settings()
	{

		/**
		 * Since `page_names` field is dynamic, we need to generated custm HTML for it
		 * 
		 */
		$pages = mfget('page_names', $this->settings, array(
			1 => __('Page 1', 'megaforms'),
			2 => __('Page 2', 'megaforms'),
		));

		$page_names = '';
		foreach ($pages as $number => $name) {
			$input_attrs = array(
				'id' => 'pagesettings_pagename_' . $number,
				'name' => 'page_names[' . $number . ']',
				'value' => mfget($number, $pages, __('Page ', 'megaforms') . $number)
			);

			if ($number == 1) {
				$input_attrs['data-conditional-rules'] = json_encode(
					array(
						'container' => '.mf-settings-field',
						'rules' => array(
							'name' => 'progress_indicator',
							'operator' => 'is',
							'value' => 'steps'
						),
					)
				);
			}

			$page_names .= '<div class="pagesettings_pagename" data-number="' . $number . '">';
			$page_names .= '<span class="mf_clearfix"></span>';
			$page_names .= '<label for="pagesettings_pagename_' . $number . '">' . __('Page ', 'megaforms') . $number . '</label>';
			$page_names .= get_mf_input('text', $input_attrs);
			$page_names .= '<span class="mf_clearfix"></span>';
			$page_names .= '</div>';
		}

		/**
		 * Return the tabs and options
		 * 
		 */
		return array(
			'tabs' => array(
				'general'    => __('General', 'megaforms')
			),
			'options' => array(
				'general' => array(
					'progress_indicator' => array(
						'type' => 'radio',
						'label' => __('Progress Indicator', 'megaforms'),
						'desc' => __('Choose what type of visual progress indicator you would like to display.', 'megaforms'),
						'value' => mfget('progress_indicator', $this->settings, 'progress_bar'),
						'options' => array(
							'progress_bar' => __('Progress Bar', 'megaforms'),
							'steps' => __('Steps', 'megaforms'),
							'none' => __('None', 'megaforms'),
						),
						'sanitization' => 'string',
					),
					'page_names' => array(
						'type' => 'custom',
						'label' => __('Page Names', 'megaforms'),
						'desc' => __('Name each of the pages on your form. Page names are displayed with the steps progress indicator.', 'megaforms'),
						'content' => $page_names,
						'sanitization' => 'custom',
					),
					'progress_indicator_position' => array(
						'type' => 'radio',
						'label' => __('Progress Indicator Position', 'megaforms'),
						'desc' => __('Choose whether to display the progress indicator above or below the form.', 'megaforms'),
						'value' => mfget('progress_indicator_position', $this->settings, 'top'),
						'options' => array(
							'top' => __('Top', 'megaforms'),
							'bottom' => __('Bottom', 'megaforms')
						),
						'sanitization' => 'string',
						'parent' => 'progress_indicator',
						'parent_value' => 'none',
						'parent_value_operator' => 'isnot'
					),
					'last_page_prev_button' => array(
						'type' => 'text',
						'label' => __('Last Page Prev Button', 'megaforms'),
						'desc' => __('Last page previous button text.', 'megaforms'),
						'value' => mfget('last_page_prev_button', $this->settings, $this->get_default_prev_text()),
						'sanitization' => 'string',
					)
				)
			),
		);
	}
	public function get_container_options()
	{
		return array(
			'next' => array(
				'type' => 'text',
				'label' => __('Next Button Text', 'megaforms'),
				'value' => mfget('next', $this->data, $this->get_default_next_text()),
				'sanitization' => 'string',
			),
			'previous' => array(
				'type' => 'text',
				'label' => __('Previous Button Text', 'megaforms'),
				'value' => mfget('previous', $this->data, $this->get_default_prev_text()),
				'sanitization' => 'string',
			),
			'page' => array(
				'type' => 'none',
				'value' => mfget('page', $this->data, 1),
				'sanitization' => 'number',
			)
		);
	}
	public function sanitize_settings()
	{
		$sanitized = parent::sanitize_settings();

		// Special sanitization for the `page_names` option
		if ($sanitized) {
			if (isset($sanitized['page_names']) && is_array($sanitized['page_names']) && count($sanitized['page_names']) > 1) {
				$old_pagenames = $sanitized['page_names'];
				$new_pagenames = array();
				$i = 1;
				foreach ($old_pagenames as $key => $val) {
					$new_pagenames[$i] = $val;
					$i++;
				}
			} else {
				$sanitized['page_names'] = array(
					1 => __('Page 1', 'megaforms'),
					2 => __('Page 2', 'megaforms'),
				);
			}
		}

		return $sanitized;
	}
	public function get_container_display()
	{
		$html = "";

		if ($this->get_loaded_page_number() !== -1) {
			$html = $this->get_page_break();
		}

		return $html;
	}
	public function get_fluid_data()
	{
		$data = array();

		if ($this->get_loaded_page_number() !== -1) {

			$data['below_header'] = '';
			$data['below_footer'] = $this->get_bottom_content();

			if (!$this->is_editor) {
				// Add progress indicator 
				$progress_indicator_pos = mfget('progress_indicator_position', $this->settings);
				if ('top' == $progress_indicator_pos) {
					$data['below_header'] .= $this->get_progress_indicator();
				} elseif ('bottom' == $progress_indicator_pos) {
					$data['below_footer'] .= $this->get_progress_indicator();
				}
				// Add previous button to the last page
				$prev_text = mfget('last_page_prev_button', $this->settings, $this->get_default_prev_text());
				$data['footer_args'] = array(
					'before_submit' => get_mf_button('button', $prev_text, array(
						'class' => 'button mf-prev-btn',
					))
				);
			}

			$data['below_header'] .= $this->get_top_content();
		}

		return $data;
	}
	public function get_editor_inline_JS()
	{
		$output = '';
		# Before form markup
		$output .= '<script type="text/html" id="tmpl-mfpage_top">';
		$output .= $this->get_top_content();
		$output .= '</script>';

		# Page break markup
		$output .= '<script type="text/html" id="tmpl-mfpage_break">';
		$output .= $this->get_page_break();
		$output .= '</script>';

		# After form markup
		$output .= '<script type="text/html" id="tmpl-mfpage_bottom">';
		$output .= $this->get_bottom_content();
		$output .= '</script>';

		# Page settings markup
		$output .= '<script type="text/html" id="tmpl-mfpage_settings">';
		$output .= $this->get_settings_content();
		$output .= '</script>';

		return $output;
	}

	public function get_top_content()
	{
		$html = "";
		if ($this->is_editor) {
			$html .= '<div class="mfpage_top mf-container" data-container="' . $this->type . '">';
			$html .= $this->get_controls_content('edit', array(), 'container_setting_controls');
			$html .= '<span>START PAGING</span>';
			$html .= '</div>';
		} else {

			$classes = $this->get_container_classes('mform_page mform_page_1');
			$styles = $this->get_loaded_page_number() !== 1 ? ' style="display:none;"' : '';

			$html .= '<div class="mform_pages">';
			$html .= '<div class="' . $classes . '"' . $styles . '>';
		}

		return $html;
	}

	public function get_bottom_content()
	{
		$html = "";
		if ($this->is_editor) {
			$html .= '<div class="mfpage_bottom mf-container" data-container="' . $this->type . '">';
			$html .= $this->get_controls_content('edit', array(), 'container_setting_controls');
			$html .= '<span>END PAGING</span>';
			$html .= '</div>';
		} else {
			$html .= '</div>'; // // close .mform_page
			$html .= '</div>'; // close .mform_pages
		}

		return $html;
	}

	/**
	 *  Get the page break markup.
	 *
	 * @since    1.0.7
	 * @return string
	 */
	public function get_page_break()
	{
		$html = "";
		if ($this->is_editor) {

			$html .= '<li class="' . $this->get_container_classes('mfpage_break') . '" data-container="' . $this->type . '">';
			$html .= $this->get_controls_content(array('trash', 'edit'));
			$html .= $this->get_options_content(__('Page Options', 'megaforms'), __('Manage page options.', 'megaforms'));
			$html .= '<span>PAGE BREAK</span>';
			$html .= '</li>';
		} else {
			$page = (int)$this->data['page'];
			// Since page break makes the the last half of current page and first half of next page (current page would be $page - 1)
			$current_page = $page - 1;
			// Close fields container and form body
			$html .= '</ul>'; // close .mform_fields
			$html .= '</div>'; // close .mform_body

			// Add the page footer
			$prev_text = mfget('previous', $this->data, $this->get_default_prev_text());
			$next_text = mfget('next', $this->data, $this->get_default_next_text());
			$footer_args = array();
			$footer_args['form'] = $this->form;
			$footer_args['submit_type'] = 'submit';
			$footer_args['submit_text']  = $next_text;
			$footer_args['submit_attribues']  = array(
				'name' => 'mform_next',
				'value' => $current_page,
				'class' => 'button mf-next-btn',
				'data-current-page' =>  $current_page,
				'formnovalidate' => 'formnovalidate',
			);

			// Previous button should only appear after the first page
			if ($current_page > 1) {
				$footer_args['before_submit']  = get_mf_button('button', $prev_text, array(
					'data-current-page' => $this->data['page'],
					'class' => 'button mf-prev-btn',
				));
			}

			$footer_template = mfget_template_filename('form', 'footer');
			$html .= '<div class="mform_footer">';
			$html .= mflocate_template_html($footer_template, $footer_args);
			$html .= '</div>';

			// Close the current page
			$html .= '</div>'; // close .mform_page

			// Start the next page
			$classes = $this->get_container_classes('mform_page' . ' mform_page_' . $page);
			$styles = $this->get_loaded_page_number() !== $page ? ' style="display:none;"' : '';
			$html .= '<div class="' . $classes . '"' . $styles . '>';
			$html .= '<div class="mform_body">';
			$html .= '<ul class="mform_fields">';
		}

		return $html;
	}
	/**
	 *  Get the progress indicator markup.
	 *
	 * @since    1.0.7
	 * @return string
	 */
	public function get_progress_indicator()
	{
		// Make sure the progress indicator doesn't appear after a successful submission
		if (!mf_submission()->is_empty() && mf_submission()->success && 'form' == mf_submission()->context) {
			return "";
		}

		$html = "";
		if (!$this->is_editor) {
			$progress_indicator = mfget('progress_indicator', $this->settings);
			$position = mfget('progress_indicator_position', $this->settings);
			$loaded_page = $this->get_loaded_page_number();
			$pagenames = mfget('page_names', $this->settings);
			$page_count = count($pagenames);

			if ('progress_bar' == $progress_indicator) {

				$percentage = (int)($loaded_page / $page_count * 100);

				$html .= '<div class="mform_pages_progress_indicator mf_progress_' . $position . ' mform_progress_bar">';
				$html .= '<div class="mf_progressbar_percentage percentbar_blue percentbar_' . $percentage . '" style="width:' . $percentage . '%;" data-count="' . $page_count . '">';
				$html .= '<span>' . $percentage . '%</span>';
				$html .= '</div>';
				$html .= '</div>';
			} elseif ('steps' == $progress_indicator) {
				$pagenames = mfget('page_names', $this->settings);

				$html .= '<div class="mform_pages_progress_indicator mf_progress_' . $position . ' mform_steps">';
				foreach ($pagenames as $page_number => $page_name) {

					$classes = 'mf_step';
					if ($page_number == 1) {
						$classes .= ' mf_step_first';
					}
					if ($page_number == $page_count) {
						$classes .= ' mf_step_last';
					}

					if ($page_number == $loaded_page) {
						$classes .= ' mf_step_active';
					} elseif ($page_number > $loaded_page) {
						$classes .= ' mf_step_pending';
					} else {
						$classes .= ' mf_step_completed';
					}

					$html .= '<div class="' . $classes . '" data-number="' . $page_number . '">';
					$html .= '<span class="mf_step_number">' . $page_number . '</span>';
					$html .= '<span class="mf_step_label">' . $page_name . '</span>';
					$html .= '</div>';
				}
				$html .= '</div>';
			}
		}

		return $html;
	}
	/**
	 *  The default previous button text
	 *
	 * @since    1.0.7
	 * @return string
	 */
	public function get_default_prev_text()
	{
		return __('Previous', 'megaforms');
	}
	/**
	 *  The default next button text
	 *
	 * @since    1.0.7
	 * @return string
	 */
	public function get_default_next_text()
	{
		return __('Next', 'megaforms');
	}
	/**
	 *  The the loaded page number based on the data available on `mf_submission`
	 *
	 * @since    1.0.8
	 * @return int
	 */
	public function get_loaded_page_number()
	{
		if ($this->loaded_page !== null) {
			return $this->loaded_page;
		}

		// Default to 1
		$number = 1;
		// Extract from `mf_submission` if this is a valid submission
		if (!mf_submission()->is_empty()) {

			// Get the current page number from the submission instance
			$page = absint(mfget('page', mf_submission()->args), 0);
			if ($page === 0) {
				$page = absint(mfget('_mf_current_page', mf_submission()->posted, $number));
			}

			if (mf_submission()->success && mf_submission()->keep_form !== true && in_array(mf_submission()->context, array('form', 'save'))) {
				$number = -1;
			} elseif (mf_submission()->success && 'page' == mf_submission()->context) {
				$number = $page + 1;
			} else {
				$number = $page;
			}
		}

		// Save the result
		$this->loaded_page = $number;

		return $number;
	}
}

MF_Containers::register(new MF_Container_Page());
