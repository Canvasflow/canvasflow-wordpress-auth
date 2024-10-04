<?php
/**
 * Handles the settings for the plugin
 *
 */
class CFA_Settings_Page {
    /**
     * Stores the default TTL for access token (minutes)
     * @var number
     */
    public const MIN_ACCESS_TOKEN_TTL = 10; 
    
    /**
     * Stores the default TTL for refresh token (day)
     * @var number
     */
    public const MIN_REFRESH_TOKEN_TTL = 1;

    /**
     * Stores the key/values for the options stored in settings 
     * @var array
     */
    private $keys;
    
    /**
     * Name of the plugin
     * @var string
     */
    public $plugin_name;

    /**
     * Title of the settings page
     * @var string
     */
    public static $title = "Canvasflow Auth";

    /**
     * Title of the menu page
     * @var string
     */
    public static $menu_title = "Canvasflow Auth";

    /**
     * Stores the plugin settings
     * @var CFA_Settings
     */
    private $settings;
    
    /**
     * Initializer for the class
     * @param CFA_Settings $settings
     * @return CFA_Settings_Page
     */
    public static function init($settings) {
        static $plugin;
        if (!isset($plugin)) {
            $plugin = new CFA_Settings_Page($settings);
        }
        return $plugin;
    }

    /**
     * Initializer the settings page
     * @param CFA_Settings $settings
     */
    function __construct($settings) {
        $this->plugin_name = $settings::plugin_name;
        add_action("admin_menu", [$this, "add_plugin_page"]);
        add_action("admin_init", [$this, "admin_init"]);
        $this->settings = $settings;
        $this->keys = CFA_Settings::$options_keys;
    }

    /**
     * Adds the plugin page to the menu
     * 
     */
    public function add_plugin_page() {
        add_options_page(
          self::$title, // Page Title
          self::$menu_title, // Menu Title
          "manage_options", // Capability
          $this->plugin_name, // Plugin Name
          [$this, "render"]
        );
    }

    /**
     * Add the page to the settings section
     * 
     */
    public function admin_init() {
        $option_group = $this->settings::option_group;

        // Register settings
        register_setting($option_group, $this->keys['role']);
        register_setting($option_group, $this->keys['access_token']);
        register_setting($option_group, $this->keys['refresh_token']);
        register_setting($option_group, $this->keys['client_id']);
        register_setting($option_group, $this->keys['secret']);

        add_settings_section(
          $option_group, 
          __("") , 
          [$this, "display_application_settings_page"], 
          $this->plugin_name
        );
    }

    /**
     * Renders the page for the setting section
     * 
     */
    public function render() {
      $plugin_name = $this->plugin_name;
      $option_role_key = $this->keys['role'];
      $setting_group = $this->settings::option_group;
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

    /**
     * Prints the html for the page
     * 
     */
    public function display_application_settings_page() {
        $this->display_user_role_section();
        echo "<br/>";
        echo "<h3>Token Configuration</h3>";
        $this->access_token_ttl_section();
        $this->refresh_token_ttl_section();
        echo "<br/>";
        echo "<h3>App Configuration</h3>";
        $this->client_id_section();
        $this->secret_key_section();
    }

    /**
     * Displays the user role selection
     * 
     */
    public function display_user_role_section() {
        $key = CFA_Settings::$options_keys['role'];
        $setting = esc_attr(get_option($key));
        echo "<h3>User Role</h3>";
        echo "<select name='{$key}' id='user-role'>";
        echo wp_dropdown_roles($setting);
        echo "</select>";
        echo '<br/>
        <small>
            Match to the same value assigned to <b> Subscriber Default Role</b> 
            in the <a href="/wp-admin/admin.php?page=wc-settings&tab=subscriptions">
            WooCommerce Subscription </a> settings page.
        </small>';
    }

    /**
     * Display the access token ttl section
     * 
     */
    public function access_token_ttl_section() {
        $key = CFA_Settings::$options_keys['access_token'];
        $default_ttl = self::MIN_ACCESS_TOKEN_TTL;
        $value = esc_attr(get_option($key, $default_ttl));
        echo "<h4>Access Token TTL</h4>";
        echo "<input type='number' 
            inputmode='numeric'
            pattern='\d*'
            step='1'
            name='{$key}' 
            value='{$value}' 
            min='{$default_ttl}'
            required>";
        echo '<br/>
        <small>Controls how long the token should be valid. 
        <em>(In minutes)</em></small>';
    }

    /**
     * Display the refresh token ttl section
     * 
     */
    public function refresh_token_ttl_section() {
        $key = CFA_Settings::$options_keys['refresh_token'];
        $default_ttl = self::MIN_REFRESH_TOKEN_TTL;
        $value = esc_attr(get_option($key, $default_ttl));
        echo "<h4>Refresh Token TTL</h4>";
        echo "<input type='number' 
            inputmode='numeric'
            pattern='\d*'
            step='1'
            name='{$key}' 
            value='{$value}' 
            min='{$default_ttl}'
            required>";
        echo '<br/>
        <small>Controls how long the refresh token should be valid.
        <em>(In days)</em></small>';
    }

    /**
     * Display the canvasflow client id section
     * 
     */
    public function client_id_section() {
        $key = CFA_Settings::$options_keys['client_id'];
        $value = esc_attr(get_option($key, ''));
        echo "<h4>Client Id</h4>";
        echo "<input type='text' 
            pattern='^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
            name='{$key}' 
            value='{$value}'
            required>";
        echo '<br/>
        <small>Client Id provided by Canvasflow</small>';
    }

    /**
     * Display the canvasflow secret key section
     * 
     */
    public function secret_key_section() {
        $key = CFA_Settings::$options_keys['secret'];
        $value = esc_attr(get_option($key, ''));
        echo "<h4>Secret Key</h4>";
        echo "<input type='text' 
            minlength='32'
            name='{$key}' 
            value='{$value}'
            required>";
        echo '<br/>
        <small>Secret key provided by Canvasflow</small>';
    }

    /**
     * Stablish default values when the page gets activated
     * 
     */
    public static function activate() {
        $key = CFA_Settings::$options_keys['role'];
        $available_roles = [];
        $get_all_roles = wp_roles()->roles;
        foreach ($get_all_roles as $k => $v) {
            array_push($available_roles, $k);
        }

        if ("" === get_option($key, "")) {
            if (in_array(AUTH_DEFAULT_ROLE, $available_roles)) {
                add_option($key, AUTH_DEFAULT_ROLE);
                return;
            }
            add_option($key, array_shift($available_roles));
        }
    }

    /**
     * Reset all options key when the page is uninstalled
     * 
     */
    public static function uninstall() {
        foreach (CFA_Settings::$options_keys as $key => $value) {
            if ("" === get_option($key, "")) {
                delete_option($key);
            }
        }
    }
}
?>