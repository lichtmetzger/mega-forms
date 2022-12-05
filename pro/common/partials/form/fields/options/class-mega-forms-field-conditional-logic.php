<?php

/**
 * @link       https://wpali.com
 * @since      1.0.6
 */

/**
 * Conditional logic field option
 *
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MegaForms_Field_Conditional_Logic extends MF_Field_Option
{

	public $type = 'conditional_logic';
	public $priority = 50;
	public $tab = 'advanced';

	public function get_option_display($field)
	{

		$label = __('Conditional Logic', 'megaforms');
		$desc = __('Create a set of rules to determine which if and when this field will be shown or hidden.', 'megaforms');
		$value = $field->get_setting_value($this->type);

		$args['id'] = $field->get_field_key('options', $this->type);
		$args['label'] = $label;
		$args['after_label'] = $field->get_description_tip_markup($label, $desc);
		$is_enabled = mfget_bool_value(mfget('enable', $value));

		$cl_inputs = '';

		$cl_inputs .= mfinput('switch', array(
			'id'            => $args['id'] . '[enable]',
			'label'         => __('Enable', 'megaforms'),
			'value'         => $is_enabled,
			'labelRight'    => true,
			'attributes'    => array(),
			'wrapper_class' => 'mf-field-inputs-switch',
			'class' => 'mf-conditional-logic-enable',
			'onchange_edit' => $field->get_js_helper_rules('none', 'handle_conditional_logic_swtich')
		), true);

		$hidden_attr = $is_enabled ? '' : ' hidden="true"';
		$cl_inputs .= '<div class="mf-conditional-logic-inputs"' . esc_attr($hidden_attr) . '>';
		$cl_inputs .= '<div class="mf-input-header-rules"><strong>' . __('Show this field if', 'megaforms') . '</strong></div>';

		// Build conditions markup
		$rules = mfget('rules', $value);
		$rules_count = 0;
		if (is_array($rules) && !empty($rules) && isset($rules['groups']) && !empty($rules['groups'])) {
			foreach ($rules['groups'] as $rule_group) {
				$rule_group_display = $this->get_rules_group_markup($rule_group, $rules_count, $field, $is_enabled);
				if ($rule_group_display !== false) {
					$cl_inputs .= $rule_group_display;
					$rules_count++;
				}
			}
		}

		// If there are no rules, create a default
		if ($rules_count === 0) {
			$cl_inputs .= $this->get_rules_group_markup(array(
				1 => array(
					'field' => '',
					'operator' => 'is',
					'value' => ''
				)
			), 0, $field, $is_enabled);
		}

		// Add a button to handle adding rules group
		$cl_inputs .= '<button class="add_rules_group_btn button" tabindex="-1">' . __('Add rules group', 'megaforms') . '</button>';
		$cl_inputs .= '</div>'; // close .mf-conditional-logic-inputs

		$args['content'] = $cl_inputs;

		$input = mfinput('custom', $args, true);

		return $input;
	}

	public function get_rules_group_markup($rules_group, $index, $field, $is_enabled)
	{

		if (is_array($rules_group) && !empty($rules_group)) {
			$rulesHTML = '';
			$input_key = $field->get_field_key('options', $this->type) . '[rules][groups][' . $index . ']';
			foreach ($rules_group as $key => $rule) {
				if (!isset($rule['field']) || !isset($rule['operator']) || !isset($rule['value'])) {
					continue;
				}

				// Set fields to disabled by default when the condition is not enabled (to avoid saving fields when the form is saved)
				$disabled_attr = $is_enabled ? '' : ' disabled';

				// Extract field label from the rule parent value
				$target_field_label = strpos($rule['field'], "|") !== false ? substr($rule['field'], strpos($rule['field'], "|") + 1) : '';

				$rulesHTML .= '<tr class="mf-conditional-rule"' . mf_esc_attr('data-key', $key) . '>';
				// Parent Field
				$rulesHTML .= '<td>';
				$rulesHTML .= '<select' . mf_esc_attr('name', $input_key . '[' . $key . '][field]') . ' class="mf-condition-rule-parent" data-placeholder="Select"' . esc_attr($disabled_attr) . '>';
				$rulesHTML .= '<option' . mf_esc_attr('value', $rule['field']) . '>' . $target_field_label . '</option>';
				$rulesHTML .= '</select>';
				$rulesHTML .= '</td>';
				// Operator
				$rulesHTML .= '<td>';
				$rulesHTML .= $this->get_rule_operators_markup($input_key . '[' . $key . '][operator]', $rule['operator'], $is_enabled);
				$rulesHTML .= '</td>';
				// Value
				$rulesHTML .= '<td>';
				$rulesHTML .= '<input type="text"' . mf_esc_attrs(array(
					'name' => $input_key . '[' . $key . '][value]',
					'value' => $rule['value']
				)) . ' class="mf-condition-rule-value"' . esc_attr($disabled_attr) . ' />';
				$rulesHTML .= '</td>';
				// Buttons
				$rulesHTML .= '<td class="mf-condition-rule-buttons">';
				$rulesHTML .= '<button class="add_conditional_rule_btn button" tabindex="-1">and</button>';
				$rulesHTML .= '<button class="remove_conditional_rule_btn button" tabindex="-1">-</button>';
				$rulesHTML .= '</td>';

				$rulesHTML .= '</tr>';
			}

			if (!empty($rulesHTML)) {


				$rulesHTML .= '<tr class="mf-condition-rule-or">';
				$rulesHTML .= '<td>' . __('Or', 'megaforms') . '</td>';
				$rulesHTML .= '<td></td>';
				$rulesHTML .= '<td></td>';
				$rulesHTML .= '<td></td>';
				$rulesHTML .= '</tr>';

				return '<table class="mf-conditional-logic-rules"' . mf_esc_attr('data-group', $index) . '><tbody>' . $rulesHTML . '</tbody></table>';
			}
		}
		return false;
	}
	public function get_rule_operators_markup($namekey, $selected, $is_enabled)
	{
		$disabled_attr = $is_enabled ? '' : ' disabled';
		$operators = array(
			'is' => 'Is',
			'isnot' => 'Is not',
			'greaterthan' => 'Greater than',
			'lessthan' => 'Less than',
			'contains' => 'Contains',
			'doesnotcontain' => 'Doesn\'t contain',
			'beginswith' => 'Begins with',
			'doesnotbeginwith' => 'Doesn\'t begin with',
			'endswith' => 'Ends with',
			'doesnotendwith' => 'Doesn\'t end with',
			'isempty' => 'Is empty',
			'isnotempty' => 'Isn\'t empty',
		);

		$output = '';
		$output .= '<select' . mf_esc_attr('name', $namekey) . ' class="mf-condition-rule-operator" data-placeholder="Select"' . esc_attr($disabled_attr) . '>';
		foreach ($operators as $key => $val) {
			$output .= '<option' . mf_esc_attr('value', $key) . ' ' . selected($selected, $key, false) . '>' . $val . '</option>';
		}
		$output .= '</select>';

		return $output;
	}
	public function handle_display_logic($display, $field)
	{
		if ($field->isStaticField) {
			return false;
		}

		$excluded_fields = array(
			'hidden'
		);

		if (in_array($field->type, $excluded_fields)) {
			return false;
		}

		return true;
	}
	public function get_field_arguments($field)
	{
		if (!$field->is_editor) {
			//build the rules
			$conditional_logic = $field->get_setting_value($this->type);
			$is_enabled = mfget_bool_value(mfget('enable', $conditional_logic));
			if ($is_enabled) {

				$rules = $conditional_logic['rules']['groups'] ?? array();
				if (!empty($rules)) {
					$prepared_rules = array();
					foreach ($rules as $rules_group) {
						$rules_set = array();
						foreach ($rules_group as $rule) {
							$target_field_id = strtok($rule['field'], '|'); // get anything before the first '|' ( extract target field id )
							if ($target_field_id !== "") {
								$target_field_name = mf_api()->get_field_key($field->form_id, (int)$target_field_id);
								// Add the approriate string to the field name if the selected field is a sub field ( child of a compound field )
								if (($sub_key_pos = strpos($rule['field'], "[")) !== false) {
									$target_field_name .= substr($rule['field'], $sub_key_pos);
								}
								// Add this to the rules set
								$rules_set[] = array(
									'name' => $target_field_name,
									'operator' => $rule['operator'],
									'value' => $rule['value'],
								);
							}
						}
						if (!empty($rules_set)) {
							$prepared_rules[] = array(
								'relation' => 'and',
								'group' => $rules_set
							);
						}
					}

					if (!empty($prepared_rules)) {
						// Make the field hidden by default
						$field->isHiddenField = true;
						// Return the `conditional_rules` argument 
						return array(
							array(
								'key' => 'conditional_rules',
								'value' => $field->get_js_conditional_rules('show', $prepared_rules, 'or', '.mfield')
							)
						);
					}
				}
			}
		}

		return false;
	}
}

MF_Extender::register_field_option(new MegaForms_Field_Conditional_Logic());
