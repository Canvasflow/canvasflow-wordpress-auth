<?php 
  class Canvasflow_Auth_Settings {
    public $title = 'Canvasflow Auth';
    public $menu_title = 'Canvasflow Auth';

    public static $plugin_name = 'canvasflow-auth';
    public static $option_group = 'canvasflow-settings-group';
    public static $option_key = 'canvasflow_auth_role';

    public static function init(){
      static $plugin;
      if ( !isset( $plugin ) ){
        $plugin = new Canvasflow_Auth_Settings();
      }
      return $plugin;
    }  

    function __construct() {
      add_action('admin_menu', [$this, 'add_plugin_page']);
      add_action('admin_init', [$this, 'admin_init']);
    }

    public function add_plugin_page() {
      add_options_page( 
        $this->title, // Page Title
        $this->menu_title, // Menu Title
        'manage_options', // Capability 
        self::$plugin_name, // Plugin Name 
        array($this, 'render')
      );
    }

    public function admin_init() {
      $option_group = self::$option_group;
      register_setting($option_group,  self::$option_key);
      add_settings_section(
        $option_group,
        __(''),
        [$this, 'user_role_section'],
        self::$plugin_name
      );
    }

    public function user_role_section(){
      $option_key = self::$option_key;
      $setting = esc_attr(get_option($option_key));
      echo "<select name='".$option_key."' id='user-role' >";
      echo  wp_dropdown_roles($setting);
      echo "</select>";
    }

    public function render() {
      $plugin_name = self::$plugin_name;
      $option_key = self::$option_key;
      $setting_group = self::$option_group;
      $is_woocommerce = in_array('woocommerce/woocommerce.php', 
        apply_filters('active_plugins', get_option( 'active_plugins'))
      );
      $selected_role = get_option($option_key, '');
      
      include( plugin_dir_path( __FILE__ ) . 'views/canvasflow-auth-view.php');
    }

    public static function activate() {
      $option_key = Canvasflow_Auth_Settings::$option_key;
      $available_roles = [];
      $get_all_roles= wp_roles()->roles;
      foreach ( $get_all_roles as $k => $v ) {
        array_push($available_roles, $k);
      }
    
      if ('' === get_option($option_key, '')) {
        if(in_array(AUTH_DEFAULT_ROLE, $available_roles)) {
          add_option($option_key, AUTH_DEFAULT_ROLE);
          return;
        }
        add_option($option_key, array_shift($available_roles));
      }
    }

    public static function uninstall() {
      $option_key = Canvasflow_Auth_Settings::$option_key;
      if ('' === get_option($option_key, '')) {
        delete_option($option_key);
      }
    }
  }
?>