<?php
/*
Plugin Name: MSDLab Theme Tools
Description: Custom tools for MSDLab Themes, usually based on Genesis by Studio Press
Author: MSDLAB
Version: 0.0.1
Author URI: http://msdlab.com
*/
if(!class_exists('GitHubPluginUpdater')){
    require_once (plugin_dir_path(__FILE__).'/lib/resource/GitHubPluginUpdater.php');
}

if ( is_admin() ) {
    new GitHubPluginUpdater( __FILE__, 'msdlab', "msdlab-theme-tools" );
}


if(!class_exists('WPAlchemy_MetaBox')){
    if(!include_once (WP_CONTENT_DIR.'/wpalchemy/MetaBox.php'))
    include_once (plugin_dir_path(__FILE__).'/lib/wpalchemy/MetaBox.php');
}
global $wpalchemy_media_access;
if(!class_exists('WPAlchemy_MediaAccess')){
    if(!include_once (WP_CONTENT_DIR.'/wpalchemy/MediaAccess.php'))
    include_once (plugin_dir_path(__FILE__).'/lib/wpalchemy/MediaAccess.php');
}
global $msdlab_theme_tools;

if (!class_exists('MSDLabThemeTools')) {
    class MSDLabThemeTools {
        //Properites
        /**
         * @var string The plugin version
         */
        var $version = '0.0.1';
        
        /**
         * @var string The options string name for this plugin
         */
        var $optionsName = 'msdlab_theme_tools_options';
        
        /**
         * @var string $nonce String used for nonce security
         */
        var $nonce = 'msdlab_theme_tools-update-options';
        
        /**
         * @var string $localizationDomain Domain used for localization
         */
        var $localizationDomain = "msdlab_theme_tools";
        
        /**
         * @var string $pluginurl The path to this plugin
         */
        var $plugin_url = '';
        /**
         * @var string $pluginurlpath The path to this plugin
         */
        var $plugin_path = '';
        
        /**
         * @var array $options Stores the options for this plugin
         */
        var $options = array();
        //Methods
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //"Constants" setup
            $this->plugin_url = plugin_dir_url(__FILE__).'/';
            $this->plugin_path = plugin_dir_path(__FILE__).'/';
            //Initialize the options
            $this->get_options();
            //check requirements
            register_activation_hook(__FILE__, array(&$this,'check_requirements'));
            //get sub-packages
            require_once(plugin_dir_path(__FILE__).'/lib/inc/devtool.php');
            require_once(plugin_dir_path(__FILE__).'/lib/inc/theme_tweaks.php');
            require_once(plugin_dir_path(__FILE__).'/lib/inc/shortcodes.php');
            if(class_exists('MSDLab_Shortcodes')){
                new MSDLab_Shortcodes(array());
            }
            if($this->genesis_test()) {
                require_once(plugin_dir_path(__FILE__) . '/lib/inc/genesis_bootstrap_hooks.php');
                require_once(plugin_dir_path(__FILE__).'/lib/inc/genesis_tweaks.php');
                require_once(plugin_dir_path(__FILE__).'/lib/inc/subtitle_support.php');
            }
        }

        /**
         * @desc Loads the options. Responsible for handling upgrades and default option values.
         * @return array
         */
        function check_options() {
            $options = null;
            if (!$options = get_option($this->optionsName)) {
                // default options for a clean install
                $options = array(
                        'version' => $this->version,
                        'reset' => true
                );
                update_option($this->optionsName, $options);
            }
            else {
                // check for upgrades
                if (isset($options['version'])) {
                    if ($options['version'] < $this->version) {
                        // post v1.0 upgrade logic goes here
                    }
                }
                else {
                    // pre v1.0 updates
                    if (isset($options['admin'])) {
                        unset($options['admin']);
                        $options['version'] = $this->version;
                        $options['reset'] = true;
                        update_option($this->optionsName, $options);
                    }
                }
            }
            return $options;
        }
        
        /**
         * @desc Retrieves the plugin options from the database.
         */
        function get_options() {
            $options = $this->check_options();
            $this->options = $options;
        }
        /**
         * @desc Check to see if requirements are met
         */
        function check_requirements(){
        }

        function genesis_test(){
            $theme_info = wp_get_theme();

            $genesis_flavors = array(
                'genesis',
                'genesis-trunk',
            );

            if ( ! in_array( $theme_info->Template, $genesis_flavors ) ) {
                return false;
            } else {
                return true;
            }
        }
        /***************************/
  } //End Class
} //End if class exists statement

//instantiate
$msd_genesis_toolkit = new MSDLabThemeTools();