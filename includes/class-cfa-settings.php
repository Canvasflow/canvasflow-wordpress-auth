<?php
/**
 * Handles the settings for the plugin
 *
 */
class CFA_Settings {
    /**
     * Name of the plugin
     * @var string
     */
    public const plugin_name = 'canvasflow-auth';
    
    /**
     * Major version of the plugin
     * @var integer
     */
    public const major_version = 1;

    /**
     * Minor version of the plugin
     * @var integer
     */
    public const minor_version = 0;

    /**
     * Patch version of the plugin
     * @var integer
     */
    public const patch_version = 0;

    /**
     * Option group for the settings page
     * @var string
     */
    public const option_group = 'cfa_settings_group';

    /**
     * Full version of the plugin
     * @var string
     */
    public $version;
    
    /**
     * Keys for the options in the settings page
     * @var array
     */
    public static $options_keys = array(
        'role' => 'cfa_role',
        'access_token' => 'cfa_access_token_ttl',
        'refresh_token' => 'cfa_refresh_token_ttl',
        'client_id' => 'cfa_client_id',
        'secret' => 'cfa_secret'
    );

    /**
    * Initialize the settings class
    * 
    */
    function __construct() {
        $this->version = implode('.', [
            self::major_version,
            self::minor_version,
            self::patch_version
        ]);        
    }
}
?>