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
            $required_files = array('msd_csf_application','msd_setting_controls','msd_export','msd_queries','msd_views');
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
            if(class_exists('MSDLAB_Queries')){
                $this->queries = new MSDLAB_Queries();
            }
            if(class_exists('MSDLAB_Display')){
                $this->display = new MSDLAB_Display();
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
        }

        function add_styles_and_scripts(){
        }

        function settings_page(){
            add_menu_page(__('CSF Management and Reports'),__('CSF Management'), 'manage_csf', 'csf-report', array(&$this,'report_page_content'),'dashicons-chart-area');
            add_submenu_page('csf-report',__('Reports'),__('Reports'),'manage_csf','csf-report', array(&$this,'report_page_content'));
            add_submenu_page('csf-report',__('General Settings'),__('General Settings'),'manage_csf','csf-settings', array(&$this,'setting_page_content'));
            add_submenu_page('csf-report',__('College Settings'),__('College Settings'),'manage_csf','csf-college', array(&$this,'college_page_content'));
            add_submenu_page(null,__('Edit College'),__('Edit College'),'manage_csf','college-edit', array(&$this,'college_edit_page_content'));
        }

        function report_page_content(){
            $fields = array(
                'ApplicantId',
                'FirstName',
                'MiddleInitial',
                'LastName',
                'Address1',
                'Address2',
                'City',
                'StateId',
                'ZipCode',
                'CountyId',
                'CellPhone',
                'AlternativePhone',
                'Email',
                'user_email',
                'Last4SSN',
                'DateOfBirth',
                'SexId',
                'FirstGenerationStudent',
                'EducationAttainmentId',
                'HighSchoolId',
                'HighSchoolGraduationDate',
                'HighSchoolGPA',
                'CollegeId',
                'MajorId',
                'OtherSchool',
                'IsIndependent',
                'PlayedHighSchoolSports',
                'Employer',
                'HardshipNote',
                'ApplicationDateTime',
                'InformationSharingAllowed',
                'IsComplete',
                'EthnicityId',
                'Activities',
                'ApplicantHaveRead',
                'ApplicantDueDate',
                'ApplicantDocsReq',
                'ApplicantReporting',
                'CPSPublicSchools',
                'GuardianHaveRead',
                'GuardianDueDate',
                'GuardianDocsReq',
                'GuardianReporting',
                'GuardianFullName1',
                'GuardianEmployer1',
                'GuardianFullName2',
                'GuardianEmployer2',
                'ApplicantEmployer',
                'ApplicantIncome',
                'SpouseEmployer',
                'SpouseIncome',
                'Homeowner',
                'HomeValue',
                'AmountOwedOnHome',
                'InformationSharingAllowedByGuardian',
                'Documents',
            );
            $result = $this->queries->get_all_applications();
            $submitted = $incomplete = array();
            foreach($result AS $applicant){
                if($applicant->status == 2){
                    $submitted[] = $applicant;
                } else {
                    $incomplete[] = $applicant;
                }
            }
            $info = '';
            $class = array('table','table-bordered');
            print '<h1 class="wp-heading-inline">Scholarship Application Reports</h1>';
            print '
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted</a></li>
    <li role="presentation"><a href="#incomplete" aria-controls="incomplete" role="tab" data-toggle="tab">incomplete</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="submitted">
    ';
            $this->display->print_table('submitted',$fields,$submitted,$info,$class);
            print '
</div>
    <div role="tabpanel" class="tab-pane" id="incomplete">';
            $this->display->print_table('incomplete',$fields,$incomplete,$info,$class);
            print '</div>
  </div>';
        }


        function setting_page_content(){
            //page content here
            if($msg = $this->queries->set_option_data('csf_settings')){
                print '<div class="updated notice notice-success is-dismissible">'.$msg.'</div>';
            }
            print '<h1 class="wp-heading-inline">Scholarship Application Period</h1>';
            $this->controls->print_settings();
        }


        function college_page_content(){
            //page content here
            print '<div class="wrap report_table">';
            print '<h1 class="wp-heading-inline">College Settings</h1>';
            //button: add college
            print '<a href="admin.php?page=college-edit&college_id=null" class="page-title-action">Add New College</a>
            <hr class="wp-header-end">';
            //list colleges with edit button, view contacts button
            //contacts in a slidedown box?
            //$this->controls->print_settings();
            $colleges = $this->queries->get_all_colleges();
            if(count($colleges)>0) {
                foreach ($colleges AS $college){
                    $contacts = $this->queries->get_all_contacts($college->CollegeId);
                    $cell['college_name'] = $college->Name;
                    $con = array();
                    foreach($contacts AS $contact){
                        $c = array();
                        $c['name'] = $contact->FirstName .' '.$contact->LastName;
                        $c['dept'] = $contact->Department;
                        $c['email'] = '<a href="mailto:'.antispambot($contact->Email).'">'.antispambot($contact->Email).'</a>';
                        $c['phone'] = $contact->PhoneNumber;
                        $con[] = implode('<br>',$c);
                    }
                    $cell['contacts'] = implode('<br><br>',$con);
                    $cell['edit'] = '<a href="admin.php?page=college-edit&college_id='.$college->CollegeId.'" class="button">Edit</a>';
                    $row[] = implode('</td><td>',$cell);
                }
                $table = implode("</td></tr>\n<tr><td>",$row);
                print '<table><tr><td>'.$table.'</td></tr></table>';
            }
            print '</div>';
        }

        function college_edit_page_content(){
            $college_id = $_GET['college_id'];
            $college = null;
            $title = 'New College';
            if($_POST) {
                $notifications = array(
                    'nononce' => 'College could not be saved.',
                    'success' => 'College saved!'
                );
                if ($msg = $this->queries->set_data('csf_college', array('college' => 'CollegeId = ' . $college_id), $notifications)) {
                    print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
                }
            }
            if(!is_null($college_id)){
                $college = $this->queries->get_college($college_id);
                $title = $college->Name;
            } else {
                $title = 'New College';
            }
            print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>            
            <hr class="wp-header-end">';
            $form = $this->controls->get_form(array('form_id' => 'csf_college','data' => $college));
            print $form;
        }

        //ultilities


        //db funzies

    } //End Class
} //End if class exists statement