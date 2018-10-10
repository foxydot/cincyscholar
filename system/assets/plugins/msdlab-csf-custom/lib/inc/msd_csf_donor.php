<?php
if (!class_exists('MSDLab_CSF_Donor')) {
    class MSDLab_CSF_Donor {
        //Properties

        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array('msd_form_controls','msd_queries','msd_report_output');
            foreach($required_files AS $rq){
                if(file_exists(plugin_dir_path(__FILE__).'/'.$rq . '.php')){
                    require_once(plugin_dir_path(__FILE__).'/'.$rq . '.php');
                } else {
                    ts_data(plugin_dir_path(__FILE__).'/'.$rq . '.php does not exisit');
                }
            }
            if(class_exists('MSDLAB_FormControls')){
                $this->form = new MSDLAB_FormControls();
            }
            if(class_exists('MSDLAB_Queries')){
                $this->queries = new MSDLAB_Queries();
            }
            if(class_exists('MSDLAB_Report_Output')){
                $this->report = new MSDLAB_Report_Output();
            }

            //register stylesheet
            //Actions
            //add_action('admin_menu', array(&$this,'settings_page'));
            add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            add_action('wp_enqueue_scripts',array(&$this,'set_up_globals'));
            //Filters

            //Shortcodes
            add_shortcode('donor_portal', array(&$this,'donor_shortcode_handler'));

        }

        function set_up_globals(){
            global $current_user,$applicant_id,$user_id;
            if(!current_user_can('csf') && !current_user_can('administrator')) {
                $user_id = $current_user->ID;
                $applicant_id = $this->queries->get_applicant_id($user_id);
            }
        }

        function add_admin_styles_and_scripts(){
            wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',false,'4.5.0');
            wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',false,'4.5.0');
            wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',array('jquery'));
        }

        function add_styles_and_scripts(){
            wp_enqueue_script('jquery-validate',plugin_dir_url(__DIR__).'/../js/jquery.validate.min.js',array('jquery'));
            wp_enqueue_script('jquery-validate-addl',plugin_dir_url(__DIR__).'/../js/additional-methods.min.js',array('jquery','jquery-validate'));
            wp_enqueue_script('jquery-mask',plugin_dir_url(__DIR__).'/../js/jquery.mask.js',array('jquery'));
            wp_enqueue_style( 'msdform-css', plugin_dir_url(__DIR__).'/../css/msdform.css' );
        }


        function donor_shortcode_handler($atts){
            extract(shortcode_atts( array(
                'donor' => 'default', //default to primary application
            ), $atts ));
            $donor_page = get_option('csf_settings_donor_welcome_page');
            if(!is_page($donor_page)){
                if(is_user_logged_in() && current_user_can('view_recommended_students')){
                    return '<a href="'.get_permalink($donor_page).'" class="button">Proceed to Donor Portal</a>';
                } else {
                    return '<div class="login-trigger"><span class="button">Login/Register</span></div>';
                }
            }
            if(is_user_logged_in()){
                $ret = array();
                if(current_user_can('view_recommended_students')){
                    //show recommendations here
                    global $current_user;
                    $ret[] = $this->get_recommendations_by_donor_access($current_user->ID);
                }
                sort($ret);
                return implode("\n\r",$ret);
            } else {
                return '<div class="login-trigger"><span class="button">Login/Register</span></div>';
            }
        }

        function get_recommendations_by_donor_access($donor_id){
            global $wpdb;
            $sql = 'SELECT donoruserscholarship.ScholarshipId, scholarship.* FROM donoruserscholarship, scholarship WHERE donoruserscholarship.ScholarshipId = scholarship.ScholarshipId AND donoruserscholarship.UserId = '.$donor_id.';';
            $scholarships = $wpdb->get_results($sql);
            foreach($scholarships AS $scholarship){
                $scholarship_id = $scholarship->ScholarshipId;
                include plugin_dir_path(__FILE__).'/admin-part/scholarship_recommends_page_content.php';
            }
        }

    } //End Class
} //End if class exists statement