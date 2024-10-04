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
        $this->option_role_key = $settings['options']['role'];
        $this->namespace = $plugin_name.'/v'.$settings['major_version'];
        $this->auth_entitlement = new Canvasflow_Auth_Entitlements();
        add_action('rest_api_init', function () {
            $this->register();
        });
    }

    /**
     * Register the controller endpoints
     *
     */
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

        register_rest_route($this->namespace, '/authorize', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array(
                $this,
                'authorize'
            ),
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route($this->namespace, '/token', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array(
                $this,
                'refresh_token'
            ),
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
            'health' => true
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
            'version' => $this->version
        ]);
        $response->set_headers(self::$headers);
        $response->set_status( 200 );
        return $response;
    }

    /**
     * Authentication Endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function authorize($request) {
        $response = new WP_REST_Response;
        $response->set_headers(self::$headers);
        $jwt = new Canvasflow_JWT($this->settings);

        $r = $this->validate_request($request);
        if($r != NULL) {
            return $r;
        }

        $parameters = $request->get_params();
        $username = $parameters['username'];
        $password = $parameters['password'];
        $login_data = [
			'user_login' => $username,
			'user_password' => $password
        ];

        $user = wp_signon($login_data, false);
        if (is_wp_error($user) ) {
            $response->set_data([
                'error' => 'Invalid credentials',
                'code' => 'INVALID_CREDENTIALS'
            ]);
            $response->set_status(403);
            return $response;
        }

        $r = $this->validate_user($user->ID); 
        if($r != NULL) {
            return $r;
        }

        $token = $this->get_token_from_user($user->ID, $jwt);
        $response->set_data([
            'access_token' => $token->access,
            'refresh_token' => $token->refresh,
            'token_type' => 'bearer',
            'expires' => $token->expires
        ]);
        $response->set_status(200);
        return $response;
    }

    /**
     * Refresh Token Endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function refresh_token($request) {
        $response = new WP_REST_Response;
        $response->set_headers(self::$headers);
        $jwt = new Canvasflow_JWT($this->settings);

        $r = $this->validate_request($request);
        if($r != NULL) {
            return $r;
        }

        $refresh_token = $_GET['refresh_token'];

        if(empty($refresh_token)) {
            $response->set_data([
                'error' => 'Missing "refresh_token" param',
                'code' => 'MISSING_PARAM'
            ]);
            $response->set_status(400);
            return $response;
        }

        $is_valid = $jwt->verify($refresh_token);
        if($is_valid == false) {
            $response->set_data([
                'error' => 'Invalid refresh token',
                'code' => 'INVALID_TOKEN'
            ]);
            $response->set_status(403);
            return $response;
        }

        $payload = $jwt->decode($refresh_token);
        $exp = $payload['exp'];
        if($exp <= time()) {
            $response->set_data([
                'error' => 'Token expired',
                'code' => 'EXPIRED_TOKEN'
            ]);
            $response->set_status(403);
            return $response;
        }

        $sub = $payload['sub'];

        $user = get_user_by('id', $sub );
        $r = $this->validate_user($sub);
        if($r != NULL) {
            return $r;
        }

        $token = $this->get_token_from_user($sub, $jwt);

        $response->set_data([
            'access_token' => $token->access,
            'expires' => $token->expires
        ]);
        
        $response->set_status(200);
        return $response;
    }

     /**
     * Validate Request function
     * 
     * This function validates that the user send all the required 
     * headers and that the plugin installed all the required information
     * to validate the request. If the request is successfull it will 
     * return NULL, otherwise it will return a WP_REST_Response with 
     * the error message
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|NULL
     */
    private function validate_request($request) {
        $response = new WP_REST_Response;
        $response->set_headers(self::$headers);
        $client_id = $request->get_header('X-Canvasflow-App-Key');
        if($client_id == NULL) {
            $response->set_data([
                'error' => 'Missing header "X-Canvasflow-App-Key"',
                'code' => 'MISSING_REQUIRED_HEADER'
            ]);
            $response->set_status(400);
            return $response;
        }

        $client_id_key = $this->settings['options']['client_id'];
        $stored_client_id = get_option($client_id_key, "");
        if($stored_client_id == '') {
            $response->set_data([
                'error' => 'The client id is not set',
                'code' => 'MISSING_CLIENT_ID'
            ]);
            $response->set_status(500);
            return $response;
        }

        if($stored_client_id != $client_id) {
            $response->set_data([
                'error' => 'Invalid client id',
                'code' => 'INVALID_CLIENT_ID'
            ]);
            $response->set_status(409);
            return $response;
        }

        return NULL;
    }

     /**
     * Validate User Function
     * 
     * This function validates that the user exist and have the required
     * roles to access the content.
     *
     * @param integer $user_id Identifier for the user
     * @return WP_REST_Response|NULL
     */
    private function validate_user($user_id) {
        $user = get_user_by('id', $user_id);
        $response = new WP_REST_Response;
        $response->set_headers(self::$headers);
        $role = get_option($this->option_role_key, "");
        if ($user == false) {
            $response->set_data([
                'error' => 'The user does not exist',
                'code' => 'NOT_EXIST_USER'
            ]);
            $response->set_status(403);
            return $response;
        }
        if (!in_array($role, $user->roles)) {
            $response->set_data([
                'error' => 'The user has an invalid or insufficient role',
                'code' => 'INVALID_ROLE'
            ]);
            $response->set_status(403);
            return $response;
        }

        return NULL;
    }

     /**
     * Get the entitlement data from the user
     *
     * @param integer $user_id Identifier for the user
     * @return TokenData
     */
    private function get_token_from_user($user_id, $jwt) {
        $token = new TokenData(
            $user_id,
            $jwt,
            $this->auth_entitlement,
            $this->settings
        );
        $token->build();
        return $token;
    }
}


/**
 * Get token data based on a user
 *
 */

class TokenData {
     /**
     * Stores the access token
     * @var string
     */
    public $access = '';

    /**
     * Stores the refresh token
     * @var string
     */
    public $refresh = '';
    
    /**
     * Stores the amount of time for the access token to expire
     * @var integer
     */
    public $expires = 0;

    /**
     * Handles the user identifier
     * @var integer
     */
    private $user_id;

    /**
     * Handles the features and subscription end date for the user
     * @var array
     */
    private $entitlement;

    /**
     * Stores utility functions for handling tokens
     * @var Canvasflow_JWT
     */
    private $jwt;

    /**
     * Stores the settings for the plugin
     * @var array
     */
    private $settings;

    /**
     * Initialize the token data
     * 
     *
     * @param integer $user_id Identifier for the user
     * @param Canvasflow_JWT $jwt
     * @param Canvasflow_Auth_Entitlements $entitlement
     * @param array $settings Settings for the plugin
     */
    function __construct($user_id, $jwt, $entitlement,  $settings) {
        $this->user_id = $user_id;
        $this->entitlement = $entitlement;
        $this->jwt = $jwt;
        $this->settings = $settings;
    }

    /**
     * Builds the data for the token and stores it in the public attributes
     * 
     */
    function build() {
        $entitlements = NULL;
        if(function_exists('wcs_user_has_subscription')){
            $data = $this->entitlement->get_user_entitlements($user_id);
            $entitlements = [
                'features' => $data['entitlements'],
                'subscription_expiration_date' => $data['expiration_date']
            ];
        }

        $access_token = $this->jwt->get_access_token(array(
            'id' => $this->user_id,
            'entitlements' => $entitlements
        ));
        
        $refresh_token = $this->jwt->get_refresh_token(array(
            'id' => $this->user_id
        ));

        $value = (int)esc_attr(get_option(
            $this->settings['options']['access_token'], 10)
        );

        $this->access = $access_token;
        $this->refresh = $refresh_token;
        $this->expires =  $value * 60;
    }
}
?>