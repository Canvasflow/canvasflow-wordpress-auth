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
 * Version:     0.1.0
 * Author:      Canvasflow
 * Author URI:  https://canvasflow.io
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define( 'AUTH_DEFAULT_ROLE', 'subscriber' );
  
require_once(plugin_dir_path( __FILE__ ) . 'includes/canvasflow-auth-controller.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/canvasflow-auth-settings.php');

$option_key = Canvasflow_Auth_Settings::$option_key;

Canvasflow_Auth_Settings::init();
Canvasflow_Auth_Controller::init($option_key);


register_activation_hook( __FILE__, 'on_activate');
register_uninstall_hook( __FILE__, 'on_uninstall');

function on_activate() {
  register_uninstall_hook( __FILE__, array( 'Canvasflow_Auth_Settings', 'activate' ));
}

function on_uninstall() {
  register_uninstall_hook( __FILE__, array( 'Canvasflow_Auth_Settings', 'uninstall' ));
}