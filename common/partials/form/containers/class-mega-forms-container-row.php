<?php

/**
 * @link       https://wpali.com
 * @since      1.0.6
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/containers
 */

/**
 * Text action type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/includes/partials/form/containers
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Container_Row extends MF_Container
{

	public  $type = 'row';

	public function get_container_title()
	{
		return __('Row', 'megaforms');
	}

	public function get_container_icon()
	{
		return 'dashicons-grid-view';
	}
	public function get_container_display()
	{

		$the_columns = '';
		$columns = !empty($this->data['columns']) ? $this->data['columns'] : array();
		$column_count = count($columns);
		$i = 1;
		if ($column_count > 0) {
			# Loop through the columns available for the current container
			foreach ($columns as $column) {

				$column_fields = '';
				$col_fields = !empty($column['fields']) ? $column['fields'] : array();

				foreach ($col_fields as $field_id) {
					# Use field ID to get the html out for that field type
					if (isset($this->form->fields[$field_id])) {
						$field = mf_api()->get_field($this->form->fields[$field_id], $this->is_editor);

						if (!empty($field)) {
							$column_fields .= $field;
						}
					}
				}

				# Insert the fields inside a column
				$the_columns .=  $this->get_column_output($column['width'], $column_fields);
				$i++;
			}
		}

		if (empty($content)) {
			$content = $this->get_column_output();
		}

		# Insert the columns inside the wrapper and append return the final markup
		return $this->get_row_output($the_columns, $column_count);
	}

	public function get_editor_inline_JS()
	{
		$output = '';
		# Container markup
		$output .= '<script type="text/html" id="tmpl-single_row">';
		$output .= $this->get_row_output();
		$output .= '</script>';

		# Column markup
		$output .= '<script type="text/html" id="tmpl-single_column">';
		$output .= $this->get_column_output();
		$output .= '</script>';

		# Container Layout options template
		$output .= '<script type="text/html" id="tmpl-container_layout">';
		$output .= '<div id="mf_ctn_layout" class="disable-sorting">';
		$output .= '<label class="mf_column-count__label" for="mf-column-size">Columns</label>';
		$output .= '<input class="mf_column-count__slider" id="mf-column-size" type="range" min="1" max="3" required="" value="{{data.count}}">';
		$output .= '<span class="mf_column-count__number">{{data.count}}</span>';
		$output .= '<span class="mf_column-count__desc">Number of columns</span>';
		$output .= '<button class="mf_column-count__save" type="button" value="Save">Save</button>';
		$output .= '</div>';
		$output .= '</script>';

		return $output;
	}
	public function sanitize_data()
	{
		$sanitized = array();
		$sanitized['type'] = $this->type;
		$sanitized['columns'] = mfget('columns', $this->data, array(
			array('width' => '100%'),
			array('fields' => array())
		));

		return $sanitized;
	}
	/**
	 *  Get the container for editor view.
	 *
	 * @since    1.0.0
	 * @param string $additional_class
	 * @return string
	 */
	public function get_single_row($additional_class = '')
	{

		$html = '';
		$html .= '<li class="' . $this->get_container_classes($additional_class) . '" data-container="' . $this->type . '">';
		$html .= $this->get_controls_content('trash', array(
			'layout' => array(
				'label' => __('Columns', 'megaforms'),
				'icon' => 'dashicons dashicons-grid-view'
			)
		));
		$html .= '{MF_ROW_CONTENT}';
		$html .= '</li>';

		return $html;
	}

	/**
	 *  Get the container inner columns
	 *
	 * @since    1.0.0
	 * @param string $additional_class
	 * @return string
	 */
	public function get_single_column($width = 100, $additional_class = '')
	{

		# Make sure the `$additional_class` is turned into an array()
		if (!is_array($additional_class) && empty($additional_class)) {
			$additional_class = array();
		} elseif (!empty($additional_class)) {
			$additional_class = array($additional_class);
		}

		if (!$this->is_editor) {
			# Add classes conditionally
			if ($width == 100) {
				$additional_class[] = 'mfcol-size-full';
			} elseif ($width == 75) {
				$additional_class[] = 'mfcol-size-third';
			} elseif ($width == 50) {
				$additional_class[] = 'mfcol-size-half';
			} elseif ($width == 25) {
				$additional_class[] = 'mfcol-size-fourth';
			} else {
				$additional_class[] = 'mfcol-size-custom';
			}
		}

		# Add styles if needed
		$style_attr = !empty($width) ? ' style="width:' . (int) $width . '%"' : '';

		$html = '';
		$html .= '<ul class="' . $this->get_column_classes($additional_class) . '"' . $style_attr . '>';
		$html .= '{MF_COLUMN_CONTENT}';
		$html .= '</ul>';

		return $html;
	}

	/**
	 *  Get the columns css classes.
	 *
	 * @since    1.0.0
	 * @param string $additional_class
	 * @return string
	 */
	public function get_column_classes($additional_class = '')
	{

		$classes     = array(
			'mf_col', # Main CSS Class added to all columns
			'mf-' . $this->type . '-col',
		);

		if ($this->is_editor) {
			$classes[]   = 'mfcol_preview'; # Preview CSS Class added only on the editor view
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
	 * Insert any defined content inside the wrapper
	 *
	 * @since    1.0.0
	 * @param  string $content
	 * @return string
	 */
	public function get_row_output($content = '', $count = 1)
	{

		// If no content assigned, assign a single column as the content
		if (empty($content)) {
			$content = $this->get_column_output();
		}

		$wrapper = $this->get_single_row('mf-' . $count . '-cols');

		return str_replace('{MF_ROW_CONTENT}', $content, $wrapper);
	}

	/**
	 * Insert any defined content inside the column
	 *
	 * @since    1.0.0
	 * @param  string $content
	 * @return string
	 */
	public function get_column_output($width = 100, $content = '')
	{

		$column = $this->get_single_column($width);

		return str_replace('{MF_COLUMN_CONTENT}', $content, $column);
	}
}

MF_Containers::register(new MF_Container_Row());
