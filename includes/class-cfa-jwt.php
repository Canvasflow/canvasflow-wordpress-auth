<?php 
/**
 * Control data related to JWT Tokens
 *
 */
class CFA_JWT {
    /**
     * Secret stored in settings
     * 
     * @var string
     */
    private $secret;

    /**
     * Header for the token
     * 
     * @var string
     */
    private $header;

    /**
     * Issuer for the token
     * 
     * @var string
     */
    private $iss;

    /**
     * Audience for the token
     * 
     * @var string
     */
    private $aud;

    /**
     * Options for the plugin
     * 
     * @var array
     */
    private $keys;

    /**
     * Initialize the JWT
     *
     */
    function __construct() {
        $this->header = $this->encode(array(
            'typ' => 'JWT',
            'alg' => 'HS256'
        ));
        $this->iss = $_SERVER['SERVER_NAME'];

        $keys = CFA_Settings::$options_keys;
        
        $secret = esc_attr(get_option($keys['secret'], ''));
        $aud = esc_attr(get_option($keys['client_id'], ''));

        $this->keys = $keys;
        $this->secret = $secret;
        $this->aud = $aud;
    }   

    /**
     * Get access token from the user
     * 
     * @param array $user
     * @return string
     */
    public function get_access_token($user) {
        // Access Token is stored in minutes
        $key = $this->keys['access_token'];
        $value = (int)esc_attr(get_option($key, 10));
        
        // Transform minutes to seconds
        $added_time = $value * 60; 

        $now = time();
        $exp = $now + $added_time;

        $header = $this->header;
        $payload = $this->encode(array(
            'iss' => $this->iss,
            'amr' => 'wordpress',
            'exp' => $exp,
            'sub' => "{$user['id']}",
            'aud' => $this->aud,
            'iat' => $now,
            'entitlements' => $user['entitlements'],
            'scope' => 'subscriptions'
        ));
        $signature = $this->get_signature($payload);
        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Get refresh token from the user
     * 
     * @param array $user
     * @return string
     */
    public function get_refresh_token($user) {
        // Refresh Token is stored in days
        $key = $this->keys['refresh_token'];
        $value = (int)esc_attr(get_option($key, 1));
        
        // Transform days to seconds
        $added_time = $value * 24 * 60 * 60;
        
        $now = time();
        $exp = $now + $added_time;

        $header = $this->header;
        $payload = $this->encode(array(         
            'amr' => 'wordpress',
            'exp' => $exp,
            'iat' => $now,
            'iss' => $this->iss,
            'sub' => "{$user['id']}"
        ));
        $signature = $this->get_signature($payload);
        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Validates if the token is valid
     * 
     * @param string $token
     * @return boolean
     */
    public function verify($token) {
        list($header, $payload, $signature) = explode('.', $token);

        $hash = base64_encode(hash_hmac(
            'sha256', 
            "{$header}.{$payload}", 
            $this->secret,
            true
        ));

        return $hash == $signature;
    }

    /**
     * Decode data from the token
     * 
     * @param string $token
     * @return array
     */
    public function decode($token) {
        list($header, $payload, $signature) = explode('.', $token);
        return json_decode(base64_decode($payload), true);
    }

    /**
     * Enconde data into a base64 json string 
     * 
     * @param array $data
     * @return string
     */
    private function encode($data) {
        return base64_encode(json_encode($data));
    }

    /**
     * Get signature for the payload
     * 
     * @param string $payload Data in base64 format
     * @return string
     */
    private function get_signature($payload) {
        return base64_encode(hash_hmac(
            'sha256', 
            "{$this->header}.{$payload}", 
            $this->secret, 
            true
        ));
    }
}
?>