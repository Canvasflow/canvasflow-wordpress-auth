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

 require_once(plugin_dir_path( __FILE__ ) . 'includes/canvasflow-auth-controller.php');

 add_action( 'rest_api_init',  function () {
	$controller = new Canvasflow_Auth_Controller();
	$controller->register_routes();
});


 function display_page() {
  echo require_once(plugin_dir_path( __FILE__ ) . 'includes/views/canvasflow-auth-view.php');
}

function hello_world_admin_menu() {
  add_menu_page(
        'Canvasflow Auth',// page title
        'Canvasflow Auth',// menu title
        'manage_options',// capability
        'canvasflow-auth',// menu slug
        'display_page' // callback function
    );
}
add_action('admin_menu', 'hello_world_admin_menu');