<?php

/**
 * Mega Forms Form Fields
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

class MegaForms_Form_Fields
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
	}

	/**
	 * Render form fields on the backend.
	 *
	 * @since    1.0.0
	 */
	public function render()
	{
		$fields_container = self::get_available_field_types(); # Built-in field types
		$last_field_id    = mf_api()->get_form_meta($this->form_id, 'last_field_id');
?>
		<div id="mega-body" class="metabox-holder columns-2 initializing" style="padding-top:0px">

			<div id="mega-fields-container" class="mega-container mfoptionsbox">
				<!-- Tabs: fields and edit field tab buttons -->
				<div class="edit-form-sidebar-header toggle-panels">
					<ul>
						<li>
							<button class="edit-form-sidebar__panel-tab edit-form__fields-tab is-active"><?php _e('All Fields', 'megaforms'); ?></button>
						</li>
						<li>
							<button class="edit-form-sidebar__panel-tab edit-form__single-field-tab" data-current="empty"><?php _e('Edit Field', 'megaforms'); ?></button>
						</li>
					</ul>
				</div>
				<!-- Tab panels: fields and edit field tab panels -->
				<div id="megaforms_fields">
					<!-- Available fields Panel -->
					<div id="draggable_fields" class="edit-form-panel fields_list_panel is-active">
						<?php
						foreach ($fields_container as $container) {
						?>
							<h2 class="megaformshndle"><span><?php echo $container['label']; ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></h2>
							<div class="inside">
								<div id="megafields" class="fieldbuttonsdivs">
									<ul id="mgformbuttons">
										<?php self::display_field_buttons($container['fields'], $container['group']); ?>
									</ul>
								</div>
							</div>
						<?php
						}
						?>
					</div>
					<!-- Form field settings panel -->
					<div class="edit-form-panel single_field_panel">
						<form data-id="<?php echo $this->form_id; ?>" <?php echo !empty($last_field_id)  ? 'data-last-field-id="' . $last_field_id . '"' : ''; ?> method="post" id="mform_fields">
							<ul class="edit-form-panel-available_fields">
								<li data-id="empty"><?php _e('No Field Selected', 'megaforms'); ?></li>
								<?php
								echo mf_api()->get_field_settings($this->form);
								?>
							</ul>
						</form>
					</div>
				</div>

			</div>

			<!-- Fileds holder: Live preview -->
			<div id="mega-body-fields" class="mega-body-content" style="position: relative;">
				<?php
				# Get fluid content ( container markup that should appear outside field wrapper regardless of how many times the container is used )
				$below_header = '';
				$below_body = '';
				$below_footer = '';
				$settings = '';

				$container_types = MF_Containers::get_container_types($this->form);
				if (!empty($container_types)) {
					foreach ($container_types as $container_type) {
						$ctn = MF_Containers::get($container_type, array(
							'form_id' => $this->form->ID,
							'form' => $this->form,
							'settings' => $this->form->containers['settings'][$container_type] ?? false
						));

						// Get the settings input
						$settings .= $ctn->get_settings_content();

						if ($ctn->is_fluid) {
							$fluid_content = $ctn->get_fluid_data();
							$below_header .= $fluid_content['below_header'] ?? '';
							$below_footer .= $fluid_content['below_footer'] ?? '';
						}
					}
				}
				if (!empty($below_header)) {
					echo $below_header;
				}

				?>
				<ul id="mfields_holder" class="single-mega-form" data-id="<?php echo $this->form_id; ?>">
					<?php
					echo mf_api()->get_fields($this->form, true);

					if (!empty($below_body)) {
						echo $below_body;
					}

					?>
				</ul>
				<div id="mfcontainers_settings" data-id="<?php echo $this->form_id; ?>" style="display:none!important;">
					<?php
					if (!empty($settings)) {
						echo $settings;
					}
					?>
				</div>

				<?php
				if (!empty($below_footer)) {
					echo $below_footer;
				}

				$containers = MF_Containers::get_containers();
				$containers_count = count($containers);
				$add_container_classes = $containers_count > 1 ? 'mf_add_container dashicons dashicons-plus has-children' : 'mf_add_container dashicons dashicons-plus';
				$add_container_text = $containers_count > 1 ? __('Add Container', 'megaforms') : __('Add Row', 'megaforms');
				if ($containers_count > 1) {
				?>
					<div id="mf-containers-list">
						<div class="mf-containers-list-items">
							<?php
							foreach ($containers as $container) {
							?>
								<a href="#" data-type="<?php echo $container->type; ?>" class="mf-containers-flyout-button mf-containers-flyout-item">
									<div class="mf-containers-flyout-label"><?php echo $container->get_container_title(); ?></div>
									<?php echo mf_api()->get_custom_icon('span', 'mgcontainer_icon mfcontainer_' . $container->type . '_icon', $container->get_container_icon()); ?>
								</a>
							<?php
							}
							?>
						</div>
					</div>
				<?php
				}
				?>
				<a href="#" class="<?php echo $add_container_classes; ?>"><span><?php echo $add_container_text; ?></span></a>
			</div>
		</div>
<?php
	}
	/**
	 * Get all field groups.
	 *
	 * @since    1.0.0
	 */
	public static function get_available_field_types()
	{

		$fields_container = apply_filters('mf_fields_list_before', array(
			'basic_fields' => array(
				'name'          => 'basic_fields',
				'label'         => __('Common Fields', 'megaforms'),
				// 'group'			=> array( 'fields' => 'Fields', 'layout' => 'Layout' ), // Enable tabs ( not fully developed feature )
				'group'			=> false,
				'fields'        => array(
					array('data-type' => 'text',),
					array('data-type' => 'textarea'),
					array('data-type' => 'radios'),
					array('data-type' => 'checkboxes'),
					array('data-type' => 'select'),
					array('data-type' => 'number'),
					array('data-type' => 'date'),
					array('data-type' => 'website'),
				),
			),
			'user_fields' => array(
				'name'   => 'user_fields',
				'label'  => __('User Fields', 'megaforms'),
				'group' => false,
				'fields' => array(
					array('data-type' => 'name'),
					array('data-type' => 'address'),
					array('data-type' => 'phone'),
					array('data-type' => 'email'),
					array('data-type' => 'password'),
				),
			),
			'misc_fields' => array(
				'name'   => 'misc_fields',
				'label'  => __('Misc Fields', 'megaforms'),
				'group' => false,
				'fields' => array(
					array('data-type' => 'hidden'),
					array('data-type' => 'section'),
					array('data-type' => 'html'),
					array('data-type' => 'divider'),
					array('data-type' => 'question'),
				),
			),
		));

		foreach (MF_Fields::get_fields() as $megaforms_field) {

			$fields_container = $megaforms_field->add_button_to_container($fields_container);
		}

		return apply_filters('mf_fields_list_after', $fields_container);
	}

	/**
	 * Display fields button.
	 *
	 * @since    1.0.0
	 */
	public static function display_field_buttons($buttons, $group)
	{
		if (is_array($group)) {

			echo '<div id="megaforms_tabs_container">';
			echo '<ul id="mg-tabs" class="mg-tabs">';
			foreach ($group as $tabkey => $tabname) {
				echo '<li class="tabs"><a href="#' . $tabkey . '">' . $tabname . '</a></li>';
			}
			echo '</ul>';

			foreach ($group as $tabkey => $tabname) {
				echo '<div id="' . $tabkey . '" class="tabs-panel">';
				foreach ($buttons as $button) {
					if ($button['group'] == $tabkey) {
						echo "<li id='mgsortable' class='megaform_field_btn'><a id='mgfield_button' ";
						foreach (array_keys($button) as $attr) {
							echo " $attr=\"{$button[$attr]}\"";
						}
						echo '><span class="' . $button['icon'] . '"></span>' . $button['value'] . '</a></li>';
					}
				}
				echo '</div>';
			}
			echo '</div>';
		} elseif ($group == false) {
			foreach ($buttons as $button) {
				echo "<li id='mgsortable' class='megaform_field_btn'><a id='mgfield_button' ";
				foreach (array_keys($button) as $attr) {
					echo " $attr=\"{$button[$attr]}\"";
				}
				echo '><span class="' . $button['icon'] . '"></span>' . $button['value'] . '</a></li>';
			}
		}
	}
}
