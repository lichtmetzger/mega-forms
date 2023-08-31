<?php

/**
 * Render Mega Forms Header Area
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/general-views
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
class MegaForms_Body_Render
{

  function __construct()
  {

    $this->render();
  }

  /**
   * Mega Forms Body.
   *
   * @since    1.0.0
   */
  private function render()
  {

    $current_page = mf_api()->get_page();

?>
    <div id="mega-forms-container" class="<?php echo $current_page; ?>_page">
      <?php
      switch ($current_page) {
        case "mf_forms":
          # Display forms list
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/forms-view/mega-forms-render.php';
          new MegaForms_Forms_Render();
          break;
        case "mf_form_editor":
          # Display single form editor
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/single-form-view/mega-form-render.php';
          new MegaForms_Form_Render();
          break;
        case "mf_settings":
          # Display settings view
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/settings-view/mega-settings-render.php';
          new MegaForms_Settings_Render();
          break;
        case "mf_entries":
        case "mf_form_entries":
          # Display main entries page
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/entries-view/mega-entries-render.php';
          new MegaForms_Entries_Render($current_page);
          break;
        case "mf_entry_view":
        case "mf_entry_editor":
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/single-entry-view/mega-entry-render.php';
          new MegaForms_Entry_Render($current_page);
          break;
        case "mf_import_export":
          # Display import and export view
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/import-export-view/mega-import-export-render.php';
          new MegaForms_Import_Export_Render();
          break;
        case "mf_addons":
          echo 'Addons';
          break;
        case "mf_help":
          # Display main help page
          require_once MEGAFORMS_ADMIN_PATH . 'partials/views/help-view/mega-help-render.php';
          new MegaForms_Entries_Render($current_page);
          break;
      }

      ?>
    </div>
<?php

  }
}
