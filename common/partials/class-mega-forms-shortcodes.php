<?php

/**
 * Mega Forms Shortcodes Class
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

class MF_Shortcodes
{
  /**
   * Initialize shortcodes.
   *
   * @since 1.0.0
   *
   */
  public static function init()
  {

    $shortcodes = array(
      'megaforms' => 'the_form',
    );

    foreach ($shortcodes as $tag => $method) {
      add_shortcode(apply_filters("{$tag}_shortcode_tag", $tag), array(__CLASS__, $method));
    }
  }

  /**
   * Process the form display Shortcode.
   *
   * @since 1.0.8
   *
   * @param array|string $atts User defined attributes for this shortcode instance
   * @param string|null $content Content between the opening and closing shortcode elements
   * @param string $name Name of the shortcode
   *
   * @return string
   */

  public static function the_form($atts, $content = null)
  {

    # Define and prepare necessary variables.
    $shortcode_output = '';
    extract(
      shortcode_atts(
        array(
          'title'        => false,
          'description'  => false,
          'id'           => 0,
          'action'       => 'form',
        ),
        $atts,
        'megaforms'
      )
    );

    # Make sure the title and desc values are boolean values
    $form        = mfget_form($id);
    $title       = filter_var($title, FILTER_VALIDATE_BOOLEAN);
    $description = filter_var($description, FILTER_VALIDATE_BOOLEAN);

    # Enqueue styles and scripts ( in footer )
    if (!empty($form->field_types)) {

      $enqueuedJSDeps = array();
      $enqueuedCSSDeps = array();

      foreach ($form->field_types as $type) {
        # JS

        $field_js_dependencies = MF_Fields::get_field_dependencies('js', $type);
        if (!empty($field_js_dependencies)) {
          foreach ($field_js_dependencies as $jsDependencyKey => $jsDependencyVal) {
            if (!in_array($jsDependencyKey, $enqueuedJSDeps)) {
              wp_enqueue_script($jsDependencyKey, $jsDependencyVal['src'], $jsDependencyVal['deps'], $jsDependencyVal['ver'], true);
              // Localize field vars if available
              if (isset($jsDependencyVal['vars'])) {
                wp_localize_script($jsDependencyKey, 'mfield_' . $type . '_vars', $jsDependencyVal['vars']);
              }
              $enqueuedJSDeps[] = $jsDependencyKey;
            }
          }
        }
        # CSS
        $field_css_dependencies = MF_Fields::get_field_dependencies('css', $type);
        if (!empty($field_css_dependencies)) {
          foreach ($field_css_dependencies as $cssDependencyKey => $cssDependencyVal) {
            # If the css file is not allowed to load in the footer, skip it. 
            # @see Mega_Forms_Common::enqueue_styles()
            if (!isset($cssDependencyVal['in_footer'])) {
              continue;
            }
            if (!in_array($cssDependencyKey, $enqueuedCSSDeps)) {
              wp_enqueue_style($cssDependencyKey, $cssDependencyVal['src'], $cssDependencyVal['deps'], $cssDependencyVal['ver'], 'all');
              $enqueuedCSSDeps[] = $cssDependencyKey;
            }
          }
        }
      }
    }
    # Enqueue the plugin public scripts
    wp_enqueue_script('mf-public');
    if (class_exists('Mega_Forms_Pro')) {
      wp_enqueue_script('mf-public-pro');
    }

    # Handle action
    if ('form' == $action) {

      if (!class_exists('MF_Form_View')) {
        require_once MEGAFORMS_PUBLIC_PATH . 'partials/class-mega-forms-public-form-view.php';
      }

      $MF_Form_View = new MF_Form_View($form, $title, $description);
      $shortcode_output = $MF_Form_View->form_display();
    } else {
      $shortcode_output = apply_filters("mf_shortcode_action_{$action}", '', $atts);
    }

    // Return the shortcode output
    $output = apply_filters("mf_shortcode_output", $shortcode_output, $atts);
    return $output;
  }
}
