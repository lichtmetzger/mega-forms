<?php

/**
 * Extend WP_List_Table for Mega Forms
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Mega_Forms_List_Table extends WP_List_Table
{

	public $filter = '';
	public $page_items_number = '';
	public $type = '';

	public function __construct($args = array())
	{

		$args = wp_parse_args($args, array(
			'plural' => $this->type = 'entries' ? 'entries' : 'forms',
			'singular' => $this->type = 'entries' ? 'entry' : 'form',
		));

		parent::__construct($args);

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable, 'title');
		$this->filter = mfget('filter');
	}


	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 */
	public function display()
	{

		$singular = $this->_args['singular'];
?>
		<div class="megacontainer_tablenav"><?php $this->display_tablenav('top'); ?></div>
		<div class="mf_clearfix"></div>
		<?php
		$this->screen->render_screen_reader_content('heading_list');
		?>
		<table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list" <?php
									if ($singular) {
										echo " data-wp-lists='list:$singular'";
									} ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
<?php
	}

	/**
	 * Generate row actions div
	 *
	 */
	protected function row_actions($actions, $always_visible = false)
	{
		$action_count = count($actions);

		if (!$action_count)
			return '';

		$out = '<div class="' . ($always_visible ? 'row-actions visible' : 'row-actions') . '">';
		foreach ($actions as $action => $link) {
			$out .= "<span class='$action'>$link</span>";
		}
		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __('Show more details') . '</span></button>';

		return $out;
	}

	/**
	 * Display the pagination.
	 *
	 */
	protected function pagination($which)
	{
		if (empty($this->_pagination_args)) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if (isset($this->_pagination_args['infinite_scroll'])) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ('top' === $which && $total_pages > 1) {
			$this->screen->render_screen_reader_content('heading_pagination');
		}



		$current = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		$current_url = remove_query_arg($removable_query_args, $current_url);

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ($current == 1) {
			$disable_first = true;
			$disable_prev = true;
		}
		if ($current == 2) {
			$disable_first = true;
		}
		if ($current == $total_pages) {
			$disable_last = true;
			$disable_next = true;
		}
		if ($current == $total_pages - 1) {
			$disable_last = true;
		}

		if ($disable_first) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(remove_query_arg('paged', $current_url)),
				__('First page'),
				'&laquo;'
			);
		}

		if ($disable_prev) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(add_query_arg('paged', max(1, $current - 1), $current_url)),
				__('Previous page'),
				'&lsaquo;'
			);
		}

		if ('bottom' === $which) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __('Current Page') . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __('Current Page') . '</label>',
				$current,
				strlen($total_pages)
			);
		}
		$html_total_pages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($total_pages));
		$page_links[] = $total_pages_before . sprintf(_x('%1$s of %2$s', 'paging'), $html_current_page, $html_total_pages) . $total_pages_after;

		if ($disable_next) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(add_query_arg('paged', min($total_pages, $current + 1), $current_url)),
				__('Next page'),
				'&rsaquo;'
			);
		}

		if ($disable_last) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url(add_query_arg('paged', $total_pages, $current_url)),
				__('Last page'),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if (!empty($infinite_scroll)) {
			$pagination_links_class .= ' hide-if-js';
		}

		$item_label = $this->_args['singular'];
		$items_label = $this->_args['plural'];

		if ($total_pages > 1) {
			$output = '<span class="displaying-num">' . sprintf(_n('Showing %1$s %3$s.', 'Showing %1$s out of %2$s %4$s.', $total_items), $this->page_items_number, number_format_i18n($total_items), $item_label, $items_label) . '</span>';
		} else {
			$output = '<span class="displaying-num">' . sprintf(_n('Showing %1$s %2$s.', 'Showing %1$s %3$s.', $total_items), number_format_i18n($total_items), $item_label, $items_label) . '</span>';
		}

		$output .= "\n<span class='$pagination_links_class'>" . join("\n", $page_links) . '</span>';

		if ($total_pages) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
}
