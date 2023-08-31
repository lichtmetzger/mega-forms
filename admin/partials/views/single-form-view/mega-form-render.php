<?php

/**
 * Render Mega Forms Form View
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/form-views
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Form_Render
{

  function __construct()
  {

    $id = mfget('id');
    // If the id is not defined and is not an integrer, bail out.
    if (empty($id) && !is_int($id)) {
      return;
    }

    // Assign form id and action
    $this->form = mfget_form($id);

    // Load dependencies
    require_once MEGAFORMS_ADMIN_PATH . 'partials/views/single-form-view/mega-form-settings.php';
    require_once MEGAFORMS_ADMIN_PATH . 'partials/views/single-form-view/mega-form-fields.php';
    require_once MEGAFORMS_ADMIN_PATH . 'partials/views/single-form-view/mega-form-actions.php';
    // Render
    $this->render();
  }

  /**
   * Form editor display.
   *
   * @since    1.0.0
   */
  private function render()
  {
?>
    <div class="mega-forms-mobile-notice">
      <div class="mf-mobile-notice-inner">
        <img class="mf_mobile_notice_logo" src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v1.svg' ?>" />
        <h2>Hey there!</h2>
        <p><?php _e('Mega Forms is optimized for desktop. Please use a different device to manage your forms.', 'megaforms'); ?></p>
      </div>
    </div>
    <?php
    if ($this->form == false) {
    ?>
      <p style="text-align: center; padding: 50px 30px;"><?php _e('The form you\'re trying to view doesn\'t exit.', 'megaforms'); ?></p>
    <?php
    } else {
    ?>
      <div class="megacontainer_row">
        <div id="mgform_pcontols">
          <span class="spinner"></span>
          <?php
          $Fields = esc_html__('Fields', 'megaforms');
          $Settings = esc_html__('Settings', 'megaforms');
          $Actions = esc_html__('Actions', 'megaforms');
          ?>

          <a href="#" title="<?php echo $Fields; ?>" class="mf-panel-toggler active" data-panel="form_field_panel" data-disable="mgform_panel" target="_self"><span class="app-menu-text"><?php echo $Fields; ?></span></a>
          <a href="#" title="<?php echo $Actions; ?>" class="mf-panel-toggler" data-panel="emails_actions_panel" data-disable="mgform_panel" target="_self"><span class="megaforms-menu-text"><?php echo $Actions; ?></span></a>
          <a href="#" title="<?php echo $Settings; ?>" class="mf-panel-toggler" data-panel="form_settings_panel" data-disable="mgform_panel" target="_self"><span class="app-menu-text"><?php echo $Settings; ?></span></a>
        </div>

        <a id="mform_save" class="megaforms-top-button" href="#"><span class="save-text"><?php esc_html_e('Save Form', 'megaforms') ?></span></a>
        <a id="fullscreenbtn" href="#" title="<?php esc_html_e('Full Screen', 'megaforms') ?>"><span class="mega-icons-fullscreen"></span></a>
        <a id="mform_embed" href="#" title="<?php esc_html_e('Embed', 'megaforms') ?>"><span class="mega-icons-share"></span></a>

        <div class="mf_clearfix"></div>
      </div>
      <ul id="poststuff" style="padding:0px;margin:0px;">
        <li class="mf_clearfix mf-no-margin"></li>
        <li id="form_field_panel" class="mgform_panel active">
          <?php
          $form_fields = new MegaForms_Form_Fields($this->form);
          $form_fields->render();
          ?>
        </li>
        <li id="emails_actions_panel" class="mgform_panel" style="display:none;">
          <?php
          $form_actions = new MegaForms_Form_Actions($this->form);
          $form_actions->render();
          ?>
        </li>
        <li id="form_settings_panel" class="mgform_panel" style="display:none;">
          <?php
          $form_settings = new MegaForms_Form_Settings($this->form);
          $form_settings->render();
          ?>
        </li>
        <div id="saving"></div>
        <li class="mf_clearfix mf-no-margin"></li>
      </ul>

<?php
    }
  }
}
