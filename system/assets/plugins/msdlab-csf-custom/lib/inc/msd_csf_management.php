<?php
if (!class_exists('MSDLab_CSF_Management')) {
    class MSDLab_CSF_Management {
        //Properties

        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array('msd_csf_application','msd_setting_controls','msd_report_controls','msd_queries','msd_report_output');
            foreach($required_files AS $rq){
                if(file_exists(plugin_dir_path(__FILE__).'/'.$rq . '.php')){
                    require_once(plugin_dir_path(__FILE__).'/'.$rq . '.php');
                } else {
                    ts_data(plugin_dir_path(__FILE__).'/'.$rq . '.php does not exisit');
                }
            }

            if(class_exists('MSDLab_CSF_Application')){
                $this->application = new MSDLab_CSF_Application();
            }
            if(class_exists('MSDLAB_SettingControls')){
                $this->controls = new MSDLAB_SettingControls();
            }
            if(class_exists('MSDLAB_ReportControls')){
                $this->search = new MSDLAB_ReportControls();
            }
            if(class_exists('MSDLAB_Queries')){
                $this->queries = new MSDLAB_Queries();
            }
            if(class_exists('MSDLAB_Report_Output')){
                $this->report = new MSDLAB_Report_Output();
            }
            if(class_exists('MSDLAB_FormControls')){
                $this->form = new MSDLAB_FormControls();
            }

            //register stylesheet
            //Actions
            add_action('admin_menu', array(&$this,'settings_page'));
            //add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles_and_scripts'));
            //Filters

            //Shortcodes

        }

        function add_admin_styles_and_scripts(){
            wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',false,'4.5.0');
            wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',false,'4.5.0');
            wp_enqueue_style('csf-report-style',preg_replace('#/inc/#i','/css/',plugin_dir_url(__FILE__)).'msdform.css');
            wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',array('jquery'));
            //wp_enqueue_script( 'jquery-ui-datepicker' );
            //wp_enqueue_style('jqueryui-smoothness','//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');

            //wp_enqueue_script('jquery-validate',plugin_dir_url(__DIR__).'/../js/jquery.validate.min.js',array('jquery'));
            //wp_enqueue_script('jquery-validate-addl',plugin_dir_url(__DIR__).'/../js/additional-methods.min.js',array('jquery','jquery-validate'));
        }

        function add_styles_and_scripts(){
        }

        function settings_page(){
            add_menu_page(__('CSF Management and Reports'),__('CSF Management'), 'manage_csf', 'csf-manage', array(&$this,'management_page_content'),'dashicons-chart-area');
            //add_submenu_page('csf-manage',__('Application Reports'),__('Application Reports'),'manage_csf','csf-report', array(&$this,'report_page_content'));
            //add_submenu_page('csf-manage',__('Renewal Reports'),__('Renewal Reports'),'manage_csf','csf-renewals', array(&$this,'renewal_report_page_content'));
            add_submenu_page('csf-manage',__('All Students'),__('Search All Students'),'manage_csf','csf-students', array(&$this,'consolidated_search_page_content'));
            add_submenu_page(null,__('View Student'),__('View Student'),'manage_csf','student-edit', array(&$this,'single_student_record_page_content'));
            add_submenu_page('csf-manage',__('General Settings'),__('General Settings'),'manage_csf','csf-settings', array(&$this,'setting_page_content'));
            add_submenu_page('csf-manage',__('College Settings'),__('College Settings'),'manage_csf','csf-college', array(&$this,'college_page_content'));
            add_submenu_page(null,__('Edit College'),__('Edit College'),'manage_csf','college-edit', array(&$this,'college_edit_page_content'));
            add_submenu_page(null,__('Edit Contact'),__('Edit Contact'),'manage_csf','contact-edit', array(&$this,'contact_edit_page_content'));
            add_submenu_page('csf-manage',__('HighSchool Settings'),__('HighSchool Settings'),'manage_csf','csf-highschool', array(&$this,'highschool_page_content'));
            add_submenu_page(null,__('Edit HighSchool'),__('Edit HighSchool'),'manage_csf','highschool-edit', array(&$this,'highschool_edit_page_content'));
            add_submenu_page('csf-manage',__('Majors Settings'),__('Major Settings'),'manage_csf','csf-major', array(&$this,'major_page_content'));
            add_submenu_page(null,__('Edit Major'),__('Edit Major'),'manage_csf','major-edit', array(&$this,'major_edit_page_content'));
            add_submenu_page('csf-manage',__('Scholarship Settings'),__('Scholarship Settings'),'manage_csf','csf-scholarship', array(&$this,'scholarship_page_content'));
            add_submenu_page(null,__('Edit Scholarship'),__('Edit Scholarship'),'manage_csf','scholarship-edit', array(&$this,'scholarship_edit_page_content'));
            //match below
        }

        function management_page_content(){
            //page content here
            print '<div class="wrap report_table">';
            print '<h1 class="wp-heading-inline">'.get_bloginfo('name').' Admin Tools</h1>
            <hr class="wp-header-end">';
            print '<h3>Reporting</h3>';
            //print '<a href="admin.php?page=csf-report" class="page-title-action">Application Reports</a>';
            //print '<a href="admin.php?page=csf-renewals" class="page-title-action">Renewal Reports</a>';
            print '<a href="admin.php?page=csf-students" class="page-title-action">Search All Students</a>';
            print '<h3>Settings</h3>';
            print '<a href="admin.php?page=csf-settings" class="page-title-action">General Settings</a>';
            print '<a href="admin.php?page=csf-college" class="page-title-action">College and Contact Settings</a>';
            print '<a href="admin.php?page=csf-highschool" class="page-title-action">High School Settings</a>';
            print '<a href="admin.php?page=csf-major" class="page-title-action">Major Settings</a>';
            print '<a href="admin.php?page=csf-scholarship" class="page-title-action">Scholarship Settings</a>';
        }

        function setting_page_content(){
            //page content here
            if($msg = $this->queries->set_option_data('csf_settings')){
                print '<div class="updated notice notice-success is-dismissible">'.$msg.'</div>';
            }
            print '<h2>Scholarship Application Period</h2>';
            $this->controls->print_settings();
        }

        function report_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/report_page_content.php');
        }

        function renewal_report_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/renewal_report_page_content.php');
        }
        function consolidated_search_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/consolidated_search_page_content.php');
        }
        function single_student_record_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/single_student_record_page_content.php');
        }

        function college_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/college_page_content.php');

        }

        function college_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/college_edit_page_content.php');

        }

        function contact_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/contact_edit_page_content.php');

        }
        function highschool_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/highschool_page_content.php');

        }

        function highschool_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/highschool_edit_page_content.php');

        }

        function major_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/major_page_content.php');

        }
        function major_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/major_edit_page_content.php');

        }
        function scholarship_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/scholarship_page_content.php');

        }
        function scholarship_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/scholarship_edit_page_content.php');

        }

        //ultilities


        //db funzies

    } //End Class
} //End if class exists statement