<?php

/**
 * Mega Forms Files API
 *
 * @link       https://wpali.com
 * @since      1.0.7
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Files_API
{
  /**
   * The single instance of the class.
   *
   * @var MF_Files_API
   * @since 1.0.7
   */
  protected static $_instance = null;

  /**
   * Main MF_Files_API Instance.
   *
   * Ensures only one instance of MF_Files_API is loaded or can be loaded.
   *
   * @since 1.0.7
   * @see mf_files()
   * @return MF_Files_API - Main instance.
   */
  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Returns megaforms upload dir path
   *
   * @since  1.0.7
   * @access public
   *
   * @return string
   */
  public static function get_upload_dir()
  {

    $dir = wp_upload_dir();

    if ($dir['error']) {
      return null;
    }

    return wp_normalize_path($dir['basedir']) . '/mega-forms/';
  }
  /**
   * Create the uploads folder
   *
   * @since  1.0.7
   * @access public
   *
   */
  public static function create_upload_dir()
  {

    $upload_dir = mf_files()->get_upload_dir();
    $upload_private_dir = $upload_dir . 'private/';

    // Create directories
    if (!is_dir($upload_private_dir)) {
      wp_mkdir_p($upload_private_dir);
    }

    // Protect the private folder: TODO
    // $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? wp_unslash($_SERVER['SERVER_SOFTWARE']) : '';
    // if (strpos(strtolower($server_software), 'nginx') !== false) {
    //   // Server is running Nginx
    //   // Add ngix config file if it's not already added
    //   if (!file_exists($upload_private_dir . '/mf-nginx-config.conf')) {
    //     file_put_contents(
    //       $upload_private_dir . '/mf-nginx-config.conf',
    //       'server { location ' . $upload_private_dir . ' { deny all; } }'
    //     );

    //     // Copy the Nginx config snippet to the sites-available directory
    //     copy($upload_private_dir . '/mf-nginx-config.conf', '/etc/nginx/sites-enabled/' . wp_unslash($_SERVER['HTTP_HOST']) . '-mega-forms.conf');

    //   }
    // } else {

    // Server is running Apache
    // Add .htaccess file if not already added
    if (!file_exists($upload_private_dir . '/.htaccess')) {
      mf_files()->add_htaccess_file($upload_private_dir, "deny from all");
    }

    // }

    // Add index file if not already added
    if (!file_exists($upload_dir . '/index.html')) {
      mf_files()->add_index_file_recursively($upload_dir);
    } else if (!file_exists($upload_private_dir . '/index.html')) {
      mf_files()->add_index_file_recursively($upload_private_dir);
    }
  }

  /**
   *  Get upload path for a given form
   *
   * @return string
   */
  public static function get_form_upload_path($form_id)
  {
    $form_id = absint($form_id);
    $upload_path = self::get_upload_dir() . 'private' . '/';
    return $upload_path . $form_id . '-' . wp_hash($form_id) . '/';
  }
  /**
   *  Generate a safe download url using the provided args
   *
   * @return string
   */
  public static function generate_safe_download_url($form_id, $field_id, $filepath, $force_download = false)
  {

    $download_url = site_url('index.php');
    $filepath = $filepath;
    $upload_path = self::get_form_upload_path($form_id);

    // Make sure the file is pointing to the correct directory
    if (strpos($filepath, $upload_path) !== false) {
      $file = str_replace($upload_path, '', $filepath);

      // Build url params
      $args = array(
        'mf-dl' => urlencode($file),
        'fmid' => $form_id,
        'fdid' => $field_id,
        'key' => self::generate_safe_download_hash($form_id, $field_id, $file),
      );

      if ($force_download) {
        $args['dl'] = 1;
      }

      $download_url = add_query_arg($args, $download_url);
    }

    return $download_url;
  }
  /**
   *  Generate a safe download hash using the provided args
   *  (we are using `wp_salts` to generate the hash and to extract data from url, if they changed, old download urls will not work)
   * 
   * @return string
   */
  public static function generate_safe_download_hash($form_id, $field_id, $file)
  {

    $data = absint($form_id) . ':' . absint($field_id) . ':' . urlencode($file);
    $algorithm = apply_filters('mf_download_hash_algorithm', 'sha256');
    $key = 'mf_download_' . wp_salt();
    $hash = hash_hmac($algorithm, $data, $key);

    return $hash;
  }
  /**
   *  Validate a megaforms download request and deliver if everything is valid
   *
   * @return void
   */
  public static function maybe_process_download()
  {
    if (isset($_GET['mf-dl'])) {
      $file = mfget('mf-dl');
      $form_id  = mfget('fmid');
      $field_id = mfget('fdid');
      $hash = mfget('key');

      // Make sure file, form_id and hash are available
      if (empty($file) || empty($form_id) || empty($hash)) {
        // Show 404 page
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        include get_query_template('404');
        exit;
      }

      // Check if this download require login
      if (apply_filters('mf_download_require_login', false, $form_id, $field_id) && !is_user_logged_in()) {
        wp_redirect(wp_login_url(add_query_arg($_GET, site_url('index.php'))));
        exit;
      }

      // Validate hash
      $hash_check = self::generate_safe_download_hash($form_id, $field_id, $file);
      $is_valid = hash_equals($hash_check, $hash);
      if ($is_valid) {
        // Deliver the file
        $upload_path = self::get_form_upload_path($form_id);
        $file_path = trailingslashit($upload_path) . $file;

        if (file_exists($file_path)) {

          $filetype = wp_check_filetype($file_path);
          $content_disposition = mfget('dl') ? 'attachment' : 'inline';

          nocache_headers();
          header('X-Robots-Tag: noindex', true);
          header('Content-Type: ' . $filetype['type']);
          header('Content-Description: File Transfer');
          header('Content-Disposition: ' . $content_disposition . '; filename="' . basename($file) . '"');
          header('Content-Transfer-Encoding: binary');

          // Clear the buffer and turn it off completely to prevent the file from getting corrupt for the reason
          // This can happen due to manipulation, or printed content before the header is sent
          if (ob_get_contents()) {
            ob_end_clean();
          }
          readfile($file_path);
          exit;
        } else {
          // Show 404 page
          global $wp_query;
          $wp_query->set_404();
          status_header(404);
          include get_query_template('404');
          exit;
        }
      } else {
        // Set header status to 401 (unauthorized) and exist
        status_header(401);
        die();
      }
    }
  }

  /**
   *  Get upload path for this temporary files
   *
   * @return string
   */
  public static function get_form_temp_upload_path($form_id)
  {
    return self::get_form_upload_path($form_id) . 'tmp' . '/';
  }
  /**
   * Creates an empty index.html file on the provided directory and all child directories
   *
   * @since  1.0.7
   * @access public
   *
   */
  public static function add_index_file_recursively($dir)
  {
    if (!is_dir($dir) || is_link($dir)) {
      return;
    }

    if (!($dp = opendir($dir))) {
      return;
    }

    // ignores all errors
    set_error_handler('__return_false', E_ALL);

    // Create an empty index.html file
    if ($f = fopen($dir . '/' . 'index.html', 'w')) {
      fclose($f);
    }

    // restores error handler
    restore_error_handler();

    while ((false !== $file = readdir($dp))) {
      if (is_dir("$dir/$file") && $file != '.' && $file != '..') {
        self::add_index_file_recursively("$dir$file");
      }
    }

    closedir($dp);
  }

  /**
   * Create an .htaccess file with the provided rules inside the provided directory
   *
   * @since  1.0.7
   * @access public
   *
   */
  public static function add_htaccess_file($dir, $rules)
  {

    if (!is_dir($dir)) {
      return;
    }

    if (!wp_is_writable($dir)) {
      return;
    }

    $htaccess_file = $dir . '.htaccess';
    if (file_exists($htaccess_file)) {
      @unlink($htaccess_file);
    }

    $rules = is_array($rules) ? $rules : explode("\n", $rules);

    if (!empty($rules)) {
      if (!function_exists('insert_with_markers')) {
        require_once(ABSPATH . 'wp-admin/includes/misc.php');
      }
      insert_with_markers($htaccess_file, 'Mega Forms', $rules);
    }
  }

  /**
   * Returns a list of forbidden file extensions
   *
   * @since  1.0.7
   * @access public
   * @return array
   */
  public static function get_disallowed_file_extensions()
  {

    $extensions = array(
      'php',
      'asp',
      'aspx',
      'cmd',
      'csh',
      'bat',
      'html',
      'htm',
      'hta',
      'jar',
      'exe',
      'com',
      'js',
      'lnk',
      'htaccess',
      'phtml',
      'ps1',
      'ps2',
      'php3',
      'php4',
      'php5',
      'php6',
      'py',
      'rb',
      'tmp',
      'idle'
    );

    return apply_filters('mf_disallowed_file_extensions', $extensions);
  }
  /**
   * Checks if a filename extensions is in the provided list of extensions
   *
   * @since  1.0.7
   * @access public
   * @return bool
   */
  public static function match_file_extension($filename, $extensions)
  {
    if (empty($extensions) || !is_array($extensions)) {
      return false;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, $extensions)) {
      return true;
    }

    return false;
  }
  /**
   * Checks if a filename has a forbidden extension
   *
   * @since  1.0.7
   * @access public
   * @return bool
   */
  public static function file_name_has_disallowed_extension($file_name)
  {
    return self::match_file_extension($file_name, self::get_disallowed_file_extensions()) || strpos(strtolower($file_name), '.php.') !== false;
  }

  /**
   * Checks if a file type and extension are valid
   *
   * @since  1.0.7
   * @access public
   * @return mixed
   */
  public static function check_type_and_ext($file, $file_name = '')
  {
    if (empty($file_name)) {
      $file_name = $file['name'];
    }
    $tmp_name = $file['tmp_name'];
    // Whitelist the mime type and extension
    $wp_filetype     = wp_check_filetype_and_ext($tmp_name, $file_name);
    $ext             = empty($wp_filetype['ext']) ? '' : $wp_filetype['ext'];
    $type            = empty($wp_filetype['type']) ? '' : $wp_filetype['type'];
    $proper_filename = empty($wp_filetype['proper_filename']) ? '' : $wp_filetype['proper_filename'];

    if ($proper_filename) {
      return new WP_Error('invalid_file', esc_html__('There was an problem while verifying your file.'));
    }
    if (!$ext) {
      return new WP_Error('illegal_extension', esc_html__('Sorry, this file extension is not permitted for security reasons.'));
    }
    if (!$type) {
      return new WP_Error('illegal_type', esc_html__('Sorry, this file type is not permitted for security reasons.'));
    }

    return true;
  }

  /**
   * Set file permissions
   *
   * @since  1.0.7
   * @access public
   */
  public static function set_permissions($path)
  {
    $permission = apply_filters('mf_file_permission', 0644, $path);
    if ($permission) {
      chmod($path, $permission);
    }
  }

  /**
   * Move temporary file to the correct folder
   *
   * @since  1.0.7
   * @access public
   */
  public static function move_temp_file($form_id, $hash, $filename)
  {

    $time = current_time('mysql');
    $year = substr($time, 0, 4);
    $month = substr($time, 5, 2);
    $sub_dir_path = $year . '/' . $month . '/';

    $form_upload_dir = self::get_form_upload_path($form_id);
    $target_dir = $form_upload_dir . $sub_dir_path;

    // Attempt to create the target directory and add index.html file
    if (!is_dir($target_dir)) {
      if (!wp_mkdir_p($target_dir)) {
        return false;
      }
      // Adding index.html files to all subfolders.
      if (!file_exists($form_upload_dir . 'index.html')) {
        self::add_index_file_recursively($target_dir);
      } elseif (!file_exists($form_upload_dir . $year . '/' . 'index.html')) {
        self::add_index_file_recursively($form_upload_dir . $year);
      } else if (!file_exists($target_dir . "index.html")) {
        self::add_index_file_recursively($target_dir);
      }
    }


    $unique_filename = wp_unique_filename($target_dir, $filename);
    $temp_upload_path = self::get_form_temp_upload_path($form_id);
    $target_path = $target_dir . $unique_filename;
    $source_path = $temp_upload_path . $hash . '.tmp';

    // If the .tmp version of the file doesn't exist, fallback to .idle version
    if (!file_exists($source_path)) {
      $source_path = $temp_upload_path . $hash . '.idle';
    }

    if (rename($source_path, $target_path)) {
      self::set_permissions($target_path);

      return array(
        'name' => $unique_filename,
        'path' => $target_path,
        'size' => filesize($target_path),
      );
    } else {
      return false;
    }
  }
  /**
   * Delete temporary file using the provided hash and form id
   *
   * @since  1.0.7
   * @access public
   */
  public static function delete_temp_file($form_id, $hash)
  {
    $temp_path = self::get_form_temp_upload_path($form_id);
    $temp_delete_path = $temp_path . $hash . '.tmp';
    $idle_delete_path = $temp_path . $hash . '.idle';
    if (file_exists($temp_delete_path)) {
      @unlink($temp_delete_path);
      return true;
    } elseif (file_exists($idle_delete_path)) {
      @unlink($idle_delete_path);
      return true;
    }

    return false;
  }

  /**
   * Delete file
   *
   * @since  1.1.5
   * @access public
   */
  public static function delete_file($path)
  {
    if (file_exists($path)) {
      @unlink($path);
      return true;
    }

    return false;
  }

  /**
   * Returns temporary files maximum age in seconds
   *
   * @since  1.0.7
   * @access int
   */
  public static function get_temp_file_max_age()
  {
    return 3 * 3600; // 3 hours in seconds
  }
  /**
   * Returns idle files maximum age in seconds
   * (idle files the temporary files for a form that was submitted via `save and continue` feature)
   * 
   * @since  1.0.8
   * @access int
   */
  public static function get_idle_file_max_age()
  {
    return MONTH_IN_SECONDS; // 3 hours in seconds
  }
  /**
   * Returns temporary files maximum age in seconds
   *
   * @since  1.0.7
   * @access int
   */
  public static function format_size_unites($bytes)
  {
    if ($bytes >= 1073741824) {
      $bytes = number_format($bytes / 1073741824, 2) . ' gb';
    } elseif ($bytes >= 1048576) {
      $bytes = number_format($bytes / 1048576, 2) . ' mb';
    } elseif ($bytes >= 1024) {
      $bytes = number_format($bytes / 1024, 2) . ' kb';
    } elseif ($bytes > 1) {
      $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
      $bytes = $bytes . ' byte';
    } else {
      $bytes = '0 bytes';
    }

    return $bytes;
  }
  /**
   * Remove old temporary files
   *
   * @since  1.0.7
   * @access public
   */
  public static function clean_temp_files($form_dirs = array(), $ignore = "")
  {

    $max_file_age = self::get_temp_file_max_age();
    $max_idle_file_age = self::get_idle_file_max_age();

    if (!empty($location)) {
      $form_dirs = is_array($form_dirs) ? $form_dirs : array($form_dirs);
    } else {
      $path = !empty($path) ? $path : self::get_upload_dir() . 'private' . '/';
      $form_dirs = glob($path . '*', GLOB_ONLYDIR);
    }

    foreach ($form_dirs as $dir) {
      $tmp_dir = $dir . '/' . 'tmp/';
      if (is_dir($tmp_dir) && ($dir = opendir($tmp_dir))) {
        while (($tmp_file = readdir($dir)) !== false) {
          if ($tmp_file != '.' && $tmp_file != '..') {
            // Remove temp file if it is older than the max age and is not the current file
            $tmp_file_path = $tmp_dir . $tmp_file;
            $tmp_file_ext = pathinfo($tmp_file, PATHINFO_EXTENSION);
            $tmp_file_time = filemtime($tmp_file_path);
            if (
              $tmp_file_path != $ignore &&
              file_exists($tmp_file_path) &&
              (($tmp_file_ext == 'tmp' && $tmp_file_time < time() - $max_file_age) ||
                ($tmp_file_ext == 'idle' && $tmp_file_time < time() - $max_idle_file_age))
            ) {
              @unlink($tmp_file_path);
            }
          }
        }
        closedir($dir);
      }
    }
  }
}
# Returns the main instance of MF_Files_API.
function mf_files()
{
  return MF_Files_API::instance();
}
