<?php
/*
Plugin Name: MSDLab Custom Client Functions
Description: Custom functions for this site.
Version: 0.1
Author: MSDLab
Author URI: http://msdlab.com/
License: GPL v2
*/

if(!class_exists('WPAlchemy_MetaBox')){
    if(!include_once (WP_CONTENT_DIR.'/wpalchemy/MetaBox.php'))
        include_once (plugin_dir_path(__FILE__).'/lib/wpalchemy/MetaBox.php');
}
global $wpalchemy_media_access;
if(!class_exists('WPAlchemy_MediaAccess')){
    if(!include_once (WP_CONTENT_DIR.'/wpalchemy/MediaAccess.php'))
        include_once (plugin_dir_path(__FILE__).'/lib/wpalchemy/MediaAccess.php');
}
$wpalchemy_media_access = new WPAlchemy_MediaAccess();
global $msd_custom;

class MSDLabClientCustom
{
    private $ver;

    function MSDLabClientCustom()
    {
        $this->__construct();
    }

    function __construct()
    {
        $this->ver = '0.1';
        /*
         * Pull in some stuff from other files
         */
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_csf_management.php'); // main program management hook
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_user_levels_management.php'); //handles the user levels and some of the switching
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/sidebar_content_support.php'); //
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/gravity-forms.php'); //some custom hooks for the donate and centennial bit
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/force-password-change.php'); //this was specific for the transfered renewal students. likely not needed for future use


        //add_action('widgets_init', @array($this,'widgets_init'));
        if(class_exists('MSDLab_Sidebar_Content_Support')){
            $this->sidebar = new MSDLab_Sidebar_Content_Support();
        }
        if(class_exists('MSDLab_User_Levels_Management')){
            $this->csfusers = new MSDLab_User_Levels_Management();
        }
        if(class_exists('MSDLab_CSF_Management')){
            $this->csfmanage = new MSDLab_CSF_Management();
        }

        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_csf_conversion_tools.php'); //this is specifically for handling migration and changes to the DB in production
        if(class_exists('MSDLab_CSF_Conversion_Tools')){
            $this->csfconvert = new MSDLab_CSF_Conversion_Tools();
        }

        register_activation_hook(__FILE__, array('MSDLab_User_Levels_Management','register_user_levels'));
        register_deactivation_hook(__FILE__, array('MSDLab_User_Levels_Management','unregister_user_levels'));
    }
    //TODO: break out Super admin tools, Admin tools, application stuff.
}
//instantiate
$msd_custom = new MSDLabClientCustom();