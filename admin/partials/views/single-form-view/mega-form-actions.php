<?php

/**
 * Mega Forms Form Actions
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

class MegaForms_Form_Actions
{

	private $form;
	private $form_id;

	function __construct($form)
	{

		if (empty($form)) {
			return;
		}
		// Set form ID
		$this->form = $form;
		$this->form_id = $form->ID;
		$this->actions = $form->actions;
	}

	/**
	 * Render form actions on the backend.
	 *
	 * @since    1.0.0
	 */
	public function render()
	{
		$actions_container = self::get_available_action_types(); # Built-in action types
?>
		<div id="mega-body" class="metabox-holder columns-2 initializing" style="padding-top:0px">

			<div id="mega-actions-container" class="mega-container mfoptionsbox">

				<div id="megaforms_actions">
					<!-- Available actions -->
					<?php
					foreach ($actions_container as $container) {
					?>
						<h2 class="megaformshndle"><span><?php echo $container['label']; ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></h2>
						<div class="inside">
							<div id="megaactions" class="actionbuttonsdivs">
								<ul id="mgformbuttons">
									<?php self::display_action_buttons($container['actions']); ?>
								</ul>
							</div>
						</div>
					<?php
					}
					?>
				</div>

			</div>

			<!-- Fileds holder: Live preview -->
			<div id="mega-body-actions" class="mega-body-content" style="position: relative;">
				<form data-id="<?php echo $this->form_id; ?>" method="post" id="mform_actions">
					<ul id="mfactions_holder" class="megaforms-actions" data-id="<?php echo $this->form_id; ?>">
						<?php
						echo mf_api()->get_actions($this->form);
						?>
					</ul>
				</form>
			</div>
		</div>
<?php
	}

	/**
	 * Get all available action buttons
	 *
	 * @since    1.0.0
	 */
	public function get_available_action_types()
	{

		$actions_container = apply_filters('mf_build_action_buttons_before', array(
			'form_actions' => array(
				'name'          => 'form_actions',
				'label'         => __('Form Actions', 'megaforms'),
				'actions'        => array(
					// array( 'data-type' => 'email_notification' ),
				),
			),
		));

		foreach (MF_Actions::get_actions() as $megaforms_action) {

			$actions_container = $megaforms_action->add_button_to_container($actions_container);
		}

		return apply_filters('mf_build_action_buttons_after', $actions_container);
	}

	/**
	 * Display action buttons.
	 *
	 * @since    1.0.0
	 */
	public static function display_action_buttons($buttons)
	{
		foreach ($buttons as $button) {

			// Duplicate the icon and unset it so it doesn't appear in the attributes
			$icon = mf_api()->get_custom_icon('span', 'mgaction_icon mgaction_icon mfaction_' . $button['data-type'] . '_icon', $button['icon']);
			unset($button['icon']);

			$buttonHTML = '';
			$buttonHTML .= "<li id='mgsortable' class='megaform_action_btn'><a id='mgaction_button' ";
			foreach (array_keys($button) as $attr) {
				$buttonHTML .= " $attr=\"{$button[$attr]}\"";
			}

			$buttonHTML .= sprintf('>%1$s<span class="mgaction_name">%2$s</span></a></li>', $icon, $button['value']);

			echo $buttonHTML;
		}
	}
}
