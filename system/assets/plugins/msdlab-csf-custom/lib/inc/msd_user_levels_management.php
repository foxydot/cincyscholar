<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 12/5/17
 * Time: 12:52 PM
 */

if (!class_exists('MSDLab_User_Levels_Management')) {
    class MSDLab_User_Levels_Management {
        //Properties
        var $cpt = 'application';
        var $caps;
        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array();
            foreach($required_files AS $rq){
                if(file_exists(plugin_dir_path(__FILE__).'/'.$rq . '.php')){
                    require_once(plugin_dir_path(__FILE__).'/'.$rq . '.php');
                } else {
                    ts_data(plugin_dir_path(__FILE__).'/'.$rq . '.php does not exisit');
                }
            }
            //Actions
            //add_action('admin_menu', array(&$this,'settings_page'));
            //add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles_and_scripts'));

            //Filters
            add_filter('login_redirect', array(&$this,'welcome_user'), 10, 3);

            //Shortcodes
            //add_shortcode('application', array(&$this,'application_shortcode_handler'));

        }

        function welcome_user($url, $query, $user){
            error_log($url);
            if ( user_can($user->ID,'student') ) {
                error_log('is student');
                //redirect to the welcome page
                $page_id = get_option('csf_settings_student_welcome_page');
                $url = get_permalink($page_id);
            }
            error_log($url);
            return $url;
        }


        function register_user_levels(){
            //Remove WordPress Default Roles that might be confusing to board members (Author, Editor)
            $defaults = array('contributor','author','editor');
            foreach($defaults AS $defjam){
                if( get_role($defjam) ){
                    remove_role( $defjam );
                }
            }
            $caps = new MSDLab_Capabilites;
            //Add Available Roles for CSF
            $subscriber_role = get_role('subscriber');
            foreach($caps->subscriber AS $c) {
                $subscriber_role->add_cap($c);
            }
            add_role('rejection','Student Non-awardee', $caps->rejection);
            add_role('applicant','Student Applicant', $caps->applicant);
            add_role('awardee','Student Awardee', $caps->awardee);
            add_role('renewal','Student Awardee Renewing', $caps->renewal);
            add_role('donor','Donor', $caps->donor);
            add_role('scholarship','Scholarship Committee', $caps->scholarship);
            add_role('csf','CSF Administration', $caps->csf);
        }

        function unregister_user_levels(){
            //Remove Available Roles for CSF
            $roles = array('rejection','applicant','awardee','renewal','donor','scholarship','csf');
            foreach($roles AS $role){
                remove_role($role);
            }
        }


    } //End Class
} //End if class exists statement

if(!class_exists('MSDLab_Capabilites')){
    class MSDLab_Capabilites{
        function __construct(){
            $roles = array('subscriber','rejection','applicant','awardee','renewal','donor','scholarship','csf');
            foreach($roles as $role){
                $this->{$role} = $this->get_my_caps($role);
            }
        }

        function get_my_caps($role){
            if($role == 'csf'){
                return array(
                    'delete_pages',
                    'delete_published_pages',
                    'edit_pages',
                    'edit_published_pages',
                    'publish_pages',
                    'read',
                    'read_private_pages',
                    'read_private_posts',
                    'unfiltered_html',
                    'upload_files',
                    'gravityforms_view_entries',
                    'gravityforms_export_entries',
                    'gravityforms_view_entry_notes',
                    'gravityforms_edit_entry_notes',
                    'gravityforms_polls_results',
                    'gravityforms_quiz_results',
                    'gravityforms_survey_results',
                    'delete_others_pages',
                    'delete_others_posts',
                    'delete_posts',
                    'delete_private_pages',
                    'delete_private_posts',
                    'delete_published_posts',
                    'edit_others_pages',
                    'edit_others_posts',
                    'edit_posts',
                    'edit_private_pages',
                    'edit_private_posts',
                    'edit_published_posts',
                    'manage_categories',
                    'manage_links',
                    'moderate_comments',
                    'publish_posts',
                    'list_users',
                    'remove_users',
                    'install_plugins',
                    'install_themes',
                    'upload_plugins',
                    'upload_themes',
                    'edit_plugins',
                    'edit_themes',
                    'edit_files',
                    'edit_users',
                    'create_users',
                    'promote_users',
                    'delete_users',
                    'unfiltered_html',
                    'activate_plugins',
                    'export',
                    'import',
                    'manage_categories',
                    'manage_links',
                    'manage_options',
                    'moderate_comments',
                    'promote_users',
                    'remove_users',
                    'switch_themes',
                    'upload_files',
                    'customize',
                    'delete_site',
                    'update_core',
                    'update_plugins',
                    'update_themes',
                    'install_plugins',
                    'install_themes',
                    'upload_plugins',
                    'upload_themes',
                    'delete_themes',
                    'delete_plugins',
                    'edit_plugins',
                    'edit_themes',
                    'edit_files',
                    'edit_users',
                    'create_users',
                    'delete_users',
                    'unfiltered_html',
                );
            } elseif($role == 'scholarship'){
                return array();
            } elseif($role == 'donor'){
                return array();
            } else {
                $allcaps = array();
                switch($role){
                    case 'renewal':
                        $allcaps['view_renewal_process'] = true;
                    case 'awardee':
                        $allcaps['view_award'] = true;
                        $allcaps['submit_renewal'] = true;
                    case 'applicant':
                    case 'rejection':
                        $allcaps['view_application_process'] = true;
                    case 'subscriber':
                        $allcaps['submit_application'] = true;
                        $allcaps['student'] = true;
                        $allcaps['read'] = true;
                }
                return $allcaps;
            }
        }
    }
}
