<?php

/**
 * Mega Forms Background Tasks Class
 *
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

require_once MEGAFORMS_COMMON_PATH . 'partials/libraries/wp-background-processing/' . 'wp-async-request.php';
require_once MEGAFORMS_COMMON_PATH . 'partials/libraries/wp-background-processing/' . 'wp-background-process.php';

class MF_Tasks extends WP_Background_Process
{

	/**
	 * The single instance of the class.
	 *
	 * @var MF_Tasks
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * @var string
	 */
	protected $action = 'megaforms_tasks';

	/**
	 * Main MF_Tasks Instance.
	 *
	 * Ensures only one instance of MF_Tasks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @see mf_tasks()
	 * @return MF_Tasks - Main instance.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task($item)
	{
		if (is_array($item) && isset($item['type']) && isset($item['data'])) {
			$type = $item['type'];
			$data = $item['data'];
			switch ($type) {
				case 'form_action':
					if (isset($data['action'])) {
						$action = $data['action'];
						$action_object = MF_Actions::get($action['type'], array('action' => $action));
						if ($action_object !== false) {
							// If prepared data is available, assign it to the action property ->prepared_data before execution
							if (isset($data['prepared_data'])) {
								$action_object->prepared_data = $data['prepared_data'];
							}

							$postedData = isset($data['posted_data']) ? $data['posted_data'] : array();
							$action_object->exec($postedData);
						}
					}
					break;
				case 'callback':
					if (count($data) > 2) {
						$callback = $data[0];
						$args = $data[1];
						call_user_func_array($callback, $args);
					} else {
						call_user_func($data);
					}
					break;
			}
		}

		return false;
	}
}

# Create a helper function that calls an instance of MF_Tasks so to that the same instance can be called anywhere.
function mf_tasks()
{
	return MF_Tasks::instance();
}
