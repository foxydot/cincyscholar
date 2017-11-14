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

class MSDLabCLHCustom
{
    private $ver;

    function MSDLabCLHCustom()
    {
        $this->__construct();
    }

    function __construct()
    {
        $this->ver = '0.1';
        /*
         * Pull in some stuff from other files
         */
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/event_calendar_widget.php');
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/sidebar_content_support.php');
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_team_cpt.php');
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/msd_resource_cpt.php');

        add_action('widgets_init', @array($this,'widgets_init'));
        if(class_exists('MSDLab_Sidebar_Content_Support')){
            $this->sidebar = new MSDLab_Sidebar_Content_Support();
        }
        if(class_exists('MSDTeamCPT')){
            $this->team_class = new MSDTeamCPT();
        }
        if(class_exists('MSDResourceCPT')){
            $this->media_class = new MSDResourceCPT();
        }
    }

    function widgets_init(){
        register_widget("MSDLab_Events_Calendar_Widget");
    }
}
//instantiate
$msd_custom = new MSDLabCLHCustom();