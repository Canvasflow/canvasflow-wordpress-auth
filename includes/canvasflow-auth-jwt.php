<?php 

class Canvasflow_JWT {
    private $secret = '';
    private $header = array();
    private $iss = '';
    private $aud = '';
    private $options = array();

    function __construct($settings) {
        $this->header = $this->encode(array(
            'typ' => 'JWT',
            'alg' => 'HS256'
        ));
        $this->iss = $_SERVER['SERVER_NAME'];
        
        $options = $settings['options'];
        $this->options = $options;

        $secret = esc_attr(get_option($options['secret_key'], ''));
        $aud = esc_attr(get_option($options['client_id'], ''));

        $this->secret = $secret;
        $this->aud = $aud;
    }   

    public function get_access_token($user) {
        // Access Token is stored in minutes
        $key = $this->options['access_token'];
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

    public function get_refresh_token($user) {
        // Refresh Token is stored in days
        $key = $this->options['refresh_token'];
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

    public function decode($token) {
        list($header, $payload, $signature) = explode('.', $token);
        return json_decode(base64_decode($payload), true);
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