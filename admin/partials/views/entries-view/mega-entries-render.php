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

  private $page = '';
  private $forms = array();
  private $form_id = 0;
  private $form_title = '';

  public function __construct($page)
  {

    $this->page = $page;
    $this->forms = mf_api()->get_forms();

    $this->display();
  }
  /**
   * Display entries.
   *
   * @since    1.0.0
   */
  public function display()
  {

    wp_enqueue_script('mf-select2');
    wp_enqueue_style('mf-select2');

    if ($this->page == 'mf_form_entries') {

      $this->form_id = mfget('id');
      $this->form_title = mf_api()->get_form_title($this->form_id);

      if (!empty($this->form_title)) {

        $this->display_entries_list();
      } else {

        $this->display_forms_selection();
      }
    } else {

      $this->display_forms_selection();
    }
  }

  /**
   * Display form selection options.
   *
   * @since    1.0.0
   */
  private function display_forms_selection()
  {

?>
    <div class="megacontainer_row">
      <h2 class="mg_forms_label">
        <?php esc_html_e('Entries', 'megaforms'); ?>
      </h2>
      <div class="mf_clearfix"></div>
    </div>
    <div id="mega_entries_list" class="mf_entries_select_form">
      <div class="mf-admin-message">
        <img class="mf_entries_logo" src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v1.svg' ?>" />

        <?php

        if ($this->forms !== false && !empty($this->forms)) {
        ?>
          <h2><?php _e("One more step!", 'megaforms'); ?></h2>
          <p><?php _e("Select a form to view the corresponding entries.", 'megaforms'); ?></p>
        <?php
          $this->display_forms_dropdown();
        } else {
        ?>
          <h2><?php _e("No available entries.", 'megaforms'); ?></h2>
          <p><?php _e("You haven't created any forms yet, please create a form to be able to view the corresponding entries here.", 'megaforms'); ?></p>
        <?php
        }
        ?>
      </div>
    </div>
  <?php
  }
  /**
   * Entries display.
   *
   * @since    1.0.0
   */
  private function display_entries_list()
  {

    if (!class_exists('Mega_Entries_Table')) {
      require_once MEGAFORMS_ADMIN_PATH . 'partials/views/entries-view/mega-entries-table.php';
    }

  ?>
    <div class="megacontainer_row">
      <h2 class="mg_forms_label" href="#">
        <span class="mf_entries_form_id">ID: <?php echo $this->form_id; ?></span>
        <?php $this->display_forms_dropdown(); ?>
      </h2>
      <a class="megaforms-top-button change-entry-headers" data-hook="show-entry-settings-modal" href="#"><span class="mega-icons-settings"></span></a>
      <div class="mf_clearfix"></div>
    </div>
    <?php
    $table = new Mega_Entries_Table(array('form_id' => $this->form_id));
    $table->process_action();
    $table->views();
    $table->prepare_items();
    ?>
    <form id="mega_forms_list" method="post" data-id="<?php echo $this->form_id; ?>">
      <input type="hidden" id="single_action" name="single_action">
      <input type="hidden" id="single_action_id" name="single_action_id">
      <?php
      $table->display();
      ?>
    </form>
  <?php
  }
  /**
   * Entries display.
   *
   * @since    1.0.0
   */
  private function display_forms_dropdown()
  {
  ?>
    <div class="mf_forms_tree_wrapper">
      <select class="mf_forms-tree" name="mf_forms_tree">
        <option></option>
        <?php
        foreach ($this->forms as $form) {

          if ($form->is_trash) {
            continue;
          }

        ?>
          <option value="<?php echo $form->id  ?>" <?php selected($this->form_id, $form->id); ?>><?php echo $form->title ?></option>
        <?php
        }
        ?>
      </select>
    </div>
    <script>
      jQuery(document).ready(function($) {
        jQuery('.mf_forms-tree').select2({
          placeholder: '<?php _e('Select a Form', 'megaforms'); ?>',
        });
        $('.mf_forms-tree').on('select2:select', function(e) {
          var data = e.params.data;
          window.location.href = 'admin.php?page=mega-forms-entries&view=form-entries&id=' + data.id;
        });
      });
    </script>
<?php
  }
}
