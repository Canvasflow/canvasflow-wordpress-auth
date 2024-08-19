<?php
class Canvasflow_Auth_Settings {
    public $title = "Canvasflow Auth";
    public $menu_title = "Canvasflow Auth";
    public $plugin_name = "";
    

    public static $option_key = 'canvasflow_auth_role';
    public static $option_group = "canvasflow-settings-group";
    
    public static function init($settings) {
        static $plugin;
        if (!isset($plugin)) {
            $plugin = new Canvasflow_Auth_Settings($settings);
        }
        return $plugin;
    }

    function __construct($settings) {
        $this->plugin_name = $settings['plugin_name'];
        add_action("admin_menu", [$this, "add_plugin_page"]);
        add_action("admin_init", [$this, "admin_init"]);
    }

    public function add_plugin_page() {
        add_options_page(
          $this->title, // Page Title
          $this->menu_title, // Menu Title
          "manage_options", // Capability
          $this->plugin_name, // Plugin Name
          [$this, "render"]
        );
    }

    public function render() {
      $plugin_name = $this->plugin_name;
      $option_key = $this->option_key;
      $setting_group = self::$option_group;
      $selected_role = get_option($option_key, "");

      // Check if plugins are active
      $is_woocommerce = is_plugin_active("woocommerce/woocommerce.php");
      $is_woocommerce_subscription = is_plugin_active(
        "woocommerce-subscriptions/woocommerce-subscriptions.php");
      $is_active = array(
        'woocommerce' => $is_woocommerce,
        'woocommerce-subscriptions' =>  $is_woocommerce_subscription
      );

      include plugin_dir_path(__FILE__) . "views/canvasflow-auth-view.php";
    }

    public function admin_init() {
        $option_group = self::$option_group;

        register_setting($option_group, self::$option_key);
        add_settings_section(
          $option_group, 
          __("") , 
          [$this, "user_role_section"], 
          $this->plugin_name
        );
    }

    public function user_role_section() {
        $option_key = $this->option_key;
        $setting = esc_attr(get_option($option_key));
        echo "<select name='" . $option_key . "' id='user-role' >";
        echo wp_dropdown_roles($setting);
        echo "</select>";
    }

    public static function activate() {
        $option_key = self::$option_key;
        $available_roles = [];
        $get_all_roles = wp_roles()->roles;
        foreach ($get_all_roles as $k => $v) {
            array_push($available_roles, $k);
        }

        if ("" === get_option($option_key, "")) {
            if (in_array(AUTH_DEFAULT_ROLE, $available_roles)) {
                add_option($option_key, AUTH_DEFAULT_ROLE);
                return;
            }
            add_option($option_key, array_shift($available_roles));
        }
    }

    public static function uninstall() {
        $option_key = self::$option_key;
        if ("" === get_option($option_key, "")) {
            delete_option($option_key);
        }
    }
}
?>
