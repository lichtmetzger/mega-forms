<?php

/**
 * The Template for displaying megaforms header area of the form in view
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/form/header.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

# Form title
if ($show_title) {
  echo '<h3 class="mform_title">' . $form->title . '</h3>';
}
# Form Description
if ($show_desc) {
  echo '<span class="mform_desc">' . $form->settings['form_description'] . '</span>';
}

# Form response markup
$response_wrapper_classes = $is_posted && !empty($message) ? 'mform_response_msg' : 'mform_response_msg mf_hidden';

echo '<div class="' . $response_wrapper_classes . '">';
if (!empty($message)) {
  $message_type = $is_posted && $success ? 'success' : 'error';
  echo get_mf_submission_msg_html($message_type, $message);
}
echo '</div>';
