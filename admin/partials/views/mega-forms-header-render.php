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
class MegaForms_Header_Render
{

	function __construct()
	{

		$this->render();
	}

	/**
	 * Mega Forms Header.
	 *
	 * @since    1.0.0
	 */
	private function render()
	{
?>
		<div class="megaforms_header">
			<div class="megaheader_left">
				<img src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v2.svg' ?>" />
				<?php
				if (mf_api()->is_page('mf_form_editor')) {
					$this->single_form_topleft_elements();
				} else {
					$this->main_topleft_buttons();
				}
				?>
			</div>
			<div class="megaheader_right">
				<?php $this->main_topright_buttons(); ?>
			</div>
			<div class="mf_clearfix"></div>
		</div>
		<?php
	}

	/**
	 * Display action buttons on top of form main Mega Forms page.
	 *
	 * @since    1.0.0
	 */
	private function single_form_topleft_elements()
	{

		$form_id = mfget('id');
		$form = mfget_form($form_id);

		if ($form !== false) {
		?>
			<div id="mgform_switcher">
				<a id="mgform_title" data-id="<?php echo $form->ID; ?>" data-name="<?php echo esc_attr($form->title); ?>"><i class="dashicons dashicons-tag"></i></a>
				<span><?php echo esc_attr($form->title); ?></span>
			</div>
		<?php
		}
	}

	/**
	 * Display action buttons on top of form main Mega Forms page.
	 *
	 * @since    1.0.0
	 */
	private function main_topleft_buttons()
	{

		// Get form fields

		?>
		<div id="mgforms_left_btns">
			<?php

			$cp = mf_api()->get_page();
			$forms_u = mf_api()->get_page_url('mf_forms');
			$entries_u = mf_api()->get_page_url('mf_entries');
			$settings_u = mf_api()->get_page_url('mf_settings');

			$forms_class_attr = $cp == 'mf_forms' || $cp == 'mf_form_editor' ? 'class="active"' : '';
			$entries_class_attr = $cp == 'mf_entries' || $cp == 'mf_form_entries' || $cp == 'mf_entry_view' || $cp == 'mf_entry_editor' ? 'class="active"' : '';
			$settings_class_attr = $cp == 'mf_settings' ? 'class="main-mf-settings active"' : 'class="main-mf-settings"';

			$forms = '<a id="top_btn" href="' . $forms_u . '" ' . $forms_class_attr . '>' . __('Forms', 'megaforms') . '</a>';
			$entries = '<a id="top_btn" href="' . $entries_u . '" ' . $entries_class_attr . '>' . __('Entries', 'megaforms') . '</a>';
			$settings = '<div class="mf-settings">';
			$settings .= '<a id="top_btn" href="' . $settings_u . '" ' . $settings_class_attr . '>' . __('Settings', 'megaforms') . '</a>';
			// $settings .= '<div class="child-mf-settings">';
			// $settings .= '<a href="' . $settings_u . '">' . __('General', 'megaforms') . '</a>';
			// $settings .= '<a href="' . $settings_u . '&view=emails">' . __('Emails', 'megaforms') . '</a>';
			// $settings .= '<a href="' . $settings_u . '&view=validation">' . __('Validation', 'megaforms') . '</a>';
			// $settings .= '<a href="' . $settings_u . '&view=integrations">' . __('Integration', 'megaforms') . '</a>';
			// $settings .= '<a href="' . $settings_u . '&view=misc">' . __('Misc', 'megaforms') . '</a>';
			// $settings .= '</div>';
			$settings .= '</div>';
			// $help = '<a id="top_btn" href="'. $cu .'?page=mega-forms-help">'. __( 'Help', 'megaforms' ) .'</a>';

			echo $forms . $entries . $settings;
			?>

		</div>

	<?php
	}
	/**
	 * Display action buttons on top of form main Mega Forms page.
	 *
	 * @since    1.0.0
	 */
	private function main_topright_buttons()
	{

		// Get top right buttons

	?>
		<div id="mgforms_left_btns">
			<?php

			// $upgrade = sprintf('<a id="top_btn" style="background:red;" href="#">'. __( 'Upgrade', 'megaforms' ) .'</a>');
			//
			// echo $upgrade;
			?>

		</div>

<?php
	}
}
