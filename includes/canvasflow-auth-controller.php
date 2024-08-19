<?php
class Canvasflow_Auth_Controller extends WP_REST_Controller {
    public $version = '';
    public $namespace = '';
    public $option_key = '';

    public static function init($role) {
        static $plugin;
        if (!isset($plugin)) {
            $plugin = new Canvasflow_Auth_Controller($role);
        }
        return $plugin;
    }

    function __construct($settings) {
        $plugin_name = $settings['plugin_name'];
        $this->version = $settings['version'];
        $this->option_key = $settings['option_key'];
        $this->namespace = $plugin_name."/v".$settings['major_version'];
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
            "health" => true
        ) , 200);
    }

     /**
     * Health Check endpoint
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function info($request) {
        return new WP_REST_Response(array(
            "version" => $this->version,
            'user_role' => get_option($this->option_key, '')
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
		// TODO Check if the params are empty don't even process


        $login_data = array(
			'user_login' => $username,
			'user_password' => $password
		);

        $user = wp_signon($login_data, false);
		$role = get_option($this->option_key, "");
        if (is_wp_error($user) || !in_array($role, $user->roles)) {
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
        $digital_access = "N";
        $raw_date=new Datetime();
        $is_subscription = wcs_user_has_subscription( $user->ID );
        if ( $is_subscription )
        {
            $subscriptions = wcs_get_users_subscriptions( $user->ID ); 
            if ( count( $subscriptions ) > 0 ) {
                foreach ( $subscriptions as $sub_id => $subscription ) {
                    if ( $subscription->get_status() == 'active' ) {
                      $sub_info =wcs_get_subscription($sub_id );
                      $end_date = $subscription->get_date('end');
                      $check_date = new DateTime($end_date);
                      if($check_date > $raw_date){
                        $raw_date = new DateTime($end_date);
                        $date = $raw_date->format(DateTime::ATOM);
                        $digital_access = "Y";
                      }
                    }
                }    
            }
        }
        return new WP_REST_Response(array(
            "success" => "Y",
            "error" => "N",
            "response" => array(
                "login" => "SUCCESS",
                "email" => $user->user_email,
                "subscription_level" => "registered",
                "digital_access" => $digital_access,
                "expiration_date" => $date
            )
        ) , 200);
    }
}
?>
