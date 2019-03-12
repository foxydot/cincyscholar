<?php
if (!class_exists('MSDLab_CSF_Management')) {
    class MSDLab_CSF_Management {
        //Properties
        var $management_pages;
        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array('msd_csf_application','msd_csf_donor','msd_setting_controls','msd_report_controls','msd_queries','msd_report_output','language');
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
            if(class_exists('MSDLab_CSF_Donor')){
                $this->donor = new MSDLab_CSF_Donor();
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
            add_action('admin_menu', array(&$this,'get_pages'),400);
            //add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles_and_scripts'));
            add_action( 'admin_bar_menu', array(&$this,'toolbar_settings'), 999 );

            //Filters

            //Shortcodes
        }

        function get_pages(){
            global $_registered_pages;
            $this->management_pages = preg_grep('/^csf-management_page_.*/', array_keys($_registered_pages));
            $this->management_pages = array_merge($this->management_pages,preg_grep('/^admin_page_.*/', array_keys($_registered_pages)));
            array_push($this->management_pages,'toplevel_page_csf-manage');
        }

        function add_admin_styles_and_scripts(){
            global $current_screen;
            if(in_array($current_screen->id,$this->management_pages)) {
                wp_enqueue_style('bootstrap-style', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', false, '4.5.0');
                wp_enqueue_style('font-awesome-style', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css', false, '4.5.0');
                wp_enqueue_style('csf-report-style', preg_replace('#/inc/#i', '/css/', plugin_dir_url(__FILE__)) . 'msdform.css');
                wp_enqueue_script('bootstrap-jquery', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'));
                //wp_enqueue_script( 'jquery-ui-datepicker' );
                //wp_enqueue_style('jqueryui-smoothness','//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');

                //wp_enqueue_script('jquery-validate',plugin_dir_url(__DIR__).'/../js/jquery.validate.min.js',array('jquery'));
                //wp_enqueue_script('jquery-validate-addl',plugin_dir_url(__DIR__).'/../js/additional-methods.min.js',array('jquery','jquery-validate'));
            }
        }

        function add_styles_and_scripts(){
        }

        function toolbar_settings( $wp_admin_bar ) {
            $args = array(
                'id'    => 'csf-manage',
                'title' => 'CSF Management',
                'href'  => admin_url('admin.php?page=csf-manage'),
                'meta'  => array( 'class' => 'csf-manage' )
            );
            $wp_admin_bar->add_node( $args );
            $args = array(
                'id'    => 'csf-reports',
                'parent'    => 'csf-manage',
                'title' => 'Search/Reports',
                'href'  => admin_url('admin.php?page=csf-reports'),
                'meta'  => array( 'class' => 'csf-reports' )
            );
            $wp_admin_bar->add_node( $args );
            $args = array(
                'id'    => 'csf-settings',
                'parent'    => 'csf-manage',
                'title' => 'Settings',
                'href'  => admin_url('admin.php?page=csf-settings'),
                'meta'  => array( 'class' => 'csf-settings' )
            );
            $wp_admin_bar->add_node( $args );
        }

        function settings_page(){
            add_menu_page(__('CSF Management and Reports'),__('CSF Management'), 'manage_csf', 'csf-manage', array(&$this,'management_page_content'),'dashicons-chart-area');
            //add_submenu_page('csf-manage',__('Application Reports'),__('Application Reports'),'manage_csf','csf-report', array(&$this,'report_page_content'));
            //add_submenu_page('csf-manage',__('Renewal Reports'),__('Renewal Reports'),'manage_csf','csf-renewals', array(&$this,'renewal_report_page_content'));
            add_submenu_page('csf-manage',__('All Students'),__('Search All Students'),'manage_csf','csf-reports', array(&$this,'consolidated_search_page_content'));
            add_submenu_page(null,__('Maintenance'),__('Maintenance'),'manage_csf','csf-maintenance', array(&$this,'maintenance_search_page_content'));
            add_submenu_page(null,__('View Student'),__('View Student'),'manage_csf','student-edit', array(&$this,'single_student_record_page_content'));
            add_submenu_page(null,__('Recommend Student'),__('Recommend Student'),'manage_csf','student-recommend', array(&$this,'single_student_recommend_page_content'));
            add_submenu_page('csf-manage',__('Reports'),__('Reports'),'manage_csf','csf-reports', array(&$this,'consolidated_search_page_content'));
            add_submenu_page(null,__('Checks to Print'),__('Checks to Print'),'manage_csf','checks-to-print', array(&$this,'checks_to_print_report_content'));
            add_submenu_page(null,__('Update check number'),__('Update check number'),'manage_csf','check-number-update', array(&$this,'check_number_update_content'));
            add_submenu_page(null,__('Check attachments'),__('Check attachments'),'manage_csf','check-attachments', array(&$this,'check_attachments_content'));


            add_submenu_page('csf-manage',__('Settings'),__('Settings'),'manage_csf','csf-settings', array(&$this,'general_page_content'));
            add_submenu_page(null,__('College Settings'),__('College Settings'),'manage_csf','csf-college', array(&$this,'college_page_content'));
            add_submenu_page(null,__('Edit College'),__('Edit College'),'manage_csf','college-edit', array(&$this,'college_edit_page_content'));
            add_submenu_page(null,__('Edit Contact'),__('Edit Contact'),'manage_csf','contact-edit', array(&$this,'contact_edit_page_content'));
            add_submenu_page(null,__('HighSchool Settings'),__('HighSchool Settings'),'manage_csf','csf-highschool', array(&$this,'highschool_page_content'));
            add_submenu_page(null,__('Edit HighSchool'),__('Edit HighSchool'),'manage_csf','highschool-edit', array(&$this,'highschool_edit_page_content'));
            add_submenu_page(null,__('Majors Settings'),__('Major Settings'),'manage_csf','csf-major', array(&$this,'major_page_content'));
            add_submenu_page(null,__('Edit Major'),__('Edit Major'),'manage_csf','major-edit', array(&$this,'major_edit_page_content'));
            add_submenu_page(null,__('Scholarship Settings'),__('Scholarship Settings'),'manage_csf','csf-scholarship', array(&$this,'scholarship_page_content'));
            add_submenu_page(null,__('Edit Scholarship'),__('Edit Scholarship'),'manage_csf','scholarship-edit', array(&$this,'scholarship_edit_page_content'));
            add_submenu_page(null,__('Scholarship Recommendations'),__('Scholarship Recommendations'),'manage_csf','scholarship-recommends', array(&$this,'scholarship_recommends_page_content'));
            add_submenu_page(null,__('County Settings'),__('County Settings'),'manage_csf','csf-county', array(&$this,'county_page_content'));
            add_submenu_page(null,__('Edit County'),__('Edit County'),'manage_csf','county-edit', array(&$this,'county_edit_page_content'));
            add_submenu_page(null,__('Ethnicity Settings'),__('Ethnicity Settings'),'manage_csf','csf-ethnicity', array(&$this,'ethnicity_page_content'));
            add_submenu_page(null,__('Edit Ethnicity'),__('Edit Ethnicity'),'manage_csf','ethnicity-edit', array(&$this,'ethnicity_edit_page_content'));
            add_submenu_page(null,__('Gender Settings'),__('Gender Settings'),'manage_csf','csf-gender', array(&$this,'gender_page_content'));
            add_submenu_page(null,__('Edit Gender'),__('Edit Gender'),'manage_csf','gender-edit', array(&$this,'gender_edit_page_content'));
            add_submenu_page(null,__('Fund Settings'),__('Fund Settings'),'manage_csf','csf-fund', array(&$this,'fund_page_content'));
            add_submenu_page(null,__('Edit Fund'),__('Edit Fund'),'manage_csf','fund-edit', array(&$this,'fund_edit_page_content'));
            add_submenu_page(null,__('High School Type Settings'),__('High School Type Settings'),'manage_csf','csf-highschooltype', array(&$this,'highschooltype_page_content'));
            add_submenu_page(null,__('Edit High School Type'),__('Edit High School Type'),'manage_csf','highschooltype-edit', array(&$this,'highschooltype_edit_page_content'));
            add_submenu_page(null,__('Employer Settings'),__('Employer Settings'),'manage_csf','csf-employer', array(&$this,'employer_page_content'));
            add_submenu_page(null,__('Edit Employer'),__('Edit Employer'),'manage_csf','employer-edit', array(&$this,'employer_edit_page_content'));
            add_submenu_page(null,__('Educational Attainment Settings'),__('Educational Attainment Settings'),'manage_csf','csf-educationalattainment', array(&$this,'educationalattainment_page_content'));
            add_submenu_page(null,__('Edit Educational Attainment'),__('Edit Educational Attainment'),'manage_csf','educationalattainment-edit', array(&$this,'educationalattainment_edit_page_content'));
            add_submenu_page(null,__('Donor Type Settings'),__('Donor Type Settings'),'manage_csf','csf-donortype', array(&$this,'donortype_page_content'));
            add_submenu_page(null,__('Edit Donor Type'),__('Edit Donor Type'),'manage_csf','donortype-edit', array(&$this,'donortype_edit_page_content'));
            add_submenu_page(null,__('Institution Term Type Settings'),__('Institution Term Type Settings'),'manage_csf','csf-institutiontermtype', array(&$this,'institutiontermtype_page_content'));
            add_submenu_page(null,__('Edit Institution Term Type'),__('Edit Institution Term Type'),'manage_csf','institutiontermtype-edit', array(&$this,'institutiontermtype_edit_page_content'));


            add_submenu_page('csf-manage',__('Donor Management'),__('Donor Management'),'manage_csf','csf-donors', array(&$this,'donor_page_content'));
            add_submenu_page(null,__('Edit Donor'),__('Edit Donor'),'manage_csf','donor-edit', array(&$this,'donor_edit_page_content'));

            //match below
        }

        function management_page_content(){
            //page content here
            print '<div class="wrap report_table">';
            print '<h1 class="wp-heading-inline">'.get_bloginfo('name').' Admin Tools</h1>';
            print '<hr class="wp-header-end">';
            print '<h3>Reporting</h3>';
            $this->report_page_content_menu();
            print '<h3>Settings</h3>';
            $this->setting_page_content_menu();
            print '<h3>Users</h3>';
            print '<ul class="menu">';
            print '<li><a href="admin.php?page=csf-donors" >Donor Management</a></li>';
            print '<li><a href="admin.php?page=csf-donortype" >Donor Type Setting</a></li>';
            print '</ul>';
        }

        function report_page_content_menu($print = true){
            $ret = array();
            $ret[] = '<ul class="menu">';
            $ret[] = '<li><a href="admin.php?page=csf-reports" >Search All Students</a></li>';
            $ret[] = '<li><a href="admin.php?page=checks-to-print" >Checks to Print</a></li>';
            $ret[] = '<li><a href="admin.php?page=check-number-update" >Check number update</a></li>';
            $ret[] = '<li><a href="admin.php?page=check-attachments" >Check attachments</a></li>';
            $ret[] = '</ul>';
            if($print){
                print implode("\n\r", $ret);
                return true;
            } else {
                return implode("\n\r", $ret);
            }
        }

        function setting_page_content_menu($print = true){
            $ret = array();
            $ret[] = '<ul class="menu">';
            $ret[] = '<li><a href="admin.php?page=csf-settings" >General Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-college" >College and Contact Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-highschool" >High School Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-major" >Major Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-scholarship" >Scholarship Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-county" >County Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-ethnicity" >Ethnicity Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-gender" >Gender Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-fund" >Fund Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-highschooltype" >High School Type Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-employer" >Employer Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-educationalattainment" >Educational Attainment Settings</a></li>';
            $ret[] = '<li><a href="admin.php?page=csf-institutiontermtype" >Institution Term Type Settings</a></li>';
            $ret[] = '</ul>';
            if($print){
                print implode("\n\r", $ret);
                return true;
            } else {
                return implode("\n\r", $ret);
            }
        }

        function general_page_content(){
            //page content here
            $this->setting_page_content_menu();
            if($msg = $this->queries->set_option_data('csf_settings')){
                print '<div class="updated notice notice-success is-dismissible">'.$msg.'</div>';
            }
            print '<h2>Scholarship Application Period</h2>';
            $this->controls->print_settings();
        }

        function report_page_content(){
            include_once(plugin_dir_path(__FILE__).'/reports/report_page_content.php');
        }

        function renewal_report_page_content(){
            include_once(plugin_dir_path(__FILE__).'/reports/renewal_report_page_content.php');
        }
        function consolidated_search_page_content(){
            $this->report_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/reports/consolidated_search_page_content.php');
        }
        function maintenance_search_page_content(){
            $this->report_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/reports/maintenance_search_page_content.php');
        }
        function single_student_record_page_content(){
            include_once(plugin_dir_path(__FILE__).'/reports/single_student_record_page_content.php');
        }
        function single_student_recomend_page_content(){
            include_once(plugin_dir_path(__FILE__).'/reports/single_student_recommend_page_content.php');
        }
        function checks_to_print_report_content(){
            $this->report_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/reports/checks_to_print_content.php');
        }
        function check_number_update_content(){
            $this->report_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/reports/check_number_update_content.php');
        }
        function check_attachments_content(){
            $this->report_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/reports/check_attachments_content.php');
        }
        function college_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/college_page_content.php');

        }
        function college_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/college_edit_page_content.php');

        }
        function contact_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/contact_edit_page_content.php');

        }


        function county_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/county_page_content.php');

        }
        function county_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/county_edit_page_content.php');

        }

        function educationalattainment_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/educationalattainment_page_content.php');

        }
        function educationalattainment_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/educationalattainment_edit_page_content.php');

        }

        function employer_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/employer_page_content.php');

        }
        function employer_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/employer_edit_page_content.php');

        }

        function ethnicity_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/ethnicity_page_content.php');

        }
        function ethnicity_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/ethnicity_edit_page_content.php');

        }



        function fund_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/fund_page_content.php');

        }
        function fund_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/fund_edit_page_content.php');

        }
        function gender_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/gender_page_content.php');

        }
        function gender_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/gender_edit_page_content.php');

        }

        function highschool_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/highschool_page_content.php');

        }

        function highschool_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/highschool_edit_page_content.php');

        }
        function highschooltype_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/highschooltype_page_content.php');

        }

        function highschooltype_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/highschooltype_edit_page_content.php');

        }

        function major_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/major_page_content.php');

        }
        function major_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/major_edit_page_content.php');

        }
        function scholarship_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/scholarship_page_content.php');

        }
        function scholarship_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/scholarship_edit_page_content.php');

        }
        function scholarship_recommends_page_content(){
            include_once(plugin_dir_path(__FILE__).'/reports/scholarship_recommends_page_content.php');

        }
        function donor_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/donor_page_content.php');

        }
        function donor_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/donor_edit_page_content.php');

        }
        function donortype_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/donortype_page_content.php');

        }
        function donortype_edit_page_content(){
            include_once(plugin_dir_path(__FILE__).'/admin-part/donortype_edit_page_content.php');

        }
        function institutiontermtype_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/institutiontermtype_page_content.php');

        }
        function institutiontermtype_edit_page_content(){
            $this->setting_page_content_menu();
            include_once(plugin_dir_path(__FILE__).'/admin-part/institutiontermtype_edit_page_content.php');

        }

        //ultilities


        //db funzies

    } //End Class
} //End if class exists statement