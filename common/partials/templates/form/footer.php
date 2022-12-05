<?php

/**
 * The Template for displaying megaforms footer area of the form in view
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/form/footer.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.8
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

echo '<div class="mf-submit-container">';

if (!empty($before_submit)) {
  echo $before_submit;
}

do_action('mf_footer_submit_before', $form);

echo get_mf_button($submit_type, $submit_text, $submit_attribues ?? array());

do_action('mf_footer_submit_after', $form);

if (!empty($after_submit)) {
  echo $after_submit;
}

echo '</div>';
