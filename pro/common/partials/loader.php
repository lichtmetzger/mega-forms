<?php

/**
 * Require available containers
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/containers/class-mega-forms-container-*.php') as $container_filename) {
    require_once($container_filename);
}

/**
 * Require field options
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/fields/options/class-mega-forms-field-*.php') as $field_option_filename) {
    require_once($field_option_filename);
}
/**
 * Require available fields
 *
 * @since    1.0.6
 */
foreach (glob(plugin_dir_path(__FILE__) . 'form/fields/class-mega-forms-field-*.php') as $field_filename) {
    require_once($field_filename);
}
