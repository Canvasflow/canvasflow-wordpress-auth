<?php
	class Canvasflow_Auth_Controller extends WP_REST_Controller {
		
		/**
		 * Register the routes for the objects of the controller.
		 */
		public function register_routes() {
            $version = "1";
			register_rest_route( 'canvasflow-auth','/health', array(
					'methods'         => WP_REST_Server::READABLE,
					'callback'        => array( $this, 'health_check' ),
                    'permission_callback' => function() { return true; }
			) );


            register_rest_route( 'canvasflow-auth','/login', array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'auth' ),
                'permission_callback' => function() { return true; }

        ) );
      			
		}


		/**
		 * Health Check endpoint
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function health_check( $request ) {
			return new WP_REST_Response(array("health" => true) , 200 );
		}


        /**
		 * Basic Auth endpoint
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function auth( $request ) {

            $parameters = $request->get_params();
            $username = $parameters['username'];
            $password = $parameters['password'];

			$login_data = array();
			$login_data['user_login'] = $username;
			$login_data['user_password'] =$password;
			
			$user = wp_signon($login_data, false );
			
			if (is_wp_error($user) || !in_array('subscriber', $user->roles)) {
					return new WP_REST_Response(array("success" =>"Y", "error"=>"N","response"=> array("login" => "FAIL", "digital_access"=>"N")) , 200 );
			} 

			$date=null;
			 //$is_subscription = wcs_user_has_subscription( $user->ID );
			//   if ( $is_subscription )
			//   {
			// 	$subscriptions = wcs_get_users_subscriptions($user->ID ); 
			// 	  $subscription  = reset( $subscriptions );
			// 	  if ( $subscription->get_status() == 'active' ) {
			// 		$date="fecha";
			// 		}

				return new WP_REST_Response(array("success" =>  "Y", "error"=>"N","response"=> array("login" => "SUCCESS", "email"=>$user->user_email,"subscription_level"=>"registered", "digital_access"=>"N", "expiration_date"=>$date)) , 200 );
			
	

	
		}
	
	}
?>