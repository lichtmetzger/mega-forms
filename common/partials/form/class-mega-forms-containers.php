<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/containers
 */

/**
 * Load containers and handle registration
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/form/containers
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MF_Containers
{

	private static $containers = array();

	public static function register($container)
	{
		self::$containers[$container->type] = $container;
	}

	public static function get_single($type, $args = array())
	{

		if (isset(self::$containers[$type])) {

			if (empty($args)) {
				// If nothing should be assigned, return the original object
				$container_object = self::$containers[$type];
			} else {
				// Clone the object before assigning property values 
				$container_object = clone self::$containers[$type];

				// Assign properties if provided
				foreach ($args as $key => $val) {
					$container_object->$key = $val;
				}
			}

			return $container_object;
		}

		return false;
	}

	public static function get($type, $args = array())
	{
		return self::get_single($type, $args);
	}

	/**
	 * Get all Mega Forms containers.
	 *
	 */
	public static function get_containers()
	{
		return self::$containers;
	}

	/**
	 * Extract container types from a from.
	 *
	 */
	public static function get_container_types($form)
	{

		if (!empty($form->containers['data'])) {

			$types = array();

			foreach ($form->containers['data'] as $data) {

				if (!isset($data['type'])) {
					continue;
				}

				if (in_array($data['type'], $types)) {
					continue;
				}

				$types[] = $data['type'];
			}

			return $types;
		}


		return array();
	}
}

require_once(MEGAFORMS_COMMON_PATH . 'partials/form/containers/class-mega-forms-container.php');
foreach (glob(MEGAFORMS_COMMON_PATH . 'partials/form/containers/class-mega-forms-container-*.php') as $container_filename) {
	require_once($container_filename);
}
