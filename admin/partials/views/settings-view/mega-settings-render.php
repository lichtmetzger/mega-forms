<?php

/**
 * Render Mega Forms General View
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/settings-views
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (!class_exists('MegaForms_Settings')) {
  require_once MEGAFORMS_ADMIN_PATH . 'partials/views/settings-view/mega-settings.php';
}

class MegaForms_Settings_Render extends MegaForms_Settings
{

  /**
   * Holds the setting option tabs available for megaforms
   *
   * @since   1.0.0
   * @var    array
   */
  public $option_tabs = array();
  /**
   * Holds the setting options available for megaforms
   *
   * @since   1.0.0
   * @var    array
   */
  public $options = array();

  /**
   * Constructor.
   */
  public function __construct()
  {

    // Define the available options and option tabs
    $this->option_tabs = self::get_option_tabs();
    $this->options = self::get_options();
    // Render
    $this->render();
  }

  /**
   * Render form settings on the backend.
   *
   * @since    1.0.0
   */
  private function render()
  {
?>
    <div class="megacontainer_row">
      <h2 class="mg_forms_label">
        <?php esc_html_e('Settings', 'megaforms'); ?>
      </h2>
      <a id="mfsettings_save" class="megaforms-top-button" href="#" data-hook="save-settings"><span class="save-text"><?php esc_html_e('Save Settings', 'megaforms') ?></span></a>
      <div class="mf_clearfix"></div>
    </div>
    <div class="mf_clearfix"></div>
    <div id="mega-body" class="metabox-holder columns-2">
      <div id="mega-settings-container" class="mega-container">
        <div id="megaforms_settings">
          <div class="mf_clearfix"></div>
          <?php
          $option_tabs = $this->option_tabs;
          $n = 1;
          foreach ($option_tabs as $key => $val) {
            $is_active = $n === 1 ? ' active' : '';
            $label = is_array($val) ? $val['title'] : $val;
            $has_children = is_array($val) && isset($val['children']) && is_array($val['children']) ? 'true' : 'false';
          ?>
            <a href="#" title="<?php echo $label; ?>" data-panel="<?php echo $key; ?>_settings" data-disable="megaforms_options" data-has-children="<?php echo $has_children; ?>" class="mf-panel-toggler<?php echo $is_active; ?>" target="_self">
              <span class="app-menu-text"><?php echo $label; ?></span>
            </a>
            <?php if ('true' === $has_children) { ?>
              <div id="<?php echo $key; ?>_settings" class="mfsetting_child_tab">
                <?php foreach ($val['children'] as $ckey => $cval) { ?>
                  <a href="#" title="<?php echo $cval; ?>" data-panel="<?php echo $ckey; ?>_settings" data-disable="megaforms_options" data-is-children="true" class="mf-panel-toggler" target="_self">
                    <span class="app-menu-text"><?php echo $cval; ?></span>
                  </a>
                <?php } ?>
              </div>
            <?php } ?>
          <?php
            $n++;
          }
          ?>
          <div class="mf_clearfix"></div>
        </div>

      </div>
      <div id="mega-body-settings" class="mega-body-content">
        <form method="post" id="megaforms_options" class="megaforms-settings">
          <?php echo mfsettings($this->options, 'mgsettingssholder', 'megaforms_options'); ?>
        </form>
      </div>
      <div id="saving" style="display: none;"></div>
    </div>
    <div class="mf_clearfix"></div>
<?php
  }
}
