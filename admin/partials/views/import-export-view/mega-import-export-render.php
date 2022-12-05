<?php

/**
 * Render Mega Forms General View
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
class MegaForms_Import_Export_Render
{

	/**
	 * Holds the available tabs
	 *
	 * @since   1.0.0
	 * @var    array
	 */
	public $tabs = array();

	public function __construct()
	{

		// Define the available options and option tabs
		$this->tabs = $this->get_tabs();

		$this->render();
	}

	/**
	 * Returns the default tabs
	 *
	 */
	public function get_tabs()
	{
		return apply_filters('mf_import_export_tabs', array(
			'import_forms' => array(
				'label' => __('Import Forms', 'megaforms'),
				'callback' => array($this, 'import_forms_callback')
			),
			'export_forms' => array(
				'label' => __('Export Forms', 'megaforms'),
				'callback' => array($this, 'export_forms_callback')
			),
			'export_entries' => array(
				'label' => __('Export Entries', 'megaforms'),
				'callback' => array($this, 'export_entries_callback')
			),
		));
	}
	/**
	 * Render import/export page on the backend.
	 *
	 * @since    1.0.0
	 */
	private function render()
	{

		wp_enqueue_script('mf-select2');
		wp_enqueue_style('mf-select2');

		$tabs = $this->tabs;
?>
		<div class="megacontainer_row">
			<h2 class="mg_forms_label">
				<?php esc_html_e('Import / Export', 'megaforms'); ?>
			</h2>
			<div class="mf_clearfix"></div>
		</div>
		<div class="mf_clearfix"></div>
		<div id="mega-body" class="metabox-holder columns-2">
			<div id="mega-import-export-container" class="mega-container">
				<div id="megaforms_settings">
					<div class="mf_clearfix"></div>
					<?php

					$n = 1;
					foreach ($tabs as $key => $args) {
						$is_active = $n === 1 ? ' active' : '';
					?>
						<a href="#" title="<?php echo $args['label']; ?>" data-panel="<?php echo $key; ?>_tab" data-disable="megaforms_options" class="mf-panel-toggler<?php echo $is_active; ?>" target="_self"><span class="app-menu-text"><?php echo $args['label']; ?></span></a>
					<?php
						$n++;
					}
					?>
					<div class="mf_clearfix"></div>
				</div>

			</div>
			<div id="mega-body-settings" class="mega-body-content">
				<form method="post" id="megaforms_options" class="megaforms-settings">
					<ul id="mgsettingssholder">
						<?php
						$i = 1;
						foreach ($tabs as $tab => $args) {
							$is_active = $i === 1 ? ' active' : '';
						?>
							<li id="<?php echo $tab; ?>_tab" class="megaforms_options<?php echo $is_active; ?>">
								<?php call_user_func($args['callback']); ?>
							</li>
						<?php
							$i++;
						}
						?>
					</ul>
				</form>
			</div>
			<div id="saving" style="display: none;"></div>
		</div>
		<div class="mf_clearfix"></div>
	<?php
	}
	/**
	 * Import forms tab callback
	 *
	 */
	public function import_forms_callback()
	{

	?>
		<p><?php _e('Use the upload field below to select a Mega Forms export file. Once the export file is uploaded, Mega Forms will import the form(s) for you.', 'megaforms'); ?></p>
		<table class="mf_import_forms" cellspacing="0" cellpadding="0" style="margin-top: 30px;margin-bottom: 30px;">
			<tbody>
				<tr>
					<th><label for="mf_import_file"></label><?php _e('Import From(s)', 'megaforms'); ?></th>
					<td class="mf-inner-field">
						<input type="file" name="mf_import_file" id="mf_import_file" accept="application/json">
					</td>
				</tr>
			</tbody>
		</table>
		<input type="submit" value="Import" name="import_mf_forms" class="button button-primary">
	<?php
	}
	/**
	 * Export forms tab callback
	 *
	 */
	public function export_forms_callback()
	{
		$forms = mf_api()->get_forms();
	?>
		<p><?php _e('Select a form below to export. Once the form is selected, you can click the download button below, Mega Forms will generate a JSON file that you can upload in the import section to import the forms.', 'megaforms'); ?></p>
		<table class="mf_export_forms" cellspacing="0" cellpadding="0" style="margin-top: 30px;margin-bottom: 30px;">
			<tbody>
				<tr>
					<th><label for="mf_export_list"></label><?php _e('Select From(s)', 'megaforms'); ?></th>
					<td class="mf-inner-field">
						<select class="mf_forms-tree" name="mf_export_list[]" multiple="multiple">
							<option></option>
							<?php foreach ($forms as $form) { ?>
								<?php
								if ($form->is_trash) {
									continue;
								}
								?>
								<option value="<?php echo $form->id  ?>"><?php echo $form->title ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<script>
			jQuery(document).ready(function($) {
				jQuery('.mf_forms-tree').select2({
					placeholder: '<?php _e('Select a Form', 'megaforms'); ?>',
				});
			});
		</script>
		<input type="submit" value="Download Export File" name="export_mf_forms" class="button button-primary">
	<?php

	}
	/**
	 * Export entries tab callback
	 *
	 */
	public function export_entries_callback()
	{
		$forms = mf_api()->get_forms();
	?>
		<p><?php _e('Select a form below to export entries. Once the form is selected, you can choose the fields you want to export and click the download button below, Mega Forms will generate a CSV file that you can save to your computer.', 'megaforms'); ?></p>
		<table class="mf_export_entries" cellspacing="0" cellpadding="0" style="margin-top: 30px;margin-bottom: 30px;">
			<tbody>
				<tr>
					<th><label for="mf_export_entries_form"></label><?php _e('Select a From', 'megaforms'); ?></th>
					<td class="mf-inner-field">
						<select class="mf_entries_forms-tree" name="mf_export_entries_form">
							<option></option>
							<?php foreach ($forms as $form) { ?>
								<?php
								if ($form->is_trash) {
									continue;
								}
								?>
								<option value="<?php echo $form->id  ?>"><?php echo $form->title ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr class="mf_export_entries_fields" style="display:none;">
					<th><label for="mf_export_entries_fields"></label><?php _e('Select Fields', 'megaforms'); ?></th>
					<td class="mf-inner-field">
					</td>
				</tr>
			</tbody>
		</table>
		<script>
			jQuery(document).ready(function($) {
				jQuery('.mf_entries_forms-tree').select2({
					placeholder: '<?php _e('Select a Form', 'megaforms'); ?>',
				});
			});
		</script>
		<input type="submit" value="Download Export File" name="export_mf_entries" class="button button-primary">
<?php

	}
}
