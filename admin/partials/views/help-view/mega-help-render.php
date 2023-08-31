<?php

/**
 * Render Mega Forms General View
 *
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/general-views
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
class MegaForms_Entries_Render
{

  public function __construct($page)
  {
    $this->display();
  }
  /**
   * Display entries.
   *
   * @since    1.0.0
   */
  public function display()
  {
?>
    <div class="megacontainer_row">
      <h2 class="mg_forms_label">
        <?php esc_html_e('Support', 'megaforms'); ?>
      </h2>
      <div class="mf_clearfix"></div>
    </div>
    <div id="mega_entries_list" class="mf_entries_select_form">
      <div class="mf-admin-message">
        <img class="mf_entries_logo" src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v1.svg' ?>" />
        <h2><?php _e("Need help?", 'megaforms'); ?></h2>
        <p><?php _e("We offer free support and also customization service to modify Mega Forms to do something special, <br>whether it is an API integration, custom feature, or anything else, we are here to help!", 'megaforms'); ?></p>
        <a href="https://wordpress.org/support/plugin/mega-forms/" target="_blank" class="button button-primary"><?php _e("Open a Ticket", 'megaforms'); ?></a>
        <a href="https://wpmegaforms.com/contact/" target="_blank" class="button button-primary"><?php _e("Send us an Email", 'megaforms'); ?></a>

      </div>
    </div>
<?php
  }
}
