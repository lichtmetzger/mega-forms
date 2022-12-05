<?php

/**
 * The Template for displaying custom fields
 * The content should be provided in the caller function.
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/input/custom.php.
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

if (!empty($label)) {

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
}

if (!empty($desc && $desc_position == 'top')) {
  printf('<span class="mf_description">%s</span>', wp_kses_post($desc));
}

$wrapper_class_attr = !empty($wrapper_class) ? mf_esc_attr('class', $wrapper_class) : '';

printf('<div %s>', $wrapper_class_attr);

if (!empty($before_input)) {
  echo $before_input;
}

echo $content;

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
