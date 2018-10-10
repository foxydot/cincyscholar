<?php
class MSDLAB_Front_End_Nav{

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    public function __construct() {
        add_action('wp_enqueue_scripts',array($this,'fix_up_menu_items'));
    }

    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_Front_End_Nav();
        }

        return self::$instance;

    }

    function fix_up_menu_items(){
        if(!is_user_logged_in()){return false;}
        if(!current_user_can('donor') && !current_user_can('manage_csf')){
            wp_enqueue_script('fix-nav',plugin_dir_url(__DIR__).'/../js/front-end-nav-applicant.js',array('jquery'));
        }
        if(current_user_can('donor')){
            wp_enqueue_script('fix-nav',plugin_dir_url(__DIR__).'/../js/front-end-nav-donor.js',array('jquery'));
        }
    }
}