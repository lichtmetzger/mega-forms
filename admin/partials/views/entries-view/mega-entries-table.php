<?php

/**
 * Extend Mega_Forms_List_Table for Mega Forms
 *
 * @link       https://wpali.com
 * @since      1.0.8
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/entries-view
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('Mega_Forms_List_Table')) {
	require_once MEGAFORMS_ADMIN_PATH . 'partials/class-mega-forms-admin-list.php';   // Load the class responsible for forms table view (based on WordPress default posts display)
}

class Mega_Entries_Table extends Mega_Forms_List_Table
{

	/**
	 * The type of data displayed in this list
	 *
	 * @var string
	 */

	public $type = 'entries';
	/**
	 * Holds the form ID.
	 *
	 * @var int
	 */

	public $form_id;
	/**
	 * Holds the the form object.
	 *
	 * @var object
	 */

	public $form;

	/**
	 * Holds the field columns.
	 *
	 * @var array
	 */
	public $field_columns;
	/**
	 * Hidden columns.
	 *
	 * @var array
	 */
	public $hidden_field_columns = array();
	/**
	 * If hidden columns are loaded from database or not
	 *
	 * @var array
	 */
	public $is_hidden_columns_from_storage = false;
	/**
	 * Hold the number of fields we should display in the columns.
	 *
	 * @var array
	 */
	public $preview_fields_count = 3;

	public function __construct($args = array())
	{

		$this->form_id = isset($args['form_id']) ? $args['form_id'] : mfget('id');
		$this->form  = mfget_form($this->form_id);

		// Check if there are any user specific preferences regarding the displayed and hidden columns.
		$hidden_columns = get_user_option('mf_' . $this->form_id . '_form_hidden_entry_columns');
		if (is_array($hidden_columns)) {
			$this->hidden_field_columns = $hidden_columns;
			$this->is_hidden_columns_from_storage = true;
		}

		parent::__construct($args);
	}

	function get_columns()
	{
		$columns = array();

		$columns = array(
			'cb'           => '<input type="checkbox" />',
		);

		if ($this->filter !== 'trash' && $this->filter !== 'spam') {
			$columns['is_starred'] = '';
		}

		$columns['entry_id'] = esc_html__('ID', 'megaforms');
		$columns['date_created'] = esc_html__('Date Submitted', 'megaforms');

		$field_columns = $this->get_field_columns();
		$hidden_columns = array();
		$i = 1;

		foreach ($field_columns as $field) {

			$field_id    = $field->field_id;
			$field_label = $field->get_setting_value('field_label', 'Untitled');

			$column_name = 'field_id_' . $field_id;
			$columns[$column_name] = $field_label;

			if ($i > $this->preview_fields_count) {
				$hidden_columns[] = $column_name;
			}
			$i++;
		}

		// Hide some columns by default.
		if (!$this->is_hidden_columns_from_storage && !empty($hidden_columns)) {
			$this->hidden_field_columns = $hidden_columns;
		}

		$columns['fields_count'] = '';
		$columns['buttons']      = '';

		return $columns;
	}

	function prepare_items()
	{

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$sort_column  = mfget('orderby', null, 'title');
		$sort_columns = array_keys($this->get_sortable_columns());
		if (!in_array(strtolower($sort_column), $sort_columns)) {
			$sort_column = 'date_created';
		}

		$sort_direction = strtoupper(mfget('order', null, 'DESC'));
		$sort_direction = $sort_direction == 'DESC' ? 'DESC' : 'ASC';

		$unread = false;
		$starred = false;
		$spam = false;
		$trash = false;
		switch ($this->filter) {
			case 'unread':
				$unread = true;
				break;
			case 'starred':
				$starred = true;
				break;
			case 'spam':
				$spam = true;
				break;
			case 'trash':
				$trash = true;
				break;
		}

		$entries = $this->entries_data($unread, $starred, $spam, $trash, $sort_column, $sort_direction);

		$per_page = 20;
		$current_page = $this->get_pagenum();
		$total_items = count($entries);

		$pages_data = array_slice($entries, (($current_page - 1) * $per_page), $per_page);

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		));

		$this->page_items_number = count($pages_data);

		$this->items = $pages_data;
	}

	function entries_data($unread = false, $starred = false, $spam = false, $trash = false, $sort_column = 'date_created', $sort_direction = 'DESC')
	{

		global $wpdb;
		$entries_table_name = $wpdb->prefix . 'mf_entries';

		$where_arr   = array();
		$where_arr[] = $wpdb->prepare('is_trash=%d', $trash);

		if (!$trash) {
			$where_arr[] = $wpdb->prepare('is_spam=%d', $spam);
		}

		if ($unread) {
			$is_read = 0;
			$where_arr[] = $wpdb->prepare('is_read=%d', $is_read);
		}

		if ($starred) {
			$where_arr[] = $wpdb->prepare('is_starred=%d', $starred);
		}

		$where_arr[] = $wpdb->prepare('form_id = %d', $this->get_form_id());

		$where_clause = '';
		if (!empty($where_arr)) {
			$where_clause = 'WHERE ' . join(' AND ', $where_arr);
		}

		$sort_keyword = $sort_direction == 'ASC' ? 'ASC' : 'DESC';

		$order_by     = !empty($sort_column) ? "ORDER BY $sort_column $sort_keyword" : '';
		$entries_sql = "SELECT *, null AS items FROM $entries_table_name $where_clause $order_by";

		$entries = $wpdb->get_results($entries_sql);

		foreach ($entries as $key => $entry) {
			if (isset($entry->id)) {
				$entry_meta = mf_api()->get_entry_meta($entry->id);
				$entries[$key]->items = $entry_meta;
			}
		}

		return $entries;
	}

	function get_entries_count()
	{

		global $wpdb;
		$entries_table_name = $wpdb->prefix . 'mf_entries';
		$form_id = $this->get_form_id();

		$select_arr   = array();
		$select_arr[] = $wpdb->prepare("(SELECT count(0) FROM $entries_table_name WHERE is_spam = 0 AND is_trash = 0 AND form_id = %d ) as total", $form_id);
		$select_arr[] = $wpdb->prepare("(SELECT count(0) FROM $entries_table_name WHERE is_read = 0 AND is_spam = 0 AND is_trash = 0 AND form_id = %d ) as unread", $form_id);
		$select_arr[] = $wpdb->prepare("(SELECT count(0) FROM $entries_table_name WHERE is_starred = 1 AND is_spam = 0 AND is_trash = 0 AND form_id = %d ) as starred", $form_id);
		$select_arr[] = $wpdb->prepare("(SELECT count(0) FROM $entries_table_name WHERE is_spam = 1 AND is_trash = 0 AND form_id = %d ) as spam", $form_id);
		$select_arr[] = $wpdb->prepare("(SELECT count(0) FROM $entries_table_name WHERE is_trash = 1 AND form_id = %d ) as trash", $form_id);

		$sub_select_clause = join(', ', $select_arr);

		$results = $wpdb->get_results("SELECT $sub_select_clause");

		return array(
			'total'		=> intval($results[0]->total),
			'unread'	=> intval($results[0]->unread),
			'starred' 	=> intval($results[0]->starred),
			'spam'		=> intval($results[0]->spam),
			'trash'		=> intval($results[0]->trash)
		);
	}

	function get_sortable_columns()
	{
		$sortable_columns = array(
			'entry_id'  => array('entry_id', false),
			'date_created'   => array('date_created', false),
		);
		return $sortable_columns;
	}

	function get_views()
	{
		$entries_count = $this->get_entries_count();
		$all_class = ($this->filter == '') ? 'current' : '';
		$unread_class = ($this->filter == 'unread') ? 'current' : '';
		$starred_class = ($this->filter == 'starred') ? 'current' : '';
		$spam_class = ($this->filter == 'spam') ? 'current' : '';
		$trash_class = ($this->filter == 'trash') ? 'current' : '';

		$page_slug = mf_api()->get_page();
		$id = $this->get_form_id();;

		$views = array(
			'all' => '<a class="' . $all_class . '" href="' . mf_api()->get_page_url($page_slug, $id) . '">' . esc_html(_x('All', 'Entry List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $entries_count['total'] . '</span>)</span></a>',
			'unread' => '<a class="' . $unread_class . '" href="' . mf_api()->get_page_url($page_slug, $id, array('filter' => 'unread')) . '">' . esc_html(_x('Unread', 'Entry List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $entries_count['unread'] . '</span>)</span></a>',
			'starred' => '<a class="' . $starred_class . '" href="' . mf_api()->get_page_url($page_slug, $id, array('filter' => 'starred')) . '">' . esc_html(_x('Starred', 'Entry List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $entries_count['starred'] . '</span>)</span></a>',
			'spam' => '<a class="' . $spam_class . '" href="' . mf_api()->get_page_url($page_slug, $id, array('filter' => 'spam')) . '">' . esc_html(_x('Spam', 'Entry List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $entries_count['spam'] . '</span>)</span></a>',
			'trash' => '<a class="' . $trash_class . '" href="' . mf_api()->get_page_url($page_slug, $id, array('filter' => 'trash')) . '">' . esc_html(_x('Trash', 'Entry List', 'megaforms')) . ' <span class="count">(<span id="all_count">' . $entries_count['trash'] . '</span>)</span></a>',
		);
		return $views;
	}

	function column_cb($entry)
	{
		$entry_id = $entry->id;
?>
		<label class="screen-reader-text" for="cb-select-<?php echo esc_attr($entry_id); ?>"><?php _e('Select entry'); ?></label>
		<input type="checkbox" class="megaform_list_checkbox" name="entries[]" value="<?php echo esc_attr($entry_id); ?>" />
	<?php
	}

	function column_is_starred($entry)
	{

		$entry_id = $entry->id;
		$is_checked = $entry->is_starred ? 'checked="checked"' : '';

	?>
		<label class="mf-star-switch">
			<input data-hook="change-entry-star" type="checkbox" value="<?php echo $entry_id; ?>" <?php echo $is_checked; ?> />
			<span class="entry_starred_handle">
				<i class="mega-icons-star"></i>
				<i class="mega-icons-star-o"></i>
			</span>
		</label>
<?php
	}

	function column_entry_id($entry)
	{
		if ($this->filter == 'trash') {
			echo esc_html($entry->id);
		} else {
			if ($entry->is_read) {
				echo '<a href="' . mf_api()->get_page_url('mf_entry_view', $entry->id) . '">#' . esc_html($entry->id) . '</a>';
			} else {
				echo '<a href="' . mf_api()->get_page_url('mf_entry_view', $entry->id) . '&action=read">#' . esc_html($entry->id) . '</a>';
			}
		}
	}

	function column_date_created($entry)
	{
		echo date("M d, Y @ H:i", strtotime($entry->date_created));
	}

	function column_fields_count($entry)
	{
		$fields_left = count($this->hidden_field_columns);
		echo '+<span class="hidden_fields_count">' . $fields_left . '</span> ' . __('other field(s)', 'megaforms');
	}

	function column_default($entry, $column_id)
	{

		$field_id = (string) str_replace('field_id_', '', $column_id);
		if (isset($entry->items[$field_id])) {
			$value = $entry->items[$field_id];
			$field = isset($this->field_columns[$field_id]) ? $this->field_columns[$field_id] : new stdClass();
			echo $field->get_formatted_value_short($value);
		}
	}

	function column_buttons($entry)
	{

		if ($this->filter == 'trash') {
			$actions = array(
				'containeropen'      => '<div class="mgexpandable"><a href="#" class="mgexpanderbtn mega-icons-settings" data-hook="expand-form-action"></a><div class="mgexpanded" data-hook="expanded-animate">',
				'restore'      => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'restore', $entry->id, __('Restore', 'megaforms')),
				'delete'    => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'delete', $entry->id, __('Delete', 'megaforms')),
				'containerclose'      => '</div></div>',
			);

			$links = sprintf('<div class="mgexpandable_btns">%1$s</div>', $this->row_actions($actions));
		} else {
			$actions = array(
				'containeropen'  => '<div class="mgexpandable"><a href="#" class="mgexpanderbtn mega-icons-settings" data-hook="expand-form-action"></a><div class="mgexpanded" data-hook="expanded-animate">',
				'view'           => sprintf('<a href="%s%s">%s</a>', mf_api()->get_page_url('mf_entry_view', $entry->id), !$entry->is_read ? '&action=read' : '', __('View', 'megaforms')),
				'read'           => sprintf('<a id="single-action mark-as-read" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'read', $entry->id, __('Mark as Read', 'megaforms')),
				'unread'         => sprintf('<a id="single-action mark-as-unread" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'unread', $entry->id, __('Mark as Unread', 'megaforms')),
				'trash'          => sprintf('<a id="single-action" data-hook="handle-item-action" data-action="%s" data-action-id="%s" href="#">%s</a>', 'trash', $entry->id, __('Trash', 'megaforms')),
				'containerclose' => '</div></div>',
			);

			$links = sprintf('<div class="mgexpander">%1$s</div>', $this->row_actions($actions));
		}

		echo $links;
	}

	function get_bulk_actions()
	{
		if ($this->filter == 'trash') {
			$actions = array(
				'restore' => esc_html__('Restore', 'megaforms'),
				'delete' => esc_html__('Delete permanently', 'megaforms'),
			);
		} elseif ($this->filter == 'unread') {
			$actions = array(
				'read' => esc_html__('Mark as read', 'megaforms'),
				'star' => esc_html__('Add star', 'megaforms'),
				'unstar' => esc_html__('Remove star', 'megaforms'),
				'trash' => esc_html__('Move to trash', 'megaforms'),
			);
		} elseif ($this->filter == 'starred') {
			$actions = array(
				'read' => esc_html__('Mark as read', 'megaforms'),
				'unread' => esc_html__('Mark as unread', 'megaforms'),
				'unstar' => esc_html__('Remove star', 'megaforms'),
				'trash' => esc_html__('Move to trash', 'megaforms'),
			);
		} elseif ($this->filter == 'spam') {
			$actions = array(
				'unspam' => esc_html__('Mark as not spam', 'megaforms'),
				'trash' => esc_html__('Move to trash', 'megaforms'),
			);
		} else {
			$actions = array(
				'read' => esc_html__('Mark as read', 'megaforms'),
				'unread' => esc_html__('Mark as unread', 'megaforms'),
				'spam' => esc_html__('Mark as spam', 'megaforms'),
				'star' => esc_html__('Add star', 'megaforms'),
				'unstar' => esc_html__('Remove star', 'megaforms'),
				'trash' => esc_html__('Move to trash', 'megaforms'),
			);
		}
		return $actions;
	}

	function process_action()
	{

		$remote_action = mfpost('single_action');
		$bulk_action = $this->current_action();
		$form_based_action = mfget('entry_action');
		if (!($bulk_action || $remote_action || $form_based_action)) {
			return;
		}

		if ($remote_action) {
			$entry_id = mfpost('single_action_id');
			switch ($remote_action) {
				case 'read':
					mf_api()->set_entry_status($entry_id, 1);
					$message = __('Entry marked as read.', 'megaforms');
					break;
				case 'unread':
					mf_api()->set_entry_status($entry_id, 0);
					$message = __('Entry marked as unread.', 'megaforms');
					break;
				case 'restore':
					mf_api()->restore_entry($entry_id);
					$message = __('Entry successfully restored.', 'megaforms');
					break;
				case 'delete':
					mf_api()->delete_entry($entry_id);
					mf_api()->set_form_entry_count($this->get_form_id());
					$message = __('Entry successfully deleted.', 'megaforms');
					break;
				case 'trash':
					mf_api()->trash_entry($entry_id);
					$message = __('Entry moved to the trash successfully.', 'megaforms');
					break;
			}
		} elseif ($bulk_action) {
			$entry_ids   = is_array(mfpost('entries')) ? mfpost('entries') : array();
			$entry_count = (string) count($entry_ids);

			$message = '';
			switch ($bulk_action) {
				case 'trash':
					foreach ($entry_ids as $entry_id) {
						mf_api()->trash_entry($entry_id);
					}
					/* translators: number of entries moved to trash. */
					$message = sprintf(_n('%s entry moved to the trash successfully.', '%s entries moved to the trash successfully.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'restore':
					foreach ($entry_ids as $entry_id) {
						mf_api()->restore_entry($entry_id);
					}
					/* translators: number of entries restored. */
					$message = sprintf(_n('%s entry successfully restored.', '%s entries successfully restored.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'delete':
					foreach ($entry_ids as $entry_id) {
						mf_api()->delete_entry($entry_id);
					}
					mf_api()->set_form_entry_count($this->get_form_id());
					/* translators: number of entries deleted. */
					$message = sprintf(_n('%s entry successfully deleted.', '%s entries successfully deleted.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'read':
					foreach ($entry_ids as $entry_id) {
						mf_api()->set_entry_status($entry_id, 1);
					}
					/* translators: number of entries marked as read. */
					$message = sprintf(_n('%s entry has been marked as read.', '%s entries have been marked as read.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'unread':
					foreach ($entry_ids as $entry_id) {
						mf_api()->set_entry_status($entry_id, 0);
					}
					/* translators: number of entries marked as unread. */
					$message = sprintf(_n('%s entry has been marked as unread.', '%s entries have been marked as unread.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'star':
					foreach ($entry_ids as $entry_id) {
						mf_api()->set_entry_star($entry_id, 1);
					}
					/* translators: number of entries starred. */
					$message = sprintf(_n('Star has been added to %s entry.', 'Star has been added to %s entries.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'unstar':
					foreach ($entry_ids as $entry_id) {
						mf_api()->set_entry_star($entry_id, 0);
					}
					/* translators: number of entries unstarred. */
					$message = sprintf(_n('Star has been removed from %s entry.', 'Star has been removed from %s entries.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'spam':
					foreach ($entry_ids as $entry_id) {
						mf_api()->set_entry_spam($entry_id, 1);
					}
					/* translators: number of entries marked as spam. */
					$message = sprintf(_n('%s entry marked as spam.', '%s entries marked as spam.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
				case 'unspam':
					foreach ($entry_ids as $entry_id) {
						mf_api()->set_entry_spam($entry_id, 0);
					}
					/* translators: number of entries marked as not spam. */
					$message = sprintf(_n('%s entry marked as not spam.', '%s entries marked as not spam.', $entry_count, 'megaforms'), number_format_i18n($entry_count));
					break;
			}
		} elseif ($form_based_action) {
			$entry_id = mfget('entry_id');
			switch ($form_based_action) {
				case 'trash':
					mf_api()->trash_entry($entry_id);
					$message = __('Entry moved to the trash successfully.', 'megaforms');
					break;
			}
		}
		if (!empty($message)) {
			echo '<div id="message" class="updated notice is-dismissible"><p>' . $message . '</p></div>';
		};
	}

	/**
	 * Displays a single row.
	 *
	 * @param object $entry
	 */
	public function single_row($entry)
	{

		$class = 'entry_tr';
		if ($this->filter !== 'trash') {
			$class .= $entry->is_read ? ' entry_read' : ' entry_unread';
			$class .= $entry->is_starred ? ' entry_starred' : '';
		}

		$class .= $entry->is_trash ? ' entry_trash' : '';

		echo '<tr class="' . $class . '" data-id="' . $entry->id . '">';
		$this->single_row_columns($entry);
		echo '</tr>';
	}

	public function single_row_columns($item)
	{
		list($columns, $old_hidden, $sortable, $primary) = $this->get_column_info();

		// Replaceing wordpress default hidden cols with our custom hidden cols
		$hidden = $this->hidden_field_columns;

		foreach ($columns as $column_name => $column_display_name) {
			$classes = "$column_name column-$column_name";
			if ($primary === $column_name) {
				$classes .= ' has-row-actions column-primary';
			}

			if (in_array($column_name, $hidden)) {
				$classes .= ' hidden';
			}

			$data = 'data-colname="' . wp_strip_all_tags($column_display_name) . '"';

			$attributes = "class='$classes' $data";

			if ('cb' === $column_name) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb($item);
				echo '</th>';
			} elseif ('is_starred' === $column_name) {
				echo '<th scope="row" ' . $attributes . '>';
				echo $this->column_is_starred($item);
				echo '</th>';
			} elseif (method_exists($this, '_column_' . $column_name)) {
				echo call_user_func(
					array($this, '_column_' . $column_name),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif (method_exists($this, 'column_' . $column_name)) {
				echo "<td $attributes>";
				echo call_user_func(array($this, 'column_' . $column_name), $item);
				echo $this->handle_row_actions($item, $column_name, $primary);
				echo '</td>';
			} else {
				echo "<td $attributes>";
				echo $this->column_default($item, $column_name);
				echo $this->handle_row_actions($item, $column_name, $primary);
				echo '</td>';
			}
		}
	}

	function no_items()
	{

		switch ($this->filter) {
			case 'unread':
				$message = esc_html__('Hurrah, it appears that you do not have any unread form submission.', 'megaforms');
				break;

			case 'starred':
				$message = esc_html__('Whoops, it appears that you have not starred any form submissions.', 'megaforms');
				break;

			case 'spam':
				$message = esc_html__('Hurrah, spam is empty.', 'megaforms');
				break;

			case 'trash':
				$message = esc_html__('Trash is empty.', 'megaforms');
				break;

			default:
				$message = esc_html__('Whoops, it appears that this form does not have any submissions yet.', 'megaforms');
		}
		echo $message;
	}

	/**
	 * Returns the current form fields.
	 *
	 * @return object
	 */
	public function get_form()
	{
		return $this->form;
	}
	/**
	 * Returns the hidden form fields.
	 *
	 * @return object
	 */
	public function get_hidden_columns()
	{
		return $this->hidden_field_columns;
	}

	/**
	 * Returns the current form ID.
	 *
	 * @return int
	 */
	public function get_form_id()
	{

		$form_id = isset($this->form_id) ? $this->form_id : mfget('id');
		return absint($form_id);
	}
	/**
	 * Returns the grid columns.
	 *
	 * @return array
	 */
	public function get_field_columns()
	{
		if (!isset($this->field_columns)) {

			$form = $this->get_form();
			$columns = array();
			$fields = mfget_form_fields($form);

			if (!empty($fields)) {
				foreach ($fields as $field) {

					$field_id     = mfget('id', $field);
					$fieldType   = mfget('type', $field);
					$fieldObj = MF_Fields::get($fieldType, array(
						'field' => $field
					));

					// Ignore static fields
					if ($fieldObj->isStaticField) {
						continue;
					}
					$columns[$field_id] = $fieldObj;
				}
			}
			$this->field_columns = $columns;
		}

		return $this->field_columns;
	}
	function get_primary_column_name()
	{
		return 'entry_id';
	}
}
