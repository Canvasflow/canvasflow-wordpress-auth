<?php
/**
 * Canvasflow Auth
 *
 * @package     CanvasflowAuth
 * @author      Canvasflow
 * @copyright   2024 Canvasflow
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Canvasflow Auth
 * Requires Plugins: WooCommerce
 * Plugin URI:  https://github.com/Canvasflow/canvasflow-wordpress-auth
 * Description: This plugin is an authentication connector for Canvasflow
 * Version:     1.0.0
 * Author:      Canvasflow
 * Author URI:  https://canvasflow.io
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 6.5
 * Requires PHP: 7.4
 */

define('AUTH_DEFAULT_ROLE', 'subscriber');

$dir_path = plugin_dir_path(__FILE__);

// Load required packages
require_once ($dir_path.'includes/class-cfa-settings.php');
require_once ($dir_path.'includes/class-cfa-jwt.php');
require_once ($dir_path.'includes/class-cfa-entitlements.php');
require_once ($dir_path.'includes/class-cfa-token-data.php');
require_once ($dir_path.'includes/class-cfa-controller.php');
require_once ($dir_path.'includes/class-cfa-settings-page.php');

$settings = new CFA_Settings();

CFA_Settings_Page::init($settings);
CFA_Controller::init($settings);

register_activation_hook(__FILE__, 'on_activate');
register_uninstall_hook(__FILE__, 'on_uninstall');

/**
 * Triggers the function when the plugin is activated
 */
function on_activate(){
    register_uninstall_hook(__FILE__, array(
        'CFA_Settings_Page',
        'activate'
    ));
}

/**
 * Triggers the function when the plugin is uninstalled
 */
function on_uninstall(){
    register_uninstall_hook(__FILE__, array(
        'CFA_Settings_Page',
        'uninstall'
    ));
}