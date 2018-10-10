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
        require_once(plugin_dir_path(__FILE__) . 'lib/inc/front-end-nav.php'); //this was specific for the transfered renewal students. likely not needed for future use


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
        if(class_exists('MSDLAB_Front_End_Nav')){
            MSDLAB_Front_End_Nav::get_instance();
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


if(!function_exists('sanitize_with_underscores')) {
    /**
     * Sanitizes a title, replacing whitespace and a few other characters with dashes.
     *
     * Limits the output to alphanumeric characters, underscore (_) and dash (-).
     * Whitespace becomes a dash.
     *
     * @since 1.2.0
     *
     * @param string $title The title to be sanitized.
     * @param string $raw_title Optional. Not used.
     * @param string $context Optional. The operation for which the string is sanitized.
     * @return string The sanitized title.
     */
    function sanitize_with_underscores($title, $raw_title = '', $context = 'display')
    {
        $title = strip_tags($title);
        // Preserve escaped octets.
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        // Remove percent signs that are not part of an octet.
        $title = str_replace('%', '', $title);
        // Restore octets.
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

        if (seems_utf8($title)) {
            if (function_exists('mb_strtolower')) {
                $title = mb_strtolower($title, 'UTF-8');
            }
            $title = utf8_uri_encode($title, 200);
        }

        $title = strtolower($title);

        if ('save' == $context) {
            // Convert nbsp, ndash and mdash to hyphens
            $title = str_replace(array('%c2%a0', '%e2%80%93', '%e2%80%94'), '_', $title);
            // Convert nbsp, ndash and mdash HTML entities to hyphens
            $title = str_replace(array('&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;'), '_', $title);
            // Convert forward slash to hyphen
            $title = str_replace('/', '_', $title);

            // Strip these characters entirely
            $title = str_replace(array(
                // iexcl and iquest
                '%c2%a1', '%c2%bf',
                // angle quotes
                '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
                // curly quotes
                '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
                '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
                // copy, reg, deg, hellip and trade
                '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
                // acute accents
                '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
                // grave accent, macron, caron
                '%cc%80', '%cc%84', '%cc%8c',
            ), '', $title);

            // Convert times to x
            $title = str_replace('%c3%97', 'x', $title);
        }

        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        $title = str_replace('.', '_', $title);

        $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '_', $title);
        $title = preg_replace('|-+|', '_', $title);
        $title = trim($title, '_');

        return $title;
    }
}