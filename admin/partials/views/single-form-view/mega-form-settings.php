<?php

/**
 * Mega Forms Form Settings
 *
 * @link       https://wpali.com
 * @since      1.0.8
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/form-views
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Form_Settings
{

  /**
   * Holds the form object ( contains form id and settings as well )
   *
   * @var object
   */
  private $form;

  /**
   * Hold the form ID
   *
   * @var int
   */
  private $form_id;
  /**
   * Holds the settings values
   *
   * @var array
   */
  private $settings;

  public function __construct($form = '')
  {

    if (!empty($form) && is_object($form)) {
      // Set form object
      $this->form = $form;
      // Set form id
      $this->form_id = $form->ID;
      // Set form settings
      $this->settings = $form->settings;
    }
  }
  /**
   * Returns the default settings tabs
   *
   */
  public function get_option_tabs()
  {

    return apply_filters('mf_form_settings_tabs', array(
      'general' => __('General', 'megaforms'),
      'restrictions' => __('Restrictions', 'megaforms'),
      'confirmation' => __('Confirmation', 'megaforms'),
    ));
  }
  /**
   * Returns list of options for each tab with their related arguements
   *
   * @return array
   */
  public function get_options()
  {

    $pages = get_pages(array('post_type' => 'page'));
    $pages_array = array();
    foreach ($pages as $page) {
      $pages_array[$page->ID] = $page->post_title;
    }
    # Assign callback functions to each tab ( a callback for every option )
    $options = array(
      'general' => array(
        'form_description' => array(
          'priority' => 10,
          'type' => 'textarea',
          'label' => __('Form Description', 'megaforms'),
          'value' => mfget('form_description', $this->settings),
          'sanitization' => 'string',
        ),
        'submit_button' => array(
          'priority' => 20,
          'type' => 'text',
          'label' => __('Submit Text', 'megaforms'),
          'value' => mfget('submit_button', $this->settings, __('Submit', 'megaforms')),
          'sanitization' => 'string',
        ),
        'form_css_class' => array(
          'priority' => 30,
          'type' => 'text',
          'label' => __('CSS Class', 'megaforms'),
          'desc' => __('Add custom CSS class names here to use on your form wrappers. Multiple classes should be separated by space.', 'megaforms'),
          'value' => mfget('form_css_class', $this->settings),
          'sanitization' => 'string',
        ),
        'disable_storing_entries' => array(
          'priority' => 40,
          'type' => 'switch',
          'label' => __('Stop Entries', 'megaforms'),
          'value' => mfget('disable_storing_entries', $this->settings),
          'desc' => __('Enable this option to prevent entries from being stored in the database.', 'megaforms'),
          'sanitization' => 'boolean',
        ),
      ),
      'restrictions' => array(
        'time_trap' => array(
          'priority' => 10,
          'type' => 'switch',
          'label' => __('Timetrap ( Anti-Spam )', 'megaforms'),
          'value' => mfget('time_trap', $this->settings, true),
          'desc' => __('Enable time trap technique to help with spam prevention. Spam bots usually fills out forms very fast. The timetrap prevents form submission until a number of seconds has passed.', 'megaforms'),
          'sanitization' => 'boolean',
        ),
        'time_trap_duration' => array(
          'priority' => 10,
          'type' => 'number',
          'label' => __('Timetrap Seconds', 'megaforms'),
          'desc' => __('The minimum time after which a normal user can submit the form (in seconds).', 'megaforms'),
          'value' => mfget('time_trap_duration', $this->settings, 5),
          'placeholder' => 5,
          'parent' => 'time_trap',
          'parent_value' => '1',
          'sanitization' => 'integer',
        ),
        'limited_entries' => array(
          'priority' => 20,
          'type' => 'switch',
          'label' => __('Limited Entries', 'megaforms'),
          'value' => mfget('limited_entries', $this->settings),
          'desc' => __('Enable this option to allow a specific number of entries for this form that can not be exceeded.', 'megaforms'),
          'sanitization' => 'boolean',
        ),
        'form_submission_limit' => array(
          'priority' => 20,
          'type' => 'number',
          'label' => __('Submissions Limit', 'megaforms'),
          'value' => mfget('form_submission_limit', $this->settings),
          'parent' => 'limited_entries',
          'parent_value' => '1',
          'sanitization' => 'integer',
        ),
        'limit_reached_msg' => array(
          'priority' => 20,
          'type' => 'textarea',
          'label' => __('Submissions Limit', 'megaforms'),
          'value' => mfget('limit_reached_msg', $this->settings),
          'parent' => 'limited_entries',
          'parent_value' => '1',
          'sanitization' => 'html',
        ),
        'login_restricted' => array(
          'priority' => 30,
          'type' => 'switch',
          'label' => __('Login Restricted', 'megaforms'),
          'value' => mfget('login_restricted', $this->settings),
          'desc' => __('Enable this option to require login to view the form.', 'megaforms'),
          'sanitization' => 'boolean',
        ),
        'login_restricted_msg' => array(
          'priority' => 30,
          'type' => 'textarea',
          'label' => __('Require Login Message', 'megaforms'),
          'value' => mfget('login_restricted_msg', $this->settings),
          'parent' => 'login_restricted',
          'parent_value' => '1',
          'sanitization' => 'html',
        ),
      ),
      'confirmation' => array(
        'confirmation_type' => array(
          'priority' => 10,
          'type' => 'radio',
          'label' => __('Confirmation Type', 'megaforms'),
          'value' => mfget('confirmation_type', $this->settings, 'message'),
          'desc' => sprintf(
            '%s<br></br><b>%s</b>%s<br></br><b>%s</b>%s<br></br><b>%s</b>%s',
            __('Select confirmation type for this form. This will be the result after the user submits the form.', 'megaforms'),
            __('Message: ', 'megaforms'),
            __('The text users will see after submitting the form.', 'megaforms'),
            __('Page: ', 'megaforms'),
            __('The page users will be redirected to after submitting the form.', 'megaforms'),
            __('Redirection: ', 'megaforms'),
            __('The URL of webpage users will be redirected to after submitting the form.', 'megaforms')
          ),
          'options' => array(
            'message' => __('Message', 'megaforms'),
            'page' => __('Page', 'megaforms'),
            'redirect' => __('Redirect', 'megaforms'),
          ),
          'sanitization' => 'string',
        ),
        'confirmation_message' => array(
          'priority' => 10,
          'type' => 'textarea',
          'label' => __('Confirmation Message', 'megaforms'),
          'value' => mfget('confirmation_message', $this->settings, __('Form has been successfully submitted. Thank you.', 'megaforms')),
          'parent' => 'confirmation_type',
          'parent_value' => 'message',
          'sanitization' => 'html',
        ),
        'keep_form' => array(
          'priority' => 10,
          'type' => 'switch',
          'label' => __('Keep Form', 'megaforms'),
          'value' => mfget('keep_form', $this->settings, false),
          'desc' => __('This option allows you to keep or hide the form fields after successful submission.', 'megaforms'),
          'parent' => 'confirmation_type',
          'parent_value' => 'message',
          'sanitization' => 'boolean',
        ),
        'confirmation_page' => array(
          'priority' => 10,
          'type' => 'select',
          'label' => __('Confirmation Page', 'megaforms'),
          'value' => mfget('confirmation_page', $this->settings, 0),
          'options' => $pages_array,
          'parent' => 'confirmation_type',
          'parent_value' => 'page',
          'sanitization' => 'string',
        ),
        'confirmation_redirect' => array(
          'priority' => 10,
          'type' => 'url',
          'label' => __('Confirmation Redirect URL', 'megaforms'),
          'value' => mfget('confirmation_redirect', $this->settings),
          'parent' => 'confirmation_type',
          'parent_value' => 'redirect',
          'sanitization' => 'url',
        ),
      ),
    );

    $options = apply_filters('mf_form_settings_options', $options, $this->settings);

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
   * Render form settings on the backend.
   *
   * @since    1.0.0
   */
  public function render()
  {
?>
    <div id="mega-body" class="metabox-holder columns-2">

      <div id="mega-settings-container" class="mega-container">
        <div id="megaforms_settings">
          <div class="mf_clearfix"></div>
          <?php
          $tabs = $this->get_option_tabs();
          $i = 0;
          foreach ($tabs as $tabKey => $tabName) {
            $active = $i === 0 ? ' active' : '';
          ?>
            <a href="#" title="<?php echo $tabName; ?>" data-panel="<?php echo $tabKey; ?>_settings" data-disable="mgform_settings" class="mf-panel-toggler<?php echo $active ?>" target="_self"><span class="megaforms-menu-text"><?php echo $tabName; ?></span></a>
          <?php
            $i++;
          }
          ?>
          <div class="mf_clearfix"></div>
        </div>
      </div>

      <div id="mega-body-settings" class="mega-body-content">
        <form data-id="<?php echo $this->form_id; ?>" method="post" id="mform_settings" class="megaforms-settings">
          <?php echo mfsettings($this->get_options(), 'mgsettingssholder', 'mgform_settings'); ?>
        </form>
      </div>
    </div>
<?php
  }
}
