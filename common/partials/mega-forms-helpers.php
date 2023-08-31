<?php

/**
 * This file is used to produce all helper functions related to this plugin.
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (!function_exists('mf_dev_env')) :

  function mf_dev_env()
  {
    return defined('MEGAFORMS_DEV');
  }

endif;

if (!function_exists('get_mf_common_js_vars')) :

  function get_mf_common_js_vars()
  {
    return array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'context' => is_admin() ? 'admin' : 'public',
      'dev_env' => mf_dev_env()
    );
  }

endif;

if (!function_exists('mf_db_update')) :

  function mf_db_update()
  {
    if (!class_exists('Mega_Forms_Updater')) {
      require_once MEGAFORMS_INC_PATH . 'class-mega-forms-updater.php';
    }

    return Mega_Forms_Updater::update_db();
  }

endif;
if (!function_exists('mfget_form')) :

  function mfget_form($form_id = 0)
  {
    $_form = false;

    if (isset($GLOBALS['mf_form'])) {
      $form = $GLOBALS['mf_form'];

      if (is_object($form) && !empty($form->ID)) {
        if (($form_id > 0 && $form_id == $form->ID) || $form_id == 0) {
          $_form = $form;
        }
      }
    }

    if (!$_form && $form_id > 0) {
      $_form = mf_api()->get_form($form_id);
    }

    if (!$_form) {
      return null;
    }

    return $_form;
  }

endif;

if (!function_exists('mfget_form_fields')) :

  function mfget_form_fields($form)
  {
    // Return an ordered list of form field ids
    $ids = array();
    if (!empty($form->containers) && !empty($form->containers['data'])) {
      foreach ($form->containers['data'] as $container) {
        // if the container is not a row, or if it has no columns, bail out
        if ($container['type'] !== "row" || empty($container['columns'])) {
          continue;
        }

        foreach ($container['columns'] as $col) {
          // if the column has no fields, bail out
          if (empty($col['fields'])) {
            continue;
          }
          // Add column field ids to the `$ids` array
          foreach ($col['fields'] as $id) {
            $ids[] = (int)$id;
          }
        }
      }
    }

    // Use the generated ordered list of ids to sort the form fields object
    uksort($form->fields, function ($key1, $key2) use ($ids) {
      return (int)(array_search($key1, $ids) > array_search($key2, $ids));
    });

    // Return the ordered form fields list
    return $form->fields;
  }

endif;

if (!function_exists('mfget_cleaned_url')) :

  function mfget_cleaned_url($url)
  {
    $url_parts = explode('?', $url);
    return $url_parts[0];
  }

endif;

if (!function_exists('mfget_template_filename')) :

  function mfget_template_filename($parts, $template_name)
  {

    // Make sure the template directry is set correctly
    if (is_array($parts)) {
      $file_name_parts = $parts;
    } else {
      $file_name_parts = array($parts);
    }
    // Add filename to the array and split everything to return correct path
    $file_name_parts[] = $template_name . '.php';
    return implode('/', $file_name_parts);
  }

endif;
if (!function_exists('mflocate_template')) :

  function mflocate_template($template_name, $args, $protected = false)
  {

    # Tempates folder name to check for in the theme
    $template_path = 'mega-forms';
    # Default templates path
    $default_path  = MEGAFORMS_COMMON_PATH . 'partials/templates/';

    # Extract array element into variables to be used inside the template
    if (!empty($args) && is_array($args)) {
      extract($args);
    }

    # Priority: Look for the passed template name within the theme. [only if the called template is not protected]
    $template = false;
    if (!$protected) {

      $template = locate_template(
        array(
          trailingslashit($template_path) . $template_name,
          $template_name,
        )
      );
    }

    # Get default template.
    if (!$template) {
      if (file_exists(trailingslashit($default_path) . $template_name)) {
        $template = trailingslashit($default_path) . $template_name;
      }
    }

    $located = !$protected ? apply_filters('mf_locate_template', $template, $template_name, $template_path) : $template;
    // Display the template if it exists
    if (file_exists($located)) {
      if (!$protected) {
        do_action('mf_before_template', $template_name, $template_path, $located, $args);
      }

      include($located);

      if (!$protected) {
        do_action('mf_after_template', $template_name, $template_path, $located, $args);
      }
    }
  }

endif;
if (!function_exists('mflocate_template_html')) :

  function mflocate_template_html($template_name, $args = array(), $protected = false)
  {
    // Like mflocate_template, but returns the HTML instead of including the file directly.
    ob_start();
    mflocate_template($template_name, $args, $protected);
    return ob_get_clean();
  }

endif;

if (!function_exists('mfget_browser')) :

  function mfget_browser()
  {

    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
      $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
      $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
      $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
      $bname = 'Internet Explorer';
      $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
      $bname = 'Mozilla Firefox';
      $ub = "Firefox";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
      $bname = 'Google Chrome';
      $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
      $bname = 'Apple Safari';
      $ub = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
      $bname = 'Opera';
      $ub = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
      $bname = 'Netscape';
      $ub = "Netscape";
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
      ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
      // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
      //we will have two since we are not using 'other' argument yet
      //see if version is before or after the name
      if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
        $version = $matches['version'][0];
      } else {
        $version = $matches['version'][1];
      }
    } else {
      $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == "") {
      $version = "?";
    }

    return array(
      'userAgent' => $u_agent,
      'name'      => $bname,
      'version'   => $version,
      'platform'  => $platform,
      'pattern'    => $pattern
    );
  }

endif;

if (!function_exists('mfget_ip_address')) :

  function mfget_ip_address()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    return $ip_address;
  }

endif;

if (!function_exists('mf_sanitize')) :

  function mf_sanitize($value, $type)
  {

    switch ($type) {
      case 'boolean':
        $value = mfget_bool_value($value);
        break;
      case 'string':
        $value = sanitize_textarea_field($value);
        break;
      case 'html':
        $value = wp_kses($value, 'post');
        break;
      case 'integer':
        $value = (int) $value;
        break;
      case 'float':
        $value = (float) $value;
        break;
      case 'email':
        $value = sanitize_email($value);
        break;
      case 'url':
        $value = esc_url_raw($value);
        break;
      case 'color':
        $value = sanitize_hex_color($value);
        break;
      default:
        if (is_array($value)) {
          $return = array();
          foreach ($value as $key => $val) {
            $return[$key] = mf_sanitize($val, 'string');
          }
          $value = $return;
        } else {
          $value = sanitize_text_field($value);
        }
        break;
    }

    return $value;
  }

endif;

if (!function_exists('mfget_option')) :

  function mfget_option($key, $fallback = '')
  {
    $value = get_option('megaforms_' . $key, $fallback);

    // Return the fallback if the value is empty
    if ($value === false) {
      return $fallback;
    }
    // Return boolean value if "true", or "false" string is provided
    elseif ($value === "true" || $value === "false") {
      return mfget_bool_value($value);
    }

    return $value;
  }

endif;

if (!function_exists('mfupdate_option')) :

  function mfupdate_option($key, $value, $type = '')
  {

    $value = stripslashes_deep(mf_sanitize($value, $type));

    // Convert boolean values to strings ( This allows us to deffernetiate between `false` values and non-existant values when using `get_option`  )
    if ('boolean' == $type) {
      $value = $value ? "true" : "false";
    }

    return update_option('megaforms_' . $key, $value);
  }

endif;

if (!function_exists('mfget_bool_value')) :

  function mfget_bool_value($value)
  {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
  }

endif;

if (!function_exists('mfget')) :

  function mfget($name, $array = null, $fallback = '')
  {
    if ($array === null) {
      # For GET values, strip all HTML tags before sending it back. ( Prevent XSS )
      $val = isset($_GET[$name]) ? wp_strip_all_tags($_GET[$name]) : $fallback;
    } else {
      $val = isset($array[$name]) ? $array[$name] : $fallback;
    }

    return $val;
  }

endif;

if (!function_exists('mfpost')) :

  function mfpost($name, $array = null, $stripslashes = true)
  {

    if ($array === null && isset($_POST[$name])) {
      return $stripslashes ? stripslashes_deep($_POST[$name]) : $_POST[$name];
    } elseif (is_array($array) && isset($array[$name])) {
      return $stripslashes ? stripslashes_deep($array[$name]) : $array[$name];
    }

    return null;
  }

endif;

if (!function_exists('mfinput')) :

  function mfinput($type, $args = array(), $is_editor = false)
  {

    # Bail out if necessary arguements are not available, or if the requested type doesn't exist.
    if (empty($args['id'])  && empty($args['name'])) {
      return false;
    }

    /*
    * Field attributes
    */
    $attrs = array();
    # Name and ID attributes
    $attrs['name'] = !empty($args['name']) ? $args['name'] : $args['id'];
    if (isset($args['id'])) {
      $attrs['id'] = $args['id'];
    }
    # Value
    if (isset($args['value']) &&  $args['value'] !== '' &&  $args['value'] !== null) {
      $attrs['value'] = $args['value'];
    } elseif (!empty($args['default']) &&  $args['default'] !== '' &&  $args['default'] !== null) {
      $attrs['value'] = $args['default'];
    }
    # Placeholder
    if (isset($args['placeholder']) && $args['placeholder'] !== '') $attrs['placeholder'] = $args['placeholder'];
    # CSS Atrributes
    if (isset($args['class']) && !empty($args['class'])) $attrs['class'] = $args['class'];
    if (isset($args['style']) && !empty($args['style'])) $attrs['style'] = $args['style'];
    # Required
    if (isset($args['required']) && $args['required'] == true) $attrs['required'] = 'required';


    # Conditional logic attributes
    if (isset($args['conditional_rules'])) {
      // Add conditional rules in json_format as a data attribute ( to be handled later with via JS )

      $attrs['data-conditional-rules'] = is_array($args['conditional_rules']) ? json_encode($args['conditional_rules']) : $args['conditional_rules'];
    }
    # Attributes for JS helpers to assist with live preview and editor conditional logic ( Only for Editor Screen )
    if ($is_editor) {
      if (isset($args['onchange_edit'])) $attrs['data-edit'] = $args['onchange_edit'];
      if (isset($args['onchange_preview']))  $attrs['data-preview'] = $args['onchange_preview'];
    }

    # Additional Attributes
    if (isset($args['attributes']) && count($args['attributes']) > 0) {
      $attrs = array_merge($attrs, $args['attributes']);
    }

    /*
    * Field Parameters
    */
    $params = array();
    $params['container_tag']   = isset($args['container_tag']) ? $args['container_tag'] : 'div';
    $params['container_class'] = isset($args['container_class']) ? 'mf_field_container ' . $args['container_class'] : 'mf_field_container';
    $params['label']           = isset($args['label']) ? $args['label'] : '';
    $params['label_hidden']    = isset($args['label_hidden']) ? $args['label_hidden'] : false;
    $params['desc']            = isset($args['desc']) ? $args['desc'] : '';
    $params['desc_position']   = isset($args['desc_position']) ? $args['desc_position'] : 'bottom';
    $params['wrapper_class']   = isset($args['wrapper_class']) ? $args['wrapper_class'] : '';
    $params['before_label']    = isset($args['before_label']) ? $args['before_label'] : '';
    $params['after_label']     = isset($args['after_label']) ? $args['after_label'] : '';
    $params['before_field']    = isset($args['before_field']) ? $args['before_field'] : '';
    $params['after_field']     = isset($args['after_field']) ? $args['after_field'] : '';
    $params['before_input']    = isset($args['before_input']) ? $args['before_input'] : '';
    $params['after_input']     = isset($args['after_input']) ? $args['after_input'] : '';
    $params['required']        = isset($args['required']) && $args['required'] == true ? true : false;
    $params['attributes']      = $attrs;

    # Enable Inline Modal for editor fields only
    if ($is_editor && isset($args['inline_modal']) && count($args['inline_modal']) > 0) {
      $params['after_input'] = $params['after_input'] . '<span class="mf-inline-modal-btn mega-icons-dots-three-horizontal" data-templates="' . esc_attr(join(',', $args['inline_modal'])) . '"></span>';
      $params['wrapper_class'] = $params['wrapper_class'] . ' has_inline_modal';
    }

    # Notices
    if (isset($args['notice'])  && !empty($args['notice']) && $args['notice'] !== false) {
      if ($type == 'subcustom') {
        $params['after_input'] = $params['after_input'] . get_mf_notice_html($args['notice'], 'compound');
      } else {
        $params['after_input'] = $params['after_input'] . get_mf_notice_html($args['notice']);
      }
    }

    switch ($type) {
      case 'text':
        $params['inputType'] = isset($args['inputType']) ? $args['inputType'] : 'text';
        break;
      case 'checkbox':
      case 'radio':
      case 'select':
        $params['options'] = isset($args['options']) && is_array($args['options']) ? $args['options'] : array();
        break;
      case 'switch':
        $params['size'] = isset($args['size']) ? $args['size'] : '';
        $params['labelRight'] = isset($args['labelRight']) ? $args['labelRight'] : false;
        break;
      case 'custom':
        $params['content'] = isset($args['content']) ? $args['content'] : '{MF_CUSTOM_INPUT}';
        // Since conditional logic can't target sub inputs that are part of compound fields, 
        // we'll add a single hidden field and check for it during submission to verify if the conditional rules were met.
        // The field will be disabled by default and only posted when the rules are met ( disabling and showing fields is managed by JS )
        if (isset($params['attributes']['data-conditional-rules'])) {
          $params['before_field'] .= get_mf_input(
            'hidden',
            array(
              'name' => $params['attributes']['id'] . '[compound_cl]',
              'value' => true,
              'data-conditional-rules' => $params['attributes']['data-conditional-rules']
            )
          );
          unset($params['attributes']['data-conditional-rules']);
        }
        break;
      case 'subcustom':
        $params['content'] = isset($args['content']) ? $args['content'] : '{MF_CUSTOM_INPUT}';
        $params['wrapper_tag'] = isset($args['wrapper_tag']) ? $args['wrapper_tag'] : 'span';
        if (isset($args['notice_css_class']) && $args['notice_css_class']) {
          $params['wrapper_class'] = $params['wrapper_class'] . ' mf_sub_input_error';
        }
        unset($params['container_tag']);
        unset($params['container_class']);
        break;
    }

    $template_name = mfget_template_filename('inputs', $type);

    $field  = mflocate_template_html($template_name, apply_filters('mf_input_args', $params), $is_editor);

    return $field;
  }
endif;

if (!function_exists('mfsettings')) :

  function mfsettings($settings, $id, $class = '', $mf_inputs = false)
  {
    ob_start();
?>
    <ul id="<?php echo $id; ?>">
      <?php
      $tabs = $settings;
      $i = 1;
      foreach ($tabs as $tab => $options) {
        $classes = $i === 1 ? $class . ' active' : $class;
      ?>
        <li id="<?php echo $tab; ?>_settings" class="mfsetting_container <?php echo $classes; ?>">
          <?php
          foreach ($options as $name => $args) {

            if (!isset($args['type'])) {
              continue;
            }

            if ($mf_inputs) {

              $args = wp_parse_args($args, array(
                'id' => $name,
                'wrapper_class' => 'mf_input_container',
              ));

              if (isset($args['desc'])) {
                $args['after_label'] = get_mfsettings_tooltip($args['label'], $args['desc']);
                unset($args['desc']);
              }

              if (isset($args['parent'])) {

                $args['conditional_rules'] = array(
                  'container' => '.sub-setting',
                  'rules' => array(
                    'name' => $args['parent'],
                    'operator' => $args['parent_value_operator'] ?? 'is',
                    'value' => $args['parent_value'],
                  ),
                );

                unset($args['parent']);
                unset($args['parent_value']);

                echo '<div class="mf-settings-field mgform_' . $tab . '_settings sub-setting">';
              } else {
                echo '<div class="mf-settings-field mgform_' . $tab . '_settings">';
              }

              echo '<div class="mf-inner-field">';
              echo mfinput($args['type'], $args);
              echo '</div>';
              echo '</div>';
            } else {
              $attrs = array();
              $attrs['name'] = $name;
              $attrs['id'] = $name;
              if (isset($args['class'])) $attrs['class'] = $args['class'];
              if (isset($args['placeholder'])) $attrs['placeholder'] = $args['placeholder'];
              if (isset($args['value']) && ($args['type'] !== 'radio' && $args['type'] !== 'checkbox')) $attrs['value'] = esc_attr($args['value']);

              if ($args['type'] == 'hidden') {
                echo get_mf_input($args['type'], $attrs);
                continue;
              }

              if (isset($args['parent'])) {

                $attrs['data-conditional-rules'] = json_encode(array(
                  'container' => '.sub-setting',
                  'rules' => array(
                    'name' => $args['parent'],
                    'operator' => 'is',
                    'value' => $args['parent_value'],
                  ),
                ));
          ?>
                <table class="mf-settings-field mgform_<?php echo $tab; ?>_settings sub-setting" cellspacing="0" cellpadding="0">
                  <tbody>
                  <?php
                } else {
                  ?>
                    <table class="mf-settings-field mgform_<?php echo $tab; ?>_settings" cellspacing="0" cellpadding="0">
                      <tbody>
                      <?php
                    }
                      ?>
                      <tr>
                        <th>
                          <label for='<?php echo $name ?>'></label><?php echo $args['label'] ?? '' ?>
                          <?php
                          if (isset($args['desc'])) {
                            echo get_mfsettings_tooltip($args['label'], $args['desc']);
                          }
                          ?>
                        </th>
                        <td class='mf-inner-field'>
                          <?php
                          if (isset($args['before_field'])) {
                            echo $args['before_field'];
                          }
                          ?>
                          <?php switch ($args['type']) {
                            case 'switch':
                              if (mfget_bool_value($args['value'])) $attrs['checked'] = 'checked';
                              echo get_mf_switch($attrs);
                              break;
                            case 'textarea':
                              echo get_mf_textarea($attrs);
                              break;
                            case 'radio':
                            case 'checkbox':
                              if (isset($args['options'])) {
                                foreach ($args['options'] as $key => $title) {
                                  $attrs['id'] = $attrs['id'] . '_' . $key;
                                  $attrs['value'] = $key;
                                  if ($args['value'] == $key) $attrs['checked'] = 'checked';
                                  echo get_mf_input($args['type'], $attrs);
                                  if (isset($attrs['checked']) == $key) unset($attrs['checked']);
                          ?>
                                  <label for="<?php echo $attrs['id']; ?>"><?php echo $title; ?></label>
                          <?php
                                }
                              }
                              unset($attrs['value']);
                              break;
                            case 'select':
                              if (isset($args['options'])) {
                                echo get_mf_select($attrs, $args['options']);
                              }
                              break;
                            case 'custom':
                              if (isset($args['content'])) {
                                echo $args['content'];
                              }
                              break;
                            default:
                              echo get_mf_input($args['type'], $attrs);
                              break;
                          } ?>
                          <?php
                          if (isset($args['after_field'])) {
                            echo $args['after_field'];
                          }
                          ?>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                <?php
              }
            }
                ?>
        </li>
      <?php
        $i++;
      }
      ?>
    </ul>
<?php
    return ob_get_clean();
  }

endif;

if (!function_exists('mf_esc_attr')) :

  function mf_esc_attr($attr_key, $attr_val)
  {

    // Don't trim the `value` attribute
    if (is_string($attr_val) && ($attr_key !== 'value')) {
      $attr_val = trim($attr_val);
    } elseif (is_bool($attr_val)) {
      $attr_val = $attr_val ? 1 : 0;
    } elseif (is_array($attr_val) || is_object($attr_val)) {
      $attr_val = json_encode($attr_val);
    }

    // return the markup.
    return sprintf(' %s="%s"', esc_attr($attr_key), esc_attr($attr_val));
  }

endif;
if (!function_exists('mf_esc_attrs')) :

  function mf_esc_attrs($attrs)
  {

    $output = '';
    foreach ($attrs as $key => $val) {
      // get the markup.
      $output .= mf_esc_attr($key, $val);
    }
    // return the markup
    return trim($output);
  }

endif;
if (!function_exists('get_mf_input')) :

  function get_mf_input($type, $attrs)
  {

    return sprintf('<input type="%s" %s/>', $type, mf_esc_attrs($attrs));
  }

endif;
if (!function_exists('get_mf_textarea')) :

  function get_mf_textarea($attrs)
  {

    $value = '';
    if (isset($attrs['value'])) {
      $value = $attrs['value'];
      unset($attrs['value']);
    }
    return sprintf('<textarea %s>%s</textarea>', mf_esc_attrs($attrs), esc_textarea($value));
  }

endif;
if (!function_exists('get_mf_checkbox')) :

  function get_mf_checkbox($attrs, $multi = false)
  {

    if ($multi) {
      $attrs['name'] = $attrs['name'] . '[]';
    }

    return get_mf_input('checkbox', $attrs);
  }

endif;
if (!function_exists('get_mf_radio')) :

  function get_mf_radio($attrs)
  {

    return get_mf_input('radio', $attrs);
  }

endif;
if (!function_exists('get_mf_switch')) :

  function get_mf_switch($attrs)
  {

    $attrs['value'] = "1";
    return '<label class="mfswitch">' . get_mf_checkbox($attrs) . '<span class="mfswitch-slider round"></span></label>';
  }

endif;
if (!function_exists('get_mf_select')) :

  function get_mf_select($attrs, $options)
  {

    $value = '';
    if (isset($attrs['value'])) {
      $value = mfget('value', $attrs);
      unset($attrs['value']);
    }

    if (isset($attrs['placeholder'])) {
      $placeholderAttrs = array(
        'value' => '',
        'class' => 'mf_select_placeholder',
        'disabled' => 'disabled',
      );
      if ('' == $value) {
        $placeholderAttrs['selected'] = 'selected';
      }
      $placeholder = sprintf('<option %s>%s</option>', mf_esc_attrs($placeholderAttrs), esc_attr($attrs['placeholder']));
      unset($attrs['placeholder']);
    } else {
      $placeholder = '';
    }

    return sprintf('<select %s>%s%s</select>', mf_esc_attrs($attrs), $placeholder, mf_select_walker($options, $value));
  }

endif;
if (!function_exists('mf_select_walker')) :

  function mf_select_walker($options, $value)
  {

    $output = '';
    // Loop over the available options and add them to the output variable.
    foreach ($options as $key => $label) {
      if (is_array($label)) {

        $output .= sprintf('<optgroup label="%s">%s</optgroup>', esc_attr($key), mf_select_walker($label, $value));
      } else {

        $attrs = array();
        $attrs['value'] = $key;
        if ((string) $key === (string) $value) {
          $attrs['selected'] = 'selected';
        }
        $output .= sprintf('<option %s>%s</option>', mf_esc_attrs($attrs), esc_html($label));
      }
    }
    return $output;
  }

endif;


if (!function_exists('get_mf_submission_msg_html')) :

  function get_mf_submission_msg_html($type, $message)
  {

    $class = 'mform_' . $type . '_msg';
    $output = '<span class="' . $class . '">' . $message . '</span>';

    return $output;
  }

endif;
if (!function_exists('get_mf_notice_html')) :

  function get_mf_notice_html($notice, $type = 'default')
  {

    if ('compound' == $type) {
      $class = 'mf_compound_notice';
    } else {
      $class = 'mf_notice';
    }

    $output = '<span class="mf-notice-holder ' . $class . '">' . $notice . '</span>';

    return $output;
  }

endif;

if (!function_exists('get_mf_formatted_number')) :

  function get_mf_formatted_number(float $num, int $precision = 2)
  {
    $absNum = abs($num);

    if ($absNum < 1000) {
      return (string)round($num, $precision);
    }

    $groups = ['k', 'M', 'B', 'T', 'Q'];

    foreach ($groups as $i => $group) {
      $div = 1000 ** ($i + 1);

      if ($absNum < $div * 1000) {
        return round($num / $div, $precision) . $group;
      }
    }

    return '999Q+';
  }

endif;

if (!function_exists('get_mfsettings_tooltip')) :

  function get_mfsettings_tooltip($label, $content)
  {
    $html = "";
    $html .= "<span class='mf-tooltip settings-tooltip mega-icons-question-circle'>";
    $html .= "<span style='display:none;' id='tooltip-content'><h4>" . $label . "</h4>" . $content . "</span>";
    $html .= "</span>";

    return $html;
  }

endif;

if (!function_exists('get_mf_button')) :

  function get_mf_button($type, $text, $attributes = array())
  {
    return sprintf('<button type="%1$s"%2$s><span class="mf-btn-txt">%3$s</span></button>', $type, mf_esc_attrs($attributes), $text);
  }

endif;

if (!function_exists('get_mf_session_token_id')) :

  function get_mf_session_token_id($form_id, $referrer)
  {
    return 'form_' . absint($form_id) . '_token_' . wp_hash(mfget_cleaned_url($referrer));
  }

endif;

if (!function_exists('get_mf_session_referrer_id')) :

  function get_mf_session_referrer_id($form_id, $referrer)
  {
    return 'form_' . absint($form_id) . '_referrer_' . wp_hash(mfget_cleaned_url($referrer));
  }

endif;
