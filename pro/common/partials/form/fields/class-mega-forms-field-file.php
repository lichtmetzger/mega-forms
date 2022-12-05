<?php

/**
 * @link       https://wpali.com
 * @since      1.0.7
 *
 */

/**
 * file field type class
 *
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Field_File extends MF_Field
{

  public $type = 'file';
  public $editorSettings = array(
    'general' => array(
      'allowed_extensions' => array(
        'priority' => 50,
      ),
      'max_upload_size' => array(
        'priority' => 60,
        'size' => 'half-left'
      ),
      'multiple_files' => array(
        'priority' => 70,
        'size' => 'half-right'
      ),
      'max_upload_count' => array(
        'priority' => 80,
      ),
    ),
  );

  public $hasJSDependency = true;
  public $hasPostSubmissionTask = true;
  public $uploads = array();

  public function get_field_title()
  {
    return esc_attr__('File Upload', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-upload';
  }

  public function get_field_js_dependencies()
  {
    return array(
      'jquery-mfupload' => array(
        'src'   => MEGAFORMS_DIR_URL . 'pro/common/assets/js/deps/mfupload.min.js',
        'deps'  => array('jquery'),
        'ver'   => '1.0.0',
        'vars'  => $this->get_field_strings()
      )
    );
  }

  public function get_field_display($value = null)
  {

    $max_upload_size = $this->get_maximum_upload_size();

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['name'] = $args['id'];
    $args['value'] = '';
    $args['class'] = 'mf-upload';
    $args['desc'] = $this->get_setting_value('field_description');
    $args['attributes']['data-max-size'] = $max_upload_size['bytes'];

    $allowed_extenstions = $this->get_allowed_extensions();
    if (!empty($allowed_extenstions)) {
      $args['attributes']['accept'] = implode(', ', $allowed_extenstions);
    } else {
      $args['attributes']['data-types'] = get_allowed_mime_types();
    }

    /**
     * Multi files upload arguements
     */

    $multiple_files = $this->get_setting_bool_value('multiple_files');
    if ($multiple_files || $this->is_editor) {

      $max_upload_count = (int)$this->get_setting_value('max_upload_count');

      // Add the necessary attributes to the file input if multi-upload is enable
      if ($multiple_files) {
        $args['attributes']['data-max-count'] = $max_upload_count;
        $args['attributes']['multiple'] = 'multiple';
        $args['attributes']['hidden'] = true;
      }
      // Hide the dropbox on in editor view if multi-upload is not enabled
      $dropbox_attrs = $this->is_editor && !$multiple_files ? ' style="display:none;"' : '';
      $dropbox_text = __('Drop files here, or', 'megaforms');
      $dropbox_btn_text = __('Browse...', 'megaforms');
      $args['before_input'] = '';
      $args['before_input'] .= '<div class="mf_files_dropable"' . $dropbox_attrs . '>';
      $args['before_input'] .= '<div class="mf_files_dock">';
      $args['before_input'] .= '<p class="mf-files-dock-uploader">';
      $args['before_input'] .= '<i class="mega-icons-upload"></i>';
      $args['before_input'] .= '<span>' . $dropbox_text . '</span>';
      $args['before_input'] .= '<label class="mf-upload-trigger button" for="' . $args['id'] . '">' . $dropbox_btn_text . '</for>';
      $args['before_input'] .= '</p>';
      $args['before_input'] .= '<span class="mf-files-dock-limit" data-max="' . $max_upload_count . '">';
      $args['before_input'] .= '<bdi class="mf-files-count">0</bdi>/<bdi class="mf-files-max">' . $max_upload_count . '</bdi>';
      $args['before_input'] .= '</span>';
      $args['before_input'] .= '</div>';
      $args['before_input'] .= '</div>';
    }

    /**
     * Prepare markup for the uploaded files
     */

    $args['after_field'] = '';
    $args['after_field'] .= '<div class="mf_files_completed">';
    // Include uploaded files if available
    if (!empty($value) && isset($value['files']) && is_array($value['files'])) {
      $i = 1;
      foreach ($value['files'] as $key => $file) {
        $hash = $file['hash'] ?? $key;
        $args['after_field'] .= '<span data-id="' . $hash . '">';
        $args['after_field'] .= '<i class="mf-delete-file mega-icons-clear"></i>';
        $args['after_field'] .= '<strong>' . $file['name'] . '</strong>';
        $args['after_field'] .= " (" . mf_files()->format_size_unites($file['size']) . ")";
        $args['after_field'] .= get_mf_input('hidden', array(
          'name' => $args['id'] . '[files][' . $hash . '][name]',
          'value' => $file['name']
        ));
        $args['after_field'] .= get_mf_input('hidden', array(
          'name' => $args['id'] . '[files][' . $hash . '][size]',
          'value' => $file['size']
        ));
        $args['after_field'] .= '</span>';

        $i++;
      }
    }
    $args['after_field'] .= '</div>';
    $args['after_field'] .= '<div class="mf_files_pending"></div>';

    # retrieve and return the input markup
    $input = mfinput('file', $args, $this->is_editor);

    return $input;
  }

  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/
  /**
   * Returns the allowed extensions option.
   *
   * @return string
   */
  protected function allowed_extensions()
  {

    $label = __('Allowed File Extensions', 'megaforms');
    $field_key = 'allowed_extensions';
    $desc1 = __('Enter the allowed file extensions. This will limit the type of files a user can upload.', 'megaforms');
    $desc2 = __('Separated with commas (i.e. jpg, gif, png, pdf)', 'megaforms');

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['value'] = $this->get_setting_value($field_key);
    $args['after_label'] = $this->get_description_tip_markup($label, $desc1);
    $args['desc'] = $desc2;

    $input = mfinput('text', $args, true);

    return $input;
  }
  /**
   * Returns the max upload option.
   *
   * @return string
   */
  protected function max_upload_size()
  {

    $label       = __('Maximum File Size', 'megaforms');
    $field_key  = 'max_upload_size';

    $max_upload_size = $this->get_maximum_upload_size(true);
    $desc       = sprintf(__('Maximum upload size allowed on this server: %sMB', 'megaforms'), $max_upload_size['megabytes']);

    $args['inputType'] = 'number';
    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['value'] = $this->get_setting_value($field_key);
    $args['after_label']  = $this->get_description_tip_markup($label, $desc);
    $args['placeholder']  = $max_upload_size['megabytes'] . 'MB';

    $input = mfinput('text', $args, true);

    return $input;
  }
  /**
   * Returns the multiple uploads option.
   *
   * @return string
   */
  protected function multiple_files()
  {

    $label = __('Multiple Files', 'megaforms');
    $field_key = 'multiple_files';
    $desc = __('Select this option to enable multiple files to be uploaded for this field.', 'megaforms');

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['value'] = $this->get_setting_value($field_key);
    $args['after_label']  = $this->get_description_tip_markup($label, $desc);
    $args['onchange_preview'] = $this->get_js_helper_rules('none', 'update_upload_area');

    $input = mfinput('switch', $args, true);

    return $input;
  }
  /**
   * Returns the maximum number of files option.
   *
   * @return string
   */
  protected function max_upload_count()
  {

    $args['inputType'] = 'number';
    $label = __('Max Number of Files', 'megaforms');
    $field_key = 'max_upload_count';
    $desc = __('Enter the maximum number of files that can be uploaded for this field.', 'megaforms');

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['value'] = $this->get_setting_value($field_key);
    $args['after_label']  = $this->get_description_tip_markup($label, $desc);
    $args['conditional_rules'] = $this->get_js_conditional_rules('show', array(
      'name' => $this->get_field_key('options', 'multiple_files'),
      'operator' => 'is',
      'value' => 'yes',
    ));

    $input = mfinput('text', $args, true);

    return $input;
  }

  /**
   * Returns custom field description option.
   *
   * @return string
   */
  protected function field_description()
  {

    $label = __('Description', 'megaforms');
    $desc = __('Write a description to provide users with directions on how this field should be completed.', 'megaforms');
    $field_key = 'field_description';

    $args['id'] = $this->get_field_key('options', $field_key);
    $args['label'] = $label;
    $args['after_label'] = $this->get_description_tip_markup($label, $desc);
    $args['onchange_preview'] = $this->get_js_helper_rules('span.mf_description', 'update_desc');

    $max_upload_size = $this->get_maximum_upload_size();
    $args['value'] = isset($this->field[$field_key]) ? $this->get_setting_value($field_key) : sprintf(__('Max. file size: %s MB.'), $max_upload_size['megabytes']);

    $input = mfinput('textarea', $args, true);
    
    return $input;
  }

  /**********************************************************************
   ***************************** Helpers ********************************
   **********************************************************************/

  /**
   *  Return the field strings to use for errors and javascript.
   *
   * @return array
   */
  public function get_field_strings($key = '')
  {
    $strings = array(
      'invalid_file_extension'      => __('This type of file is not allowed. Must be one of the following: ', 'megaforms'),
      'file_exceeds_limit'          => __('File exceeds size limit.', 'megaforms'),
      'file_exceeds_defined_limit'  => __('File exceeds size limit. Maximum file size is ', 'megaforms'),
      'illegal_extension'           => __('This type of file is not allowed.', 'megaforms'),
      'illegal_type'                => __('The uploaded file type is not allowed.', 'megaforms'),
      'invalid_size_and_type'       => __('File exceeds size limit & type of the file is not allowed.', 'megaforms'),
      'max_reached'                 => __('Maximum number of files reached.', 'megaforms'),
      'unknown_error'               => __('There was a problem while saving the file on the server.', 'megaforms'),
      'currently_uploading'         => __('Please wait for the uploading to complete.', 'megaforms'),
      'cancel_to_upload'            => __('Upload in progress, you need to cancel to upload a different file.', 'megaforms'),
      'cancel'                      => __('Cancel', 'megaforms'),
    );

    if (!empty($key)) {
      return isset($strings[$key]) ? $strings[$key] : '';
    }
    return $strings;
  }
  /**
   *  Return the maximum upload size for this field.
   *
   * @return array
   */
  public function get_maximum_upload_size($wp_only = false)
  {

    $max_upload_size_bytes = wp_max_upload_size();
    $max_upload_size_mbytes = $max_upload_size_bytes / 1048576;

    if ($wp_only !== false) {
      $field_max_upload_size_mbytes = (float)$this->get_setting_value('max_upload_size', 0);
      $field_max_upload_size_bytes = $field_max_upload_size_mbytes * 1048576;
      if ($field_max_upload_size_mbytes > 0 && $field_max_upload_size_bytes < $max_upload_size_bytes) {
        $max_upload_size_bytes = $field_max_upload_size_mbytes;
        $max_upload_size_mbytes = $field_max_upload_size_mbytes;
      }
    }

    return array(
      'bytes' => $max_upload_size_bytes,
      'megabytes' => $max_upload_size_mbytes
    );
  }
  /**
   *  Return the allowed file extenstions for this field
   *
   * @return array
   */
  public function get_allowed_extensions($stripdots = false)
  {

    $allowed = array();
    $field_allowed = $this->get_setting_value('allowed_extensions');

    if ($field_allowed) {
      // Make sure all extensions are in the correct format
      $exts = explode(',', $field_allowed);
      foreach ($exts as $key => $ext) {
        $ext = trim($ext);
        if ($stripdots) {
          $ext = strpos($ext, '.') !== false ? str_replace('.', '', $ext) : $ext;
        } else {
          $ext = strpos($ext, '.') !== false ? $ext : '.' . $ext;
        }


        $exts[$key] = strtolower($ext);
      }
      $allowed = $exts;
    }

    return $allowed;
  }

  public function get_formatted_value_short($value)
  {

    if (isset($value) && is_array($value)) {

      $output = '';
      foreach ($value as $file) {
        if (isset($file['path'])) {
          $safe_url = mf_files()->generate_safe_download_url($this->form_id, $this->field_id, $file['path']);
          $item = '<a href="' . $safe_url . '" target="_blank">' . esc_html($file['name']) . '</a>';
        } else {
          $item = $file['name'];
        }
        $output .= $item . ', ';
      }
      return trim($output, ', ');
    }

    return '';
  }

  public function get_formatted_value_long($value)
  {
    if (isset($value) && is_array($value)) {

      $output = '';
      $output .= '<ul class="mf_formatted_' . $this->type . '_value">';
      foreach ($value as $file) {
        $output .= '<li>';
        if (isset($file['path'])) {
          $safe_url = mf_files()->generate_safe_download_url($this->form_id, $this->field_id, $file['path']);
          $output .= '<a href="' . $safe_url . '" target="_blank">' . esc_html($file['name']) . '</a>';
        } else {
          $output .= esc_html($file['name']);
        }
        $output .= '</li>';
      }
      $output .= '</ul>';
      return $output;
    }
    return esc_html($value);
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/

  public function required_check($value)
  {

    $is_required = $this->get_setting_bool_value('field_required');
    // Otherwise continue
    if ($is_required) {
      $to_upload = mfget($this->get_field_key(), $_FILES);
      $uploaded = $value['files'] ?? array();
      $multiple_files = $this->get_setting_bool_value('multiple_files');
      $empty = $multiple_files ? empty($uploaded) : (!isset($to_upload['tmp_name']) || empty($to_upload['tmp_name'])) && empty($uploaded);

      if ($empty) {
        return array(
          'notice' => mf_api()->get_validation_required_notice($this->field),
        );
      }
    }

    return true;
  }

  public function validate($value, $context = '')
  {

    $to_upload = mfget($this->get_field_key(), $_FILES, array());
    $uploaded = $value['files'] ?? array();
    // Ensure empty file submission is not treated
    if (isset($to_upload['tmp_name']) && empty($to_upload['tmp_name'])) {
      $to_upload = array();
    }

    // Validate multiple files upload
    $multiple_files = $this->get_setting_bool_value('multiple_files');
    $max_upload_count = (int)$this->get_setting_value('max_upload_count');
    if ($multiple_files) {
      // Empty `$to_upload` variable since the uploaded files are treated on client side
      if (!empty($to_upload)) {
        $to_upload = array();
      }
      // Validate files count
      if ($max_upload_count > 0) {
        $uploaded_count = count($uploaded);

        if (($uploaded_count) > $max_upload_count) {
          return array(
            'notice' => __('Maximum number of files exceeded.', 'megaforms'),
            'notice_code' => 'max_exceeded',
          );
        }
      }
    }

    // Check if we have a valid temp folder
    if (mf_files()->get_form_temp_upload_path($this->form_id) === false) {
      return array(
        'notice' => __('There was an error while uploading. Please refresh the page and try again.', 'megaforms'),
        'notice_code' => 'invalid_form',
      );
    };

    $notices = array();
    // Validate the file that needs uploading ( only when multiple files option is disabled )
    if (!$multiple_files && !empty($to_upload)) {
      $result = $this->validate_and_upload_file($to_upload);
      if ($result !== true) {
        $notices[] = $to_upload['name'] . ' - ' . $result;
      }
      // Remove existing files if available
      if (!empty($uploaded)) {
        foreach ($uploaded as $hash => $data) {
          $hash = $data['hash'] ?? $hash;
          mf_files()->delete_temp_file($this->form_id, $hash);
        }
        $uploaded = array();
      }
    }

    // Validate the existance of files that has already been uploaded
    foreach ($uploaded as $hash => $data) {
      if (!isset($data['hash'])) {
        $data['hash'] = $hash;
      }
      $this->validate_uploaded_file($data);
    }

    if (!empty($notices)) {
      return array(
        'notice' => implode("\n", $notices),
        'notice_code' => 'invalid_file_upload',
      );
    } else {
      return true;
    }
  }

  public function sanitize($value)
  {
    // Update the posted value with the files array to ensure recently uploaded files will appear after page refresh, or ajax submission
    if (
      !$this->is_editor && function_exists('mf_submission') && !mf_submission()->is_empty()
    ) {
      // If this is a `save and continue` submission, change the temp files extension to .idle ( this allows storage for a longer duration )
      if ('save' == mf_submission()->context) {
        foreach ($this->uploads as  $file) {
          $tmp_upload_dir = mf_files()->get_form_temp_upload_path($this->form_id);
          if (file_exists($tmp_upload_dir . $file['hash'] . '.tmp')) {
            rename($tmp_upload_dir . $file['hash'] . '.tmp', $tmp_upload_dir . $file['hash'] . '.idle');
          }
        }
      }

      // Replace submitted field values for file fields with the uploaded files
      mf_submission()->posted['fields'][$this->field_id] = array('files' => $this->uploads);
    }

    return $this->uploads;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $sanitized['allowed_extensions'] = sanitize_text_field($this->get_setting_value('allowed_extensions'));
    $sanitized['max_upload_size'] = (float)$this->get_setting_value('max_upload_size');
    $sanitized['multiple_files'] = $this->get_setting_bool_value('multiple_files');

    if ($sanitized['multiple_files']) {
      $max_upload_count = $this->get_setting_value('max_upload_count');
      if ($max_upload_count) {
        $sanitized['max_upload_count'] = (int)$max_upload_count;
      }
    }

    return $sanitized;
  }
  public function post_submission_task($submission_value)
  {
    // Move temporary files to the correct location and updated values
    if (isset($submission_value['values']['raw'])) {
      $new_values = array();
      if (is_array($submission_value['values']['raw']) && !empty($submission_value['values']['raw'])) {
        foreach ($submission_value['values']['raw'] as $file) {
          $result = mf_files()->move_temp_file($this->form_id, $file['hash'], $file['name']);
          if ($result !== false) {
            $new_values[] = $result;
          }
        }
      }
      $submission_value['values']['raw'] = $new_values;
      $submission_value['values']['formatted_short'] = $this->get_formatted_value_short($new_values);
      $submission_value['values']['formatted_long'] = $this->get_formatted_value_long($new_values);
    }

    if (isset($submission_value['has_task'])) unset($submission_value['has_task']);

    return $submission_value;
  }
  /**
   *  Validate a file and upload it to temporary folder
   *
   * @return bool|string
   */
  public function validate_and_upload_file($file)
  {

    /**
     *  Validate
     */

    $max_upload_size = $this->get_maximum_upload_size();
    $allowed_extensions = $this->get_allowed_extensions(true);

    $fileinfo = pathinfo(mfpost('name', $file));
    $basename = mfpost('basename', $fileinfo);
    $size = mfpost('size', $file);
    $error = mfget('error', $file);

    // Validate form and field
    if ($this->form_id === 0 || $this->field_id === 0) {
      return __('Failed to upload the file.', 'megaforms');
    }

    // File error validation
    if ($error > 0) {
      switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $error_msg = $this->get_field_strings('file_exceeds_defined_limit') . $max_upload_size['megabytes'] . 'MB.';
          break;
        default:
          $error_msg = sprintf(__('There was an error while uploading the file. Error code: %d', 'megaforms'), $error);
      }
      return $error_msg;
    }

    // File type validation
    $type_check_result = mf_files()->check_type_and_ext($file);
    if (is_wp_error($type_check_result)) {
      return $this->get_field_strings('illegal_type');
    }

    // Validate size
    if ($size >  $max_upload_size['bytes']) {
      return $this->get_field_strings('file_exceeds_defined_limit') . $max_upload_size['megabytes'] . 'MB';
    }

    // Validate extension
    if (!empty($allowed_extensions)) {
      if (!mf_files()->match_file_extension($basename, $allowed_extensions)) {
        return $this->get_field_strings('invalid_file_extension') . implode(', ', $allowed_extensions);
      }
    } else {
      if (mf_files()->file_name_has_disallowed_extension($basename)) {
        return $this->get_field_strings('illegal_extension');
      }
    }

    /**
     *  Upload
     */

    // Create file tmp directory if not already available
    $upload_dir = mf_files()->get_form_upload_path($this->form_id);
    $target_dir = mf_files()->get_form_temp_upload_path($this->form_id);
    $tmp_file_hash = wp_hash($basename);
    $tmp_file_name = $tmp_file_hash . '.tmp';
    $tmp_file_path = $target_dir . $tmp_file_name;
    $cleanup_target_dir = true; // If we should clean remove old temp files

    // Make sure the hash is unique
    if (file_exists($tmp_file_path)) {
      $number = 1;
      $tmp_file_hash = $tmp_file_hash . '-' . $number;
      $tmp_file_name = $tmp_file_hash . '.tmp';
      $tmp_file_path = $target_dir . $tmp_file_name;

      while (file_exists($tmp_file_path)) {
        $new_number = $number + 1;

        $tmp_file_hash = str_replace('-' . $number, '-' . $new_number, $tmp_file_hash);
        $tmp_file_name = $tmp_file_hash . '.tmp';
        $tmp_file_path = $target_dir . $tmp_file_name;

        $number = $new_number;
      }
    }


    // Create the target directory if it doesn't already exist
    if (!is_dir($target_dir)) {
      // Create the directory
      if (!wp_mkdir_p($target_dir)) {
        return __('File could not be uploaded.', 'megaforms');
      }
    } else {
      // Remove old temp files
      if ($cleanup_target_dir) {
        mf_files()->clean_temp_files($upload_dir, $tmp_file_path);
      }
    }
    // Add index file
    if (!file_exists($upload_dir . '/index.html')) {
      mf_files()->add_index_file_recursively($upload_dir);
    }
    if (!file_exists($target_dir . '/index.html')) {
      mf_files()->add_index_file_recursively($target_dir);
    }

    $move_file = @move_uploaded_file($file['tmp_name'], $tmp_file_path);

    if ($move_file) {

      // Set correct file permissions.
      mf_files()->set_permissions($tmp_file_path);

      $this->uploads[] = array(
        'hash' => $tmp_file_hash,
        'name' => $basename,
        'size' => $size,
      );
    } else {
      return __('The file upload failed.', 'megaforms');
    }

    return true;
  }

  /**
   *  Validate an uploaded file
   *
   * @return bool
   */
  public function validate_uploaded_file($file)
  {
    $tmp_upload_dir = mf_files()->get_form_temp_upload_path($this->form_id);
    $tmp_file_path = $tmp_upload_dir . $file['hash'] . '.tmp';
    $idle_file_path = $tmp_upload_dir . $file['hash'] . '.idle';
    if (file_exists($tmp_file_path) || file_exists($idle_file_path)) {
      $this->uploads[] = $file;
      return true;
    }

    return false;
  }
}

MF_Extender::register_field(new MF_Field_File());
