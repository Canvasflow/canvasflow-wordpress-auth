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
 */

define('AUTH_DEFAULT_ROLE', 'subscriber');

$dir_path = plugin_dir_path(__FILE__);
require_once ($dir_path.'includes/canvasflow-auth-jwt.php');
require_once ($dir_path.'includes/canvasflow-auth-entitlements.php');
require_once($dir_path.'includes/canvasflow-auth-controller.php');
require_once ($dir_path.'includes/canvasflow-auth-settings.php');

$major_version = 1;
$plugin_name = 'canvasflow-auth';

$settings = array(
  'major_version' => $major_version,
  'version' => $major_version.".0.0",
  'options' => [
    'role' => Canvasflow_Auth_Settings::$option_role_key,
    'access_token' => Canvasflow_Auth_Settings::$option_access_token_ttl_key,
    'refresh_token' => Canvasflow_Auth_Settings::$option_refresh_token_ttl_key,
    'client_id' => Canvasflow_Auth_Settings::$option_client_id_key,
    'secret_key' => Canvasflow_Auth_Settings::$option_secret_key
  ],
  'plugin_name' => $plugin_name
);

Canvasflow_Auth_Settings::init($settings);
Canvasflow_Auth_Controller::init($settings);

register_activation_hook(__FILE__, 'on_activate');
register_uninstall_hook(__FILE__, 'on_uninstall');

function on_activate(){
    register_uninstall_hook(__FILE__, array(
        'Canvasflow_Auth_Settings',
        'activate'
    ));
}

function on_uninstall(){
    register_uninstall_hook(__FILE__, array(
        'Canvasflow_Auth_Settings',
        'uninstall'
    ));
}