<?php

/**
 * Mega Forms Class Responsible for Processing Form Submissions
 *
 * @link       https://wpali.com
 * @since      1.0.4
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/public/partials
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (is_admin()  && !defined('DOING_AJAX')) {
  return; // Exit if current request came from admin screen, but is not an ajax request
}

if (defined('DOING_AJAX') && isset($_POST['action']) && ($_POST['action'] !== 'megaforms_public_request' || $_POST['action'] !== 'megaforms_admin_request')) {
  return; // Exit if current request is ajax call made outside the plugin
}


class MF_Form_Submit
{
  /**
   * The single instance of the class.
   *
   * @var MF_Form_Submit
   * @since 1.0.0
   */
  protected static $_instance = null;
  /**
   * Holds the status of the form submission.
   *
   * @since 1.0.0
   * @var bool
   */
  public $success = false;

  /**
   * Stores the response message ( error and success )
   *
   * @since 1.0.0
   * @var string
   */
  public $message;

  /**
   * Holds the success redirect URL, if required.
   *
   * @since 1.0.0
   * @var array
   */
  public $redirect = false;

  /**
   * Whether to keep the form in display after a successfull submission
   *
   * @since 1.0.7
   * @var array
   */
  public $keep_form = false;

  /**
   * Stores the list of notices.
   *
   * @since 1.0.0
   * @var array
   */
  public $notices = array();

  /**
   * Stores the list of data for notice keys.
   *
   * @since 1.0.0
   * @var array
   */
  public $compound_notices = array();

  /**
   * Holds the form object ( fields, settings and actions ).
   *
   * @since 1.0.0
   *
   * @var object
   */
  public $form;

  /**
   * Hold the request data ( $_POST )
   *
   * @since 1.0.0
   *
   * @var array
   */
  public $posted;
  /**
   * Hold the submission values in different formats ( non-formatted sanitized values, text formatted, html formatted )
   *
   * @since 1.0.0
   *
   * @var array
   */
  public $submission_values = array();

  /**
   * Holds the CLASS status ( Whether a form submission has been handled by this CLASS or not ).
   *
   * @since 1.0.0
   *
   * @var array
   */
  protected $is_empty = true;

  /**
   * Holds the validation text strings needed in our response.
   *
   * @since 1.0.0
   *
   * @var array
   */
  public $validation_text = array();

  /**
   * Holds any additional arguements passed to the `start` method.
   *
   * @since 1.0.7
   *
   * @var array
   */
  public $args = array();

  /**
   * Whether this submission was detected as a spam submission
   *
   * @since 1.2.7
   *
   * @var bool
   */
  public $is_spam = false;

  /**
   * This will hold the status of custom submissions ( submission with the context not set to 'form', 'entry' )
   *
   * @since 1.0.7
   *
   * @var bool
   */
  public $custom_submission_valid = false;


  /**
   * Main MF_Form_Submit Instance.
   *
   * Ensures only one instance of MF_Form_Submit is loaded or can be loaded.
   *
   * @since 1.0.0
   * @see mf_api()->submit()
   * @return MF_Form_Submit - Main instance.
   */
  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Define required variables for this class.
   *
   * This must be called before header is sent to be able to handle redirections.
   * If the response output will be handled by JS, this can be loaded and started only when needed.
   *
   * @since 1.0.7
   *
   */
  public function exec($form, $post_data, $context = '', $args = array())
  {

    # Allow getting fields by form ID
    if (is_numeric($form)) {
      $form = mfget_form($form);
    }

    # Prepare posted data
    $posted = $this->prepare_request($post_data);

    # Load form object, posted data and validation messages each into a class property for later use ( required )
    $this->form            = $form;
    $this->posted          = apply_filters('mf_posted_data_before_process', $posted, $form, $context);
    $this->validation_text = mf_api()->get_validation_messages($form);
    $this->context         = !empty($context) ? $context : 'form';
    $this->args            = !empty($args) ? $args : array();
    $this->is_empty        = false;

    # Start processing the form
    if ($this->context == 'form') {
      $this->process_submission();
    } elseif ($this->context == 'entry') {
      $this->process_entry_changes();
    } else {
      $this->process_custom_submission();
    }
  }

  /**
   * replace the submitted field keys with their ID, and put them together in one array.
   *
   * @since 1.0.0
   *
   * @param string $posted Request data.
   * @return array Prepared request data
   */
  protected function prepare_request($posted)
  {

    $prepared = array();
    $prepared['fields'] = array();
    // Extract posted data
    if (is_array($posted)) {
      foreach ($posted as $key => $val) {
        // See if this is a field key, then extract the ID from it, and save them into a sub array.
        if (($pos = strpos($key, '_mfield_')) !== false) {
          $field_id = substr($key, $pos + 8);
          $prepared['fields'][$field_id] = $val;
        } else {
          $prepared[$key] = $val;
        }
      }
    }

    return $prepared;
  }

  /**
   * Process default form submission
   *
   * @since 1.0.7
   */
  protected function process_submission()
  {

    try {

      do_action('mf_submission_validation', $this);

      // Validation
      $this->validate_submitted_form();
      $this->validate_submitted_fields();
    } catch (Exception $e) {

      $this->message = $e->getMessage();

      return false;
    }

    # Processing
    $this->process_actions();
    $this->create_entry();

    # Set confirmation message as a fallback, and redirects if available
    $confirmation_type = mfget('confirmation_type', $this->form->settings);
    $confirmation_message = mfget('confirmation_message', $this->form->settings);
    $this->message = !empty($confirmation_message) ? $confirmation_message : $this->get_validation_text('form_validation_success');
    $this->keep_form = mfget('keep_form', $this->form->settings, false);

    switch ($confirmation_type) {
      case 'page':
        $confirmation_page = mfget('confirmation_page', $this->form->settings);
        $page_link = get_page_link($confirmation_page);
        if (filter_var($page_link, FILTER_VALIDATE_URL) !== FALSE) {
          $this->redirect = $page_link;
        }
        break;
      case 'redirect':
        $confirmation_redirect = mfget('confirmation_redirect', $this->form->settings);
        if (filter_var($confirmation_redirect, FILTER_VALIDATE_URL) !== FALSE) {
          $this->redirect = $confirmation_redirect;
        }
        break;
    }
    # Set the rest
    $this->success = true;

    do_action('mf_submission_completed', $this);

    return true;
  }

  /**
   * Process entry changes
   *
   * @since 1.0.7
   */
  protected function process_entry_changes()
  {

    try {
      // Validation
      $this->validate_entry_form();
      $this->validate_entry_fields();
    } catch (Exception $e) {

      $this->message = $e->getMessage();

      return false;
    }

    # Saving
    $this->save_entry_changes();
    # Set variables
    $this->success = true;
    $this->message = __('Changes were successfully saved! Refreshing...', 'megaforms');

    return true;
  }

  /**
   * Process custom form submission
   *
   * @since 1.0.7
   */
  protected function process_custom_submission()
  {

    try {

      do_action('mf_custom_submission_validation', $this);

      // Extra security to ensure fraud submissions don't pass the validation
      if ($this->custom_submission_valid !== true) {
        throw new Exception($this->get_validation_text('form_validation_invalid_custom_submission'));
      }
    } catch (Exception $e) {

      $this->message = $e->getMessage();

      do_action('mf_custom_submission_failed', $this);

      return false;
    }

    # Set variables
    $this->success = true;

    do_action('mf_custom_submission_completed', $this);

    return true;
  }

  /**
   * Run all needed processes to validate the form from settings.
   *
   * @since 1.0.4
   *
   */
  public function validate_submitted_form()
  {

    # Check if form exists
    if (!$this->form || empty($this->posted)) {
      throw new Exception($this->get_validation_text('form_validation_invalid_submission'));
    }

    # Validate form token from user session
    $submitReferrer = mfget_cleaned_url(mfpost('_mf_referrer', $this->posted));
    $sessionReferrer = mfget_cleaned_url(mf_session()->get(get_mf_session_referrer_id($this->form->ID, $submitReferrer)));
    $submitNonce = mfpost('_mf_nonce', $this->posted);
    $sessionNonce = mf_session()->get(get_mf_session_token_id($this->form->ID, $submitReferrer));
    $bypassSessionErrors = apply_filters('mf_bypass_session_error', false, $submitReferrer, $sessionReferrer, $submitNonce, $sessionNonce);

    if (!$submitNonce || !$sessionNonce || !$submitReferrer || !$sessionReferrer || $submitNonce !== $sessionNonce || $submitReferrer !== $sessionReferrer) {
      // Allow filtering the session exception error
      if (!$bypassSessionErrors) {
        throw new Exception($this->get_validation_text('form_validation_session_error'));
      }
    }

    # Verify wp nonce
    $wp_nonce = mfpost('_mf_extra_nonce', $this->posted, false);
    $verify_wp_nonce = wp_verify_nonce($wp_nonce, $sessionNonce);

    if (!$verify_wp_nonce && !$bypassSessionErrors) {
      throw new Exception($this->get_validation_text('form_validation_wpnonce_error'));
    }

    # Verify timetrap ( Anti-Spam )
    $is_timetrap_enabled = mfget('time_trap', $this->form->settings);
    if ($is_timetrap_enabled) {
      $timetrap = mfpost('_mf_t_token', $this->posted);
      $timetrap_decoded = json_decode(base64_decode($timetrap));
      $minimumTime = mfget('time_trap_duration', $this->form->settings, 5);
      if (is_numeric($timetrap_decoded) && time() - ($timetrap_decoded / $this->form->ID) < $minimumTime) {
        throw new Exception($this->get_validation_text('form_validation_timetrap_error'));
      }
    }

    # Check if honeypot field was populated ( Anti-Spam )
    $honeypot_value = $this->get_value('hp');
    if (!empty($honeypot_value)) {
      throw new Exception($this->get_validation_text('form_validation_honeypot_error'));
    }

    # Check if there is a limitation on this form
    $is_limited = mfget('limited_entries', $this->form->settings);
    if ($is_limited) {
      $submission_limit = mfget('form_submission_limit', $this->form->settings);
      $leads_count = mf_api()->get_form_entry_count($this->form->ID);
      $leads_limit = !empty($submission_limit) && is_numeric($submission_limit) ? (int) $submission_limit : false;
      if ($is_limited && $leads_limit !== false && $leads_count >= $leads_limit) {
        $limit_reached_text = mfget('limit_reached_msg', $this->form->settings);
        $limit_reached_msg = !empty($limit_reached_text) ? $limit_reached_text : $this->get_validation_text('form_validation_limit_reached');
        throw new Exception($limit_reached_msg);
      }
    }
  }
  /**
   * Run all needed processes to validate the fields.
   *
   * @since 1.0.7
   *
   */
  public function validate_submitted_fields()
  {

    $fields = mfget_form_fields($this->form);
    $result = $this->validate_fields($fields);

    if ($result['valid'] === false) {
      // Submission not valid
      throw new Exception($this->get_validation_text('form_validation_errors'));
    } else {
      // If spam was detected, set the submission as "spam"
      if ($result['spam']) {
        $this->is_spam = true;
      }
      // Submission is valid
      $this->submission_values = $result['values'];
    }
  }

  /**
   * Perform any tasks or success actions associated with this form ( Emails, API requests...etc ).
   *
   * @since 1.0.3
   *
   */
  public function process_actions()
  {

    // Do not process any actions if the current submission is considered spam
    if ($this->is_spam) {
      return false;
    }

    # Store current user submission count to mf session (to_extend: enable single submission or you already submitted message)
    $form_submitted_key = 'form_' . $this->form->ID . '_submitted';
    $user_submission_count = 1;
    $user_submission = mf_session()->get($form_submitted_key);
    if (!empty($submit_count)) {
      $user_submission_count = (int) ($user_submission) + 1;
    }
    mf_session()->set($form_submitted_key, $user_submission_count);

    # Process field actions
    $task_fields = array_filter($this->submission_values, function ($v) {
      return isset($v['has_task']);
    });

    if (!empty($task_fields)) {
      foreach ($task_fields as $field_id => $submission_value) {
        $field = $this->form->fields[$field_id] ?? null;
        if ($field) {
          $fieldObject = MF_Fields::get($field['type'], array('field' => $field));
          $this->submission_values[$field_id] = $fieldObject->post_submission_task($submission_value);
        }
      }
    }

    # Process Form Actions
    $count = 0;
    $actions = $this->form->actions;

    if (is_array($actions) && !empty($actions)) {
      foreach ($actions as $action) {
        if (!mfget('enabled', $action, false)) {
          continue;
        }

        // Prepare action data before saving as a task ( avoid issues with merge-tags, current user data, current page, GET, POST data...etc )
        $mf_action = MF_Actions::get($action['type'], array('action' => $action));
        $prepared_data = $mf_action->pre_process_action($this->submission_values);
        // // Build the task array
        $task = array(
          'type' => 'form_action',
          'data' => array(
            'action' => $action,
            'posted_data' => $this->submission_values,
            'prepared_data' => $prepared_data,
          ),
        );

        mf_tasks()->push_to_queue($task);
        $count++;
      }
    }

    if ($count > 0) {
      mf_tasks()->save()->dispatch();
    }
  }

  /**
   * Create the submission entry and associated meta data.
   *
   * @since 1.0.7
   *
   */
  public function create_entry()
  {

    # If storing entries is disabled, bail out.
    $is_storing_entries_disabled = mfget('disable_storing_entries', $this->form->settings);
    if (mfget_bool_value($is_storing_entries_disabled)) {
      return false;
    }

    # Proceed with creating a new entry
    $referrer = htmlspecialchars_decode(urldecode(mfpost('_mf_referrer', $this->posted)));
    $spam = $this->is_spam;
    // Save only raw sanitized values to entry meta
    $entry_meta = array_map(function ($item) {
      return $item['values']['raw'];
    }, $this->submission_values);

    $entry_id = mf_api()->create_entry($this->form, $entry_meta, $referrer, $spam);

    if ($entry_id === false) {
      // Throw an error
      throw new Exception($this->get_validation_text('form_processing_entry_failed', __('We couldn\'t process your submission.', 'megaforms')));
    } else {
      // Perform any tasks that should run directly after entry creation.
      do_action('mf_process_entry_actions', $entry_id, $entry_meta, $this->form, $this->submission_values);
    }
  }
  /**
   * Run all needed processes to validate if the entry change request is valid.
   * Make sure no one can change entries, unless allowed.
   * 
   * @since 1.0.7
   *
   */
  public function validate_entry_form()
  {

    # Check if form exists
    if (!$this->form || empty($this->posted)) {
      throw new Exception($this->get_validation_text('entry_validation_invalid_submission'));
    }

    # Validate entry token from user session
    $entryNonce = mfpost('mf_entry_' . $this->form->ID . '_token', $this->posted, false);
    if (!wp_verify_nonce($entryNonce, 'mf_save_entry_' . $this->form->ID)) {
      throw new Exception($this->get_validation_text('entry_validation_session_error'));
    }
  }

  /**
   * Run all needed processes to validate the entry field changes.
   *
   * @since 1.0.7
   *
   */
  public function validate_entry_fields()
  {

    $fields = mfget_form_fields($this->form);
    $result = $this->validate_fields($fields, false);

    if ($result['valid'] === false) {
      throw new Exception($this->get_validation_text('form_validation_errors'));
    } else {
      $this->submission_values = $result['values'];
    }
  }
  /**
   * Save entry changes as meta data.
   *
   * @since 1.0.7
   *
   */
  public function save_entry_changes()
  {

    $entry_id = mfget('entry_id', $this->posted, false);
    if ($entry_id) {
      // Save only raw sanitized values to entry meta
      $entry_meta = array_map(function ($item) {
        return $item['values']['raw'];
      }, $this->submission_values);
      // Update meta
      foreach ($entry_meta as $meta_key => $meta_value) {
        mf_api()->update_entry_meta($entry_id, $meta_key, $meta_value);
      }
    }
  }
  /**
   * Validate the supplied fields against the submission value
   *
   * @since 1.0.7
   *
   * @param array $fields The fields to validate
   * @param bool $validate_required_fields If the method should validate required fields
   * @return array an array presenting if the fields are valid and the submission values
   */
  public function validate_fields($fields, $validate_required_fields = true)
  {

    $submission_values = array();
    $is_spam = false;
    if (empty($fields) || !is_array($fields)) {
      $is_valid = false;
    } else {
      $is_valid = true;
    }

    foreach ($fields as $field) {

      $fieldID       = $field['id'];
      $fieldValue    = $this->get_value($fieldID);
      $fieldType     = $field['type'];
      $fieldObject   = MF_Fields::get($fieldType, array('field' => $field));

      // Ignore static fields
      if ($fieldObject->isStaticField) {
        continue;
      }

      // If the current field is a compound field, we are expecting the the value to be an array
      if ($fieldObject->isCompoundField && !is_array($fieldValue)) {
        $is_valid = false;
        $this->add_notice($fieldID, $this->get_validation_text('field_value_wrong_format'));
        continue;
      }

      // Field required check ( Make sure the required check is only implemented when the fields are available for the user )
      if ($validate_required_fields) {
        $fieldVisibility = $fieldObject->get_setting_value('field_visibility');
        $conditional_logic = $fieldObject->get_setting_value('conditional_logic');
        $conditional_logic_enabled = mfget_bool_value(mfget('enable', $conditional_logic));

        if ($fieldVisibility == 'administrator' && !current_user_can('administrator')) {
          // Pass the "required check" when the field is only available for admin and current user is not admin
          $requiredCheck = true;
        } elseif (!$fieldObject->isCompoundField && $conditional_logic_enabled && !isset($this->posted['fields'][$fieldID])) {
          // Pass the "required check" when conditional logic is enabled and the simple field was not posted
          $requiredCheck = true;
        } elseif ($fieldObject->isCompoundField && $conditional_logic_enabled && !isset($this->posted['fields'][$fieldID]['compound_cl'])) {
          // Pass the "required check" when conditional logic is enabled and the compound field's `compound_cl` input was not posted
          $requiredCheck = true;
        } else {
          // Run the check otherwise
          $requiredCheck = $fieldObject->required_check($fieldValue);
        }

        if ($requiredCheck !== true) {

          $is_valid = false;
          // Since the value didn't pass the 'required check', we have to add the associated notice or notices.
          if (isset($requiredCheck['notice']) || isset($requiredCheck['compound_notices'])) {
            if (isset($requiredCheck['notice'])) {
              $this->add_notice($fieldID, $requiredCheck['notice']);
            }
            if (isset($requiredCheck['compound_notices'])) {
              $this->add_compound_notices($fieldID, $requiredCheck['compound_notices']);
            }
          } else {
            $this->add_notice($fieldID, $this->get_validation_text('field_required_check_failed'));
          }
          continue;
        }
      }

      // If the field is not required, which we confirmed from the "requiredCheck" block above and the field value is empty, 
      // then we do not need to do any additional validation ( except if it's a `file` input )
      if (empty($fieldValue) && empty(mfget($fieldObject->get_field_key(), $_FILES))) {
        continue;
      }

      // Validate the value
      $validation = $fieldObject->validate($fieldValue, $this->context);

      if ($validation !== true) {
        $is_valid = false;

        // Set field notice or and compound notices, if they exists!
        if (isset($validation['notice']) || isset($validation['compound_notices'])) {

          if (isset($validation['notice'])) {

            $notice = isset($validation['notice']) ? ucfirst(strtolower($validation['notice'])) : '';
            $notice_code = isset($validation['notice_code']) ? $fieldType . '_validation_' . $validation['notice_code'] : 'field_validation_ignore';

            $this->add_notice($fieldID, $this->get_validation_text($notice_code, $notice));
          }

          if (isset($validation['compound_notices'])) {
            $this->add_compound_notices($fieldID, $validation['compound_notices']);
          }
        } else {
          $this->add_notice($fieldID, $this->get_validation_text('field_validation_failed'));
        }

        continue;
      }

      // Sanitize the value and save them to an array to be used later for entry creation
      $sanitizedValue = $fieldObject->sanitize($fieldValue);

      // Get formatted values after sanitization and saved them to an array to be used for submission tasks
      $submission_values[$fieldID] = array(
        'label' => $fieldObject->get_setting_value('field_label'),
        'type' => $fieldObject->type,
        'values' => array(
          'raw' => $sanitizedValue,
          'formatted_short' => $fieldObject->get_formatted_value_short($sanitizedValue),
          'formatted_long' => $fieldObject->get_formatted_value_long($sanitizedValue)
        )
      );

      // If this is a form submission and the current field has a post submission task attached to it, save that with the value for processing later
      if ('form' == $this->context && $fieldObject->hasPostSubmissionTask) {
        $submission_values[$fieldID]['has_task'] = true;
      }


      // Check if this submitted value for this field is a spam value
      if ($fieldObject->is_spam($fieldValue)) {
        $is_spam = true;
      }
    }

    return array(
      'valid' => $is_valid,
      'spam' => $is_spam,
      'values' => $submission_values
    );
  }
  /**
   * Get the value of a specific field from the request by ID
   *
   * @since 1.0.0
   *
   * @param string $field_id Field ID to get the value for
   * @return array $value the value retrieved from submission request for the provided ID
   */
  public function get_value($field_id)
  {

    $value = mfpost($field_id, $this->posted['fields']);

    return $value;
  }

  /**
   * Get single validation message using the key provided.
   *
   * @since 1.0.0
   *
   * @param string $key The message key.
   * @param string $fallback A fall back for the validation message.
   * @return string|bool Validation text, if it exists.
   */
  public function get_validation_text($key, $fallback = 'Something went wrong!')
  {

    if (isset($this->validation_text[$key])) {
      return $this->validation_text[$key];
    }

    return $fallback;
  }
  /**
   * Get single notice message using the key provided.
   *
   * @since 1.0.0
   *
   * @param string $field_id Notice code to retrieve message.
   * @return string|bool Notice message, if it exists.
   */
  public function get_notice($field_id)
  {

    if (isset($this->notices[$field_id])) {
      return $this->notices[$field_id];
    }

    return false;
  }

  /**
   * Retrieve notice data for notice code.
   *
   * @since 1.0.0
   *
   * @param string|int $field_id Optional. Notice code.
   * @return mixed Notice data, if it exists.
   */
  public function get_compound_notices($field_id)
  {

    if (isset($this->compound_notices[$field_id])) {
      return $this->compound_notices[$field_id];
    }

    return false;
  }

  /**
   * Add an notice or append additional message to an existing notice.
   *
   * @since 1.0.0
   *
   * @param string $field_id Notice code.
   * @param string $message Notice message.
   * @param mixed $data Optional. Notice data.
   */
  public function add_notice($field_id, $message)
  {

    $this->notices[$field_id] = $message;
  }

  /**
   * Add data for notice code.
   *
   * The notice code can only contain one notice data.
   *
   * @since 1.0.0
   *
   * @param mixed $data Notice data.
   * @param string $field_id Notice code.
   */
  public function add_compound_notices($field_id, $notices)
  {

    $this->compound_notices[$field_id] = $notices;
  }
  /**
   * Check if any megaform submission has been treated in the current request.
   *
   * @since    1.0.0
   *
   * @return bool
   */
  public function is_empty()
  {
    return $this->is_empty;
  }
}

# Returns the main instance of MFAPI.
function mf_submission()
{
  return MF_Form_Submit::instance();
}
