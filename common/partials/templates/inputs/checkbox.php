<?php

/**
 * The Template for displaying input type: checkbox
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/input/checkbox.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

printf('<%s %s>', $container_tag, mf_esc_attr('class', $container_class));

if (!empty($before_field)) {
  echo $before_field;
}

printf('<label for="%s"%s>', esc_attr($attributes['id']), $label_hidden ? mf_esc_attr("class", 'mf_hidden') : '');

if (!empty($before_label)) {
  echo $before_label;
}

if (!empty($label)) {
  printf('<span class="mf_label">%s</span>', wp_kses_post($label));
}

if (!empty($after_label)) {
  echo $after_label;
}

if ($required) {
  echo '<span class="mf_required">*</span>';
}

echo '</label>';

if (!empty($desc && $desc_position == 'top')) {
  printf('<span class="mf_description">%s</span>', wp_kses_post($desc));
}

$wrapper_class_attr = !empty($wrapper_class) ? mf_esc_attr('class', $wrapper_class) : '';

printf('<div %s>', $wrapper_class_attr);

if (!empty($before_input)) {
  echo $before_input;
}

echo '<ul class="mf-choice mf-checkboxes">';

// Unset value and options so they don't get added as input attributes
$value = isset($attributes['value']) ? $attributes['value'] : '';
unset($attributes['value']);
// Define whether this field is going to include one checkbox or multiple
$multi = false;
if (count($options) > 1) {
  $multi = true;
}
// If there are multiple options, remove the ID to avoid duplicate IDs
if ($multi && isset($attributes['id'])) {
  unset($attributes['id']);
}

// build attributes variables
$all_attrs = array();
$specific_attrs = array();
if (!empty($attributes) && is_array($attributes)) {
  foreach ($attributes as $attrKey => $attrVal) {
    if (isset($options[$attrKey])) {
      $specific_attrs[$attrKey] = $attrVal;
      unset($attributes[$attrKey]);
    } else {
      $all_attrs[$attrKey] = $attrVal;
    }
  }
}
// build the markup for our checkbox(es)
foreach ($options as $key => $cblabel) {

  $option_attrs = array();
  $option_attrs['value'] = $key;
  if ((is_array($value) && in_array($key, $value)) || (!is_array($value) && (string) $value === (string) $key)) {
    $option_attrs['checked'] = 'checked';
  }
  $option_attrs = array_merge($option_attrs, $all_attrs);
  if (isset($specific_attrs[$key]) && is_array($specific_attrs[$key])) {
    $option_attrs = array_merge($option_attrs, $specific_attrs[$key]);
  }

  echo '<li><label>';
  echo get_mf_checkbox($option_attrs, $multi);
  echo '<span class="mf-choice-desc mf-checkbox-desc">' . $cblabel . '</span>';
  echo '</label></li>';
}
echo '</ul>';

if (!empty($after_input)) {
  echo $after_input;
}

echo '</div>';

if (!empty($desc && $desc_position == 'bottom')) {
  printf('<span class="mf_description">%s</span>', wp_kses_post($desc));
}

if (!empty($after_field)) {
  echo $after_field;
}

printf('</%s>', $container_tag);
