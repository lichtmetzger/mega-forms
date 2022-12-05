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

class MegaForms_Forms_Render
{

	public function __construct()
	{
		$this->display_forms_list();
	}

	/**
	 * Forms display.
	 *
	 * @since    1.0.0
	 */
	private function display_forms_list()
	{

		if (!class_exists('Mega_Forms_Table')) {
			require_once MEGAFORMS_ADMIN_PATH . 'partials/views/forms-view/mega-forms-table.php';
		}

?>
		<div class="megacontainer_row">
			<h2 class="mg_forms_label" href="#"><?php esc_html_e('Forms', 'megaforms') ?></h2>
			<a class="megaforms-top-button add-new-mega-form" data-hook="show-form-modal" href="#"><?php esc_html_e('New Form', 'megaforms') ?></a>
			<div class="mf_clearfix"></div>
		</div>
		<?php
		$table = new Mega_Forms_Table();

		$table->process_action();
		$table->views();
		$table->prepare_items();
		?>
		<form id="mega_forms_list" method="post">
			<input type="hidden" id="single_action" name="single_action">
			<input type="hidden" id="single_action_id" name="single_action_id">
			<?php
			$table->display();
			?>
		</form>
<?php
	}
}
