<?php

/**
 * Mega Forms View Class
 *
 * @link       https://wpali.com
 * @since      1.0.8
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/public/partials
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (defined('DOING_CRON')) {
  return; // Return if the current request is doing a cron job
}

class MF_Form_View
{

  private $form;
  private $title;
  private $description;

  public function __construct($form, $title = false, $description = false)
  {

    if (empty($form)) {
      return;
    }

    # Allow getting fields by form ID
    if (is_numeric($form)) {
      $form = mfget_form($form);
    }

    $this->form = $form;
    $this->title = $title;
    $this->description = $description;
  }
  /**
   * Display form based on ID
   *
   * @since    1.0.8
   *
   * @return string
   */
  public function form_display()
  {

    ob_start();

    # Make sure the form is available and active.
    if (empty($this->form) || $this->form === false || $this->form->is_trash == 1  || $this->form->is_active == 0 || empty($this->form->fields)) {

      if (empty($this->form) || $this->form === false) {
        echo '<!-- Mega Forms: form does not exist. -->';
      } elseif ($this->form->is_trash == 1) {
        echo '<!-- Mega Forms: form is trashed. -->';
      } elseif ($this->form->is_active == 0) {
        echo '<!-- Mega Forms: form is not active. -->';
      } elseif (empty($this->form->fields)) {
        echo '<!-- Mega Forms: form does not have any available fields. -->';
      }

      return ob_get_clean();
    }

    # Before displaying the form, make sure the user is allowed to see it.
    $is_restricted  = mfget('login_restricted', $this->form->settings);
    $is_limited     = mfget('limited_entries', $this->form->settings);
    if ($is_restricted || $is_limited) {

      # Make sure validation text is loaded before we can retrieve it for later use.
      $validation_messages = mf_api()->get_validation_messages($this->form);

      # Check if user logged in
      $user_logged = is_user_logged_in();
      if ($is_restricted && !$user_logged) {
        $login_restricted_text = mfget('login_restricted_msg', $this->form->settings);
        $login_restricted_msg = !empty($login_restricted_text) ? $login_restricted_text : mfget('form_validation_login_restricted', $validation_messages);
        echo do_shortcode($login_restricted_msg);
        return ob_get_clean();
      }
      # Check if limit is reached
      $submission_limit = mfget('form_submission_limit', $this->form->settings);
      $leads_count = mf_api()->get_form_entry_count($this->form->ID);
      $leads_limit = !empty($submission_limit) && is_numeric($submission_limit) ? (int) $submission_limit : false;
      if ($is_limited && $leads_limit !== false && $leads_count >= $leads_limit) {
        $limit_reached_text = mfget('limit_reached_msg', $this->form->settings);
        $limit_reached_msg = !empty($limit_reached_text) ? $limit_reached_text : mfget('form_validation_limit_reached', $validation_messages);
        echo do_shortcode($limit_reached_msg);
        return ob_get_clean();
      }
    }

    # Action hook before the output
    do_action('mf_form_view_output_before', $this->form);


    # Increase form view count by one if this is not a submit request
    if (mf_submission()->is_empty()) {
      mf_api()->set_form_view($this->form->ID);
    }

    # Define necessary variables ( Allow them to be customized )
    $classes = apply_filters('mf_view_form_tag_classes', array(
      'mform_container',
      $this->form->settings['form_css_class'],
    ), $this->form);

    $this->form_attrs = apply_filters('mf_view_form_tag_attributes', array(
      'id'    => sprintf('mega-form-%d', absint($this->form->ID)),
      'class' => 'single-mega-form',
      'method'  => 'post',
      'enctype' => 'multipart/form-data',
      'action'  => '',
      'data-id'  => absint($this->form->ID),
    ), $this->form);

    $this->form_attributes = array();
    foreach ($this->form_attrs as $attr => $val) {
      $this->form_attributes[] = esc_attr($attr) . '="' . esc_attr($val) . '"';
    }

    # Start building the output
    printf('<div id="mform_%d" class="%s" novalidate="novalidate">', $this->form->ID, implode(' ', $classes));
    printf('<form %s>', implode(' ', $this->form_attributes));

    # Save a reference to the submission status for this specific form.
    $is_posted = !mf_submission()->is_empty() && isset(mf_submission()->form->ID) && mf_submission()->form->ID == $this->form->ID ? true : false;
    $is_success = $is_posted && mf_submission()->success;
    $keep_form = $is_posted && mf_submission()->context == 'form' ? mf_submission()->keep_form : true;

    $args = array('form' => $this->form);
    $args['is_posted'] = $is_posted;
    $args['success']   = $is_success;
    # Reset submission values if the submission of this form is successful
    if ($is_posted && $args['success'] && mf_submission()->context == 'form') {
      mf_submission()->posted = null;
      mf_submission()->submission_values = array();
    }

    # Get fluid content ( container markup that should appear outside field wrapper regardless of how many times the container is used )
    $below_header = '';
    $below_body = '';
    $below_footer = '';
    $fluid_args = array();
    $fluid_header_args = array();
    $fluid_footer_args = array();

    $container_types = MF_Containers::get_container_types($this->form);
    if (!empty($container_types)) {
      foreach ($container_types as $container_type) {
        $ctn = MF_Containers::get($container_type, array(
          'form_id' => $this->form->ID,
          'form' => $this->form,
          'settings' => $this->form->containers['settings'][$container_type] ?? false
        ));
        if ($ctn->is_fluid) {
          $fluid_data = $ctn->get_fluid_data();
          # Content
          $below_header .= $fluid_data['below_header'] ?? '';
          $below_body .= $fluid_data['below_body'] ?? '';
          $below_footer .= $fluid_data['below_footer'] ?? '';
          # Template arguements
          if (isset($fluid_data['args'])) {
            $fluid_args = array_merge($fluid_args, $fluid_data['args']);
          }
          if (isset($fluid_data['header_args'])) {
            $fluid_header_args = array_merge($fluid_header_args, $fluid_data['header_args']);
          }
          if (isset($fluid_data['footer_args'])) {
            $fluid_footer_args = array_merge($fluid_footer_args, $fluid_data['footer_args']);
          }
        }
      }
    }

    # Merge fluid arguements, if they exist
    if (!empty($fluid_args)) {
      $args = array_merge($args, $fluid_args);
    }

    /**
     * Load head area of the form
     */
    $header_args = $args;
    $header_args['show_title'] = $this->title;
    $header_args['show_desc']  = $this->description;
    $header_args['message']    = mf_submission()->message;

    # Merge fluid header arguements, if they exist
    if (!empty($fluid_header_args)) {
      $header_args = array_merge($header_args, $fluid_header_args);
    }

    $header_template = mfget_template_filename('form', 'header');
    echo '<div class="mform_header">';
    mflocate_template($header_template, $header_args);
    echo '</div>';

    if (!empty($below_header)) {
      echo $below_header;
    }

    /**
     * Load body area of the form
     */
    $body_template = mfget_template_filename('form', 'body');
    # Return nothing if the form is set to hide after a successful submission
    if ($is_posted && $is_success && $keep_form !== true) {
      do_action('mf_form_view_body_alt', $this->form, $args);
    } else {
      echo '<div class="mform_body">';
      echo '<ul class="mform_fields">';
      mflocate_template($body_template, $args);
      echo '</ul>';
      echo '</div>';

      if (!empty($below_body)) {
        echo $below_body;
      }
    }

    /**
     * Load footer area of the form
     */
    $footer_template = mfget_template_filename('form', 'footer');
    if ($is_posted && $is_success && $keep_form !== true) {
      do_action('mf_form_view_footer_alt', $this->form, $args);
    } else {

      $footer_args = $args;
      $footer_args['submit_type'] = 'submit';
      $footer_args['submit_text']  = $this->form->settings['submit_button'];
      $footer_args['submit_attribues']  = array(
        'id' => 'mf-submit-' . $this->form->ID,
        'name' => 'mform_submit',
        'class' => 'button mf-submit-btn',
        'formnovalidate' => 'formnovalidate'
      );

      # Merge fluid footer arguements, if they exist
      if (!empty($fluid_footer_args)) {
        $footer_args = array_merge($footer_args, $fluid_footer_args);
      }

      echo '<div class="mform_footer">';
      $this->form_hidden_inputs();
      mflocate_template($footer_template, $footer_args);
      echo '</div>';

      if (!empty($below_footer)) {
        echo $below_footer;
      }
    }

    echo '</form>';
    echo '</div>';

    # Action hook after the output
    do_action('mf_form_view_output_after', $this->form);


    return ob_get_clean();
  }
  /**
   * Output the form hidden inputs ( Set of hidden inputs to use for stats and for extra security )
   *
   * @since    1.0.8
   */
  public function form_hidden_inputs()
  {

    # Generate a unique token for current form and save it to user session.
    $referrer = wp_doing_ajax() && wp_get_referer() ? esc_attr(wp_get_referer()) : esc_attr(wp_unslash($_SERVER['REQUEST_URI']));
    $session_token_id = get_mf_session_token_id($this->form->ID, $referrer);
    $session_referrer_id = get_mf_session_referrer_id($this->form->ID, $referrer);
    $form_token = mf_session()->get($session_token_id);
    $form_referrer = mf_session()->get($session_referrer_id);

    if (empty($form_token) || empty($form_referrer)) {
      $form_token  = esc_attr(wp_generate_uuid4());
      $form_referrer  = $referrer;
      mf_session()->set($session_token_id, $form_token);
      mf_session()->set($session_referrer_id, $form_referrer);
    }

    ob_start();
    # Form ID
    echo '<input type="hidden" name="_mf_form_id" value="' . esc_attr($this->form->ID) . '">';
    # Referer hidden field.
    echo '<input type="hidden" name="_mf_referrer" value="' . $form_referrer . '">';
    # Unique form token
    echo '<input type="hidden" name="_mf_nonce" value="' . $form_token . '">';
    # WP Nonce
    echo '<input type="hidden" name="_mf_extra_nonce" value="' . wp_create_nonce($form_token) . '">';
    # Timestamp
    echo '<input type="hidden" name="_mf_t_token" value="' . base64_encode(json_encode(time() * $this->form->ID)) . '">';
    # Safety/security unique token that changes on each page load
    echo '<input type="hidden" name="_mf_s_token" value="' . sprintf( '%04x-%04x-%04x-%04x', crc32(microtime()), wp_rand(), mt_rand(0, 0xffff), mt_rand(0, 0x3fff) | 0x8000,) . '">';

    do_action('mf_after_hidden_inputs', $this->form);

    $output = ob_get_clean();
    echo $output;
  }
}
