<?php
/*
Plugin Name: MSD  Bootstrap Tools
Description: Some bootstrap tools
Author: MSDLAB
Version: 0.1.5
Author URI: http://msdlab.com
*/

if(!class_exists('GitHubPluginUpdater')){
    require_once (plugin_dir_path(__FILE__).'/lib/resource/GitHubPluginUpdater.php');
}

if ( is_admin() ) {
    new GitHubPluginUpdater( __FILE__, 'msdlab', "msd-bootstrap-tools" );
}

global $msd_bootstrap_tools;

/*
 * Pull in some stuff from other files
*/
if(!function_exists('requireDir')){
    function requireDir($dir){
        $dh = @opendir($dir);

        if (!$dh) {
            throw new Exception("Cannot open directory $dir");
        } else {
            while($file = readdir($dh)){
                $files[] = $file;
            }
            closedir($dh);
            sort($files); //ensure alpha order
            foreach($files AS $file){
                if ($file != '.' && $file != '..') {
                    $requiredFile = $dir . DIRECTORY_SEPARATOR . $file;
                    if ('.php' === substr($file, strlen($file) - 4)) {
                        require_once $requiredFile;
                    } elseif (is_dir($requiredFile)) {
                        requireDir($requiredFile);
                    }
                }
            }
        }
        unset($dh, $dir, $file, $requiredFile);
    }
}
if (!class_exists('MSDBootstrapTools')) {
    class MSDBootstrapTools {
        //Properites
        /**
         * @var string The plugin version
         */
        var $version = '0.1.3';
        
        /**
         * @var string The options string name for this plugin
         */
        var $optionsName = 'msd_bootstrap_tools_options';
        
        /**
         * @var string $nonce String used for nonce security
         */
        var $nonce = 'msd_bootstrap_tools-update-options';
        
        /**
         * @var string $localizationDomain Domain used for localization
         */
        var $localizationDomain = "msd_bootstrap_tools";
        
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
            requireDir(plugin_dir_path(__FILE__).'/lib/inc');
            add_action( 'wp_enqueue_scripts', array( &$this, 'maybe_load_bootstrap' ), 30 );
            //here are some examples to get started with
            if(class_exists('MSDBootstrapShortcodes')){
                add_action( 'plugins_loaded', array( 'MSDBootstrapShortcodes', 'get_instance' ) );
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
        /***************************/
        
        function maybe_load_bootstrap(){
            if(!wp_script_is( 'bootstrap-jquery', $list = 'enqueued' ) && !wp_script_is( 'bootstrap', $list = 'enqueued' )){
                wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js',array('jquery'));
            }
            if(!wp_style_is( 'bootstrap-style', $list = 'enqueued' ) && !wp_style_is( 'bootstrap', $list = 'enqueued' )){
                wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css');
            }
        }
        
        
  } //End Class
} //End if class exists statement

//instantiate
$msd_bootstrap_tools = new MSDBootstrapTools();