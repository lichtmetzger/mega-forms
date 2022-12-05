<?php

/**
 * Render Mega Forms General View
 *
 * @link       https://wpali.com
 * @since      1.0.8
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/settings-views
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
class MegaForms_Settings
{

  /**
   * Returns the default settings
   *
   */
  public static function get_option_tabs()
  {

    return apply_filters('mf_option_tabs', array(
      'general'       => __('General', 'megaforms'),
      'email'         => __('Emails', 'megaforms'),
      'validation'    => __('Validation Messages', 'megaforms'),
      'misc'          => __('Misc', 'megaforms')
    ));
  }
  /**
   * Returns the callbacks for all the options per each tab.
   * Each callback must return an array that contains the following: ( `key`, `label`, `description (optional)`, `content` )
   * Each option is assigned a value representing the type of data of that option
   *
   * @return array
   */
  public static function get_options()
  {

    $validation_messages = mf_api()->get_validation_messages();

    # Assign callback functions to each tab ( a callback for every option )
    $options = array(
      'general' => array(
        'load_form_styling' => array(
          'priority' => 10,
          'type' => 'switch',
          'label' => __('Load Form Styling', 'megaforms'),
          'desc' => __('Switching this off will stop all fields and containers related CSS from loading. Only switch off if you\'re going to load your custom CSS.', 'megaforms'),
          'value' => mfget_option('load_form_styling', true),
          'sanitization' => 'boolean',
        ),
        'storing_user_details' => array(
          'priority' => 20,
          'type' => 'switch',
          'label' => __('Store User Details', 'megaforms'),
          'desc' => __(' Switch this off to disable storing the IP address and User Agent on all forms. This helps with GDPR compliance.', 'megaforms'),
          'value' => mfget_option('storing_user_details', true),
          'sanitization' => 'boolean',
        ),
      ),
      'email' => array(
        'email_from_name' => array(
          'priority' => 10,
          'type' => 'text',
          'label' => __('From Name', 'megaforms'),
          'desc' => __('Type the default sender name for all of your outgoing Mega Forms emails.', 'megaforms'),
          'value' => mfget_option('email_from_name', get_bloginfo('name')),
          'sanitization' => 'string',
        ),
        'email_from_address' => array(
          'priority' => 20,
          'type' => 'email',
          'label' => __('From Address', 'megaforms'),
          'desc' => __('Type the default sender email address for all of your outgoing Mega Forms emails.', 'megaforms'),
          'value' => mfget_option('email_from_address', get_bloginfo('admin_email')),
          'sanitization' => 'email',
        ),
        'email_template' => array(
          'priority' => 30,
          'type' => 'custom',
          'label' => __('Template', 'megaforms'),
          'desc' => __('Select which format you want for all of your outgoing Mega Forms emails.', 'megaforms'),
          'value' => mfget_option('email_template', 'html'),
          'content' => '<label for="email_template[1]" class="option-html"><span class="mega-icons-document-list"></span><input type="radio" id="email_template[1]" name="email_template" value="html" ' . checked(mfget_option('email_template', 'html'), 'html', false) . '>' . __('HTML Template', 'megaforms') . '</label><label for="email_template[2]" class="option-none"><span class="mega-icons-document-text"></span><input type="radio" id="email_template[2]" name="email_template" value="none" ' . checked(mfget_option('email_template', 'html'), 'none', false) . '>' . __('Plain Template', 'megaforms') . '</label>',
          'sanitization' => 'string',
        ),
        'email_header_image' => array(
          'priority' => 30,
          'type' => 'url',
          'label' => __('Header Image', 'megaforms'),
          'desc' => __('URL to the logo you want to be displayed at the top of all your outgoing Mega Forms emails.', 'megaforms'),
          'value' => mfget_option('email_header_image'),
          'sanitization' => 'url',
        ),
        'email_footer_text' => array(
          'priority' => 40,
          'type' => 'textarea',
          'label' => __('Footer Text', 'megaforms'),
          'desc' => __('Type the text to appear in the footer of all your outgoing Mega Forms emails.', 'megaforms'),
          'value' => mfget_option('email_footer_text', '{mf:wp site_title}'),
          'sanitization' => 'html',
        ),
        'email_primary_text_color' => array(
          'priority' => 50,
          'type' => 'text',
          'label' => __('Primary Color', 'megaforms'),
          'desc' => __('The primary text color for Mega Forms email templates.', 'megaforms'),
          'value' => mfget_option('email_primary_text_color', '#000000'),
          'class' => 'mf-color-field',
          'sanitization' => 'color',
        ),
        'email_secondary_text_color' => array(
          'priority' => 60,
          'type' => 'text',
          'label' => __('Secondary Color', 'megaforms'),
          'desc' => __('The secondary text color for Mega Forms email templates.', 'megaforms'),
          'value' => mfget_option('email_secondary_text_color', '#cccccc'),
          'class' => 'mf-color-field',
          'sanitization' => 'color',
        ),
        'email_primary_bg_color' => array(
          'priority' => 70,
          'type' => 'text',
          'label' => __('Primary Bg Color', 'megaforms'),
          'desc' => __('The primary background color for Mega Forms email templates.', 'megaforms'),
          'value' => mfget_option('email_primary_bg_color', '#f3f3f5'),
          'class' => 'mf-color-field',
          'sanitization' => 'color',
        ),
        'email_secondary_bg_color' => array(
          'priority' => 80,
          'type' => 'text',
          'label' => __('Secondary Bg Color', 'megaforms'),
          'desc' => __('The secondary background color for Mega Forms email templates.', 'megaforms'),
          'value' => mfget_option('email_secondary_bg_color', '#ffffff'),
          'class' => 'mf-color-field',
          'sanitization' => 'color',
        ),
      ),
      'validation' => array(
        'timetrap_error' => array(
          'priority' => 10,
          'type' => 'text',
          'label' => __('Timetrap Error', 'megaforms'),
          'desc' => __('Type the error message for spam entries detected by Timetrap.', 'megaforms'),
          'placeholder' => mfget('form_validation_timetrap_error', $validation_messages),
          'value' => mfget_option('timetrap_error'),
          'sanitization' => 'string',
        ),
        'honeypot_error' => array(
          'priority' => 20,
          'type' => 'text',
          'label' => __('Honeypot Error', 'megaforms'),
          'desc' => __('Type the error message for spam entries detected by Honeypot trap.', 'megaforms'),
          'placeholder' => mfget('form_validation_honeypot_error', $validation_messages),
          'value' => mfget_option('honeypot_error'),
          'sanitization' => 'string',
        ),
        'required_notice' => array(
          'priority' => 30,
          'type' => 'text',
          'label' => __('Required Field', 'megaforms'),
          'desc' => __('Type the error message for required single entry fields.', 'megaforms'),
          'placeholder' => mf_api()->get_validation_required_notice(),
          'value' => mfget_option('required_notice'),
          'sanitization' => 'string',
        ),
        'compound_required_notice' => array(
          'priority' => 40,
          'type' => 'text',
          'label' => __('Required Fields', 'megaforms'),
          'desc' => __('Type the error message for required multiple entry fields (e.g. name, email, password ...etc).', 'megaforms'),
          'placeholder' => mf_api()->get_validation_compound_required_notice(),
          'value' => mfget_option('compound_required_notice'),
          'sanitization' => 'string',
        ),
        'options_required_notice' => array(
          'priority' => 50,
          'type' => 'text',
          'label' => __('Required Option', 'megaforms'),
          'desc' => __('Type the error message for required option fields (e.g. radios, checkboxes, selectboxes).', 'megaforms'),
          'placeholder' => mf_api()->get_validation_required_notice(array('type' => 'choice')),
          'value' => mfget_option('options_required_notice'),
          'sanitization' => 'string',
        ),
        'address_required_notice' => array(
          'priority' => 60,
          'type' => 'text',
          'label' => __('Required Address', 'megaforms'),
          'desc' => __('Type the error message for required address field.', 'megaforms'),
          'placeholder' => mf_api()->get_validation_compound_required_notice(array('type' => 'address')),
          'value' => mfget_option('address_required_notice'),
          'sanitization' => 'string',
        ),
        'email_validation' => array(
          'priority' => 70,
          'type' => 'text',
          'label' => __('Invalid Email', 'megaforms'),
          'desc' => __('Type the error message for invalid email address.', 'megaforms'),
          'placeholder' => __('Please enter a valid email.', 'megaforms'),
          'value' => mfget_option('email_validation'),
          'sanitization' => 'string',
        ),
        'website_validation' => array(
          'priority' => 80,
          'type' => 'text',
          'label' => __('Invalid Website', 'megaforms'),
          'desc' => __('Type the error message for invalid web address.', 'megaforms'),
          'placeholder' => __('Please enter a valid url.', 'megaforms'),
          'value' => mfget_option('website_validation'),
          'sanitization' => 'string',
        ),
        'date_validation' => array(
          'priority' => 90,
          'type' => 'text',
          'label' => __('Invalid Date', 'megaforms'),
          'desc' => __('Type the error message for invalid date.', 'megaforms'),
          'placeholder' => __('Please enter a valid date.', 'megaforms'),
          'value' => mfget_option('date_validation'),
          'sanitization' => 'string',
        ),
        'date_range_validation' => array(
          'priority' => 100,
          'type' => 'text',
          'label' => __('Invalid Date Range', 'megaforms'),
          'desc' => __('Type the error message for invalid date range.', 'megaforms'),
          'placeholder' => __('Please enter a valid date range.', 'megaforms'),
          'value' => mfget_option('date_range_validation'),
          'sanitization' => 'string',
        ),
      ),
      'misc' => array(
        'uninstall' => array(
          'priority' => 10,
          'type' => 'switch',
          'label' => __('Uninstall', 'megaforms'),
          'desc' => __('Permanently delete all Mega Forms data upon uninstallation.', 'megaforms'),
          'value' => mfget_option('uninstall', false),
          'sanitization' => 'boolean',
        ),
      ),
    );

    $options = apply_filters('mf_settings_options', $options);

    // Order by priority
    foreach ($options as $key => $val) {
      // Sort the links by priority
      uasort($options[$key], function ($a, $b) {
        return isset($a['priority']) && isset($b['priority']) ? $a['priority'] <=> $b['priority'] : false;
      });
    }

    return $options;
  }
  /**
   * Update options into the database
   *
   * @return bool
   */
  public static function update_options($settings)
  {

    $available_options = self::get_options();

    $options = is_array($available_options) && !empty($available_options) ? call_user_func_array('array_merge', array_values($available_options)) : array();

    foreach ($options as $option_key => $option_args) {
      // Make sure switch/checkbox field values are also included
      if ($option_args['sanitization'] == 'boolean') {
        $value = mfget($option_key, $settings, false);
      } else {
        $value = mfget($option_key, $settings);
      }

      mfupdate_option($option_key, $value, $option_args['sanitization']);
    }

    return true;
  }
}
