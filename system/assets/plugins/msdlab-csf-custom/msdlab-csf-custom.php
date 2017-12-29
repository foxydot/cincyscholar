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
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_csf_management.php');
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_user_levels_management.php');
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/sidebar_content_support.php');

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

        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_csf_conversion_tools.php');
        if(class_exists('MSDLab_CSF_Conversion_Tools')){
            $this->csfconvert = new MSDLab_CSF_Conversion_Tools();
        }

        register_activation_hook(__FILE__, array('MSDLab_User_Levels_Management','register_user_levels'));
        register_deactivation_hook(__FILE__, array('MSDLab_User_Levels_Management','unregister_user_levels'));
    }

}
//instantiate
$msd_custom = new MSDLabClientCustom();