<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Mega Forms
 * Plugin URI:        http://wpmegaforms.com/
 * Description:       Megaforms is an easy to use, feature rich, drag and drop form builder for WordPress.
 * Version:           1.3.4
 * Author:            Ali Khallad
 * Author URI:        https://alikhallad.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       megaforms
 * Domain Path:       /languages
 * 
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('MEGAFORMS', '1.3.4');
define('MEGAFORMS_DIR_URL', plugin_dir_url(__FILE__));
define('MEGAFORMS_DIR_PATH', plugin_dir_path(__FILE__));
define('MEGAFORMS_INC_PATH', plugin_dir_path(__FILE__) . 'includes/');
define('MEGAFORMS_ADMIN_PATH', plugin_dir_path(__FILE__) . 'admin/');
define('MEGAFORMS_PUBLIC_PATH', plugin_dir_path(__FILE__) . 'public/');
define('MEGAFORMS_COMMON_URL', plugin_dir_url(__FILE__) . 'common/');
define('MEGAFORMS_COMMON_PATH', plugin_dir_path(__FILE__) . 'common/');


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, public-facing and common site hooks.
 */
require MEGAFORMS_INC_PATH . 'class-mega-forms.php';

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */
function run_megaforms()
{

	$plugin = new Mega_Forms(MEGAFORMS);
	$plugin->run();

	/**
	 * Run the pro plugin core class that is used to extend megaforms,
	 * with premuim features.
	 */
	require MEGAFORMS_DIR_PATH . 'pro/class-mega-forms-pro.php';

	$pro_plugin = new Mega_Forms_Pro(MEGAFORMS);
	$pro_plugin->run();
}
run_megaforms();

/**
 * The code that runs during plugin activation.
 */
function activate_Mega_Forms($network_wide = false)
{
	require_once MEGAFORMS_INC_PATH . 'class-mega-forms-activator.php';
	Mega_Forms_Activator::activate($network_wide);
}
register_activation_hook(__FILE__, 'activate_Mega_Forms');

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_Mega_Forms()
{
	require_once MEGAFORMS_INC_PATH . 'class-mega-forms-deactivator.php';
	Mega_Forms_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_Mega_Forms');

/**
 * The code that runs on plugin unistallation.
 */
function uninstall_Mega_Forms()
{
	require_once MEGAFORMS_INC_PATH . 'class-mega-forms-uninstaller.php';
	Mega_Forms_Uninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'uninstall_Mega_Forms');

/**
 * Check if the plugin database was updated and perform any necessary actions.
 */
function update_Mega_Forms()
{
	require_once MEGAFORMS_INC_PATH . 'class-mega-forms-updater.php';
	new Mega_Forms_Updater();
}
update_Mega_Forms();
