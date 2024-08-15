<?php 
function my_admin_menu() {
	add_menu_page( 'My Plugin', 'My Plugin', 'manage_options', 'my-plugin', 'my_options_page' );
  }
  
  function my_admin_init() {
	register_setting( 'my-settings-group', 'my-setting' );
	add_settings_section( 'section-one', 'Role Selection', 'section_one_callback', 'my-plugin' );
	add_settings_field( 'field-one', 'Type', 'field_one_callback', 'my-plugin', 'section-one' );
  }
  
  function section_one_callback() {
   echo 'Choose a role for this plugin:';
  }
  
  function field_one_callback() {
	$setting = esc_attr( get_option( 'my-setting' ) );
	echo "<select name='my-setting' id='role' > 
	<option value='subscriber' " . ($setting==="subscriber" ? "selected" : "") . ">Subscriber</option>
	<option value='customer' " . ($setting==="customer" ? "selected" : "") . ">Customer</option> 
	<option value='contributor' " . ($setting==="contributor" ? "selected" : "") . ">Contributor</option> 
	</select>";
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
  
?>