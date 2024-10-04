<?php
/**
 * Get token data based on a user
 *
 */
class CFA_Token_Data {
    /**
     * Stores the access token
     * 
     * @var string
     */
    public $access = '';

    /**
     * Stores the refresh token
     *
     * @var string
     */
    public $refresh = '';
   
    /**
     * Stores the amount of time for the access token to expire
     *
     * @var integer
     */
    public $expires = 0;

    /**
     * Handles the user identifier
     *
     * @var integer
     */
    private $user_id;

    /**
     * Handles the features and subscription end date for the user
     *
     * @var CFA_Entitlements
     */
    private $entitlement;

    /**
     * Stores utility functions for handling tokens
     *
     * @var CFA_JWT
     */
    private $jwt;

    /**
     * Initialize the token data
     * 
     * @param integer $user_id Identifier for the user
     * @param CFA_JWT $jwt
     * @param CFA_Entitlements $entitlement
     */
    function __construct($user_id, $jwt, $entitlement) {
        $this->user_id = $user_id;
        $this->entitlement = $entitlement;
        $this->jwt = $jwt;
    }

    /**
     * Builds the data for the token and stores it in the public attributes
     * 
     */
    function build() {
        $entitlements = NULL;
        if(function_exists('wcs_user_has_subscription')){
            $data = $this->entitlement->get_user_entitlements($this->user_id);
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
        $key = CFA_Settings::$options_keys['access_token'];
        $value = (int)esc_attr(get_option($key, 10));

        $this->access = $access_token;
        $this->refresh = $refresh_token;
        $this->expires =  $value * 60;
    }
}
?>