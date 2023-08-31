<?php

/**
 * @link       https://wpali.com
 * @since      1.3.1
 */

/**
 * Conditional logic action option
 *
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MegaForms_Action_Conditional_Logic extends MF_Action_Option
{

	public $type = 'conditional_logic';

	public function handle_display_logic($display, $action)
	{
		// Enable on all actions
		return true;
	}

	public function get_option_display($action)
	{

		$label = __('Conditional Logic', 'megaforms');
		$desc = __('Create a set of rules to determine if and when this action will be triggered.', 'megaforms');
		$value = $action->get_setting_value($this->type);

		$args['id'] = $action->get_action_field_key($this->type);
		$args['label'] = $label;
		$args['after_label'] = $action->get_description_tip_markup($label, $desc);
		$is_enabled = mfget_bool_value(mfget('enable', $value));

		$cl_inputs = '';

		$cl_inputs .= mfinput('switch', array(
			'id'            => $args['id'] . '[enable]',
			'label'         => __('Enable', 'megaforms'),
			'value'         => $is_enabled,
			'labelRight'    => true,
			'attributes'    => array(),
			'wrapper_class' => 'mf-field-inputs-switch',
			'class' => 'mf-action-field-switch',
			'onchange_edit' => $action->get_js_helper_rules('none', 'handle_conditional_logic_swtich')
		), true);

		$hidden_attr = $is_enabled ? "" : " hidden='1'";
		$cl_inputs .= '<div class="mf-conditional-logic-inputs"' . esc_attr($hidden_attr) . '>';
		$cl_inputs .= '<div class="mf-input-header-rules"><strong>' . __('Trigger this action if', 'megaforms') . '</strong></div>';

		// Build conditions markup
		$rules = mfget('rules', $value);
		$rules_count = 0;
		if (is_array($rules) && !empty($rules) && isset($rules['groups']) && !empty($rules['groups'])) {
			foreach ($rules['groups'] as $rule_group) {
				$rule_group_display = $this->get_rules_group_markup($rule_group, $rules_count, $action, $is_enabled);
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
			), 0, $action, $is_enabled);
		}

		// Add a button to handle adding rules group
		$cl_inputs .= '<button class="add_rules_group_btn button" tabindex="-1">' . __('Add rules group', 'megaforms') . '</button>';
		$cl_inputs .= '</div>'; // close .mf-conditional-logic-inputs

		$args['content'] = $cl_inputs;

		$input = mfinput('custom', $args, true);

		return $input;
	}

	public function get_rules_group_markup($rules_group, $index, $action, $is_enabled)
	{

		if (is_array($rules_group) && !empty($rules_group)) {
			$rulesHTML = '';
			$input_key = $action->get_action_field_key($this->type) . '[rules][groups][' . $index . ']';
			foreach ($rules_group as $key => $rule) {
				if (!isset($rule['field']) || !isset($rule['operator']) || !isset($rule['value'])) {
					continue;
				}

				// Set fields to disabled by default when the condition is not enabled (to avoid saving fields when the form is saved)
				$disabled_attr = $is_enabled ? '' : ' disabled';

				// Extract actin label from the rule parent value
				$target_action_label = strpos($rule['field'], "|") !== false ? substr($rule['field'], strpos($rule['field'], "|") + 1) : '';

				$rulesHTML .= '<tr class="mf-conditional-rule"' . mf_esc_attr('data-key', $key) . '>';
				// Parent Field
				$rulesHTML .= '<td>';
				$rulesHTML .= '<select' . mf_esc_attr('name', $input_key . '[' . $key . '][field]') . ' class="mf-condition-rule-parent" data-placeholder="Select"' . esc_attr($disabled_attr) . '>';
				$rulesHTML .= '<option' . mf_esc_attr('value', $rule['field']) . '>' . $target_action_label . '</option>';
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

	public function evaluate_rules($rules, $posted_values)
	{
		$logic = 'or'; // Conditional logic between each rules set.
		// Prepare the rules
		$prepared_rules = array();
		foreach ($rules['groups'] as $rules_group) {
			$rules_set = array();
			foreach ($rules_group as $rule) {
				$target_field_id = strtok($rule['field'], '|'); // get anything before the first '|' ( extract target field id )
				if ($target_field_id !== "") {
					$parts = '';
					// Add the approriate string to the field name if the selected field is a sub field ( child of a compound field )
					if (($sub_key_pos = strpos($rule['field'], "[")) !== false) {
						$parts = substr($rule['field'], $sub_key_pos);
					}
					// Add this to the rules set
					$rules_set[] = array(
						'id' => $target_field_id,
						'parts' => $parts,
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

		// Now begin the evaluation
		if (!empty($prepared_rules)) {
			foreach ($prepared_rules as $rules) {
				$is_rule_met = false;
				if (isset($rules['group'])) {
					// Evaluate grouped rules
					$relation = $rules['relation'] ?? 'and';
					$is_group_rules_met = false;
					foreach ($rules['group'] as $rule) {
						$is_group_rules_met = $this->evaluate_rule($rule, $posted_values);
						// Break out of this for loop if we have a final decision about this rule ( met or not )
						if ($is_group_rules_met == false && 'and' == $relation) {
							$is_rule_met = false;
							break;
						} else if ($is_group_rules_met && 'or' == $relation) {
							$is_rule_met = true;
							break;
						}

						$is_rule_met = $is_group_rules_met;
					}
				}

				// Break out of this for loop if we have a final decision about this rule, or rules group ( met or not )
				if ($is_rule_met === false && 'and' == $logic) {
					$is_condition_met = false;
					break;
				} else if ($is_rule_met && 'or' == $logic) {
					$is_condition_met = true;
					break;
				}

				$is_condition_met = $is_rule_met;
			}
		}


		return $is_condition_met;
	}
	public function evaluate_rule($rule, $posted_values)
	{
		$id = absint($rule['id']);
		$parts = $rule['parts'];
		$operator = $rule['operator'];
		$target_value = $rule['value'];
		$is_rule_met = false;

		if (isset($posted_values[$id])) {
			if (!empty($parts)) {
				// Extract parts
				preg_match_all('/\\[(.*?)\\]/', $parts, $matches, PREG_SET_ORDER);
				if (isset($matches[0][1])) {
					$posted_value = $posted_values[$id]['values']['raw'][$matches[0][1]];
				}
			} else {
				$posted_value = $posted_values[$id]['values']['raw'];
			}

			$is_rule_met = $this->compare_values($operator, $target_value, $posted_value);
		}

		return $is_rule_met;
	}
	public function compare_values($operator, $target_value, $posted_value)
	{

		$posted_value = $posted_value ? strtolower($posted_value) : "";
		$target_value = $target_value ? strtolower($target_value) : "";

		switch ($operator) {
			case "is":
				return $target_value === $posted_value;
				break;
			case "isnot":
				return $target_value !== $posted_value;
				break;
			case "greaterthan":
				return !is_numeric($posted_value) || !is_numeric($target_value) ? false : (float)$posted_value > (float)$target_value;
				break;
			case "lessthan":
				return !is_numeric($posted_value) || !is_numeric($target_value) ? false : (float)$posted_value < (float)$target_value;
				break;
			case "contains":
				return strpos($posted_value, $target_value) !== false;
				break;
			case "doesnotcontain":
				return strpos($posted_value, $target_value) === false;
				break;
			case "beginswith":
				return substr($posted_value, 0, strlen($target_value)) === $target_value;
				break;
			case "doesnotbeginwith":
				return substr($posted_value, 0, strlen($target_value)) !== $target_value;
				break;
			case "endswith":
				return substr($posted_value, -strlen($target_value)) === $target_value;
				break;
			case "doesnotendwith":
				return substr($posted_value, -strlen($target_value)) !== $target_value;
				break;
			case "isempty":
				return $posted_value === "";
				break;
			case "isnotempty":
				return $posted_value !== "";
				break;
		}

		return false;
	}
}

MF_Extender::register_action_option(new MegaForms_Action_Conditional_Logic());
