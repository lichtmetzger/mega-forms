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

class MegaForms_Entry_Render
{

  public $entry;
  public $form;

  function __construct($page)
  {

    $id = mfget('id');
    // If the id is not defined and is not an integrer, bail out.
    if (empty($id) && !is_int($id)) {
      return;
    }

    // Assign current page, id, entry data and form data
    $this->page = $page;
    $this->entry = mf_api()->get_entry($id, true);
    $this->form = mfget_form($this->entry->form_id);

    // Mark the entry as read
    if (mfget('action') == 'read') {
      mf_api()->set_entry_status($id, 1);
    }

    $this->display();
  }
  /**
   * Display entry.
   *
   * @since    1.0.0
   */
  public function display()
  {

    if ($this->page == 'mf_entry_editor') {

      $this->display_entry_editor();
    } else {

      $this->display_entry_view();
    }
  }
  /**
   * Entry editor display.
   *
   * @since    1.0.0
   */
  private function display_entry_editor()
  {
  }

  /**
   * Entry view display.
   *
   * @since    1.0.0
   */
  private function display_entry_view()
  {
    if ($this->form == false || $this->entry == false) {
?>
      <p style="text-align: center; padding: 50px 30px;"><?php _e('The entry you\'re trying to view doesn\'t exit.', 'megaforms'); ?></p>
    <?php
    } else {
    ?>
      <div class="megacontainer_row">
        <div class="mf_clearfix"></div>
        <h2 class="mg_forms_label">
          <span class="mf_entries_form_id">Entry #<?php echo esc_html($this->entry->id); ?></span>
        </h2>

        <a id="mfentry_save" class="megaforms-top-button mf_hidden" href="#"><span class="save-text"><?php esc_html_e('Save Changes', 'megaforms') ?></span></a>
        <a id="fullscreenbtn" href="#" title="<?php esc_html_e('Full Screen', 'megaforms') ?>"><span class="mega-icons-fullscreen"></span></a>
        <div class="mf_clearfix"></div>
      </div>
      <ul id="poststuff" style="padding:0px;margin:0px;">
        <li class="mf_clearfix no-margin"></li>
        <li id="mf_entry_panel" class="mgform_panel active initializing" data-id="<?php echo $this->entry->id; ?>">
          <?php $this->display_entry_panels(); ?>
          <div id="saving" style="display: none;"></div>
        </li>
        <li class="mf_clearfix no-margin"></li>
      </ul>

    <?php
    }
  }
  /**
   * Entry view display.
   *
   * @since    1.0.0
   */
  private function display_entry_panels()
  {
    ?>
    <div id="mega-body" class="metabox-holder columns-2">
      <div id="mega-entry-left-panel" class="mega-body-content">
        <?php $this->display_entry_left_panel(); ?>
      </div>
      <div id="mega-entry-right-panel" class="mega-container">
        <?php $this->display_entry_right_panel(); ?>
      </div>
    </div>
  <?php
  }
  /**
   * Entry left panel display.
   *
   * @since    1.0.0
   */
  private function display_entry_left_panel()
  {

  ?>

    <div class="mf_clearfix"></div>
    <div class="mf_entry_show_hidden mf-field-inputs-switch">
      <label class="mfswitch mfswitch-size-small mfswitch-labelright">
        <input type="checkbox" name="mf_entry_show_hidden" value="yes">
        <span class="mfswitch-slider round"></span></label>
      <label for="mf_entry_show_hidden" class="mf_label"><?php _e('Show empty fields', 'megaforms'); ?></label>
    </div>
    <div class="mf_clearfix"></div>
    <form class="single-mega-form" method="post">
      <table cellspacing="0" class="mf-entry-values-table">
        <tbody>
          <?php
          $fields = mfget_form_fields($this->form);
          if (!empty($fields)) {
            foreach ($fields as $field) {

              $fieldID     = mfget('id', $field);
              $fieldType   = mfget('type', $field);
              $fieldObject = MF_Fields::get($fieldType, array('field' => $field));

              // Ignore static fields
              if ($fieldObject->isStaticField) {
                continue;
              }

              $label = $fieldObject->get_setting_value('field_label', 'untitled');
              $unformatted_value = '';
              if (isset($this->entry->meta[$fieldID])) {
                $unformatted_value = $this->entry->meta[$fieldID];
                $formatted_value = $fieldObject->get_formatted_value_long($unformatted_value);
              } else {
                $formatted_value = '';
              }

              $field_display = $fieldObject->get_field_display($unformatted_value);
              $wrapper_classes = empty($fieldObject->get_formatted_value_short($unformatted_value)) ? 'mf_entry_row mf_entry_empty_row mf_hidden' : 'mf_entry_row';

          ?>
              <tr class="mf_entry_row_label <?php echo esc_attr($wrapper_classes); ?>">
                <th>
                  <span class="mf_clearfix"></span>
                  <span class="mf_entry_field_label left"><?php echo esc_html($label); ?></span>
                  <span class="mf_entry_field_id right">(ID: <?php echo esc_html($fieldID); ?>)</span>
                  <span class="mf_clearfix"></span>
                </th>
              </tr>

              <tr class="mf_entry_row_value <?php echo esc_attr($wrapper_classes); ?>">
                <td>
                  <div class="edit mfield mf_hidden" data-id="<?php echo esc_attr($fieldID); ?>">
                    <?php echo $field_display; ?>
                  </div>
                  <div class="view">
                    <?php echo $formatted_value; ?>
                  </div>
                </td>
              </tr>
          <?php
            }
          }
          ?>
        </tbody>
      </table>
      <?php wp_nonce_field('mf_save_entry_' . $this->form->ID, 'mf_entry_' . $this->form->ID . '_token'); ?>
    </form>
  <?php

  }
  /**
   * Entry right panel display.
   *
   * @since    1.0.0
   */
  private function display_entry_right_panel()
  {
  ?>
    <div id="mf_entry_sidebar" class="mf-entry-accordions mf_hidden">

      <h2 class="megaformshndle mf-entry-details-handle"><span><?php _e('Entry Details'); ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></h2>
      <div class="inside mf-entry-details">
        <table cellspacing="0" class="mf-entry-details-table">
          <tbody>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-hashtag"></span><?php _e('Entry ID'); ?></th>
              <td><?php echo $this->entry->id; ?></td>
            </tr>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-copy"></span><?php _e('Form'); ?></th>
              <td><?php echo mf_api()->get_form_title($this->entry->form_id); ?></td>
            </tr>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-calendar"></span><?php _e('Submitted On'); ?></th>
              <td><?php echo date("M d, Y @ H:i", strtotime($this->entry->date_created)); ?></td>
            </tr>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-user"></span><?php _e('Submitted By'); ?></th>
              <td>
                <?php
                $user_id = $this->entry->user_id;
                if ($user_id == 0) {
                  _e('Guest', 'megaforms');
                } else {
                  $user = get_userdata($user_id);
                  $display_name = $user->display_name;
                  echo '<a href="' . get_edit_user_link($user_id) . '">' . $display_name . '</a>';
                }
                ?>
              </td>
            </tr>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-globe"></span><?php _e('User Agent'); ?></th>
              <td><?php echo !empty($this->entry->user_agent) ? $this->entry->user_agent : '::'; ?></td>
            </tr>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-question-circle"></span><?php _e('User IP'); ?></th>
              <td><?php echo !empty($this->entry->user_ip) ? $this->entry->user_ip : '::'; ?></td>
            </tr>
            <tr class="mf_entry_details_row">
              <th><span class="mega-icons-location-arrow"></span><?php _e('Referrer'); ?></th>
              <td><?php echo !empty($this->entry->referrer) ? $this->entry->referrer : ''; ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2 class="megaformshndle mf-entry-actions-handle"><span><?php _e('Entry Actions'); ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></h2>
      <div class="inside mf-entry-actions">
        <a href="<?php echo mf_api()->get_page_url('mf_form_entries', $this->entry->form_id, array('entry_action' => 'trash', 'entry_id' => $this->entry->id)); ?>">
          <span class="mega-icons-trash-o"></span>
          <?php _e('Trash entry', 'megaforms'); ?>
        </a>
        <a class="mf-entry-action-edit" href="#">
          <span class="mega-icons-pencil"></span>
          <?php _e('Edit Entry', 'megaforms'); ?>
        </a>
        <a class="mf-entry-action-view mf_hidden" href="#">
          <span class="mega-icons-eye"></span>
          <?php _e('View Entry', 'megaforms'); ?>
        </a>
        <a href="#" onclick="window.print();return false;">
          <span class="mega-icons-printer"></span>
          <?php _e('Print entry', 'megaforms'); ?>
        </a>
        <!--
        <a class="mf-entry-action-resend-emails" href="#">
          <span class="mega-icons-envelope-o"></span>
          <?php _e('Resend emails', 'megaforms'); ?>
        </a>
        -->
      </div>

      <h2 class="megaformshndle mf-entry-notes-handle"><span><?php _e('Entry Notes'); ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></h2>
      <div class="inside mf-entry-notes">
        <div class="mf-entry-notes-list">
          <?php
          $notes_available = false;

          if (isset($this->entry->meta['notes']) && !empty($this->entry->meta['notes']) && is_array($this->entry->meta['notes'])) {
            foreach ($this->entry->meta['notes'] as $key => $note) {
          ?>
              <div class="mf-entry-single-note" data-id="<?php echo $key; ?>">
                <span class="mf-entry-note-box">
                  <?php echo $note['text']; ?>
                </span>
                <span class="mf-entry-note-date">
                  <?php echo date("M d, Y @ H:i", strtotime($note['date'])); ?>
                  <a href="#" class="mf-entry-note-delete"><?php _e('Delete'); ?></a>
                </span>
              </div>
          <?php
            }
            $notes_available = true;
          }

          $no_notes_class = $notes_available === false ? 'no-notes' : 'no-notes mf_hidden';
          ?><span class="<?php echo $no_notes_class ?>"><?php _e('You haven\'t added notes!'); ?></span><?php

                                                                                                        ?>
        </div>
        <div class="mf-entry-notes-insert">
          <label for="mf_entry_note">Add Note</label>
          <textarea type="text" name="mf_entry_note" id="mf_entry_note" rows="4"></textarea>
          <span class="mf_clearfix"></span>
          <a href="#" id="mf_entry_save_note" class="save_entry_note mfbutton mfbutton-small"><?php _e('Add Note'); ?></a>
        </div>
      </div>

    </div>

<?php
  }
}
