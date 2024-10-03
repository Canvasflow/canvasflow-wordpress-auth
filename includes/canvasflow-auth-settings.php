<?php

class Canvasflow_Auth_Settings {
    public $title = "Canvasflow Auth";
    public $menu_title = "Canvasflow Auth";
    public $plugin_name = "";

    public static $option_role_key = 'canvasflow_auth_role';
    public static $option_access_token_ttl_key = 'canvasflow_access_token_ttl';
    public static $option_refresh_token_ttl_key = 'canvasflow_refresh_token_ttl';
    public static $option_group = "canvasflow-settings-group";
    
    // Min TTL is 10 minutes
    const MIN_ACCESS_TOKEN_TTL = 10; 
    
    // Min TTL is 1 day
    const MIN_REFRESH_TOKEN_TTL = 1;

    private $auth_entitlement = null;
    
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
        $this->auth_entitlement = new Canvasflow_Auth_Entitlements();
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
      $option_role_key = self::$option_role_key;
      $setting_group = self::$option_group;
      $selected_role = get_option($option_role_key, "");

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

        // Register settings
        register_setting($option_group, self::$option_role_key);
        register_setting($option_group, self::$option_access_token_ttl_key);
        register_setting($option_group, self::$option_refresh_token_ttl_key);

        add_settings_section(
          $option_group, 
          __("") , 
          [$this, "application_settings_section"], 
          $this->plugin_name
        );
    }

    public function application_settings_section() {
        $this->user_role_section();
        echo "<br/>";
        $this->access_token_ttl_section();
        echo "<br/>";
        $this->refresh_token_ttl_section();
    }

    public function user_role_section() {
        $option_key = self::$option_role_key;
        $setting = esc_attr(get_option($option_key));
        echo "<h3>User Role</h3>";
        echo "<select name='{$option_key}' id='user-role'>";
        echo wp_dropdown_roles($setting);
        echo "</select>";
        echo '<br/>
        <small>
            Match to the same value assigned to <b> Subscriber Default Role</b> 
            in the <a href="/wp-admin/admin.php?page=wc-settings&tab=subscriptions">
            WooCommerce Subscription </a> settings page.
        </small>';
    }

    public function access_token_ttl_section() {
        $option_key = self::$option_access_token_ttl_key;
        $default_ttl = self::MIN_ACCESS_TOKEN_TTL;
        $value = esc_attr(get_option($option_key, $default_ttl));
        echo "<h3>Access Token TTL</h3>";
        echo "<input type='number' 
            inputmode='numeric'
            pattern='\d*'
            step='1'
            name='{$option_key}' 
            value='{$value}' 
            min='{$default_ttl}'
            required>";
        echo '<br/>
        <small>Controls how long the token should be valid.</small>';
    }

    public function refresh_token_ttl_section() {
        $option_key = self::$option_refresh_token_ttl_key;
        $default_ttl = self::MIN_REFRESH_TOKEN_TTL;
        $value = esc_attr(get_option($option_key, $default_ttl));
        echo "<h3>Refresh Token TTL</h3>";
        echo "<input type='number' 
            inputmode='numeric'
            pattern='\d*'
            step='1'
            name='{$option_key}' 
            value='{$value}' 
            min='{$default_ttl}'
            required>";
        echo '<br/>
        <small>Controls how long the refresh token should be valid.</small>';
    }

    public static function activate() {
        $option_role_key = self::$option_role_key;
        $available_roles = [];
        $get_all_roles = wp_roles()->roles;
        foreach ($get_all_roles as $k => $v) {
            array_push($available_roles, $k);
        }

        if ("" === get_option($option_role_key, "")) {
            if (in_array(AUTH_DEFAULT_ROLE, $available_roles)) {
                add_option($option_role_key, AUTH_DEFAULT_ROLE);
                return;
            }
            add_option($option_role_key, array_shift($available_roles));
        }
    }

    public static function uninstall() {
        $option_role_key = self::$option_role_key;
        if ("" === get_option($option_role_key, "")) {
            delete_option($option_role_key);
        }
    }
}
?>
