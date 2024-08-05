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
 * Plugin URI:  https://github.com/Canvasflow/canvasflow-wordpress-auth
 * Description: This plugin is an authentication connector for Canvasflow
 * Version:     0.1.0
 * Author:      Canvasflow
 * Author URI:  https://canvasflow.io
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

 function display_hello_world_page() {
  echo 'This is the page for canvasflow auth';
}

function hello_world_admin_menu() {
  add_menu_page(
        'Canvasflow Auth',// page title
        'Canvasflow Auth',// menu title
        'manage_options',// capability
        'canvasflow-auth',// menu slug
        'display_hello_world_page' // callback function
    );
}
add_action('admin_menu', 'hello_world_admin_menu');