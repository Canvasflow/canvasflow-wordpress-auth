<?php

class Canvasflow_Auth_Controller extends WP_REST_Controller {
    public $version = '';
    public $namespace = '';
    public $option_role_key = '';
    private $settings = array();

    public static $headers = [
        'Access-Control-Allow-Origin'   => '*',
        'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
    ];

    public $auth_entitlement = null;

    public static function init($role) {
        static $plugin;
        if (!isset($plugin)) {
            $plugin = new Canvasflow_Auth_Controller($role);
        }
        return $plugin;
    }

    function __construct($settings) {
        $this->settings = $settings;
        $plugin_name = $settings['plugin_name'];
        $this->version = $settings['version'];
        $this->option_role_key = $settings['option_role_key'];
        $this->namespace = $plugin_name."/v".$settings['major_version'];
        $this->auth_entitlement = new Canvasflow_Auth_Entitlements();
        add_action('rest_api_init', function () {
            $this->register();
        });
    }

    public function register() {
        register_rest_route($this->namespace, '/health', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array(
                $this,
                'health_check'
            ) ,
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route($this->namespace, '/info', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array(
                $this,
                'info'
            ) ,
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route($this->namespace, '/login', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array(
                $this,
                'auth'
            ),
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route($this->namespace, '/entitlements', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array(
                $this,
                'get_entitlements'
            ) ,
            'permission_callback' => function () {
                return true;
            }
        ));
    }

    /**
     * Health Check endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function health_check($request) {
        $response = new WP_REST_Response;
        $response->set_data([
            "health" => true
        ]);
        $response->set_headers(self::$headers);
        $response->set_status( 200 );
        return $response;
    }

     /**
     * Health Check endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function info($request) {
        $response = new WP_REST_Response;
        $response->set_data([
            "version" => $this->version,
            'user_role' => get_option($this->option_role_key, '')
        ]);
        $response->set_headers(self::$headers);
        $response->set_status( 200 );
        return $response;
    }

    /**
     * Basic Auth endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function auth($request) {
        $response = new WP_REST_Response;
        $response->set_headers(self::$headers);
        $jwt = new Canvasflow_JWT($this->settings);

        $parameters = $request->get_params();
        $username = $parameters['username'];
        $password = $parameters['password'];
        $login_data = [
			'user_login' => $username,
			'user_password' => $password
        ];

        $user = wp_signon($login_data, false);
		$role = get_option($this->option_role_key, "");
        if (is_wp_error($user) || !in_array($role, $user->roles)) {
            $response->set_data([
                "error" => "Invalid credentials"
            ]);
            $response->set_status(403);
            return $response;
        }

        $entitlements = array();
        $subscription_expiration_date = NULL;
        if(function_exists('wcs_user_has_subscription')){
            $data = $this->auth_entitlement->get_user_entitlements($user->ID);
            $entitlements = $data['entitlements'];
            $subscription_expiration_date = $data['expiration_date'];
        }

        $access_token = $jwt->get_access_token(array(
            'id' => $user->ID,
            'entitlements' => $entitlements,
            'subscription_expiration_date' => $subscription_expiration_date
        ));
        
        $refresh_token = $jwt->get_refresh_token(array(
            'id' => $user->ID
        ));

        $value = (int)esc_attr(get_option($this->settings['option_access_token_key'], 10));
        $expires = $value * 60; 
        
        $response->set_data([
            "access_token" => $access_token,
            "refresh_token" => $refresh_token,
            "token_type" => 'bearer',
            "expires" => $expires
        ]);
        
        $response->set_status(200);
        return $response;
    }


    /**
     * Entilements endpoint
     *
     * @param WP_REST_Request
     * @return WP_Error|WP_REST_Response
     */
    public function get_entitlements($request) {
        $response = new WP_REST_Response;
        $response->set_headers(self::$headers);

        $parameters = $request->get_params();
        $user_id = (int)$parameters['user_id'];
        // TODO Validate that the user send the data
        // TODO Validate that the user id actually exist
        
        $data = $this->auth_entitlement->get_user_entitlements($user_id);
        
        $response->set_data([
            "id" => $user_id,
            "entitlements" => $data['entitlements'],
            "expirationDate" => $data['expiration_date']
        ]);
        $response->set_status(200);
        return $response;
    }
}
?>