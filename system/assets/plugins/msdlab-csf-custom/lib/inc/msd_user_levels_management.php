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
            add_action('profile_update',array(&$this,'msdlab_on_user_save'), 10, 2);


            //Filters
            add_filter('login_redirect', array(&$this,'welcome_user'), 10, 3);

            //Shortcodes
            //add_shortcode('application', array(&$this,'application_shortcode_handler'));

        }

        function welcome_user($url, $query, $user){
            //error_log($url);
            if ( user_can($user->ID,'student') ) {
               // error_log('is student');
                //redirect to the welcome page
                $page_id = get_option('csf_settings_student_welcome_page');
                $url = get_permalink($page_id);
            }
           // error_log($url);
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
            foreach($caps->subscriber AS $k => $c) {
                $subscriber_role->add_cap($k);
            }
            $administrator_role = get_role('administrator');
            foreach($caps->administrator AS $k => $c) {
                $administrator_role->add_cap($k);
            }
            add_role('rejection','Student Non-awardee', $caps->rejection);
            add_role('applicant','Student Applicant', $caps->applicant);
            add_role('awardee','Student Awardee', $caps->awardee);
            add_role('nonrenewable','Non-Renewable Awardee', $caps->nonrenewable);
            add_role('renewal','Student Awardee Renewing', $caps->renewal);
            add_role('donor','Donor', $caps->donor);
            add_role('scholarship','Scholarship Committee', $caps->scholarship);
            add_role('csf','CSF Administration', $caps->csf);
        }

        function unregister_user_levels(){
            //Remove Available Roles for CSF
            $roles = array('rejection','applicant','nonrenewable','awardee','renewal','donor','scholarship','csf');
            foreach($roles AS $role){
                remove_role($role);
            }
        }

        function msdlab_on_user_save($user_id, $old_user_data){
            global $wpdb;
            $new_role = $_POST['role'];
            $old_role = $old_user_data->roles[0];
            if($new_role == $old_role){
                return;
            }
            if(($old_role == 'subscriber' && $new_role == 'awardee') || ($old_role == 'applicant' && $new_role == 'awardee')){ //user is can renew
                $user = get_user_by('ID',$user_id); //get the user data for the replacements
                //check for an application by this userid
                $sql = "SELECT * FROM applicant WHERE applicant.UserId = ".$user_id;
                if($row = $wpdb->get_row($sql)){return;}//return if found
                //check for an application by email
                $sql = "SELECT * FROM applicant WHERE applicant.Email = ".$user->user_email;
                if($row = $wpdb->get_row($sql)){
                    //change userid and return
                    $sql = "UPDATE applicant SET applicant.UserId = ".$user_id." WHERE applicant.Email = ".$user->user_email;
                    if($wpdb->query($sql)){
                        return;
                    }
                }
                //create application and return
                $sql = 'INSERT INTO applicant SET applicant.ApplicationDateTime = "'.date("Y-m-d h:i:s").'", applicant.UserId = "'.$user_id.'", applicant.Email = "'.$user->user_email.'", applicant.FirstName = "'.$user->first_name.'", applicant.MiddleInitial = "", applicant.LastName = "'.$user->last_name.'", applicant.Last4SSN = "0000", applicant.DateOfBirth = "'.date("Y-m-d h:i:s").'", applicant.Address1 = "Unknown", applicant.Address2 = "", applicant.City = "Unknown", applicant.StateId = "OH", applicant.CountyId = "24", applicant.ZipCode = "00000", applicant.CellPhone = "unknown", applicant.AlternativePhone = "", applicant.EthnicityId = "24", applicant.StudentId = "00000000";';
                if($wpdb->query($sql)){
                    return;
                }
            }
        }

    } //End Class
} //End if class exists statement

if(!class_exists('MSDLab_Capabilites')){
    class MSDLab_Capabilites{
        function __construct(){
            $roles = array('subscriber','rejection','applicant','nonrenewable','awardee','renewal','donor','scholarship','csf','administrator');
            foreach($roles as $role){
                $this->{$role} = $this->get_my_caps($role);
            }
        }

        function get_my_caps($role){
            if($role == 'administrator'){
                return array(
                    'review_application' => 1,
                    'manage_csf' => 1,
                    'view_application_process' => 1,
                    'view_csf_reports' => 1,
                    'view_recommended_students' => 1,
                );
            }
            if($role == 'csf'){
                return array(
                    'edit_users' => 1,
                    'edit_files' => 1,
                    'manage_options' => 1,
                    'moderate_comments' => 1,
                    'manage_categories' => 1,
                    'manage_links' => 1,
                    'upload_files' => 1,
                    'unfiltered_html' => 1,
                    'edit_posts' => 1,
                    'edit_others_posts' => 1,
                    'edit_published_posts' => 1,
                    'publish_posts' => 1,
                    'edit_pages' => 1,
                    'read' => 1,
                    'edit_others_pages' => 1,
                    'edit_published_pages' => 1,
                    'publish_pages' => 1,
                    'delete_pages' => 1,
                    'delete_others_pages' => 1,
                    'delete_published_pages' => 1,
                    'delete_posts' => 1,
                    'delete_others_posts' => 1,
                    'delete_published_posts' => 1,
                    'delete_private_posts' => 1,
                    'edit_private_posts' => 1,
                    'read_private_posts' => 1,
                    'delete_private_pages' => 1,
                    'edit_private_pages' => 1,
                    'read_private_pages' => 1,
                    'delete_users' => 1,
                    'create_users' => 1,
                    'edit_dashboard' => 1,
                    'update_plugins' => 1,
                    'update_themes' => 1,
                    'update_core' => 1,
                    'list_users' => 1,
                    'remove_users' => 1,
                    'promote_users' => 1,
                    'export' => 1,
                    'edit_theme_options' => 1,
                    'wpseo_bulk_edit' => 1,
                    'read_private_tribe_events' => 1,
                    'edit_tribe_events' => 1,
                    'edit_others_tribe_events' => 1,
                    'edit_private_tribe_events' => 1,
                    'edit_published_tribe_events' => 1,
                    'delete_tribe_events' => 1,
                    'delete_others_tribe_events' => 1,
                    'delete_private_tribe_events' => 1,
                    'delete_published_tribe_events' => 1,
                    'publish_tribe_events' => 1,
                    'read_private_tribe_venues' => 1,
                    'edit_tribe_venues' => 1,
                    'edit_others_tribe_venues' => 1,
                    'edit_private_tribe_venues' => 1,
                    'edit_published_tribe_venues' => 1,
                    'delete_tribe_venues' => 1,
                    'delete_others_tribe_venues' => 1,
                    'delete_private_tribe_venues' => 1,
                    'delete_published_tribe_venues' => 1,
                    'publish_tribe_venues' => 1,
                    'read_private_tribe_organizers' => 1,
                    'edit_tribe_organizers' => 1,
                    'edit_others_tribe_organizers' => 1,
                    'edit_private_tribe_organizers' => 1,
                    'edit_published_tribe_organizers' => 1,
                    'delete_tribe_organizers' => 1,
                    'delete_others_tribe_organizers' => 1,
                    'delete_private_tribe_organizers' => 1,
                    'delete_published_tribe_organizers' => 1,
                    'publish_tribe_organizers' => 1,
                    'wpseo_manage_options' => 1,
                    'review_application' => 1,
                    'manage_csf' => 1,
                    'view_application_process' => 1,
                    'view_csf_reports' => 1,
                    'view_recommended_students' => 1,
                );
            } elseif($role == 'scholarship'){
                return array(
                    'view_recommended_students' => 1,
                );
            } elseif($role == 'donor'){
                return array(
                    'view_recommended_students' => 1,
                );
            } else {
                $allcaps = array();
                switch($role){
                    case 'renewal':
                        $allcaps['view_renewal_process'] = true;
                    case 'awardee':
                        $allcaps['submit_renewal'] = true;
                    case 'nonrenewable':
                        $allcaps['view_award'] = true;
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
