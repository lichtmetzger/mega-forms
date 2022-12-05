<?php

/**
 * Extend Mega_Forms_List_Table for Mega Forms
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/forms-view
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('Mega_Forms_List_Table')) {
	require_once MEGAFORMS_ADMIN_PATH . 'partials/class-mega-forms-admin-list.php';   // Load the class responsible for forms table view (based on WordPress default posts display)
}

class Mega_Forms_Table extends Mega_Forms_List_Table
{

	public $type = 'forms';

	public function __construct($args = array())
	{

		parent::__construct($args);
	}

	function get_columns()
	{
		$columns = array();

		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'status'      => esc_html__('Status', 'megaforms'),
			'title'      => esc_html__('Form name', 'megaforms'),
			'lead_count' => esc_html__('Entries', 'megaforms'),
			'view_count' => esc_html__('Views', 'megaforms'),
			'conversion' => esc_html__('Conversion', 'megaforms'),
			'shortcode' => esc_html__('Shortcode', 'megaforms'),
			'buttons' => '',
		);

		if ($this->filter == 'trash') {
			unset($columns['status']);
		}

		return $columns;
	}

	function prepare_items()
	{

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$sort_column  = empty($_GET['orderby']) ? 'title' : $_GET['orderby'];
		$sort_columns = array_keys($this->get_sortable_columns());
		if (!in_array(strtolower($sort_column), $sort_columns)) {
			$sort_column = 'title';
		}

		$sort_direction = empty($_GET['order']) ? 'ASC' : strtoupper($_GET['order']);
		$sort_direction = $sort_direction == 'ASC' ? 'ASC' : 'DESC';
		$trash = false;
		switch ($this->filter) {
			case '':
				$active = null;
				break;
			case 'active':
				$active = true;
				break;
			case 'inactive':
				$active = false;
				break;
			case 'trash':
				$active = null;
				$trash = true;
		}
		$forms = $this->forms_data($active, $trash, $sort_column, $sort_direction);

		$per_page = 20;
		$current_page = $this->get_pagenum();
		$total_items = count($forms);

		$pages_data = array_slice($forms, (($current_page - 1) * $per_page), $per_page);

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		));

		$this->page_items_number = count($pages_data);

		$this->items = $pages_data;
	}

	function forms_data($is_active = null, $is_trash = false, $sort_column = 'title', $sort_dir = 'ASC')
	{

		global $wpdb;
		$form_table_name = $wpdb->prefix . 'mf_forms';
		$where_arr   = array();

		$where_arr[] = $wpdb->prepare('is_trash=%d', $is_trash);
		if ($is_active !== null) {
			$where_arr[] = $wpdb->prepare('is_active=%d', $is_active);
		}

		$where_clause = '';
		if (!empty($where_arr)) {
			$where_clause = 'WHERE ' . join(' AND ', $where_arr);
		}

		$sort_keyword = $sort_dir == 'ASC' ? 'ASC' : 'DESC';
		if ($sort_column == 'conversion') {
			$sort_column = "ROUND(lead_count * 100.0 / view_count, 1)";
		}

		$order_by     = !empty($sort_column) ? "ORDER BY $sort_column $sort_keyword" : '';
		$sql = "SELECT * FROM $form_table_name $where_clause $order_by";

		$forms = $wpdb->get_results($sql);

		return $forms;
	}

	function _column_status($item)
	{
		$item_id = $item->id;
		echo '<th scope="row" class="manage-column column-status">';
		if ($this->filter !== 'trash') {
			echo $item->is_active == '1' ? '<label class="mfswitch"><input data-hook="change-form-status" type="checkbox" value="' . $item_id . '" checked><span class="mfswitch-slider round"></span></label>' : '<label class="mfswitch"><input data-hook="change-form-status" type="checkbox" value="' . $item_id . '"><span class="mfswitch-slider round"></span></label>';
		}
		echo '</th>';
	}

	function column_shortcode($item)
	{
		$item_id = $item->id;
		echo '<input class="display-shorcode" onclick="this.focus();this.select()" readonly="readonly" value="[megaforms id=' . $item_id . ']">';
	}

	function get_form_count()
	{
		global $wpdb;
		$form_table_name = $wpdb->prefix . 'mf_forms';
		$results         = $wpdb->get_results(
			"
			SELECT
			(SELECT count(0) FROM $form_table_name WHERE is_trash = 0) as total,
			(SELECT count(0) FROM $form_table_name WHERE is_active=1 AND is_trash = 0 ) as active,
			(SELECT count(0) FROM $form_table_name WHERE is_active=0 AND is_trash = 0 ) as inactive,
			(SELECT count(0) FROM $form_table_name WHERE is_trash=1) as trash
			"
		);

		return array(
			'total'    => intval($results[0]->total),
			'active'   => intval($results[0]->active),
			'inactive' => intval($results[0]->inactive),
			'trash'    => intval($results[0]->trash)
		);
	}

	function get_sortable_columns()
	{
		$sortable_columns = array(
			'title'  => array('title', false),
			'view_count'   => array('view_count', false),
			'lead_count'   => array('lead_count', false),
			'conversion'   => array('conversion', false)
		);
		return $sortable_columns;
	}

	function get_views()
	{
		$form_count = $this->get_form_count();
		$all_class = ($this->filter == '') ? 'current' : '';
		$active_class = ($this->filter == 'active') ? 'current' : '';
		$inactive_class = ($this->filter == 'inactive') ? 'current' : '';
		$trash_class = ($this->filter == 'trash') ? 'current' : '';


		$page_slug = mf_api()->get_page();

		$views = array(
			'all' => '<a class="' . $all_class . '" href="' . mf_api()->get_page_url($page_slug, false) . '">' . esc_html(_x('All', 'Form List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $form_count['total'] . '</span>)</span></a>',
			'active' => '<a class="' . $active_class . '" href="' . mf_api()->get_page_url($page_slug, false, array('filter' => 'active')) . '">' . esc_html(_x('Active', 'Form List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $form_count['active'] . '</span>)</span></a>',
			'inactive' => '<a class="' . $inactive_class . '" href="' . mf_api()->get_page_url($page_slug, false, array('filter' => 'inactive')) . '">' . esc_html(_x('Inactive', 'Form List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $form_count['inactive'] . '</span>)</span></a>',
			'trash' => '<a class="' . $trash_class . '" href="' . mf_api()->get_page_url($page_slug, false, array('filter' => 'trash')) . '">' . esc_html(_x('Trash', 'Form List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $form_count['trash'] . '</span>)</span></a>',
		);
		return $views;
	}

	function get_bulk_actions()
	{
		if ($this->filter == 'trash') {
			$actions = array(
				'restore' => esc_html__('Restore', 'megaforms'),
				'delete' => esc_html__('Delete permanently', 'megaforms'),
			);
		} else {
			$actions = array(
				'activate' => esc_html__('Mark as Active', 'megaforms'),
				'deactivate' => esc_html__('Mark as Inactive', 'megaforms'),
				'delete_entries' => esc_html__('Delete Entries', 'megaforms'),
				'trash' => esc_html__('Move to trash', 'megaforms'),
			);
		}
		return $actions;
	}

	function column_title($form)
	{
		if ($this->filter == 'trash') {

			echo esc_html($form->title);
		} else {

			echo '<a href="?page=' . mfget('page') . '&action=edit&id=' . absint($form->id) . '">' . esc_html($form->title) . '</a>';
		}
	}

	function column_buttons($form)
	{
		if ($this->filter == 'trash') {
			$actions = array(
				'containeropen'      => '<div class="mgexpandable"><a href="#" class="mgexpanderbtn mega-icons-settings" data-hook="expand-form-action"></a><div class="mgexpanded" data-hook="expanded-animate">',
				'restore'      => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'restore', $form->id, __('Restore', 'megaforms')),
				'delete'    => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'delete', $form->id, __('Delete', 'megaforms')),
				'containerclose'      => '</div></div>',
			);

			$links = sprintf('<div class="mgexpandable_btns">%1$s</div>', $this->row_actions($actions));

			echo $links;
		} else {

			$actions = array(
				'containeropen'      => '<div class="mgexpandable"><a href="#" class="mgexpanderbtn mega-icons-settings" data-hook="expand-form-action"></a><div class="mgexpanded" data-hook="expanded-animate">',
				'edit'      => sprintf('<a href="%s">%s</a>', mf_api()->get_page_url('mf_form_editor', $form->id), __('Edit', 'megaforms')),
				'trash'    => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'trash', $form->id, __('Trash', 'megaforms')),
				'duplicate'    => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'duplicate', $form->id, __('Duplicate', 'megaforms')),
				'containerclose'      => '</div></div>',
			);

			$links = sprintf('<div class="mgexpander">%1$s</div>', $this->row_actions($actions));


			echo $links;
		}
	}
	function column_view_count($form)
	{
		echo get_mf_formatted_number(absint($form->view_count), 1);
	}

	function column_lead_count($form)
	{
		echo '<a href="' . mf_api()->get_page_url('mf_form_entries', $form->id) . '">' . get_mf_formatted_number(absint($form->lead_count), 1) . '</a>';
	}

	function column_conversion($form)
	{
		$percentage = '0%';
		if ($form->view_count > 0) {
			$percentage = (number_format($form->lead_count / $form->view_count, 3) * 100) . '%';
		}
		$conversion = '<div title="' . $percentage . '" class="conversion_calc"><span style="width:' . $percentage . '"></span></div>';
		echo $conversion;
	}

	function column_cb($form)
	{
		$form_id = $form->id;
?>
		<label class="screen-reader-text" for="cb-select-<?php echo esc_attr($form_id); ?>"><?php _e('Select form'); ?></label>
		<input type="checkbox" class="megaform_list_checkbox" name="form[]" value="<?php echo esc_attr($form_id); ?>" />
<?php
	}



	function no_items()
	{

		if ($this->filter == 'trash') {
			esc_html_e('Trash is empty.', 'megaforms');
		} elseif ($this->filter == 'inactive') {
			esc_html_e('You don\'t have any inactive forms.', 'megaforms');
		} elseif ($this->filter == 'active') {
			esc_html_e('You don\'t have any active forms.', 'megaforms');
		} else {
			/* translators: HTML `a` tags. */
			printf(esc_html__("You haven't created a form yet. Want to %s give it a go%s?", 'megaforms'), '<a class="add-new-mega-form" data-hook="show-form-modal" href="#">', '</a>');
		}
	}

	function process_action()
	{

		$remote_action = mfpost('single_action');
		$bulk_action = $this->current_action();
		$form_based_action = mfget('form_action');
		if (!($bulk_action || $remote_action || $form_based_action)) {
			return;
		}

		if ($remote_action) {
			$form_id = mfpost('single_action_id');
			switch ($remote_action) {
				case 'restore':
					mf_api()->restore_form($form_id);
					$message = __('Form successfully restored.', 'megaforms');
					break;
				case 'delete':
					mf_api()->delete_form($form_id);
					$message = __('Form successfully deleted.', 'megaforms');
					break;
				case 'trash':

					mf_api()->trash_form($form_id);
					$message = __('Form moved to the trash successfully.', 'megaforms');
					break;
				case 'duplicate':
					mf_api()->duplicate_form($form_id);
					$message = __('Form successfully duplicated.', 'megaforms');
					break;
			}
		} elseif ($bulk_action) {
			$form_ids   = is_array(mfpost('form')) ? mfpost('form') : array();
			$form_count = (string) count($form_ids);
			$message = '';
			switch ($bulk_action) {
				case 'trash':
					foreach ($form_ids as $form_id) {
						mf_api()->trash_form($form_id);
					}
					/* translators: number of forms moved to trash. */
					$message = sprintf(_n('%s form moved to the trash successfully.', '%s forms moved to the trash successfully.', $form_count, 'megaforms'), number_format_i18n($form_count));
					break;
				case 'restore':
					foreach ($form_ids as $form_id) {
						mf_api()->restore_form($form_id);
					}
					/* translators: number of forms restored. */
					$message = sprintf(_n('%s form successfully restored.', '%s forms successfully restored.', $form_count, 'megaforms'), number_format_i18n($form_count));
					break;
				case 'delete':
					foreach ($form_ids as $form_id) {
						mf_api()->delete_form($form_id);
					}
					/* translators: number of forms deleted. */
					$message = sprintf(_n('%s form successfully deleted.', '%s forms successfully deleted.', $form_count, 'megaforms'), number_format_i18n($form_count));
					break;
				case 'delete_entries':
					foreach ($form_ids as $form_id) {
						mf_api()->delete_form_entries($form_id);
					}
					/* translators: number of entries deleted. */
					$message = sprintf(_n('Entries for %s form have been deleted successfully.', 'Entries for %s forms have been deleted successfully.', $form_count, 'megaforms'), number_format_i18n($form_count));
					break;
				case 'activate':
					foreach ($form_ids as $form_id) {
						mf_api()->set_form_status($form_id, 1);
					}
					/* translators: number of forms marked as active. */
					$message = sprintf(_n('%s form has been marked as active.', '%s forms have been marked as active.', $form_count, 'megaforms'), number_format_i18n($form_count));
					break;
				case 'deactivate':
					foreach ($form_ids as $form_id) {
						mf_api()->set_form_status($form_id, 0);
					}
					/* translators: number of forms marked as inactive. */
					$message = sprintf(_n('%s form has been marked as inactive.', '%s forms have been marked as inactive.', $form_count, 'megaforms'), number_format_i18n($form_count));
					break;
			}
		} elseif ($form_based_action) {
			$form_id = mfget('id');
			switch ($form_based_action) {
				case 'trash':
					mf_api()->trash_form($form_id);
					$message = __('Form moved to the trash successfully.', 'megaforms');
					break;
				case 'activate':
					mf_api()->set_form_status($form_id, 1);
					$message = __('Form has been marked as active.', 'megaforms');
					break;
				case 'deactivate':
					mf_api()->set_form_status($form_id, 0);
					$message = __('Form has been marked as inactive.', 'megaforms');
					break;
			}
		}
		if (!empty($message)) {
			echo '<div id="message" class="updated notice is-dismissible"><p>' . $message . '</p></div>';
		};
	}

	public function single_row($form)
	{
		echo '<tr>';
		$this->single_row_columns($form);
		echo '</tr>';
	}

	function get_primary_column_name()
	{
		return 'title';
	}
}
