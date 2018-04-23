<?php
/*
Plugin Name: MSD Specialty Pages
Description: Framework to create specialty templated pages with custom backends. Currently supports two types of LayerCake design structures.
Author: MSDLAB
Version: 0.2.9
Author URI: http://msdlab.com
*/

if(!class_exists('GitHubPluginUpdater')){
    require_once (plugin_dir_path(__FILE__).'/lib/resource/GitHubPluginUpdater.php');
}

if ( is_admin() ) {
    new GitHubPluginUpdater( __FILE__, 'msdlab', "msd-specialty-pages" );
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
global $msd_custom_pages;

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
if (!class_exists('MSDCustomPages')) {
    class MSDCustomPages {
        //Properites
        /**
         * @var string The plugin version
         */
        var $version = '0.0.1';
        
        /**
         * @var string The options string name for this plugin
         */
        var $optionsName = 'msd_custom_pages_options';
        
        /**
         * @var string $nonce String used for nonce security
         */
        var $nonce = 'msd_custom_pages-update-options';
        
        /**
         * @var string $localizationDomain Domain used for localization
         */
        var $localizationDomain = "msd_custom_pages";
        
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
            //here are some examples to get started with
            if(class_exists('PageTemplater')){
                add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );
            }
            if(class_exists('MSDSimpleSectionedPage')){
                add_action('admin_print_footer_scripts',array('MSDSimpleSectionedPage','info_footer_hook') ,100);     
                add_action('admin_enqueue_scripts',array('MSDSimpleSectionedPage','enqueue_admin')); 
            }
            if(class_exists('MSDSectionedPage')){
                add_action('admin_print_footer_scripts',array('MSDSectionedPage','info_footer_hook') ,100);     
                add_action('admin_enqueue_scripts',array('MSDSectionedPage','enqueue_admin')); 
                add_action( 'init', array( 'MSDSectionedPage', 'add_metaboxes' ) );
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
  } //End Class
} //End if class exists statement

//instantiate
$msd_custom_pages = new MSDCustomPages();

if(!function_exists('get_attachment_id_from_src')){
    function get_attachment_id_from_src ($src) {
      global $wpdb;
      $reg = "/-[0-9]+x[0-9]+?.(jpg|jpeg|png|gif)$/i";
      $src1 = preg_replace($reg,'',$src);
      if($src1 != $src){
          $ext = pathinfo($src, PATHINFO_EXTENSION);
          $src = $src1 . '.' .$ext;
      }
      $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$src'";
      $id = $wpdb->get_var($query);
      return $id;
    }
}
