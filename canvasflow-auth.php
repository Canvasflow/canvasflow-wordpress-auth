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

// function hello_world_admin_menu() {
//   add_menu_page(
//         'Canvasflow Auth',// page title
//         'Canvasflow Auth',// menu title
//         'manage_options',// capability
//         'canvasflow-auth',// menu slug
//         'display_page' // callback function
//     );
// }
// add_action('admin_menu', 'hello_world_admin_menu');

add_action( 'admin_menu', 'my_admin_menu' );
add_action( 'admin_init', 'my_admin_init' );

function my_admin_menu() {
  add_menu_page( 'CF Auth test', 'CF Auth test', 'manage_options', 'my-plugin', 'my_options_page' );
}

function my_admin_init() {
  register_setting( 'my-settings-group', 'my-setting' );
  add_settings_section( 'section-one', 'Role Selection', 'section_one_callback', 'my-plugin' );
  add_settings_field( 'field-one', 'Type', 'field_one_callback', 'my-plugin', 'section-one' );
}

function section_one_callback() {
  display_page();
 echo 'Choose a role for this plugin:';

}

function field_one_callback() {
  $setting = esc_attr( get_option( 'my-setting' ) );
  echo "<select name='my-setting' id='role' >";
  echo  wp_dropdown_roles($setting);
  echo "</select>";

}


function get_role_names() {
  global $wp_roles;
  
  if ( ! isset( $wp_roles ) )
      $wp_roles = new WP_Roles();
  
  return $wp_roles->get_names();
  }


function my_options_page() {
  ?>
  <div class="wrap">
    <h2>Canvasflow Auth</h2>
    <form action="options.php" method="POST">
      <?php settings_fields( 'my-settings-group' ); ?>
      <?php do_settings_sections( 'my-plugin' ); ?>
      <?php submit_button(); ?>
    </form>
  </div>
  
  <?php
 
}

