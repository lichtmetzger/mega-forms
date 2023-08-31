<?php

/**
 * Mega Forms API
 *
 * @link       https://wpali.com
 * @since      1.0.5
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MFAPI
{
  /**
   * The single instance of the class.
   *
   * @var MFAPI
   * @since 1.0.0
   */
  protected static $_instance = null;

  /**
   * Main MFAPI Instance.
   *
   * Ensures only one instance of MFAPI is loaded or can be loaded.
   *
   * @since 1.0.0
   * @see mf_api()
   * @return MFAPI - Main instance.
   */
  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Retrieve
   *
   * @since    1.0.0
   *
   * @return array|bool
   */
  public static function get_forms()
  {

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_forms';

    $forms = $wpdb->get_results("SELECT * FROM $table_name");

    if (!empty($forms)) {
      return $forms;
    } else {
      return false;
    }
  }

  /**
   * Searches the database for a specific form based on ID
   *
   * @since    1.0.0
   *
   * @param int $form_id The id for the form we are trying to locate
   *
   * @return object
   */
  public static function get_form($form_id, $type = false)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    $form_record = wp_cache_get('mf_' . $form_id . '_get_form');
    if (!$form_record) {

      global $wpdb;

      $form_table_name = $wpdb->prefix . 'mf_forms';

      $form_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $form_table_name WHERE id = %d", $form_id));
      wp_cache_set('mf_' . $form_id . '_get_form', $form_record);
    }

    if ($form_record === null) {
      return false;
    }

    $form = array();
    $form['ID']             = $form_record->id;
    $form['type']           = $form_record->type;
    $form['title']          = $form_record->title;
    $form['date_created']   = $form_record->form_created;
    $form['date_modified']  = $form_record->form_modified;
    $form['view_count']     = $form_record->view_count;
    $form['lead_count']     = $form_record->lead_count;
    $form['is_trash']       = $form_record->is_trash;
    $form['is_active']      = $form_record->is_active;


    if (false === $type) {
      // Return all form meta
      $form_meta = self::get_bulk_form_meta($form_id, array(
        'field_types',
        'fields',
        'containers',
        'actions',
        'settings',
      ));

      $form['fields'] = isset($form_meta['fields']) ? $form_meta['fields'] : array();
      $form['field_types'] = isset($form_meta['field_types']) ? $form_meta['field_types'] : array();
      $form['containers'] = isset($form_meta['containers']) ? $form_meta['containers'] : array();
      $form['actions'] = isset($form_meta['actions']) ? $form_meta['actions'] : array();
      $form['settings'] = isset($form_meta['settings']) ? $form_meta['settings'] : array();
    } else if (is_array($type)) {
      // Return the specified meta data
      $form_meta = self::get_bulk_form_meta($form_id, $type);
      foreach ($type as $type_key) {
        $form[$type_key] = isset($form_meta[$type_key]) ? $form_meta[$type_key] : array();
      }
    } else {
      // Return a single meta
      if ($type === 'fields') {
        $form['fields'] = self::get_form_meta($form_id, 'fields');
      } elseif ($type === 'field_types') {
        $form['field_types'] = self::get_form_meta($form_id, 'field_types');
      } elseif ($type === 'containers') {
        $form['containers'] = self::get_form_meta($form_id, 'containers');
      } elseif ($type === 'actions') {
        $form['actions'] = self::get_form_meta($form_id, 'actions');
      } elseif ($type === 'settings') {
        $form['settings'] = self::get_form_meta($form_id, 'settings');
      }
    }

    /**
     * Convert the form array into an object
     * 
     */
    $_form = (object) $form;

    /**
     * Hold a reference to the form object
     * use this global for treating form merge tags, post submission 
     * 
     */
    $GLOBALS['mf_form'] = $_form;


    return $_form;
  }

  /**
   * Determines the form Status.
   *
   * @since    1.0.0
   *
   * @param int $form_id
   *
   * @return string
   */
  public function get_form_status($form_id)
  {

    global $wpdb;

    $table_name = $wpdb->prefix . 'mf_forms';
    $status = $wpdb->get_row("SELECT is_active FROM $table_name WHERE id = $form_id ");

    if ($status->is_active == '1') {
      return 'active';
    } elseif ($status->is_active == '0') {
      return 'inactive';
    }
  }

  /**
   * Insert form meta columns into the database.
   *
   * @since    1.0.0
   * @param int $form_id
   * @param string $key
   * @param mixed $value
   * @return int
   */
  public static function create_form_meta($form_id, $key, $value = '')
  {
    global $wpdb;

    if (!$key || !is_numeric($form_id)) {
      return false;
    }

    $object_id = absint($form_id);

    if (!$object_id) {
      return false;
    }

    $table = $wpdb->prefix . 'mf_formsmeta';
    if (empty($table)) {
      return false;
    }

    $meta_value = wp_unslash($value);
    $meta_value = maybe_serialize($meta_value);
    $data = array(
      'form_id'     => $object_id,
      'meta_key'    => $key,
      'meta_value'  => $meta_value !== null ? $meta_value : ''
    );
    $format = array('%d', '%s', '%s',);

    $result = $wpdb->insert($table, $data, $format);

    if (!$result)
      return false;

    $mid = (int) $wpdb->insert_id;

    return $mid;
  }

  /**
   * Update form meta in the database.
   *
   * @since    1.0.0
   * @param int $form_id
   * @param string $key
   * @param mixed $value
   * @return boolean
   */
  public static function update_form_meta($form_id, $key, $value = '')
  {

    if (!$key || !is_numeric($form_id)) {
      return false;
    }

    $object_id = absint($form_id);
    if (!$object_id) {
      return false;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'mf_formsmeta';

    if (empty($table)) {
      return false;
    }

    $meta_ids = $wpdb->get_col($wpdb->prepare("SELECT `meta_id` FROM `$table` WHERE `form_id` = %d AND `meta_key` = %s ", $object_id, $key));

    if (empty($meta_ids)) {
      return self::create_form_meta($form_id, $key, $value);
    }

    $meta_value = wp_unslash($value);
    $meta_value = maybe_serialize($meta_value);

    $data = array('meta_value' => $meta_value);
    $where = array('form_id' => $object_id,  'meta_key' => $key);
    $format = array('%s');
    $result = $wpdb->update($table, $data, $where, $format);

    // Clear cache
    wp_cache_delete('mf_' . $form_id . '_get_form_meta', $key);

    if (!$result) {
      return false;
    }

    return true;
  }
  /**
   * Retrieve form meta from the database.
   *
   * @since    1.0.0
   * @param int $form_id
   * @param string $key
   * @return mixed
   */
  public static function get_form_meta($form_id, $key)
  {

    if (!$key || !is_numeric($form_id)) {
      return false;
    }

    $object_id = absint($form_id);
    if (!$object_id) {
      return false;
    }

    $meta_result = wp_cache_get('mf_' . $form_id . '_get_form_meta', $key);
    if (!$meta_result) {

      global $wpdb;

      $table = $wpdb->prefix . 'mf_formsmeta';

      $meta_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$table` WHERE `form_id` = %d AND `meta_key` = %s", $form_id, $key));
      wp_cache_set('mf_' . $form_id . '_get_form_meta', $meta_result, $key);
    }


    $meta = !empty($meta_result->meta_value) ? $meta_result->meta_value : '';

    return maybe_unserialize($meta);
  }
  /**
   * Retrieve multiple form meta from the database.
   *
   * @since    1.0.0
   * @param int $form_id
   * @param array $keys
   * @return mixed
   */
  public static function get_bulk_form_meta($form_id, $keys)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    if (!is_array($keys)) {
      return false;
    }

    $object_id = absint($form_id);
    if (!$object_id) {
      return false;
    }


    $meta = wp_cache_get('mf_' . $form_id . '_get_bulk_form_meta');
    if (!$meta) {

      global $wpdb;

      $table = $wpdb->prefix . 'mf_formsmeta';

      $parameters = array();
      $query = "SELECT * FROM ( SELECT meta_key, meta_value FROM `$table` WHERE `form_id` = %d ) AS meta WHERE";
      $parameters[] = $form_id;

      $i = 0;
      foreach ($keys as $key) {
        if ($i > 0) {
          $query .= " OR";
        }
        $query .= " `meta_key` = %s";
        $parameters[] = $key;
        $i++;
      }
      $query .= ";";

      $meta_result = $wpdb->get_results($wpdb->prepare($query, $parameters));

      $meta = array();
      if (!empty($meta_result)) {
        foreach ($meta_result as $result) {
          $meta[$result->meta_key] = maybe_unserialize($result->meta_value);
        }
      }

      wp_cache_set('mf_' . $form_id . '_get_bulk_form_meta', $meta);
    }


    return $meta;
  }
  /**
   * Get form title from the database.
   *
   * @since    1.0.0
   * @param int $form_id
   * @return string
   */
  public static function get_form_title($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    $form_title = wp_cache_get('mf_' . $form_id . '_get_form_title');

    if (!$form_title) {

      global $wpdb;

      $table_name = $wpdb->prefix . 'mf_forms';

      $form = $wpdb->get_results("SELECT title FROM $table_name WHERE id = $form_id");

      $form_title = $form[0]->title;
      wp_cache_set('mf_' . $form_id . '_get_form_title', $form_title);
    }

    return $form_title;
  }
  /**
   * Change form status
   *
   * @since    1.0.0
   * @param int $form_id
   * @param bool $is_active
   */
  public static function set_form_status($form_id, $is_active = null)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_forms';

    $run_query = $wpdb->query($wpdb->prepare("UPDATE $table_name SET is_active=%d WHERE id=%d", $is_active, $form_id));

    return $run_query;
  }
  /**
   * Increment form views count by 1
   *
   * @since    1.0.0
   * @param int $form_id
   */
  public static function set_form_view($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_forms';

    $wpdb->query($wpdb->prepare("UPDATE $table_name SET view_count = view_count + 1 WHERE id = %d", $form_id));

    return true;
  }
  /**
   * Create a new form
   *
   * @since    1.0.0
   * @param string $form_name
   * @return int $form_id
   */
  public static function create_form($form_name, $args = array())
  {

    global $wpdb;
    $form_table_name = $wpdb->prefix . 'mf_forms';
    $form_title = stripslashes_deep($form_name);
    $date = date('m/d/Y h:i:s', time());

    $wpdb->insert(
      $form_table_name,
      array(
        'type' => isset($args['type']) ? $args['type'] : 'form',
        'title' => $form_title,
        'form_created' => $date,
        'form_modified' => $date,
        'is_active' => isset($args['is_active']) ? $args['is_active'] : 1
      ),
      array(
        '%s', '%s', '%s', '%s', '%d',
      )
    );
    $form_id = $wpdb->insert_id;

    do_action('mf_after_form_creation', $form_id);

    return $form_id;
  }
  /**
   * Set is_trash column in the database to 0 ( Bring form out of trash ).
   *
   * @since    1.0.0
   * @param int $form_id
   * @return int
   */
  public static function restore_form($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $form_table_name = $wpdb->prefix . 'mf_forms';
    // Take the form out of trash
    $run_query = $wpdb->query($wpdb->prepare("UPDATE `$form_table_name` SET is_trash = 0 WHERE id = %d", $form_id));
    // Clear the form cache
    wp_cache_delete('mf_' . $form_id . '_get_form');

    return $run_query;
  }

  /**
   * Delete all form records and meta from the database.
   *
   * @since    1.0.0
   * @param int $form_id
   */
  public static function delete_form($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $form_table_name = $wpdb->prefix . 'mf_forms';
    $meta_table_name = $wpdb->prefix . 'mf_formsmeta';

    do_action('mf_before_delete', $form_id);

    // Delete Leads
    self::delete_form_entries($form_id);
    // Delete Meta
    $wpdb->query($wpdb->prepare("DELETE FROM `$form_table_name` WHERE id = %d", $form_id));
    // Delete Form
    $wpdb->query($wpdb->prepare("DELETE FROM `$meta_table_name` WHERE form_id = %d", $form_id));

    do_action('mf_after_delete', $form_id);

    return true;
  }

  /**
   * Set is_trash column in the database to 1 ( Send form to trash ).
   *
   * @since    1.0.0
   * @param int $form_id
   * @return int
   */
  public static function trash_form($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_forms';
    // Send the form to trash
    $run_query = $wpdb->query($wpdb->prepare("UPDATE `$table_name` SET is_trash = 1 WHERE id = %d", $form_id));
    // Clear the form cache
    wp_cache_delete('mf_' . $form_id . '_get_form');

    return $run_query;
  }

  /**
   * Create a form clone by duplicating all associated records in the database.
   *
   * @since    1.0.7
   * @param mixed $form
   */
  public static function duplicate_form($form)
  {

    // Allow deplucating forms using form ID
    if (is_numeric($form)) {
      $form = self::get_form($form);
    }

    // Make sure all the data we need is available
    if (
      !isset($form->title) ||
      !isset($form->type) ||
      !isset($form->is_active) ||
      !isset($form->containers) ||
      !isset($form->fields) ||
      !isset($form->settings) ||
      !isset($form->actions)
    ) {
      return false;
    }

    $form_title = $form->title;
    if (self::is_form_title_exist($form_title)) {
      // See if count exists in the title and replace it with new count if needed.
      preg_match_all('/\((\#([0-9]))\)$/i', $form_title, $count_in_title);
      if (empty($count_in_title[2][0])) {
        $count = 1;
      } else {
        $count = (int) $count_in_title[2][0] + 1;
        $form_title = preg_replace('/\((\#([0-9]))\)$/i', '', $form_title);
      }

      $form_name = trim($form_title) . ' (#' . $count . ')';
      // Check if the title exist, increment count and repeat if the condition returns true
      while (self::is_form_title_exist($form_name)) {
        $form_name = trim($form_title) . ' (#' . $count++ . ')';
      }
    } else {
      $form_name = trim($form_title);
    }

    $new_form_id = self::create_form($form_name, array(
      'type' => $form->type,
      'is_active' => $form->is_active
    ));

    // Make sure the old form ID for fields and actions is replaced with the new form ID
    foreach ($form->fields as &$field) {
      $field['formId'] = $new_form_id;
    }
    foreach ($form->actions as &$action) {
      $action['formId'] = $new_form_id;
    }

    // Now save the form
    self::save_form($new_form_id, $form->containers, $form->fields, $form->settings, $form->actions);

    return $new_form_id;
  }
  /**
   * Import form(s) from the provided json object
   *
   * @since    1.0.7
   * @param string $json_data the exported forms string
   * @param string $is_template If the imported data belongs to an existing template
   * 
   * @return mixed false, or the imported form id as int, or ids stored inside of an array
   */
  public static function import_forms($json_data, $is_template = false)
  {

    $content = json_decode($json_data, true);
    if (is_array($content) && isset($content['forms'])) {

      $forms = $content['forms'];
      if (is_array($forms) && count($forms) > 0) {
        $ids = array();
        foreach ($forms as $form) {
          $form_id = self::duplicate_form((object)$form);
          if ($form_id) {
            $ids[] = $form_id;
          }
        }

        if (count($ids) > 0) {

          // Update database if needed
          if (!$is_template && version_compare(MEGAFORMS, $content['version'], '>')) {
            Mega_Forms_Updater::update_db($content['version']);
          }

          return count($ids) === 1 ? $ids[0] : $ids;
        }
      }
    }

    return false;
  }
  /**
   * Download a Mega Forms export file
   * Note: This must be called before header are loaded
   * *
   * @since    1.0.7
   * @param array $ids
   * @param string $type (forms|entries)
   * @param array $entry_fields 
   */
  public static function export_forms($ids, $type = 'forms', $export_fields = array())
  {

    if ('forms' == $type && !is_array($ids)) {
      return false;
    }

    if ('forms' == $type && count($ids) === 0) {
      return false;
    }

    if ('entries' == $type) {

      $form_id = absint($ids);
      $form = mf_api()->get_form($form_id, array('fields', 'containers'));

      // Get field objects into "$fields_objects" variable
      $fields = mfget_form_fields($form);
      $form_fields = array();
      if (!empty($fields)) {
        foreach ($fields as $field) {
          $field_id = mfget('id', $field);

          // If the fields is not selected for export, move on to the next one
          if (!in_array($field_id, $export_fields)) {
            continue;
          }

          // Ignore static fields
          $field_type   = mfget('type', $field);
          $field_object = MF_Fields::get($field_type, array(
            'field' => $field
          ));

          if ($field_object->isStaticField) {
            continue;
          }

          $form_fields[$field_id] = $field_object;
        }
      }

      // Prepare entry field labels
      $entry_fields = array(
        'id'              => __('Entry ID', 'megaforms'),
        'user_id'         => __('User ID', 'megaforms'),
        'date_created'    => __('Date Created', 'megaforms'),
        'referrer'        => __('Referrer', 'megaforms'),
        'user_ip'         => __('User IP', 'megaforms'),
        'user_agent'      => __('User Agent', 'megaforms'),
      );

      // Create an CSV file with the appropriate headers
      $upload_path = mf_files()->get_upload_dir() . 'private';
      $file_name = 'megaforms-' . $form_id . '-entries-export-' . date('Y-m-d') . '.csv';
      $file_path = $upload_path . '/' . $file_name;
      $csv_headers = array();

      foreach ($export_fields as $key) {
        if (isset($form_fields[$key])) {
          $csv_headers[] = $form_fields[$key]->get_setting_value('field_label', 'Untitled');
        } elseif (isset($entry_fields[$key])) {
          $csv_headers[] = $entry_fields[$key];
        }
      }

      $handle = fopen($file_path, 'w');
      fputcsv($handle, $csv_headers);

      if (file_exists($file_path)) {

        // Retrieve entries
        global $wpdb;
        $entries_table_name = $wpdb->prefix . 'mf_entries';

        $where_arr   = array();
        $where_arr[] = $wpdb->prepare('form_id = %d', $form_id);
        $where_arr[] = $wpdb->prepare('is_trash=%d', false);

        $where_clause = '';
        if (!empty($where_arr)) {
          $where_clause = 'WHERE ' . join(' AND ', $where_arr);
        }
        $entries = $wpdb->get_results("SELECT * FROM $entries_table_name $where_clause ORDER BY date_created ASC");

        // Add entries to the CSV file
        foreach ($entries as $entry) {
          $row = array();
          $entry_meta = mf_api()->get_entry_meta($entry->id);
          foreach ($export_fields as $key) {
            if (isset($form_fields[$key])) {
              if ('file' == $form_fields[$key]->type) {
                $download_links = array();
                if (!empty($entry_meta[$key])) {
                  foreach ($entry_meta[$key] as $file) {
                    if (isset($file['path'])) {
                      $download_links[] = mf_files()->generate_safe_download_url($form_fields[$key]->form_id, $form_fields[$key]->field_id, $file['path']);
                    }
                  }
                }
                $row[] = !empty($download_links) ? implode("\n", $download_links) : '';
              } else {
                $row[] = isset($entry_meta[$key]) ? $form_fields[$key]->get_formatted_value_short($entry_meta[$key]) : '';
              }
            } elseif (isset($entry_fields[$key])) {
              $row[] = $entry->$key;
            }
          }
          fputcsv($handle, $row);
        }

        fclose($handle);

        // Get ready to download the file
        $filetype = wp_check_filetype($file_path);
        nocache_headers();
        header('X-Robots-Tag: noindex', true);
        header('Content-Type: ' . $filetype['type']);
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Transfer-Encoding: binary');

        // Clear the buffer and turn it off completely to prevent the file from getting corrupt for the reason
        // This can happen due to manipulation, or printed content before the header is sent
        if (ob_get_contents()) {
          ob_end_clean();
        }

        // Download the CSV file and delete it afterwards
        if (readfile($file_path)) {
          unlink($file_path);
        }
        exit;
      }
    } else {
      $forms = array();
      $forms['version'] = MEGAFORMS;
      $forms['type'] = 'forms';
      $filename = 'megaforms-export-' . date('Y-m-d') . '.json';
      header("Content-Disposition: attachment; filename=$filename");
      header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);

      $forms['forms'] = array();
      foreach ($ids as $id) {
        $forms['forms'][$id] = self::get_form($id);
      }
      // Serialize and encode the form object
      echo json_encode($forms);
      die();
    }
  }
  /**
   * Create a form clone by duplicating all associated records in the database.
   *
   * @since    1.0.0
   * @param int $form_id
   */
  public static function is_form_title_exist($title)
  {

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_forms';

    $title_exist = $wpdb->get_results($wpdb->prepare("SELECT title FROM `$table_name` WHERE is_trash = 0 AND title = %s",  $title));

    if ($title_exist) {
      return true;
    }

    return false;
  }

  /**
   * Change form name
   *
   * @since    1.0.0
   * @param string $form_name
   * @param int $form_id
   * @return int
   */
  public static function rename_form($form_name, $form_id)
  {

    global $wpdb;
    $form_table_name = $wpdb->prefix . 'mf_forms';
    $form_title = stripslashes_deep($form_name);
    $date = date('m/d/Y h:i:s', time());
    // Rename the form
    $rename = $wpdb->update($form_table_name, array('title' => $form_title, 'form_modified' => $date), array('id' => $form_id,), array('%s', '%s'));
    // Clear the form cache
    wp_cache_delete('mf_' . $form_id . '_get_form');
    wp_cache_delete('mf_' . $form_id . '_get_form_title');

    return $rename;
  }
  /**
   * Save form data into the database
   *
   * @since    1.0.0
   * @param int $form_id
   * @param array $fields
   * @param array $settings
   * @param array $actions
   * @return bool
   */
  public static function save_form($form_id, $containers, $fields, $settings, $actions)
  {

    // Prepare containers before saving them
    $containers = self::prepare_form_containers($containers);
    // Prepare fields before saving them
    $fields = self::prepare_form_fields($fields);
    // Prepare settings before saving them
    $settings = self::prepare_form_settings($settings);
    // Prepare actions before saving them
    $actions = self::prepare_form_actions($actions);

    // Make sure field types are saved separately to the database ( Used to call JS and CSS dependencies conditionally )
    // Also capture the highest field ID to save it separately as well.
    $field_types = array();
    $last_field_id = 0;

    foreach ($fields as $field) {
      if (!empty($field['type'])) {
        $field_types[] = $field['type'];
      }

      if ((int) $field['id'] >  $last_field_id) {
        $last_field_id = (int) $field['id'];
      }
    }
    $field_types = array_unique($field_types);

    // Save other data
    $data = array(
      'containers'    => $containers,
      'fields'        => $fields,
      'field_types'   => $field_types,
      'settings'      => $settings,
      'actions'       => $actions,
    );
    // Extra meta field to avoid duplicate field IDs
    if ($last_field_id > 0) {
      $data['last_field_id'] = $last_field_id;
    }

    // Save meta
    foreach ($data as $key => $val) {
      self::update_form_meta($form_id, $key, $val);
    }

    // Clear meta cache
    wp_cache_delete('mf_' . $form_id . '_get_bulk_form_meta');

    // Update modification date
    global $wpdb;
    $form_table_name = $wpdb->prefix . 'mf_forms';
    $date = date('m/d/Y h:i:s', time());
    $wpdb->update(
      $form_table_name,
      array('form_modified' => $date),
      array('id' => $form_id,),
      array('%s')
    );


    do_action('mf_after_form_save', $form_id);

    return true;
  }

  /**
   * Prepare fields array before saving
   *
   * Set field ID as the key for the field array
   * Make sure all required options are available
   * Data sanitization
   *
   * @since    1.0.7
   * @param array $containers
   * @return array
   */
  public static function prepare_form_containers($containers)
  {
    $containers = is_array($containers) && !empty($containers) ? $containers : array();
    $prepared = array(
      'settings' => array(),
      'data' => array()
    );

    if (isset($containers['data']) && !empty($containers['data'])) {
      $settings = $containers['settings'] ?? false;
      foreach ($containers['data'] as $data) {

        if (!isset($data['type'])) {
          continue;
        }

        $type = $data['type'];

        // get the container object
        $container = MF_Containers::get($type, array(
          'data' => $data,
          'settings' => $settings[$type] ?? array(),
        ));

        // Prepare container global settings ( make sure this happens only once )
        if (isset($settings[$type]) && !isset($prepared['settings'][$type])) {
          $sanitized_settings = $container->sanitize_settings();
          if ($sanitized_settings) {
            $prepared['settings'][$type] = $sanitized_settings;
          }
        }
        // Prepare container options
        $sanitized_data = $container->sanitize_data();
        if ($sanitized_data) {
          $prepared['data'][] = $sanitized_data;
        }
      }
    }

    // Set `settings` to `false` if settings are not available
    if (empty($prepared['settings'])) {
      $prepared['settings'] = false;
    }

    return apply_filters('mf_container_options_sanitized', $prepared);
  }
  /**
   * Prepare fields array before saving
   *
   * Set field ID as the key for the field array
   * Make sure all required options are available
   * Data sanitization
   *
   * @since    1.0.0
   * @param array $fields
   * @return array
   */
  public static function prepare_form_fields($fields)
  {
    $fields = is_array($fields) && !empty($fields) ? $fields : array();
    $prepared = array();
    foreach ($fields as $key => $field) {
      if (!isset($field['type'])) {
        continue;
      }
      // get the field object
      $megaform_field = MF_Fields::get($field['type'], array('field' => $field));
      // Set field ID as the key and sanitize the settings
      $prepared[$field['id']] = $megaform_field->sanitize_settings();
    }

    return apply_filters('mf_field_options_sanitized', $prepared);
  }
  /**
   * Prepare settings array before saving ( sanitization...etc )
   *
   * @since    1.0.0
   * @param array $fields
   * @return array
   */
  public static function prepare_form_settings($settings)
  {
    # merge settings into a one level array ( the provided result is breaking settings into multiple arrays although each have a unique key )
    if (is_array($settings)) {
      $settings = !empty($settings) && isset($settings[0])  ? call_user_func_array('array_merge', array_values($settings)) : $settings;
    } else {
      $settings = array();
    }


    # Get the available options ( this to avoid saving options that are not expected )
    if (!class_exists('MegaForms_Form_Settings')) {
      require_once MEGAFORMS_ADMIN_PATH . 'partials/views/single-form-view/mega-form-settings.php';
    }

    $settings_obj = new MegaForms_Form_Settings();
    $available_options = $settings_obj->get_options();
    $options =  is_array($available_options) && !empty($available_options) ? call_user_func_array('array_merge', array_values($available_options)) : array();

    # prepare saved options
    $prepared = array();

    foreach ($options as $key => $option) {
      if (isset($settings[$key])) {
        $prepared[$key] = mf_sanitize($settings[$key], $option['sanitization']);
      } else {
        if ($option['sanitization'] == 'boolean') {
          $prepared[$key] = false;
        }
      }
    }

    return apply_filters('mf_setting_options_sanitized', $prepared);
  }
  /**
   * Prepare form actions before saving ( sanitization...etc )
   *
   * @since    1.0.0
   * @param array $actions
   * @return array
   */
  public static function prepare_form_actions($actions)
  {
    $actions = is_array($actions) && !empty($actions) ? $actions : array();
    $prepared = array();

    foreach ($actions as $key => $action) {
      if (!isset($action['type'])) {
        continue;
      }
      // get the field object
      $megaform_action = MF_Actions::get($action['type'], array('action' => $action));
      // Set action ID as the key and sanitize the settings
      $prepared[$action['id']] = $megaform_action->sanitize_settings();
    }

    return apply_filters('mf_action_options_sanitized', $prepared);
  }

  /**
   * Searches the database for a specific entry based on ID
   *
   * @since    1.0.0
   *
   * @param int $entry_id The id for the entry we are trying to locate
   * @param bool $include_meta whether to include entry meta in the returned object or not
   *
   * @return object
   */
  public static function get_entry($entry_id, $include_meta = false)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    $form_record = wp_cache_get('mf_' . $entry_id . 'get_entry');
    if (!$form_record) {

      global $wpdb;

      $entries_table_name = $wpdb->prefix . 'mf_entries';
      $entry_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $entries_table_name WHERE id = %d", $entry_id));
      wp_cache_set('mf_' . $entry_id . 'get_entry', $entry_record);
    }

    if ($entry_record === null) {
      return false;
    }

    $entry = $entry_record;

    if ($include_meta) {
      $entry_meta = self::get_entry_meta($entry->id);
      $entry->meta = $entry_meta;
    }

    return (object) $entry;
  }
  /**
   * Create an entry
   *
   * @since    1.0.0
   * @param int $form_id
   * @return int
   */
  public static function create_entry($form, $entry_meta, $referrer = '', $is_spam = false)
  {

    # Allow getting fields by form ID
    if (is_numeric($form)) {
      $form = mfget_form($form);
    }

    do_action('mf_before_entry_creation', $form, $entry_meta);

    $form_id    = $form->ID;
    $browser    = mfget_browser();
    $user_ip    = mfget_ip_address();
    $user_agent = $browser['name'] . '/' . $browser['platform'];

    // GDPR compliance, disable saving USER IP and USER AGENT.
    if (mfget_option('storing_user_details', true) === false) {
      $user_agent = '';
      $user_ip    = '';
    }

    $entry_data = array(
      'form_id'         => $form->ID,
      'user_id'         => get_current_user_id(),
      'date_created'    => current_time('mysql', true),
      'referrer'        => $referrer,
      'user_ip'         => sanitize_text_field($user_ip),
      'user_agent'      => sanitize_text_field($user_agent),
      'is_spam'         => $is_spam,
    );

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';
    $entry = $wpdb->insert($table_name, $entry_data);

    // Return false if the entry could not be inserted to the database.
    if (!$entry) {
      return false;
    }

    // Set lead count for form
    self::set_form_entry_count($form_id);


    $entry_id = $wpdb->insert_id;
    // insert entry meta to database
    foreach ($form->fields as $field) {
      $meta_key = $field['id'];
      if (isset($entry_meta[$meta_key])) {
        $meta_value = $entry_meta[$meta_key];

        self::create_entry_meta($entry_id, $form_id, $meta_key, $meta_value);
      }
    }

    do_action('mf_after_entry_creation', $entry_id, $form, $entry_meta);

    return $entry_id;
  }
  /**
   * Set is_trash column in the database to 0 ( Bring entry out of trash ).
   *
   * @since    1.0.0
   * @param int $entry_id
   * @return int
   */
  public static function restore_entry($entry_id)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';
    // Take entry out of trash
    $restore = $wpdb->query($wpdb->prepare("UPDATE `$table_name` SET is_trash = 0  WHERE id = %d", $entry_id));
    // Delete entry cache
    wp_cache_delete('mf_' . $entry_id . 'get_entry');
    return $restore;
  }

  /**
   * Delete all entry records and meta from the database.
   *
   * @since    1.0.0
   * @param int $entry_id
   */
  public static function delete_entry($entry_id)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $entry_table_name = $wpdb->prefix . 'mf_entries';
    $meta_table_name = $wpdb->prefix . 'mf_entriesmeta';

    // Delete any associated files
    $entry_meta = mf_api()->get_entry_meta($entry_id);
    if (!empty($entry_meta)) {
      foreach ($entry_meta as $meta) {
        if (is_array($meta)) {
          foreach ($meta as $meta_item) {
            if (isset($meta_item['path'])) {
              mf_files()->delete_file($meta_item['path']);
            }
          }
        }
      }
    }

    // Delete Meta
    $wpdb->query($wpdb->prepare("DELETE FROM `$entry_table_name` WHERE id = %d", $entry_id));
    // Delete Entry
    $wpdb->query($wpdb->prepare("DELETE FROM `$meta_table_name` WHERE entry_id = %d", $entry_id));

    $form_id = self::get_form_id_by_entry_id($entry_id);
    // Set lead count for form
    self::set_form_entry_count($form_id);

    return true;
  }

  /**
   * Set is_trash column in the database to 1 ( Send entry to trash ).
   *
   * @since    1.0.0
   * @param int $entry_id
   * @return int
   */
  public static function trash_entry($entry_id)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';
    // Mark entry as trash
    $trash = $wpdb->query($wpdb->prepare("UPDATE `$table_name` SET is_trash = 1 WHERE id = %d", $entry_id));
    // Delete entry cache
    wp_cache_delete('mf_' . $entry_id . 'get_entry');
    return $trash;
  }
  /**
   * Delete all leads associated with a specific form
   *
   * @since    1.0.0
   * @param int $form_id
   * @return int
   */
  public static function delete_form_entries($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $entry_table_name = $wpdb->prefix . 'mf_entries';
    $meta_table_name = $wpdb->prefix . 'mf_entriesmeta';

    do_action('mf_entry_before_delete', $form_id);

    // Delete Meta
    $wpdb->query($wpdb->prepare("DELETE FROM `$entry_table_name` WHERE form_id = %d", $form_id));
    // Delete entries
    $wpdb->query($wpdb->prepare("DELETE FROM `$meta_table_name` WHERE form_id = %d", $form_id));
    // Set lead count for form
    self::set_form_entry_count($form_id);

    do_action('mf_entry_after_delete', $form_id);

    return true;
  }
  /**
   * Retrieve entry meta data from the database
   *
   * @since    1.0.0
   * @param int $entry_id
   * @param string $key
   * @return array
   */
  public static function get_entry_meta($entry_id, $key = '')
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entriesmeta';
    if (empty($key)) {
      $metas = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $table_name WHERE entry_id = %d", $entry_id));

      $entry_meta = array();
      if (!empty($metas)) {
        foreach ($metas as $meta) {
          $entry_meta[$meta->meta_key] = maybe_unserialize($meta->meta_value);
        }
      }
    } else {
      $meta = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE meta_key = %s AND entry_id = %d", $key, $entry_id));
      $entry_meta = maybe_unserialize($meta);
    }


    return $entry_meta;
  }
  /**
   * Update entry meta data in the database
   *
   * @since    1.0.0
   * @param int $entry_id
   * @param string $key
   * @return array
   */
  public static function update_entry_meta($entry_id, $key, $value = '')
  {


    if (!$key || !is_numeric($entry_id)) {
      return false;
    }

    $object_id = absint($entry_id);
    if (!$object_id) {
      return false;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'mf_entriesmeta';

    if (empty($table)) {
      return false;
    }


    $meta_ids = $wpdb->get_col($wpdb->prepare("SELECT `meta_id` FROM `$table` WHERE `entry_id` = %d AND `meta_key` = %s ", $object_id, $key));

    if (empty($meta_ids)) {

      $form_id = self::get_form_id_by_entry_id($object_id);

      return self::create_entry_meta($object_id, $form_id, $key, $value);
    }

    $meta_key = wp_unslash($key);
    $meta_value = wp_unslash($value);
    $meta_value = maybe_serialize($meta_value);

    $data = array('meta_value' => $meta_value);
    $where = array('entry_id' => $object_id,  'meta_key' => $meta_key);
    $format = array('%s');
    $result = $wpdb->update($table, $data, $where, $format);

    if (!$result) {
      return false;
    }

    return true;
  }
  /**
   * Insert entry meta columns into the database.
   *
   * @since    1.0.0
   * @param int $entry_id
   * @param int $form_id
   * @param string $key
   * @param mixed $value
   * @return int
   */
  public static function create_entry_meta($entry_id, $form_id, $key, $value = '')
  {

    if (!$key || !is_numeric($entry_id)) {
      return false;
    }

    $object_id = absint($entry_id);

    if (!$object_id) {
      return false;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'mf_entriesmeta';

    if (empty($table)) {
      return false;
    }

    $meta_key   = wp_unslash($key);
    $meta_value = wp_unslash($value);
    $meta_value = maybe_serialize($meta_value);

    $data = array(
      'form_id'     => $form_id,
      'entry_id'    => $object_id,
      'meta_key'    => $meta_key,
      'meta_value'  => $meta_value !== null ? $meta_value : ''
    );
    $format = array('%d', '%d', '%s', '%s',);

    $result = $wpdb->insert($table, $data, $format);

    if (!$result)
      return false;

    $mid = (int) $wpdb->insert_id;

    return $mid;
  }

  /**
   * count the number of leads per specific form
   *
   * @since    1.0.0
   * @param int $form_id
   */
  public static function get_form_entry_count($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';

    $entries_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE form_id = %d AND is_spam = %d", $form_id, 0));

    return (int) $entries_count;
  }
  /**
   * Set form lead_count by counting existing entries for supplied form
   *
   * @since    1.0.0
   * @param int $form_id
   */
  public static function set_form_entry_count($form_id)
  {

    if (!is_numeric($form_id)) {
      return false;
    }

    $count = self::get_form_entry_count($form_id);

    global $wpdb;
    $forms_table_name = $wpdb->prefix . 'mf_forms';

    $wpdb->query($wpdb->prepare("UPDATE $forms_table_name SET lead_count = %d WHERE id = %d", $count, $form_id));

    return $count;
  }
  /**
   * Set entry read status ( Read / Unread )
   *
   * @since    1.0.0
   * @param int $entry_id
   * @param bool $is_read
   */
  public static function set_entry_status($entry_id, $is_read)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';

    $sql = $wpdb->prepare("UPDATE $table_name SET is_read=%d WHERE id=%d", $is_read, $entry_id);
    $run_query = $wpdb->query($sql);

    return $run_query;
  }
  /**
   * Set entry star
   *
   * @since    1.0.0
   * @param int $entry_id
   * @param bool $is_starred
   */
  public static function set_entry_star($entry_id, $is_starred)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';

    $sql = $wpdb->prepare("UPDATE $table_name SET is_starred=%d WHERE id=%d", $is_starred, $entry_id);
    $run_query = $wpdb->query($sql);

    return $run_query;
  }

  /**
   * Set entry spam status
   *
   * @since    1.2.7
   * @param int $entry_id
   * @param bool $is_spam
   */
  public static function set_entry_spam($entry_id, $is_spam)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mf_entries';

    $sql = $wpdb->prepare("UPDATE $table_name SET is_spam=%d WHERE id=%d", $is_spam, $entry_id);
    $run_query = $wpdb->query($sql);

    return $run_query;
  }
  /**
   * Get form ID by entry id
   *
   * @since    1.0.0
   * @param int $entry_id
   */
  public static function get_form_id_by_entry_id($entry_id)
  {

    if (!is_numeric($entry_id)) {
      return false;
    }

    global $wpdb;
    $entries_table = $wpdb->prefix . 'mf_entries';

    $form_id = $wpdb->get_var($wpdb->prepare("SELECT `form_id` FROM `$entries_table` WHERE `id` = %d", $entry_id));

    return $form_id;
  }
  /**
   * Get a single field HTML markup for the settings (Singular)
   *
   * @since    1.0.0
   * @param array $field
   * @return string
   */
  public static function get_field_setting($field)
  {

    $the_setting = '';

    $type = $field['type'];
    $megaform_field = MF_Fields::get($type, array('field' => $field));

    if ($megaform_field) {
      $the_setting .= $megaform_field->get_field_settings();
    }


    return $the_setting;
  }

  /**
   * Get a single field HTML markup for the input and settings (Singular)
   *
   * @since    1.0.7
   * @param array $field
   * @param mixed $value
   * @param bool $is_editor
   * @return array
   */
  public static function get_field($field, $is_editor = false)
  {

    $the_field = '';
    $the_value = '';

    $type = $field['type'];

    $megaform_field = MF_Fields::get($type, array('field' => $field));

    if ($megaform_field) {
      // Retrieve the submission value, if available
      if (
        !$is_editor && // Make sure we are not in editor view
        function_exists('mf_submission') && // Make sure the submission class exists
        !mf_submission()->is_empty() && // Make sure the submission is not empty
        (int)mf_submission()->form->ID == (int)$field['formId'] // Make sure the field belongs to the same form that was submitted
      ) {
        $the_value = mf_submission()->get_value($megaform_field->field_id);
      }

      $the_field = $megaform_field->get_the_field($the_value, $is_editor);
    }

    return $the_field;
  }
  /**
   * Get a single field key (name attribute)
   *
   * @since    1.0.4
   * @param int $form_id
   * @param int|string $field_id
   * @return string
   */
  public static function get_field_key($form_id, $field_id)
  {
    return sprintf('mform_%d_mfield_%s', $form_id, $field_id);
  }

  /**
   * Get all fields markup (Plural)
   *
   * @since    1.0.4
   * @param object|int $form
   * @param bool $is_editor
   * @return string
   */
  public static function get_fields($form, $is_editor = false)
  {

    # Allow getting fields by form ID
    if (is_numeric($form)) {
      $form = self::get_form($form);
    }

    # Make sure no editor markup will appear on the front end or any page that is not the actual megaform editor.
    if ($is_editor && !self::is_page('mf_form_editor')) {
      $is_editor = false;
    }

    $the_fields = '';
    if (!empty($form->containers) && !empty($form->containers['data'])) {

      # Build populated containers ( each container will handle the display for inner fields )
      foreach ($form->containers['data'] as $data) {

        if (!isset($data['type'])) {
          continue;
        }

        $ctn = MF_Containers::get($data['type'], array(
          'form_id' => $form->ID,
          'form' => $form,
          'data' => $data,
          'settings' => $form->containers['settings'][$data['type']] ?? false
        ));
        $the_fields .= $ctn->get_container_display();
      }

      # Insert honeypot field ( Only on frontend )
      if (!$is_editor) {
        $hp_key = self::get_field_key($form->ID, 'hp');
        $hp_val = mfpost($hp_key);
        $honeypot_obj = new MegaForms_Honeypot(array(
          'form_id' => $form->ID,
          'field_id' => 0
        ));
        $honeypot_field = $honeypot_obj->get_the_field($hp_val, $is_editor);
        $the_fields .= $honeypot_field;
      }
    } else {

      # Create an empty row ( row type container )
      $ctn = MF_Containers::get('row');
      $the_fields .= $ctn->get_row_output(); # This will return a single row with an empty column

    }

    return $the_fields;
  }

  /**
   * Get a all fields HTML markup for the settings (Plural)
   *
   * @since    1.0.0
   * @param array $form
   * @return string
   */
  public static function get_field_settings($form)
  {

    # Allow getting fields by form ID
    if (is_numeric($form)) {
      $form = self::get_form($form);
    }


    $the_settings = '';

    if (!empty($form->fields)) {
      foreach ($form->fields as $field) {
        $type = $field['type'];
        $megaform_field = MF_Fields::get($type, array('field' => $field));

        if ($megaform_field) {
          $the_settings .= $megaform_field->get_field_settings();
        }
      }
    }

    return $the_settings;
  }
  /**
   * Get all actions markup ( Pluaral )
   *
   * @since    1.0.0
   * @param object|int $form
   * @return string
   */
  public static function get_actions($form)
  {

    # Allow getting fields by form ID
    if (is_numeric($form)) {
      $form = self::get_form($form);
    }

    # Build actions markup
    $actions = '';
    $actions .= '<li class="mf-row-container mf-container">';
    $actions .= '<ul class="mf-row-col mfcol_preview mf_action_col" style="width:100%">';
    if (!empty($form->actions)) {
      foreach ($form->actions as $action) {

        $actions .= self::get_action($action);
      }
    }
    $actions .= '</ul>';
    $actions .= '</li>';

    return $actions;
  }
  /**
   * Get a single action settings HTML markup (Singular)
   *
   * @since    1.0.0
   * @param array $action
   * @return array
   */
  public static function get_action($action)
  {
    $the_action = '';

    $type = $action['type'];

    $megaform_action = MF_Actions::get($type, array('action' => $action));

    if ($megaform_action) {

      $the_action = $megaform_action->get_action_settings();
    }

    return $the_action;
  }
  /**
   * Return megaforms default validation messages, with a filter.
   *
   * @since  1.0.0
   * @access public
   *
   * @param $form Current form object, if available.
   * @return array
   */
  public static function get_validation_messages($form = array())
  {

    $notices = apply_filters('mf_default_validation_notices', array(
      'form_validation_invalid_submission' => __('Invalid submission. Please refresh the page and try again.', 'megaforms'),
      'form_validation_invalid_custom_submission' => __('Your submission is not valid. Please refresh the page and try again.', 'megaforms'),
      'form_validation_session_error' => __('Your session has expired, please refresh the page and try again.', 'megaforms'),
      'form_validation_wpnonce_error' => __('We were unable to process your submission, please refresh the page and try again.', 'megaforms'),
      'form_validation_timetrap_error' => __('We couldn\'t process your submission, try again in few seconds.', 'megaforms'),
      'form_validation_honeypot_error' => __('We couldn\'t process your submission, try again later.', 'megaforms'),
      'form_validation_login_restricted' => __('Sorry, you need to be logged in to view the form.', 'megaforms'),
      'form_validation_limit_reached' => __('Sorry, new submissions are no longer accepted.', 'megaforms'),
      'form_validation_errors' => __('Please correct the highlighted errors before submitting this form.', 'megaforms'),
      'form_validation_success' => __('We have recieved your submission, thank you!', 'megaforms'),
      'field_validation_failed' => __('Error: this field can\'t be validated!', 'megaforms'),
      'field_value_wrong_format' => __('Error: this field can\'t be submitted.', 'megaforms'),
      'field_required_check_failed' => __('Error: this field can\'t be empty.', 'megaforms'),
      'entry_validation_invalid_submission' => __('Invalid entry. Please refresh the page and try again.', 'megaforms'),
      'entry_validation_session_error' => __('We were unable to process your changes, please refresh the page and try again.', 'megaforms'),
    ), $form);

    // Retrieve and assign additional validation messages from megaforms settings
    $timetrap_validation = mfget_option('timetrap_error', false);
    if ($timetrap_validation !== false) {
      $notices['form_validation_timetrap_error'] = $timetrap_validation;
    }
    $honeypot_validation = mfget_option('honeypot_error', false);
    if ($honeypot_validation !== false) {
      $notices['form_validation_honeypot_error'] = $honeypot_validation;
    }
    $email_validation = mfget_option('email_validation', false);
    if ($email_validation !== false) {
      $notices['invalid_email'] = $email_validation;
    }
    $website_validation = mfget_option('website_validation', false);
    if ($website_validation !== false) {
      $notices['invalid_url'] = $website_validation;
    }
    $date_validation = mfget_option('date_validation', false);
    if ($date_validation !== false) {
      $notices['invalid_date'] = $date_validation;
    }
    $date_range_validation = mfget_option('date_range_validation', false);
    if ($date_range_validation !== false) {
      $notices['invalid_date_range'] = $date_range_validation;
    }

    // Additional condition to help managing validation messages on the submission class
    if (isset($notices['field_validation_ignore'])) {
      unset($notices['field_validation_ignore']);
    }

    return $notices;
  }
  /**
   * Return the required validation notice, with a filter.
   *
   * @since  1.0.0
   * @return string
   */
  public static function get_validation_required_notice($field = null)
  {

    if (!empty($field) && isset($field['type']) && ($field['type'] == 'choice' || $field['type'] == 'radios' || $field['type'] == 'select' || $field['type'] == 'checkboxes')) {

      $option = mfget_option('options_required_notice', false);
      if (!$option) {
        $notice = $field['type'] == 'checkboxes' ? __('Please select at least one option.') : __('Please select an option.');
      } else {
        $notice = $option;
      }
    } else {

      $option = mfget_option('required_notice', false);
      if (!$option) {
        /* translators: field label. */
        $notice = sprintf(__('%s is required.', 'megaforms'), ucfirst(strtolower(mfget('field_label', $field, __('This field', 'megaforms')))));
      } else {
        $notice = $option;
      }
    }

    return apply_filters('mf_default_validation_required_notice', $notice, $field);
  }

  /**
   * Return the required validation notice, with a filter.
   *
   * @since  1.0.0
   * @return string
   */
  public static function get_validation_compound_required_notice($field = null, $sub_fields = null)
  {

    if (!empty($field) && isset($field['type']) && $field['type'] == 'address') {

      $option = mfget_option('address_required_notice', false);
      if (!$option) {
        $notice = __('Please enter a complete address.', 'megaforms');
      } else {
        $notice = $option;
      }
    } else {

      $option = mfget_option('compound_required_notice', false);
      if (!$option) {
        $notice = __('Please fill out the required fields.', 'megaforms');
      } else {
        $notice = $option;
      }
    }

    return apply_filters('mf_default_validation_required_notice', $notice, $field, $sub_fields);
  }

  /**
   * Get the slug for current page.
   *
   * @since  1.0.0
   * @access public
   *
   * @return bool|string Page slug or false.
   *   Available slugs:
   *
   *   form_editor
   */
  public static function get_page()
  {

    if (!is_admin()) {
      return false;
    }

    if (mfget('page') == 'mega-forms' && empty(mfget('action')) && empty(mfget('id'))) {
      return 'mf_forms';
    }

    if (mfget('page') == 'mega-forms' && mfget('action') == 'edit' && mfget('id') > 0) {
      return 'mf_form_editor';
    }

    if (mfget('page') == 'mega-forms-entries' && empty(mfget('view')) && empty(mfget('id'))) {
      return 'mf_entries';
    }

    if (mfget('page') == 'mega-forms-entries' && mfget('view') == 'form-entries' && mfget('id') > 0) {
      return 'mf_form_entries';
    }

    if (mfget('page') == 'mega-forms-entries' && mfget('view') == 'entry' && mfget('id') > 0) {
      return 'mf_entry_view';
    }

    if (mfget('page') == 'mega-forms-entries' && mfget('view') == 'edit' && mfget('id') > 0) {
      return 'mf_entry_editor';
    }

    if (mfget('page') == 'mega-forms-settings') {
      return 'mf_settings';
    }

    if (mfget('page') == 'mega-forms-import-export') {
      return 'mf_import_export';
    }

    if (mfget('page') == 'mega-forms-addons') {
      return 'mf_addons';
    }

    if (mfget('page') == 'mega-forms-help') {
      return 'mf_help';
    }

    return false;
  }
  /**
   * Get the url for a megaform admin page based on slug.
   *
   * @since  1.0.0
   * @access public
   *
   * @return bool|string Page url or false.
   */
  public static function get_page_url($slug, $id = false, $additional_params = false)
  {

    if (!is_admin()) {
      return false;
    }

    // Extract current query params, but make sure to exclude any params that are used by our plugin
    if (is_array($additional_params)) {
      $extraParams = $additional_params;
    } else {
      $extraParams = array();
    }

    $params = array();

    switch ($slug) {
      case 'mf_forms':
        $params['page'] = 'mega-forms';
        break;
      case 'mf_form_editor':
        if (is_numeric($id) && $id > 0) {
          $params['page'] = 'mega-forms';
          $params['action'] = 'edit';
          $params['id'] = $id;
        }
        break;
      case 'mf_entries':
        $params['page'] = 'mega-forms-entries';
        break;
      case 'mf_form_entries':
        if (is_numeric($id) && $id > 0) {
          $params['page'] = 'mega-forms-entries';
          $params['view'] = 'form-entries';
          $params['id'] = $id;
        }
        break;
      case 'mf_entry_view':
        if (is_numeric($id) && $id > 0) {
          $params['page'] = 'mega-forms-entries';
          $params['view'] = 'entry';
          $params['id'] = $id;
        }
        break;
      case 'mf_entry_editor':
        if (is_numeric($id) && $id > 0) {
          $params['page'] = 'mega-forms-entries';
          $params['view'] = 'edit';
          $params['id'] = $id;
        }
        break;
      case 'mf_settings':
        $params['page'] = 'mega-forms-settings';
        break;
      case 'mf_import_export':
        $params['page'] = 'mega-forms-import-export';
        break;
      case 'mf_addons':
        $params['page'] = 'mega-forms-addons';
        break;
      case 'mf_help':
        $params['page'] = 'mega-forms-help';
        break;
    }

    if (!empty($params)) {

      $finalParams = !empty($extraParams) ? array_merge($params, $extraParams) : $params;
      $urlParams = http_build_query($finalParams);

      return admin_url('admin.php?' . $urlParams);
    } else {
      return false;
    }
  }
  /**
   * Determine whether the current page is one of existing mega-form admin pages.
   *
   * @since  1.0.0
   * @access public
   *
   * @return bool
   */
  public static function is_page($slug)
  {

    if ($slug == self::get_page()) {
      return true;
    }

    return false;
  }
  /**
   * Return the SVG code for megaforms icon.
   *
   * @since  1.0.0
   * @access public
   *
   * @return string
   */
  public static function get_mf_icon($color = "#000000", $base64 = false)
  {

    $svgCode = '<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" height="110.97054" width="116.93835" xml:space="preserve" viewBox="0 0 116.93834 110.97054" y="0px" x="0px" id="Layer_1" version="1.1"><g transform="translate(-8.9266005,-12.025929)" id="g7459"><g id="g7457"><path style="fill:%1$s;fill-opacity:1;fill-rule:nonzero;stroke-width:0.27563247" d="m 72.208649,114.87581 c -2.147602,-0.31313 -4.243955,-1.45876 -5.815123,-3.17787 -1.321399,-1.44581 -2.516665,-3.86981 -2.516665,-5.10377 0,-0.30155 0.662854,-1.35188 1.473007,-2.33407 3.508713,-4.25378 4.580841,-8.500383 2.94795,-11.676576 -0.280955,-0.546499 -1.452624,-2.217766 -2.60371,-3.713924 l -2.09288,-2.720289 V 72.262743 58.376175 h 1.76971 c 1.985151,0 1.833361,0.192711 2.253108,-2.860511 l 0.184842,-1.34453 -3.275267,0.130137 c -2.758331,0.1096 -3.524093,0.2427 -4.851796,0.843312 -1.59958,0.723598 -4.349571,3.027723 -4.349571,3.644357 0,0.185528 0.750015,0.73708 1.666697,1.225669 1.339304,0.713841 1.745379,0.823041 2.067244,0.555917 0.348575,-0.28929 0.400546,0.605639 0.400546,6.897259 0,6.355206 -0.05067,7.21024 -0.418934,7.068926 -1.719514,-0.659839 -6.586853,-4.813444 -8.401305,-7.169358 -0.530593,-0.688929 -1.087674,-1.257295 -1.23796,-1.263033 -0.412434,-0.01575 -3.17216,1.946458 -3.17216,2.255453 0,0.61444 10.705791,15.97019 15.520281,22.261364 1.448479,1.892754 2.590724,3.484245 2.538321,3.536646 -0.0524,0.0524 -0.361244,-0.04706 -0.686317,-0.221035 -1.364171,-0.730085 -3.315029,0.599142 -3.315029,2.258711 0,0.604377 0.380202,1.208789 1.49669,2.379309 l 1.49669,1.569122 -0.510998,0.67745 -0.510995,0.67744 -1.654313,-1.577949 c -1.960615,-1.870112 -3.042042,-2.085397 -4.276159,-0.85128 -1.187668,1.187669 -1.006831,2.317689 0.651496,4.071129 0.757989,0.80146 1.378162,1.52497 1.378162,1.6078 0,0.0828 -0.20187,0.22807 -0.4486,0.32275 -0.299602,0.11497 -0.890742,-0.24143 -1.779803,-1.07304 -1.069633,-1.00052 -1.52664,-1.24519 -2.325845,-1.24519 -1.309519,0 -1.911161,0.35115 -2.328684,1.35914 -0.252793,0.6103 -0.266547,1.04929 -0.05214,1.66433 0.260611,0.74759 0.233634,0.83539 -0.256691,0.83539 -1.352314,0 -3.103969,-1.40122 -8.394814,-6.71534 C 37.885732,94.983092 32.442848,89.051743 27.649799,83.411049 l -1.562897,-1.839293 0.980515,-1.947043 c 0.539284,-1.070874 1.221992,-2.982669 1.51713,-4.248436 1.07009,-4.58932 2.415321,-7.124819 6.957058,-13.112695 2.701336,-3.561472 4.600926,-7.138048 5.504926,-10.36477 0.791961,-2.82681 0.851619,-7.633195 0.128296,-10.336218 -1.093139,-4.085003 -4.299062,-9.133648 -7.835702,-12.339568 l -1.297816,-1.176451 -1.786986,0.171653 c -7.098505,0.681862 -13.437074,5.408132 -16.972457,12.655284 -2.59914,5.327957 -3.7016618,10.363817 -3.7034953,16.916023 -0.00127,4.494067 0.4990953,8.369614 1.6552153,12.8207 0.437646,1.684947 0.87368,3.373629 0.968964,3.752624 0.26346,1.047918 -1.596003,-1.908791 -3.3302902,-5.295452 C 7.2037754,65.809244 5.4652982,60.883554 4.7097578,57.273645 4.0397459,54.072383 4.0360715,47.526316 4.7025225,44.383582 6.2202928,37.226344 9.9068264,30.137497 14.222469,26.07765 c 4.131468,-3.886589 8.774372,-5.330596 13.533069,-4.20897 l 1.778143,0.419108 1.086546,-1.318988 c 1.973295,-2.395437 6.177953,-6.304276 8.710762,-8.097918 5.828268,-4.1273603 11.842128,-6.6844324 19.171038,-8.1514595 3.851396,-0.7709336 13.043199,-0.6922919 16.951398,0.14503 17.118783,3.6676569 30.126295,15.1529085 35.557055,31.3958405 2.90512,8.688983 3.08824,18.763499 0.49952,27.482548 -1.01665,3.424185 -2.38224,6.753685 -3.89292,9.491504 l -1.155,2.093227 -0.15242,-6.063914 c -0.1358,-5.402714 -0.21888,-6.229214 -0.76195,-7.579893 -1.62338,-4.037559 -5.05393,-6.714184 -9.352941,-7.297469 l -1.447071,-0.196339 v 2.062434 2.062434 l 1.033622,0.190271 c 1.761796,0.324321 3.191386,1.112324 4.39167,2.420732 2.07942,2.266746 2.01679,1.490102 2.01679,25.005712 0,22.66235 -0.023,22.28683 1.56589,25.53184 0.40406,0.82519 1.15695,1.99372 1.6731,2.59672 l 0.93845,1.09637 -16.352705,-0.0344 c -8.993984,-0.0189 -17.006626,-0.12973 -17.805866,-0.24627 z m 24.744109,-10.60683 v -2.06724 H 84.549297 72.145835 v 2.06724 2.06725 h 12.403462 12.403461 z m 0,-10.749664 V 91.452072 H 84.549297 72.145835 v 2.067244 2.067243 h 12.403462 12.403461 z m 0,-10.749667 V 80.702406 H 84.549297 72.145835 v 2.067243 2.067244 H 84.549297 96.952758 Z M 82.895502,71.74435 v -2.067244 h -5.374834 -5.374833 v 2.067244 2.067244 h 5.374833 5.374834 z M 68.143547,41.759166 c 1.640854,-0.92933 2.392429,-2.41438 2.279803,-4.504697 -0.16162,-2.999595 -2.113219,-4.679732 -5.17831,-4.458025 -1.882738,0.136185 -3.232133,1.013245 -3.978356,2.585791 -0.661702,1.394433 -0.48127,3.833012 0.3723,5.031743 1.380687,1.938997 4.36603,2.556386 6.504563,1.345188 z M 99.49939,41.498289 c 1.23572,-0.942534 1.91863,-2.405043 1.90564,-4.081139 -0.0241,-3.116298 -2.330472,-4.985646 -5.684663,-4.60758 -4.387041,0.494482 -5.4916,6.549907 -1.631461,8.944012 1.49766,0.928868 4.013612,0.81015 5.410484,-0.255293 z" id="path923"  transform="translate(4.7216423,7.84)"/><path style="fill:%1$s;fill-opacity:1;fill-rule:nonzero;stroke-width:0.27563247" d="m 120.69076,41.27973 c -0.0889,-4.75975 -1.27128,-10.184687 -2.98621,-13.701634 -1.96919,-4.038343 -5.52799,-6.950472 -9.02837,-7.387815 -1.45197,-0.181412 -1.54391,-0.245969 -3.16751,-2.224172 l -1.67215,-2.037336 h 1.11768 c 0.61472,0 2.07371,0.195682 3.2422,0.434849 4.81984,0.986524 8.18441,3.637108 10.35393,8.156759 1.78652,3.721752 2.40617,6.443169 2.56245,11.253931 0.0714,2.198169 0.0132,4.740878 -0.12929,5.650465 l -0.25912,1.653795 z" id="path925"  transform="translate(4.7216423,7.84)"/></g></g></svg>';

    $icon = sprintf($svgCode, $color);

    if ($base64) {
      $icon = 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" encoding="utf-8"?>' . $icon);
    }

    return $icon;
  }
  /**
   * Return the path for Mega Forms SVG icon.
   *
   * @since  1.0.0
   * @access public
   *
   * @return string
   */
  public static function get_mf_icon_image_path()
  {
    return MEGAFORMS_ADMIN_PATH . 'assets/images/logo-v1.svg';
  }
  /**
   * Return markup for any provided icon.
   *
   * @since  1.0.0
   * @access public
   *
   * @return string
   */
  public static function get_custom_icon($tag, $class, $icon)
  {

    $img = '';
    $img_style_attr = '';
    $img_class = '';
    if (!empty($icon)) {
      $img = '<img src="' . esc_url(set_url_scheme($icon)) . '" alt="" />';

      if (0 === strpos($icon, 'data:image/svg+xml;base64,')) {
        $img       = '<br />';
        $img_style_attr = ' style="background-image:url(\'' . esc_attr($icon) . '\')"';
        $img_class .= ' svg';
      } elseif (0 === strpos($icon, 'data:image/png;base64,')) {
        $img       = '<br />';
        $img_style_attr = ' style="background-image:url(\'' . esc_attr($icon) . '\')"';
        $img_class .= ' png';
      } elseif (0 === strpos($icon, 'dashicons-')) {
        $img       = '<br />';
        $img_class .= ' dashicons-before ' . sanitize_html_class($icon);
      } elseif (0 === strpos($icon, 'mega-icons-')) {
        $img       = '<br />';
        $img_class .= ' mega-icon-before ' . sanitize_html_class($icon);
      }
    }

    return sprintf('<%1$s class="%2$s%3$s"%4$s">%5$s</%1$s>', $tag, $class, $img_class, $img_style_attr, $img);
  }
  /**
   * Return pre-made form templates.
   *
   * @since  1.0.5
   * @access public
   *
   * @return array
   */
  public static function get_ready_form_templates($get_single = false, $args = array())
  {

    $templates = array();

    # Create the main templates
    $templates['main'] = array(
      'label' => __('Select a Template', 'megaforms'),
      'desc' => __('Create a form by selecting one of the pre-made templates, or start with a <a href="#" data-hook="create-form" data-template-parent="main" data-template-type="blank" data-template-title="Blank Form">blank form</a>.', 'megaforms'),
      'templates' => array(
        'blank' => array(
          'label' => __('Blank Form', 'megaforms')
        ),
        'simple_contact' => array(
          'label' => __('Simple Contact Form', 'megaforms'),
          'import_path' => MEGAFORMS_ADMIN_PATH . 'assets/json/simple-contact.json'
        ),
        'advanced_contact' => array(
          'label' => __('Advanced Contact Form', 'megaforms'),
          'import_path' => MEGAFORMS_ADMIN_PATH . 'assets/json/advanced-contact.json'
        ),
        'job_application' => array(
          'label' => __('Job Application Form', 'megaforms'),
          'import_path' => MEGAFORMS_ADMIN_PATH . 'assets/json/job-application.json'
        ),
        'feedback' => array(
          'label' => __('Feedback Form', 'megaforms'),
          'import_path' => MEGAFORMS_ADMIN_PATH . 'assets/json/feedback.json'
        ),
      )
    );
    # Allow users to create their own templates
    $templates['additional'] = apply_filters('mf_ready_form_templates', array(
      'label' => __('Additional Templates', 'megaforms'),
      'templates' => array()
    ));

    if ($get_single) {
      $template = array();
      $parent = $args['parent'];
      $type = $args['type'];
      if (isset($parent) && isset($type) && isset($templates[$parent]['templates'][$type])) {
        $template = $templates[$parent]['templates'][$type];
      }
      return $template;
    } else {
      return $templates;
    }
  }
}
# Returns the main instance of MFAPI.
function mf_api()
{
  return MFAPI::instance();
}
