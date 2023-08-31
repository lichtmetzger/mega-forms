<?php

/**
 * This file is used to load internal and external add-ons.
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/extender
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Extender
{

  private static $field_options = array();
  private static $action_options = array();
  private static $container_options = array();

  /**********************************************************
   ************************* FIELDS *************************
   **********************************************************/

  /**
   * Register a new field.
   *
   */
  public static function register_field($field)
  {
    if ($field instanceof MF_Field) {
      MF_Fields::register($field);
    }
  }
  /**
   * Register a new fields option.
   *
   */
  public static function register_field_option($option)
  {
    if ($option instanceof MF_Field_Option) {
      self::$field_options[$option->type] = $option;
    }
  }
  /**
   * Get single custom field option.
   *
   */
  public static function get_single_field_option($type)
  {
    if (isset(self::$field_options[$type])) {
      return self::$field_options[$type];
    }
    return false;
  }
  /**
   * Get all custom field options.
   *
   */
  public static function get_field_options()
  {
    return self::$field_options;
  }

  /**********************************************************
   ************************ ACTIONS *************************
   **********************************************************/

  /**
   * Register a new action.
   *
   */
  public static function register_action($action)
  {
    if ($action instanceof MF_Action) {
      MF_Actions::register($action);
    }
  }
  /**
   * Register a new action option.
   *
   */
  public static function register_action_option($option)
  {
    if ($option instanceof MF_Action_Option) {
      self::$action_options[$option->type] = $option;
    }
  }
  /**
   * Get single custom action option.
   *
   */
  public static function get_single_action_option($type)
  {
    if (isset(self::$action_options[$type])) {
      return self::$action_options[$type];
    }
    return false;
  }
  /**
   * Get all custom field options.
   *
   */
  public static function get_action_options()
  {
    return self::$action_options;
  }
}


