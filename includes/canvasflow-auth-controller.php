<?php
class Canvasflow_Auth_Controller extends WP_REST_Controller {
    public $version = '1';
    public $namespace = '';
    public $option_key = '';

    public static function init($role) {
        static $plugin;
        if (!isset($plugin))
        {
            $plugin = new Canvasflow_Auth_Controller($role);
        }
        return $plugin;
    }

    function __construct($role) {
        $this->option_key = $role;
        $this->namespace = 'canvasflow-auth/v' . $this->version;
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

        register_rest_route($this->namespace, '/login', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array(
                $this,
                'auth'
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
        return new WP_REST_Response(array(
            "health" => true,
            'role' => get_option($this->option_key, '')
        ) , 200);
    }

    /**
     * Basic Auth endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function auth($request) {
        $parameters = $request->get_params();
        $username = $parameters['username'];
        $password = $parameters['password'];

        $login_data = array();
        $login_data['user_login'] = $username;
        $login_data['user_password'] = $password;

        $user = wp_signon($login_data, false);
        if (is_wp_error($user) || !in_array('subscriber', $user->roles))
        {
            return new WP_REST_Response(array(
                "success" => "Y",
                "error" => "N",
                "response" => array(
                    "login" => "FAIL",
                    "digital_access" => "N"
                )
            ) , 200);
        }

        $date = null;
        //$is_subscription = wcs_user_has_subscription( $user->ID );
        //   if ( $is_subscription )
        //   {
        // 	$subscriptions = wcs_get_users_subscriptions($user->ID );
        // 	  $subscription  = reset( $subscriptions );
        // 	  if ( $subscription->get_status() == 'active' ) {
        // 		$date="fecha";
        // 		}
        return new WP_REST_Response(array(
            "success" => "Y",
            "error" => "N",
            "response" => array(
                "login" => "SUCCESS",
                "email" => $user->user_email,
                "subscription_level" => "registered",
                "digital_access" => "N",
                "expiration_date" => $date
            )
        ) , 200);
    }

}
?>
