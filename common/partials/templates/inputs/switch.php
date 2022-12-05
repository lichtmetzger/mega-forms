<?php

/**
 * The Template for displaying custom input type: switch
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/input/switch.php.
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

if (!$labelRight) {

  printf('<label for="%s">', esc_attr($attributes['id']));

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
}

if (!empty($desc && $desc_position == 'top')) {
  printf('<span class="mf_description">%s</span>', wp_kses_post($desc));
}

$wrapper_class_attr = !empty($wrapper_class) ? mf_esc_attr('class', $wrapper_class) : '';

printf('<div %s>', $wrapper_class_attr);

if (!empty($before_input)) {
  echo $before_input;
}

// Unset value so it doesn't get added as input attribute
$value = isset($attributes['value']) ? $attributes['value'] : '';
unset($attributes['value']);

$size_class       = !empty($size) ?  sprintf(' mfswitch-size-%s', esc_attr($size)) : '';
$labelRight_class = $labelRight ?  ' mfswitch-labelright' : '';

$cbkey = 'yes';
$r_attrs = array();
if (((string) $value === (string) $cbkey) || $value === true) {
  $r_attrs['checked'] = "checked";
}
$r_attrs['value'] = $cbkey;
$r_attrs = array_merge($attributes, $r_attrs);

printf('<label class="mfswitch%s%s">', $size_class, $labelRight_class);
echo get_mf_checkbox($r_attrs);
echo '<span class="mfswitch-slider round"></span>';
echo '</label>';
if ($labelRight) {
  if (!empty($label)) {
    printf('<label for="%s" class="mf_label">%s</label>', esc_attr($attributes['id']), wp_kses_post($label));
  }
}

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
