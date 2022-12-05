<?php

/**
 * Mega Forms Merge Tags Class
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Merge_Tags
{

	/**
	 * The single instance of the class.
	 *
	 * @var MF_Merge_Tags
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main MF_Merge_Tags Instance.
	 *
	 * Ensures only one instance of MF_Merge_Tags is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @see mf_merge_tags()
	 * @return MF_Merge_Tags - Main instance.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Create the available merge tags.
	 *
	 * @since 1.0.0
	 *
	 * @return array array of the all available merge tags clustered by group
	 */
	public function get_tags_list()
	{
		return apply_filters('mf_merge_tags', array(
			'fields' => array(
				'label' => __('Fields', 'megaforms'),
				'tags' => array(
					'all_fields' => __('All Fields', 'megaforms'),
				)
			),
			'wp' => array(
				'label' => __('WordPress', 'megaforms'),
				'tags' => array(
					'site_title' => __('Site Title', 'megaforms'),
					'site_url' => __('Site URL', 'megaforms'),
					'admin_email' => __('Admin Email', 'megaforms'),
					'post_id' => __('Post ID', 'megaforms'),
					'post_title' => __('Post Title', 'megaforms'),
					'post_url' => __('Post URL', 'megaforms'),
					'post_meta="META_KEY"' => __('Post Meta', 'megaforms'),
					'user_id' => __('User ID', 'megaforms'),
					'user_first' => __('User First Name', 'megaforms'),
					'user_last' => __('User Last Name', 'megaforms'),
					'user_email' => __('User Email', 'megaforms'),
					'user_meta="META_KEY"' => __('User Meta', 'megaforms'),
					'url_referer' => __('HTTP Referer', 'megaforms'),
					'url_login' => __('Login URL', 'megaforms'),
					'url_logout' => __('Logout URL', 'megaforms'),
					'url_register' => __('Registration URL', 'megaforms'),
					'url_lost_password' => __('Password Lost URL', 'megaforms'),

				)
			),
			'form' => array(
				'label' => __('Form', 'megaforms'),
				'tags' => array(
					'id' => __('Form ID', 'megaforms'),
					'title' => __('Form Title', 'megaforms'),
					'view_count' => __('View Count', 'megaforms'),
					'lead_count' => __('Submission Count', 'megaforms'),
				)
			),
			'misc' => array(
				'label' => __('Misc', 'megaforms'),
				'tags' => array(
					'get="KEY"' => __('GET', 'megaforms'),
					'post="KEY"' => __('POST', 'megaforms'),
					'user_ip' => __('User IP Address', 'megaforms'),
					'date="F j, Y H:i:s"' => __('Current Date', 'megaforms'),
				)
			),
		));
	}
	/**
	 * Returns a single merge tags group.
	 *
	 */
	public function get_tags_group($group)
	{
		$merge_tags = $this->get_tags_list();
		if (isset($merge_tags[$group])) {
			return $group;
		}

		return false;
	}

	/**
	 * Proccess merge tags and replace them with the right data
	 *
	 * @since 1.0.0
	 *
	 * @param array $content. the string to search for merge tags and process them.
	 * @param mixed $data Optional. Any data necessary to process the tags
	 * @return string The merga tags search regular expression
	 */
	public function process($content, $data = false)
	{
		# Quick check
		if (strpos($content, '{mf:') === false) {
			return $content;
		}
		# Extract tags
		$matches = $this->extract_tags($content);
		# Bail out if no tags found
		if ($matches === false || !is_array($matches) || empty($matches)) {
			return $content;
		}
		# Process the supplied string if tags are available ( each group is processed separately )
		foreach ($matches as $group => $match) {
			if (!is_array($match) || empty($match)) {
				continue;
			}

			if ($group == 'fields') {
				foreach ($match as $tags) {
					switch ($tags['tag']) {
						case 'all_fields':
							$all_fields = $this->prepare_all_fields_tag($data);
							$content = str_replace($tags['shortcode'], $all_fields, $content);
							break;
						default:
							$field_id = $tags['tag'];
							if (is_numeric($field_id) && isset($data[$field_id])) {
								# Handle merge tags with field IDs and replace the shortcodes with the field value
								$content = str_replace($tags['shortcode'], $data[$field_id]['values']['formatted_short'], $content);
							} else {
								$content = str_replace(
									$tags['shortcode'],
									apply_filters('mf_process_merge_tag', '', $content, $group, $tags, $data), # Remove the shortcode as a default behaviour if no value is assigned to it ( allow it to be filtered )
									$content
								);
							}
							break;
					}
				}
			} elseif ($group == 'wp') {
				foreach ($match as $tags) {
					switch ($tags['tag']) {
						case 'site_title':
						case 'site_name':
							$content = str_replace($tags['shortcode'], get_bloginfo('name'), $content);
							break;
						case 'site_url':
							$content = str_replace($tags['shortcode'], get_bloginfo('url'), $content);
							break;
						case 'admin_email':
							$content = str_replace($tags['shortcode'], sanitize_email(get_bloginfo('admin_email')), $content);
							break;
						case 'post_id':
							$id = get_the_ID() ? get_the_ID() : '';
							$content = str_replace($tags['shortcode'], $id, $content);
							break;
						case 'post_title':
							$title = get_the_title() ? get_the_title() : '';
							$content = str_replace($tags['shortcode'], $title, $content);
							break;
						case 'post_url':
							global $wp;
							$url     = home_url(add_query_arg($_GET, $wp->request));
							$content = str_replace($tags['shortcode'], $url, $content);
							break;
						case 'post_meta':
							$meta = '';
							if (!empty($tags['params'])) {
								$meta = get_the_ID() ? get_post_meta(get_the_ID(), sanitize_text_field($tags['params']), true)  : '';
							}
							$content = str_replace($tags['shortcode'], $meta, $content);
							break;
						case 'user_id':
							$id = is_user_logged_in() ? get_current_user_id() : '';
							$content = str_replace($tags['shortcode'], $id, $content);
							break;
						case 'user_first':
							$firstname = '';
							if (is_user_logged_in()) {
								$user = wp_get_current_user();
								$firstname = sanitize_text_field($user->user_firstname);
							}
							$content = str_replace($tags['shortcode'], $firstname, $content);
							break;
						case 'user_last':
							$lastname = '';
							if (is_user_logged_in()) {
								$user = wp_get_current_user();
								$lastname = sanitize_text_field($user->user_lastname);
							}
							$content = str_replace($tags['shortcode'], $lastname, $content);
							break;
						case 'user_email':
							$email = '';
							if (is_user_logged_in()) {
								$user = wp_get_current_user();
								$email = sanitize_email($user->user_email);
							}
							$content = str_replace($tags['shortcode'], $email, $content);
							break;
						case 'user_meta':
							$meta = '';
							if (!empty($tags['params'])) {
								$meta = is_user_logged_in() ? get_user_meta(get_current_user_id(), sanitize_text_field($tags['params']), true)  : '';
							}
							$content = str_replace($tags['shortcode'], $meta, $content);
							break;
						case 'url_referer':
							$referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
							$content = str_replace($tags['shortcode'], $referer, $content);
							break;
						case 'url_login':
							$content = str_replace($tags['shortcode'], wp_login_url(), $content);
							break;
						case 'url_logout':
							$content = str_replace($tags['shortcode'], wp_logout_url(), $content);
							break;
						case 'url_register':
							$content = str_replace($tags['shortcode'], wp_registration_url(), $content);
							break;
						case 'url_lost_password':
							$content = str_replace($tags['shortcode'], wp_lostpassword_url(), $content);
							break;
						default:
							$content = str_replace(
								$tags['shortcode'],
								apply_filters('mf_process_merge_tag', '', $content, $group, $tags, $data), # Remove the shortcode as a default behaviour if no value is assigned to it ( allow it to be filtered )
								$content
							);
							break;
					}
				}
			} elseif ($group == 'form') {

				foreach ($match as $tags) {
					switch ($tags['tag']) {
						case 'id':
							$form = mfget_form();
							$content = str_replace($tags['shortcode'], $form->ID, $content);
							break;
						case 'title':
							$form = mfget_form();
							$content = str_replace($tags['shortcode'], $form->title, $content);
							break;
						case 'view_count':
							$form = mfget_form();
							$content = str_replace($tags['shortcode'], $form->view_count, $content);
							break;
						case 'lead_count':
							$form = mfget_form();
							$content = str_replace($tags['shortcode'], $form->lead_count, $content);
							break;
						default:
							$content = str_replace(
								$tags['shortcode'],
								apply_filters('mf_process_merge_tag', '', $content, $group, $tags, $data), # Remove the shortcode as a default behaviour if no value is assigned to it ( allow it to be filtered )
								$content
							);
							break;
					}
				}
			} elseif ($group == 'misc') {
				foreach ($match as $tags) {
					switch ($tags['tag']) {
						case 'get':
							$value = '';
							if (!empty($tags['params'])) {
								$value = mfget($tags['params']);
							}
							$content = str_replace($tags['shortcode'], $value, $content);
							break;
						case 'post':
							$value = '';
							if (!empty($tags['params'])) {
								$value = mfpost($tags['params']);
							}
							$content = str_replace($tags['shortcode'], '', $content);
							break;
						case 'user_ip':
							$ip = mfget_ip_address();
							$content = str_replace($tags['shortcode'], $ip, $content);
							break;
						case 'date':
							$date = '';
							if (!empty($tags['params'])) {
								$date = date($tags['params'], time() + (get_option('gmt_offset') * 3600));
							}
							$content = str_replace($tags['shortcode'], $date, $content);
							break;
						default:
							$content = str_replace(
								$tags['shortcode'],
								apply_filters('mf_process_merge_tag', '', $content, $group, $tags, $data), # Remove the shortcode as a default behaviour if no value is assigned to it ( allow it to be filtered )
								$content
							);
							break;
					}
				}
			} else {
				foreach ($match as $tags) {
					$content = str_replace(
						$tags['shortcode'],
						apply_filters('mf_process_merge_tag', '', $content, $group, $tags, $data), # Remove the shortcode as a default behaviour if no value is assigned to it ( allow it to be filtered )
						$content
					);
				}
			}
		}

		return $content;
	}
	/**
	 * Extract merge tags from the supplied string and organize them in the form of an array
	 *
	 * @since 1.0.0
	 *
	 * @param array $content. The string to extract tags from.
	 * @return array array of the matching groups ( shortcode, tag, params ) organized by merge tags groups.
	 */
	public function extract_tags($content)
	{
		$pattern = $this->get_tag_regex();
		preg_match_all($pattern, $content, $tags);

		if (!empty($tags[0]) && !empty($tags[1]) && !empty($tags[2])) {
			$results = array();
			foreach ($tags[1] as $key => $group) {
				$params = isset($tags[3][$key]) && !empty($tags[3][$key]) ? $tags[3][$key] : '';
				$results[$group][] = array(
					'shortcode' => $tags[0][$key],
					'tag' => $tags[2][$key],
					'params' => $params,
				);
			}

			return $results;
		}

		return false;
	}
	/**
	 * Retrieve the merga tags regular expression for searching.
	 *
	 * The regular expression combines the shortcode tags in the regular expression
	 * in a regex class.
	 *
	 * The regular expression contains 4 different matching groups.
	 *
	 * 1 - The full shortcode
	 * 2 - The merge tag group
	 * 3 - the tag
	 * 4 - the tag parameters if available
	 *
	 * @since 1.0.0
	 *
	 * @param array $tagnames Optional. List of merge tags groups to find. Defaults to all registered groups.
	 * @return string The merga tags search regular expression
	 */
	public function get_tag_regex($tagnames = null)
	{

		if (empty($tagnames)) {
			$tags = $this->get_tags_list();
			$tagnames = array_keys($tags);
		}
		$tagregexp = join('|', array_map('preg_quote', $tagnames));

		return '/\{mf:(' . $tagregexp . ')\s(?:([^\}\/]*?)(?:\=[\'\"](.+?)[\'\"])?)\}/';
	}
	/**
	 * Build the output for `all_fields` merge tag
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 * @return string HTML/TEXT output
	 */
	public function prepare_all_fields_tag($fields)
	{
		ob_start();
?>
		<table cellspacing="0">
			<tbody>
				<?php foreach ($fields as $field) { ?>
					<tr>
						<th valign="top" style="padding: 15px 10px; text-align: left;"><?php echo esc_html($field['label']); ?></th>
						<td valign="top" style="padding: 15px 10px; text-align: left;"><?php echo $field['values']['formatted_short']; ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
<?php

		return ob_get_clean();
	}
}

# Create a helper function that calls an instance of MF_Merge_Tags so to that the same instance can be called anywhere.
function mf_merge_tags()
{
	return MF_Merge_Tags::instance();
}
