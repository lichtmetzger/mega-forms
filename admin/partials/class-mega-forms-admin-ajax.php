<?php

/**
 * Mega Forms Ajax Class
 *
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Admin_Ajax extends MF_Ajax
{

  protected $validation = array('current_user_can' => 'manage_options',);

  public function set_form_status()
  {

    $form_id = $this->get_value('form_id');
    $is_active = $this->get_value('is_active');

    $set_status = mf_api()->set_form_status($form_id, $is_active);

    if ($set_status) {
      $this->success();
    } else {
      throw new MF_Ajax_Exception(__("The form you're trying to change does not exist.", 'megaforms'));
    }
  }

  public function create_form()
  {

    $form_name = $this->get_value('form_name');
    $template_type = $this->get_value('template_type');
    $template_parent = $this->get_value('template_parent');

    $form_id = false;
    if ($template_type == 'blank') {

      $form_id = mf_api()->create_form(sanitize_text_field($form_name));
    } else {

      $template = mf_api()->get_ready_form_templates(true, array(
        'parent' => $template_parent,
        'type' => $template_type,
      ));

      if (isset($template['import_path']) && !empty($template['import_path'])  && file_exists($template['import_path'])) {
        $json_data = file_get_contents($template['import_path']);
        $form_id = mf_api()->import_forms($json_data, true);
        if ($form_id) {
          mf_api()->rename_form($form_name, $form_id);
        }
      }
    }

    if ($form_id) {
      $redirect = sprintf('?page=%s&action=%s&id=%s', 'mega-forms', 'edit', $form_id);
      $args = array('redirect' => $redirect);
      $this->success(__('Form created! Redirecting...', 'megaforms'), $args);
    } else {
      throw new MF_Ajax_Exception(__("The form could not be created.", 'megaforms'));
    }
  }

  public function rename_form()
  {

    $form_name = $this->get_value('form_name');
    $form_id = $this->get_value('form_id');

    $renamed = false;
    if (!empty($form_name)) {
      $renamed = mf_api()->rename_form(sanitize_text_field($form_name), $form_id);
    }
    if ($renamed) {
      $this->success(__('Form renamed!', 'megaforms'));
    } else {
      throw new MF_Ajax_Exception(__("The form could not be renamed.", 'megaforms'));
    }
  }

  public function update_form()
  {

    $form_id    = $this->get_value('form_id');
    $containers = $this->get_value('containers');
    $fields     = $this->maybe_get_value('fields');
    $settings   = $this->maybe_get_value('settings');
    $actions    = $this->maybe_get_value('actions');

    $updated = false;

    if (!empty($fields) || !empty($settings)) {
      $updated = mf_api()->save_form($form_id, $containers, $fields, $settings, $actions);
    }

    if ($updated) {
      $this->success(__('Form saved successfully.', 'megaforms'));
    } else {
      throw new MF_Ajax_Exception(__("The form couldn't be saved.", 'megaforms'));
    }
  }

  public function get_field()
  {

    $type = $this->get_value('type');
    $field_id = $this->get_value('field_id');
    $form_id = $this->get_value('form_id');
    $values = $this->maybe_get_value('values');
    // Get Field (main params are: ID, formID, Type for any field)
    $field = array(
      'id'         => $field_id,
      'formId'     => $form_id,
      'type'       => $type,
    );

    // Extract key/value pairs from setting values and add them to the field array
    if (!empty($values)) {
      foreach ($values as $key => $val) {
        $field_key = $key;
        if (strpos($field_key, '_mfield_') !== false) {
          $key_parts = explode('_mfield_', $key);
          $field_key = $key_parts[0];
        }
        $field[$field_key] = $val;
      }
    }

    // Retrieve the field preview and settings
    $field_display  = mf_api()->get_field($field, true);
    $field_settings = mf_api()->get_field_setting($field);

    if (!empty($field_display) && !empty($field_settings)) {
      $this->success(array(
        'display' => $field_display,
        'settings' => $field_settings,
      ));
    } else {
      throw new MF_Ajax_Exception(__("The field you are trying to add doesn't exist.", 'megaforms'));
    }
  }
  public function get_action()
  {

    $type        = $this->get_value('type');
    $action_id    = $this->get_value('action_id');
    $form_id     = $this->get_value('form_id');
    $action_label = $this->get_value('action_label');
    // Get action (main params are: ID, formID, Type for any action)
    $action = array(
      'id'           => $action_id,
      'formId'       => $form_id,
      'type'         => $type,
      'action_label' => $action_label,
      'enabled'   => true,
    );

    $action_output  = mf_api()->get_action($action);

    if (!empty($action_output)) {
      $this->success(array(
        'output' => $action_output,
      ));
    } else {
      throw new MF_Ajax_Exception(__("The action you are trying to add doesn't exist.", 'megaforms'));
    }
  }

  public function set_entry_star()
  {

    $entry_id = $this->get_value('entry_id');
    $is_starred = $this->get_value('is_starred');

    $set_star = mf_api()->set_entry_star($entry_id, $is_starred);

    if ($set_star) {
      $this->success();
    } else {
      throw new MF_Ajax_Exception(__("The form you're trying to change does not exist.", 'megaforms'));
    }
  }

  public function save_form_hidden_entry_columns()
  {

    $form_id = $this->get_value('form_id');
    $hidden_columns = $this->maybe_get_value('hidden_columns');

    if (empty($hidden_columns)) {
      $hidden_columns = array();
    }

    $user_id = get_current_user_id();

    if ($user_id) {
      update_user_option($user_id, 'mf_' . $form_id . '_form_hidden_entry_columns', $hidden_columns);
      $this->success();
    } else {
      throw new MF_Ajax_Exception(__("We couldn\'t save your preference.", 'megaforms'));
    }
  }

  public function add_entry_note()
  {

    $entry_id = $this->get_value('entry_id');
    $note = $this->get_value('note');

    $available_notes = mf_api()->get_entry_meta($entry_id, 'notes');
    if (empty($available_notes) || !is_array($available_notes)) {
      $notes = array();
    } else {
      $notes = $available_notes;
    }

    $current_date = date("M d, Y @ H:i");
    $notes[] = array(
      'text' => sanitize_textarea_field($note),
      'date' => $current_date,
    );

    # save the notes back to database
    $save_note = mf_api()->update_entry_meta($entry_id, 'notes', $notes);

    if ($save_note) {

      $this->success(array(
        'id' => count($notes) - 1,
        'date' => $current_date,
      ));
    } else {
      throw new MF_Ajax_Exception(__("We couldn't save the added note.", 'megaforms'));
    }
  }
  public function delete_entry_note()
  {

    $entry_id = $this->get_value('entry_id');
    $note_id = $this->get_value('note_id');

    $delete_note = false;
    $available_notes = mf_api()->get_entry_meta($entry_id, 'notes');

    if (!empty($available_notes) && is_array($available_notes) && isset($available_notes[$note_id])) {
      unset($available_notes[$note_id]);
      # save the notes back to database
      $delete_note = mf_api()->update_entry_meta($entry_id, 'notes', $available_notes);
    }

    if ($delete_note) {
      $this->success();
    } else {
      throw new MF_Ajax_Exception(__("Something wrong happened while deleting this note, please refresh the page and try again.", 'megaforms'));
    }
  }
  public function save_entry_changes()
  {

    $entry_id = $this->get_value('entry_id');
    $data = $this->get_value('fields');

    $updated = false;
    $notices = array();
    if (!empty($data) && is_array($data) && is_numeric($entry_id)) {

      $entry = mf_api()->get_entry($entry_id);
      $form_id = $entry->form_id;
      $postedData = $data;
      $postedData['entry_id'] = $entry_id;

      mf_submission()->exec($form_id, $postedData, 'entry');
      if (mf_submission()->is_empty() !== true && mf_submission()->success) {
        $message = mf_submission()->message;
        $updated = true;
      } else {
        $message = mf_submission()->message;
        $notices = mf_submission()->notices;
      }
    }

    if ($updated) {
      $this->success($message);
    } else {
      throw new MF_Ajax_Exception($message, array('notices' => $notices));
    }
  }
  public function save_settings()
  {

    $settings = $this->get_value('settings');

    if (!class_exists('MegaForms_Settings')) {
      require_once MEGAFORMS_ADMIN_PATH . 'partials/views/settings-view/mega-settings.php';
    }

    $saved = MegaForms_Settings::update_options($settings);

    if ($saved) {
      $this->success(__('Settings saved successfully.', 'megaforms'));
    } else {
      throw new MF_Ajax_Exception(__('Settings could not be saved.', 'megaforms'));
    }
  }
  public function import_forms()
  {

    $file = $this->get_value('mf_import_file', 'FILES');
    $filename = sanitize_file_name($file['name']);
    $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $imported = false;

    if ($fileType === 'json') {
      $json = file_get_contents($file['tmp_name']);
      $imported = mf_api()->import_forms($json);
    }

    if ($imported) {
      $this->success(__('Form(s) imported successfully.', 'megaforms'));
    } else {
      throw new MF_Ajax_Exception(__('Please make sure you have selected a valid Mega Forms export file.', 'megaforms'));
    }
  }
  public function get_form_fields()
  {

    $form_id = absint($this->get_value('form_id'));
    $form = mf_api()->get_form($form_id, array('fields', 'containers'));
    $fields = mfget_form_fields($form);

    if ($fields) {

      // Prepare the entry main fields array
      $entry_fields = array(
        'id'              => __('Entry ID', 'megaforms'),
        'user_id'         => __('User ID', 'megaforms'),
        'date_created'    => __('Date Created', 'megaforms'),
        'referrer'        => __('Referrer', 'megaforms'),
        'user_ip'         => __('User IP', 'megaforms'),
        'user_agent'      => __('User Agent', 'megaforms'),
      );

      // Add form fields to the array
      foreach ($fields as $field) {

        // Ignore static fields
        $field_type   = mfget('type', $field);
        $field_object = MF_Fields::get($field_type, array(
          'field' => $field
        ));

        if ($field_object->isStaticField) {
          continue;
        }

        $entry_fields[$field['id']] = $field['field_label'];
      }

      $select_label = __('Select All', 'megaforms');
      $deselect_label = __('Deselect All', 'megaforms');
      $html = '';
      ob_start();
?>
      <ul id="mf_export_field_list">
        <li>
          <input id="select_all" type="checkbox" onclick="jQuery('.mf_export_field').prop('checked', this.checked); jQuery('#mf_export_checkall').html(this.checked ? '<strong><?php echo $deselect_label; ?></strong>' : '<strong><?php echo $select_label; ?></strong>'); " onkeypress="jQuery('.mf_export_field').prop('checked', this.checked); jQuery('#mf_export_checkall').html(this.checked ? '<strong><?php echo $deselect_label; ?></strong>' : '<strong><?php echo $select_label; ?></strong>'); ">
          <label id="mf_export_checkall" for="select_all">
            <strong><?php echo $select_label; ?></strong>
          </label>
        </li>

        <?php foreach ($entry_fields as $key => $label) { ?>
          <li>
            <input type="checkbox" id="mf_export_field_<?php echo $key; ?>" name="mf_export_entries_fields[]" value="<?php echo $key; ?>" class="mf_export_field">
            <label for="mf_export_field_<?php echo $key; ?>"> <?php echo $label; ?></label>
          </li>
        <?php } ?>
      </ul>
<?php
      $html = ob_get_clean();
      $this->success($html);
    } else {
      throw new MF_Ajax_Exception(__('Something went wrong, please refresh and try again.', 'megaforms'));
    }
  }
}
