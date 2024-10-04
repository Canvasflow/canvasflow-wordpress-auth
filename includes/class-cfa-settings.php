<?php
/**
 * Handles the settings for the plugin
 *
 */
class CFA_Settings {
    public const plugin_name = 'canvasflow-auth';
    public const major_version = 1;
    public const minor_version = 0;
    public const patch_version = 0;
    public const option_group = 'cfa_settings_group';

    public $version;
    
    public static $options_keys = array(
        'role' => 'cfa_role',
        'access_token' => 'cfa_access_token_ttl',
        'refresh_token' => 'cfa_refresh_token_ttl',
        'client_id' => 'cfa_client_id',
        'secret' => 'cfa_secret'
    );

    function __construct() {
        $this->version = implode('.', [
            self::major_version,
            self::minor_version,
            self::patch_version
        ]);        
    }
}
?>