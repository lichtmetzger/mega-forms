<?php

/**
 * The Template for displaying sub fields of custom fields
 * The input should be provided in the caller function.
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/input/subcustom.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.6
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (!empty($before_field)) {
  echo $before_field;
}

$wrapper_classes = !empty($wrapper_class) ?  'mf_sub_field ' . $wrapper_class : 'mf_sub_field';

$wrapper_tag_name   = !empty($wrapper_tag) ?  $wrapper_tag : 'span';
$desc_classes = 'mf_sub_label';
$style_attr = !empty($desc_position) &&  $desc_position == 'hidden' ? ' style="display:none;"' : '';

printf('<%1$s class="%2$s">', esc_attr($wrapper_tag_name), esc_attr($wrapper_classes));


if ($desc_position == 'top') {
  printf('<label for="%1$s" id="%1$s" class="%2$s"%3$s>%4$s</label>', esc_attr($attributes['name']), esc_attr($desc_classes), $style_attr, esc_html($desc));
}

if (!empty($before_input)) {
  echo $before_input;
}

echo $content;

if (!empty($after_input)) {
  echo $after_input;
}

if (empty($desc_position) ||  $desc_position == 'bottom' || $desc_position == 'hidden') {
  printf('<label for="%1$s" id="%1$s" class="%2$s"%3$s>%4$s</label>', esc_attr($attributes['name']), esc_attr($desc_classes), $style_attr, esc_html($desc));
}

printf('</%s>', $wrapper_tag_name);

if (!empty($after_field)) {
  echo $after_field;
}
