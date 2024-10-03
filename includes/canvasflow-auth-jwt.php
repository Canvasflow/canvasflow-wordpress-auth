<?php 

class Canvasflow_JWT {
    private $secret = '';
    private $header = array();
    private $iss = '';
    private $aud = '';

    private $option_access_token_key = '';
    private $option_refresh_token_key = '';

    function __construct($settings) {
        $this->header = $this->encode(array(
            'typ' => 'JWT',
            'alg' => 'HS256'
        ));
        $this->secret = 'canvasflow'; // TODO In here we get the value from settings
        $this->iss = $_SERVER['SERVER_NAME']; // TODO In here we get the domain
        $this->aud = 'canvasflow'; // TODO We get this from the api-key value
        $this->option_access_token_key = $settings['option_access_token_key'];
        $this->option_refresh_token_key = $settings['option_refresh_token_key'];
    }   

    public static function validate() {
        // TODO This function validates that the settings are setup
    }

    public function get_access_token($user) {
        // Access Token is stored in minutes
        $value = (int)esc_attr(get_option($this->option_access_token_key, 10));
        
        // Transform minutes to seconds
        $added_time = $value * 60; 

        $now = time();
        $exp = $now + $added_time;

        $header = $this->header;
        $payload = $this->encode(array(
            'iss' => $this->iss,
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

    public function get_refresh_token($user) {
        // Refresh Token is stored in days
        $value = (int)esc_attr(get_option($this->option_refresh_token_key, 1));
        
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

    private function encode($data) {
        return base64_encode(json_encode($data));
    }

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