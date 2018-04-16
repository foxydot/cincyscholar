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
            $required_files = array('msd_csf_application','msd_setting_controls','msd_report_controls','msd_export','msd_queries','msd_views');
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
            add_submenu_page('csf-report',__('Application Reports'),__('Application Reports'),'manage_csf','csf-report', array(&$this,'report_page_content'));
            add_submenu_page('csf-report',__('Renewal Reports'),__('Renewal Reports'),'manage_csf','csf-renewals', array(&$this,'renewal_report_page_content'));
            add_submenu_page('csf-report',__('General Settings'),__('General Settings'),'manage_csf','csf-settings', array(&$this,'setting_page_content'));
            add_submenu_page('csf-report',__('College Settings'),__('College Settings'),'manage_csf','csf-college', array(&$this,'college_page_content'));
            add_submenu_page(null,__('Edit College'),__('Edit College'),'manage_csf','college-edit', array(&$this,'college_edit_page_content'));
            add_submenu_page(null,__('Edit Contact'),__('Edit Contact'),'manage_csf','contact-edit', array(&$this,'contact_edit_page_content'));

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
            $tabs = '';
            $pane = array();
            if($_POST) {
                //ts_data($_POST);
                $result = $this->queries->get_report_set($fields);
                $submitted = $incomplete = array();
                foreach ($result AS $k => $applicant) {
                    if(!empty($_POST['employer_search_input'])){
                        if(stripos($applicant->Employer,$_POST['employer_search_input'])===false &&
                            stripos($applicant->GuardianEmployer1,$_POST['employer_search_input'])===false &&
                            stripos($applicant->GuardianEmployer2,$_POST['employer_search_input'])===false){
                            continue;
                        }
                    }

                    if(isset($_POST['cps_employee_search_input'])){
                        if($applicant->CPSPublicSchools != 1){
                            continue;
                        }
                    }

                    if ($applicant->status == 2) {
                        $submitted[] = $applicant;
                    } else {
                        $incomplete[] = $applicant;
                    }
                }
                $info = '';
                $class = array('table','table-bordered');
                if($result){
                    $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted</a></li>
    <li role="presentation"><a href="#incomplete" aria-controls="incomplete" role="tab" data-toggle="tab">incomplete</a></li>
  </ul>';

                    if(count($submitted)>0){
                        $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            ' . implode("\n\r",$this->display->print_table('application_submitted',$fields,$submitted,$info,$class,false)) .'
                        </div>';
                    } else {
                        $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
                    }
                    if(count($incomplete)>0){
                        $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            ' . implode("\n\r",$this->display->print_table('application_incomplete',$fields,$incomplete,$info,$class,false)) .'
                        </div>';
                    } else {
                        $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
                    }
                } else {
                    $tabs = '<div class="notice bg-info text-info">No results</div>';
                }
            }
            print '<h2>Scholarship Application Reports</h2>';
            if(!$_POST) {
                $this->search->javascript['search-btn'] = '
        $(".search-button input").val("Load All Applications");
        $(".query-filter input, .query-filter select").change(function(){
            $(".search-button input").val("SEARCH");
        });';
            }
            $this->search->print_form('application');

            print $tabs;
            print '

  <!-- Tab panes -->
  <div class="tab-content">';
            print $pane['submitted'];
            print $pane['incomplete'];

            print '</div>';
        }

        function renewal_report_page_content(){
            $fields = array(
                'RenewalId',
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
                'Last4SSN',
                'DateOfBirth',
                'CurrentCumulativeGPA',
                'CoopStudyAbroadNote',
                'RenewalDateTime',
                'AnticipatedGraduationDate',
                'YearsWithCSF',
                'CollegeId',
                'MajorId',
                'TermsAcknowledged',
                'RenewalLocked',
                'Notes'
            );
            $tabs = '';
            $pane = array();
            if($_POST) {
                //ts_data($_POST);
                $result = $this->queries->get_renewal_report_set($fields);
                $submitted = $incomplete = array();
                foreach ($result AS $k => $renewal) {
                    if(!empty($_POST['employer_search_input'])){
                        if(stripos($renewal->Employer,$_POST['employer_search_input'])===false &&
                            stripos($renewal->GuardianEmployer1,$_POST['employer_search_input'])===false &&
                            stripos($renewal->GuardianEmployer2,$_POST['employer_search_input'])===false){
                            continue;
                        }
                    }

                    if(isset($_POST['cps_employee_search_input'])){
                        if($renewal->CPSPublicSchools != 1){
                            continue;
                        }
                    }

                    if ($renewal->status == 2) {
                        $submitted[] = $renewal;
                    } else {
                        $incomplete[] = $renewal;
                    }
                }
                $info = '';
                $class = array('table','table-bordered');
                if($result){
                    $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted</a></li>
    <li role="presentation"><a href="#incomplete" aria-controls="incomplete" role="tab" data-toggle="tab">incomplete</a></li>
  </ul>';

                    if(count($submitted)>0){
                        $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            ' . implode("\n\r",$this->display->print_table('renewal_submitted',$fields,$submitted,$info,$class,false)) .'
                        </div>';
                    } else {
                        $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
                    }
                    if(count($incomplete)>0){
                        $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            ' . implode("\n\r",$this->display->print_table('renewal_incomplete',$fields,$incomplete,$info,$class,false)) .'
                        </div>';
                    } else {
                        $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
                    }
                } else {
                    $tabs = '<div class="notice bg-info text-info">No results</div>';
                }
            }
            print '<h2>Scholarship Renewal Reports</h2>';
            if(!$_POST) {
                $this->search->javascript['search-btn'] = '
        $(".search-button input").val("Load All Renewals");
        $(".query-filter input, .query-filter select").change(function(){
            $(".search-button input").val("SEARCH");
        });';
            }
            $this->search->print_form('renewal');

            print $tabs;
            print '

  <!-- Tab panes -->
  <div class="tab-content">';
            print $pane['submitted'];
            print $pane['incomplete'];

            print '</div>';
        }

        function college_page_content(){
            //page content here
            print '<div class="wrap report_table">';
            print '<h1 class="wp-heading-inline">College Settings</h1>';
            //button: add college
            print ' <a href="admin.php?page=college-edit" class="page-title-action">Add New College</a>
           <a href="admin.php?page=contact-edit" class="page-title-action">Add New Contact</a>
            <hr class="wp-header-end">';
            //list colleges with edit button, view contacts button
            //contacts in a slidedown box?
            //$this->controls->print_settings();
            $colleges = $this->queries->get_all_colleges();
            if(count($colleges)>0) {
                $alphas = range('A', 'Z');
                foreach ($alphas AS $a){
                    $links[] = '<a href="#colleges-'.$a.'">'.$a.'</a>';
                }
                $linkstrip = implode(' | ',$links);
                foreach ($colleges AS $college){
                    $contacts = $this->queries->get_all_contacts($college->CollegeId);
                    $cell['college_name'] = '<span id="colleges-'.substr($college->Name,0,1).'">'.$college->Name.'</span><br /><a href="admin.php?page=college-edit&college_id='.$college->CollegeId.'" class="button">Edit College</a>';
                    $con = array();
                    foreach($contacts AS $contact){
                        $c = array();
                        $c['name'] = $contact->FirstName .' '.$contact->LastName;
                        $c['dept'] = $contact->Department;
                        $c['email'] = '<a href="mailto:'.antispambot($contact->Email).'">'.antispambot($contact->Email).'</a>';
                        $c['phone'] = $contact->PhoneNumber;
                        $c['edit'] = '<a href="admin.php?page=contact-edit&contact_id='.$contact->CollegeContactId.'" class="button">Edit '.$contact->FirstName .' '.$contact->LastName.'</a>';
                        $con[] = implode('<br>',$c);
                    }
                    $cell['contacts'] = implode('<br><br>',$con);
                    $row[] = implode('</td><td>',$cell);
                }
                $table = implode("</td></tr>\n<tr><td>",$row);
                print $linkstrip;
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
            print '<div class="wrap">';
            print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-college" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
            $form = $this->controls->get_form(array('form_id' => 'csf_college','data' => $college));
            print $form;
            print '</div>';
        }

        function contact_edit_page_content(){
            $contact_id = $_GET['contact_id'];
            $contact = null;
            $title = 'New Contact';
            if($_POST) {
                $notifications = array(
                    'nononce' => 'Contact could not be saved.',
                    'success' => 'Contact saved!'
                );
                if ($msg = $this->queries->set_data('csf_contact', array('collegecontact' => 'CollegeContactId = ' . $contact_id), $notifications)) {
                    print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
                }
            }
            if(!is_null($contact_id)){
                $contact = $this->queries->get_contact($contact_id);
                $title = $contact->FirstName .' '. $contact->LastName;
            } else {
                $title = 'New Contact';
            }
            print '<div class="wrap">';
            print '<h1 class="wp-heading-inline">'.$title.' Settings</h1> <a href="admin.php?page=csf-college" class="page-title-action">Return To List</a>         
            <hr class="wp-header-end">';
            $form = $this->controls->get_form(array('form_id' => 'csf_contact','data' => $contact));
            print $form;
            print '</div>';
        }

        //ultilities


        //db funzies

    } //End Class
} //End if class exists statement