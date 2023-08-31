<?php

/**
 * Load available containers
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/containers/class-mega-forms-container-*.php') as $container_filename) {
    require_once($container_filename);
}

/**
 * Load field options
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/fields/options/class-mega-forms-field-*.php') as $field_option_filename) {
    require_once($field_option_filename);
}
/**
 * Load available fields
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/fields/class-mega-forms-field-*.php') as $field_filename) {
    require_once($field_filename);
}


/**
 * Load action options
 *
 * @since    1.3.1
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/actions/options/class-mega-forms-action-*.php') as $action_option_filename) {
    require_once($action_option_filename);
}

/**
 * Load available actions
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/actions/class-mega-forms-action-*.php') as $action_filename) {
    require_once($action_filename);
}
