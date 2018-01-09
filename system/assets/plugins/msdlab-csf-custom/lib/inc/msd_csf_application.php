<?php
if (!class_exists('MSDLab_CSF_Application')) {
    class MSDLab_CSF_Application {
        //Properties
        var $sex_array;
        var $ethnicity_array;
        var $states_array;
        var $counties_array;
        var $college_array;
        var $major_array;
        var $educationalattainment_array;
        var $highschool_array;
        var $gradyr_array;

        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array('msd_form_controls');
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
            if(class_exists('MSDLAB_Display')){
                $this->display = new MSDLAB_Display();
            }

            //register stylesheet
            //Actions
            add_action('admin_menu', array(&$this,'settings_page'));
            add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            add_action('wp_enqueue_scripts',array(&$this,'set_up_globals'));
            //Filters

            //Shortcodes
            add_shortcode('application', array(&$this,'application_shortcode_handler'));

        }

        function set_up_globals(){
            global $current_user,$applicant_id,$user_id;
            $user_id = $current_user->ID;
            $applicant_id = $this->get_applicant_id($user_id);
        }

        function add_admin_styles_and_scripts(){
            wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css',false,'4.5.0');
            wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',false,'4.5.0');
            wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js',array('jquery'));
        }

        function add_styles_and_scripts(){
            wp_enqueue_script('jquery-validate',plugin_dir_url(__DIR__).'/../js/jquery.validate.min.js',array('jquery'));
            wp_enqueue_script('jquery-validate-addl',plugin_dir_url(__DIR__).'/../js/additional-methods.min.js',array('jquery','jquery-validate'));
            wp_enqueue_style( 'msdform-css', plugin_dir_url(__DIR__).'/../css/msdform.css' );
        }

        function application_shortcode_handler($atts,$content){
            extract(shortcode_atts( array(
                'application' => 'default', //default to primary application
            ), $atts ));

            if($content == ''){
                $content = get_option('csf_settings_alt_text');
            }
            $start_date = strtotime(get_option('csf_settings_start_date'));
            $end_date = strtotime(get_option('csf_settings_end_date'));
            $today = time();
            if($today >= $start_date && $today <= $end_date){
                if(is_user_logged_in()){
                    $ret = array();
                    if(current_user_can('view_renewal_process')){
                        $ret[] = 'VIEW RENEWAL PROCESS';
                    }
                    if(current_user_can('view_award')){
                        $ret[] = 'VIEW AWARD';
                    }
                    if(current_user_can('view_application_process')){
                        $ret[] = $this->get_user_application_status();
                    }
                    if(current_user_can('submit_application')){
                        $ret[] = implode("\n\r",$this->get_form('application'));
                    }
                    return implode("\n\r",$ret);
                } else {
                    return '<div class="login-trigger"><span class="button">Login/Register</span></div>';
                }
            } else {
                return $content;
            }
        }

        //ultilities

        function get_applicant_id($user_id){
            global $wpdb;
            $sql = "SELECT ApplicantId FROM Applicant WHERE UserId = ". $user_id;
            error_log($sql);
            $result = $wpdb->get_results($sql);
            return $result[0]->ApplicantId;
        }

        function numToOrdinalWord($num)
        {
            $first_word = array('eth','First','Second','Third','Fouth','Fifth','Sixth','Seventh','Eighth','Ninth','Tenth','Elevents','Twelfth','Thirteenth','Fourteenth','Fifteenth','Sixteenth','Seventeenth','Eighteenth','Nineteenth','Twentieth');
            $second_word =array('','','Twenty','Thirty','Forty','Fifty');

            if($num <= 20)
                return $first_word[$num];

            $first_num = substr($num,-1,1);
            $second_num = substr($num,-2,1);

            return $string = str_replace('y-eth','ieth',$second_word[$second_num].'-'.$first_word[$first_num]);
        }

        //meat

        function get_form($form_id,$options = array()){
            global $current_user,$applicant_id,$user_id;
            $defaults = array();

            //just in case
            $options = array_merge($defaults,$options);

            $jquery = $ret = array();
            $ret['form_header'] = $this->form->form_header($form_id,array($form_id,'row'));
            switch($form_id) {
                case 'application':
                    $form_page_number = isset($_POST['form_page_number']) ? $_POST['form_page_number'] : 1;
                    $step = isset($_POST['form_page_number']) ? $_POST['form_page_number'] + 1 : 1;
                    $set['where'] = $applicant_id > 0 ? array('Applicant' => 'Applicant.ApplicantId = ' . $applicant_id) : array('Applicant' => 'Applicant.UserId = ' . $user_id);
                    $data['where'] = 'Applicant.ApplicantId = ' . $applicant_id;
                    switch ($step) {
                        case 1:
                            break;
                        case 2:
                            $set['where']['ApplicationProcess'] = 'ApplicationProcess.ApplicantId = ' . $applicant_id .' AND ApplicationProcess.ProcessStepId = 1';
                            $data['where'] .= ' AND ApplicantCollege.ApplicantId = ' . $applicant_id;
                            break;
                        case 3:
                            $set['where']['ApplicantCollege'] = 'ApplicantCollege.ApplicantId = ' . $applicant_id;
                            $data['where'] .= ' AND ApplicantIndependent.ApplicantId = ' . $applicant_id;
                            break;
                        case 4:
                            $set['where']['ApplicantIndependent'] = 'ApplicantIndependent.ApplicantId = ' . $applicant_id;
                            $data['where'] .= ' AND Guardian.ApplicantId = ' . $applicant_id;
                            break;
                        case 5:
                            $set['where']['Guardian'] = 'Guardian.ApplicantId = ' . $applicant_id;
                            break;
                    }
                    if ($_POST['application_form']) {
                        //Do the stuff
                        //ts_data($_POST);
                        print $this->queries->set_data($form_id . $form_page_number, $set['where']);
                        if (!current_user_can('view_application_process')) {
                            wp_update_user(array('ID' => $user_id, 'role' => 'applicant'));
                        }
                        //Work out the page
                        if (isset($_POST['form_page_next'])) {
                            $form_page_number = $_POST['form_page_next'];
                        }
                    }

                    //get the form selects
                    $this->sex_array = $this->queries->get_select_array_from_db('Sex', 'SexId', 'Sex');
                    $this->ethnicity_array = $this->queries->get_select_array_from_db('Ethnicity', 'EthnicityId', 'Ethnicity');
                    $this->states_array = $this->queries->get_select_array_from_db('State', 'StateId', 'State');
                    $this->counties_array = $this->queries->get_select_array_from_db('County', 'CountyId', 'County');
                    $this->college_array = $this->queries->get_select_array_from_db('College', 'CollegeId', 'Name');
                    $this->major_array = $this->queries->get_select_array_from_db('Major', 'MajorId', 'MajorName');
                    $this->educationalattainment_array = $this->queries->get_select_array_from_db('EducationalAttainment', 'EducationalAttainmentId', 'EducationalAttainment');
                    $this->highschool_array = $this->queries->get_select_array_from_db('HighSchool', 'HighSchoolId', 'SchoolName');
                    for ($yr = 2000; $yr <= date("Y"); $yr++) {
                        $this->gradyr_array[$yr] = $yr;
                    }
                    $this->gradyr_array = array_reverse($this->gradyr_array);
                    //build the jquery
                    $jquery[] = "$('#prevBtn_button').click(function(e){
                        e.preventDefault();
                        $('#".$form_id." #form_page_next').val(".($form_page_number - 1).");
                        $('#".$form_id."').submit();
                    });";
                    $jquery[] = "$('#saveBtn_button').click(function(e){
                        e.preventDefault();
                        $('#".$form_id." #form_page_next').val(".($form_page_number + 1).");
                        $('#".$form_id."').submit();
                    });";
                    $jquery[] = "$('.ui-toggle-btn').each(function(){
                        var toggled = $(this).parent().next('.switchable');
                        if($(this).find('input[type=checkbox]').is(':checked')){
                            toggled.slideDown(0);
                        } else {
                            toggled.slideUp(0);
                        }
                    });";
                    $jquery[] = "$('.ui-toggle-btn').click(function(){
                            var toggled = $(this).parent().next('.switchable');
                            if($(this).find('input[type=checkbox]').is(':checked')){
                                toggled.slideDown(500);
                            } else {
                                toggled.slideUp(500);
                            }
                        });";
                    //TODO: sort out js validation
                    $btnTitle = "Save & Continue";
                    $ret['form_type'] = $this->form->field_utility('application_form', true);
                    $ret['form_page_number'] = $this->form->field_utility('form_page_number', 1);
                    $ret['form_page_next'] = $this->form->field_utility('form_page_next', 1);
                    $ret['ApplicantId'] = $this->form->field_utility("ApplicantId", $applicant_id); //matching user_id to applicantID. HRM. This is autoincremented in the DB. We will need to create userids for all the old data and start UIDs at a higher number than exisiting applicant IDs
                    switch ($form_page_number) {
                        case 1: //personal info
                            //sets up the query
                            $data['tables']['Applicant'] = array('ApplicationDateTime', 'FirstName', 'MiddleInitial', 'LastName', 'Last4SSN', 'Address1', 'Address2', 'City', 'StateId',
                                'CountyId', 'ZipCode', 'CellPhone', 'AlternativePhone', 'DateOfBirth', 'EthnicityId', 'SexId');
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];
                            //the fields
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 1);
                            $ret['hdrPersInfo'] = $this->form->section_header('hdrPersInfo', 'Personal Information');
                            $ret['Applicant_ApplicationDateTime'] = $this->form->field_hidden("Applicant_ApplicationDateTime", $result->ApplicationDateTime ? $result->ApplicationDateTime : time());
                            $ret['Applicant_UserId'] = $this->form->field_hidden("Applicant_UserId", $user_id);
                            $ret['Applicant_Email'] = $this->form->field_hidden("Applicant_Email", $current_user->user_email);
                            $ret['Applicant_FirstName'] = $this->form->field_textfield('Applicant_FirstName', $result->FirstName ? $result->FirstName : null, 'First Name', null, array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
                            $ret['Applicant_MiddleInitial'] = $this->form->field_textfield('Applicant_MiddleInitial', $result->MiddleInitial ? $result->MiddleInitial : null, 'Middle Initial', null, array(), array('col-md-2', 'col-sm-12'));
                            $ret['Applicant_LastName'] = $this->form->field_textfield('Applicant_LastName', $result->LastName ? $result->LastName : null, 'Last Name', null, array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
                            $ret['Applicant_Last4SSN'] = $this->form->field_textfield('Applicant_Last4SSN', $result->Last4SSN ? $result->Last4SSN : null, 'Last 4 numbers of your SS#', '0000', array('type' => 'number', 'maxlength' => 4, 'minlength' => 4, 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_DateOfBirth'] = $this->form->field_date('Applicant_DateOfBirth', $result->DateOfBirth ? $result->DateOfBirth : null, 'Date of Birth', array('required' => 'required', 'type' => 'date'), array('datepicker', 'required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_Address1'] = $this->form->field_textfield('Applicant_Address1', $result->Address1 ? $result->Address1 : null, 'Address', '123 Any Street', array('type' => 'text', 'minlength' => '2', 'required' => 'required'), array('required', 'col-md-12'));
                            $ret['Applicant_Address2'] = $this->form->field_textfield('Applicant_Address2', $result->Address2 ? $result->Address2 : null, '', 'Apartment or Box number', array('type' => 'text'), array('col-md-12'));
                            $ret['Applicant_City'] = $this->form->field_textfield('Applicant_City', $result->City ? $result->City : null, 'City', null, array('type' => 'text', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
                            $ret['Applicant_StateId'] = $this->form->field_select('Applicant_StateId', $result->StateId ? $result->StateId : 'OH', 'State', array('option' => 'Select', 'value' => 'OH'), $this->states_array, array('required' => 'required'), array('required', 'col-md-2', 'col-sm-12'));
                            $ret['Applicant_CountyId'] = $this->form->field_select('Applicant_CountyId', $result->CountyId ? $result->CountyId : null, 'County', array('option' => 'Select', 'value' => '24'), $this->counties_array, null, array('col-md-3', 'col-sm-12'));
                            $ret['Applicant_ZipCode'] = $this->form->field_textfield('Applicant_ZipCode', $result->ZipCode ? $result->ZipCode : null, 'ZIP Code', '00000', array('type' => 'number', 'minlength' => 5, 'maxlength' => 10), array('required', 'col-md-2', 'col-sm-12'));
                            $ret['Applicant_CellPhone'] = $this->form->field_textfield('Applicant_CellPhone', $result->CellPhone ? $result->CellPhone : null, 'Mobile Phone Number', '(000)000-0000', array('required' => 'required', 'type' => 'tel'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_AlternativePhone'] = $this->form->field_textfield('Applicant_AlternativePhone', $result->AlternativePhone ? $result->AlternativePhone : null, 'Alternative Phone Number', '(000)000-0000', array('type' => 'tel'), array('col-md-6', 'col-sm-12'));
                            //some optional fields
                            $ret[] = '<hr class="col-md-12">';
                            $ret['disclaim'] = '<div>The Cincinnati Scholarship Foundation administers some scholarships that are restricted to members of a certain ethnic background or gender. While you are not required to supply this information, it may be to your advantage to do so.</div>';
                            $ret['Applicant_EthnicityId'] = $this->form->field_select('Applicant_EthnicityId', $result->EthnicityId ? $result->EthnicityId : null, 'Ethnicity', array('option' => 'Select', 'value' => '24'), $this->ethnicity_array, null, array('col-md-6', 'col-sm-12'));
                            $ret['Applicant_SexId'] = $this->form->field_radio('Applicant_SexId', $result->SexId ? $result->SexId : null, 'Gender', $this->sex_array, null, array('col-md-6', 'col-sm-12'));
                            //to set the process "in motion"
                            $ret['ApplicationProcess_ApplicantId'] = $this->form->field_hidden("ApplicationProcess_ApplicantId", $applicant_id);
                            $ret['ApplicationProcess_ProcessStepId'] = $this->form->field_hidden("ApplicationProcess_ProcessStepId", 1);
                            $ret['ApplicationProcess_ProcessStepBool'] = $this->form->field_hidden("ApplicationProcess_ProcessStepBool", 1);
                            break;
                        case 2: //academic
                            //sets up the query
                            $data['tables']['Applicant'] = array('MajorId', 'EducationAttainmentId', 'HighSchoolGraduationDate', 'HighSchoolId', 'HighSchoolGraduationDate', 'HighSchoolGPA', 'PlayedHighSchoolSports', 'FirstGenerationStudent');
                            $data['tables']['ApplicantCollege'] = array('CollegeId');
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];
                            //the fields
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 2);
                            $ret['hdrCollegeInfo'] = $this->form->section_header('hdrCollegeInfo', 'Academic Information');
                            $ret['ApplicantCollege_ApplicantId'] = $this->form->field_hidden("ApplicantCollege_ApplicantId", $applicant_id);
                            $ret['ApplicantCollege_CollegeId'] = $this->form->field_select('ApplicantCollege_CollegeId', $result->CollegeId ? $result->CollegeId : null, 'College Applied To or Attending', null, $this->college_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            //$ret['ApplicantCollege_Unlisted'.$i] = $this->form->field_textfield('ApplicantCollege_Unlisted'.$i, null,'',null, array('text'=>true)); //how are we handling "other" in the new DB?
                            $ret['Applicant_MajorId'] = $this->form->field_select('Applicant_MajorId', $result->MajorId ? $result->MajorId : 5122, 'Intended Major (If Uncertain, select Undecided)', null, $this->major_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_EducationAttainmentId'] = $this->form->field_select("Applicant_EducationAttainmentId", $result->EducationAttainmentId ? $result->EducationAttainmentId : null, "Year in School Fall Semester", array('option' => 'Select', 'value' => '5'), $this->educationalattainment_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_FirstGenerationStudent'] = $this->form->field_boolean('Applicant_FirstGenerationStudent', $result->FirstGenerationStudent ? $result->FirstGenerationStudent : 0, 'Are you the first person in your family to attend college?', null, array('col-md-6', 'col-sm-12'));
                            $ret[] = '<hr class="clear" />';
                            $ret['Applicant_HighSchoolId'] = $this->form->field_select('Applicant_HighSchoolId', $result->HighSchoolId ? $result->HighSchoolId : 136, "High School Attended", $result->HighSchoolId ? $result->HighSchoolId : null, $this->highschool_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_HighSchoolGraduationDate'] = $this->form->field_select('Applicant_HighSchoolGraduationDate', $result->HighSchoolGraduationDate ? date("Y", strtotime($result->HighSchoolGraduationDate)) : date("Y"), "Year of High School Graduation", date("Y"), $this->gradyr_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_HighSchoolGPA'] = $this->form->field_textfield('Applicant_HighSchoolGPA', $result->HighSchoolGPA ? $result->HighSchoolGPA : null, 'HS Weighted GPA', '0.00', array('required' => 'required', 'type' => 'number', 'minlength' => 1), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_PlayedHighSchoolSports'] = $this->form->field_boolean('Applicant_PlayedHighSchoolSports', $result->PlayedHighSchoolSports ? $result->PlayedHighSchoolSports : 0, 'Did you participate in sports while attending High School?');
                            $ret['Applicant_Activities'] = $this->form->field_textarea('Applicant_Activities',$result->Activities ? $result->Activities : '',"Activities participated in, with years active.");
                            break;
                        case 3:
                            //determine independance
                            //sets up the query
                            $data['tables']['Applicant'] = array('IsIndependent');
                            $data['tables']['ApplicantIndependent'] = array('ApplicantId', 'AdvancedDegree', 'Children', 'Married', 'TwentyFour', 'Veteran', 'Orphan', 'Emancipated', 'Homeless');
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];
                            //the fields
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 3);
                            $ret['Applicant_IsIndependent'] = $this->form->field_hidden('Applicant_IsIndependent', $result->IsIndependent ? $result->IsIndependent : null);
                            $ret['ApplicantIndependent_ApplicantId'] = $this->form->field_hidden("ApplicantIndependent_ApplicantId", $applicant_id);
                            $ret[] = "Do any of the following apply to you?";
                            $ret['ApplicantIndependent_AdvancedDegree'] = $this->form->field_boolean('ApplicantIndependent_AdvancedDegree', $result->AdvancedDegree ? $result->AdvancedDegree : null, 'Working on a Master\'s or Doctorate degree?', null, array('indybool'));
                            $ret['ApplicantIndependent_Children'] = $this->form->field_boolean('ApplicantIndependent_Children', $result->Children ? $result->Children : null, 'Have a child or other legal dependants?', null, array('indybool'));
                            $ret['ApplicantIndependent_Married'] = $this->form->field_boolean('ApplicantIndependent_Married', $result->Married ? $result->Married : null, 'Married?', null, array('indybool'));
                            $ret['ApplicantIndependent_TwentyFour'] = $this->form->field_boolean('ApplicantIndependent_TwentyFour', $result->TwentyFour ? $result->TwentyFour : null, 'At least 24 years old?', null, array('indybool'));
                            $ret['ApplicantIndependent_Veteran'] = $this->form->field_boolean('ApplicantIndependent_Veteran', $result->Veteran ? $result->Veteran : null, 'Veteran of the U.S. Armed Forces?', null, array('indybool'));
                            $ret['ApplicantIndependent_Orphan'] = $this->form->field_boolean('ApplicantIndependent_Orphan', $result->Orphan ? $result->Orphan : null, 'Deceased parents, in foster care, or ward of the court?', null, array('indybool'));
                            $ret['ApplicantIndependent_Emancipated'] = $this->form->field_boolean('ApplicantIndependent_Emancipated', $result->Emancipated ? $result->Emancipated : null, 'An emancipated child as determined by a court judge?', null, array('indybool'));
                            $ret['ApplicantIndependent_Homeless'] = $this->form->field_boolean('ApplicantIndependent_Homeless', $result->Homeless ? $result->Homeless : null, 'Homeless, at risk of being homeless as determined by the director of an HUD approved homeless shelter, testimonial program or high school liason?', null, array('indybool'));
                            //if any of the above apply, the student is independant. set this.
                            $jquery[] = "$('.indybool input').each(function(){
                            var sp = $('#Applicant_IsIndependent_input');
                            if($(this).is(':checked')){
                                sp.val(1);
                            } 
                        });";
                            $jquery[] = "$('.indybool input').click(function(){
                            var sp = $('#Applicant_IsIndependent_input');
                            if($(this).is(':checked')){
                                sp.val(1);
                            } else {
                                sp.val(0);
                                $('.indybool input').each(function(){
                                    if($(this).is(':checked')){
                                    sp.val(1);
                                    }
                                });
                            }
                        });";
                            break;
                        case 4:
                            //financial
                            //sets up the query
                            $data['tables']['Guardian'] = array('GuardianFullName1', 'GuardianEmployer1', 'GuardianFullName2', 'GuardianEmployer2', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                            $data['tables']['Applicant'] = array('IsIndependent', 'Employer', 'HardshipNote');
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];
                            //different fields for independent and dependent students
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 4);
                            if ($result->IsIndependent == true) {
                                //Independent Form
                                $ret['hdrFinancialInfo'] = $this->form->section_header('hdrFinancialInfo', 'Independent Student Financial Information');
                                $ret[''] = '';
                                $ret[] = "Indy form";
                            } else {
                                //Dependent Form
                                $jquery[] = "$('#SingleParent_input').each(function(){
                            var sp = $('.second-guardian');
                            if($(this).is(':checked')){
                                sp.slideUp(0);
                            } else {
                                sp.slideDown(0);
                            }
                        });";
                                $jquery[] = "$('#SingleParent_input').click(function(){
                            var sp = $('.second-guardian');
                            if($(this).is(':checked')){
                                sp.slideUp(500);
                            } else {
                                sp.slideDown(500);
                            }
                        });";
                                $ret['SingleParent'] = $this->form->field_boolean('SingleParent', strlen($result->GuardianFullName2 < 1), "Is this a single parent household?");
                                $ret['Guardian_ApplicantId'] = $this->form->field_hidden("Guardian_ApplicantId", $applicant_id);
                                $ret['Guardian_GuardianFullName1'] = $this->form->field_textfield('Guardian_GuardianFullName1', $result->GuardianFullName1 ? $result->GuardianFullName1 : null, "First Guardian Full Name");
                                $ret['Guardian_GuardianEmployer1'] = $this->form->field_textfield('Guardian_GuardianEmployer1', $result->GuardianEmployer1 ? $result->GuardianEmployer1 : null, "Place of Employment");
                                $ret[] = '<div class="second-guardian">';
                                $ret['Guardian_GuardianFullName2'] = $this->form->field_textfield('Guardian_GuardianFullName2', $result->GuardianFullName2 ? $result->GuardianFullName2 : null, "Second Guardian Full Name");
                                $ret['Guardian_GuardianEmployer2'] = $this->form->field_textfield('Guardian_GuardianEmployer2', $result->GuardianEmployer2 ? $result->GuardianEmployer2 : null, "Place of Employment");
                                $ret[] = '</div>';
                                $ret['Applicant_Employer'] = $this->form->field_textfield('Applicant_Employer', $result->Employer ? $result->Employer : null, "Applicant Employer");
                                //property
                                $ret['Guardian_Homeowner'] = $this->form->field_boolean('Guardian_Homeowner', $result->Homeowner ? $result->Homeowner : 0, "Do the applicant's parents own their home?");
                                $ret[] = '<div class="switchable">';
                                $ret['Guardian_HomeValue'] = $this->form->field_textfield('Guardian_HomeValue', $result->HomeValue ? $result->HomeValue : null, "Current Value");
                                $ret['Guardian_AmountOwedOnHome'] = $this->form->field_textfield('Guardian_AmountOwedOnHome', $result->AmountOwedOnHome ? $result->AmountOwedOnHome : null, "Amount Owed");
                                $ret[] = '</div>';
                                //hardships

                                $ret['Applicant_HardshipNote'] = $this->form->field_textarea('Applicant_HardshipNote', $result->HardshipNote ? $result->HardshipNote : null, "If applicable, please use this space to describe how you overcame hardships (family environment, health issues, or physical challenges, etc.) to achieve your dream of pursuing a college education.");
                            }
                            $btnTitle = "Save";
                            break;
                        case 5:
                            //final checks
                            //sets up query
                            //fields
                            break;
                    }
                    $jquery[] = '$("#' . $form_id . '").validate({
                    
		errorPlacement: function(error, element) {
			// Append error within linked label
			$( element )
				.closest( "form" )
					.find( "label[for=\'" + element.attr( "id" ) + "\']" )
						.append( error );
		},
		errorElement: "span",
		onfocusout: function(element) {
            // "eager" validation
            this.element(element);  
        }
});';

                    if ($step != 1){
                        $ftr['prev'] = $this->form->field_button('prevBtn', 'Back', array('prev', 'btn'));
                    }
                    $ftr['button'] = $this->form->field_button('saveBtn',$btnTitle,array('submit','btn'));

                    $ret['form_footer'] = $this->form->form_footer('form_footer',implode("\n",$ftr),array('form-footer', 'col-md-12'));

                    $ret['javascript'] = $this->form->build_jquery($form_id,$jquery);
                    break;
                default:
                    break;
            }
            $ret['nonce'] = wp_nonce_field( $form_id . $form_page_number );
            $ret['form_close'] = $this->form->form_close();
//utility to be removed
            foreach($ret AS $k => $v){
                $myk = str_replace('ApplicantIndependent_','',$k);
                //$crunch[] = "`".$myk."` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',";
                $crunch[] = '$result->'.$myk.'?$result->'.$myk.':null';
            }
            //ts_data(implode("\n",$crunch));
//end utility            
            return $ret;
        }

        function get_user_application_status(){
            global $current_user,$applicant_id,$wpdb;
            $sql = "SELECT * FROM ApplicationProcess,ProcessSteps WHERE ApplicationProcess.ApplicantId = ".$applicant_id." AND ApplicationProcess.ProcessStepId = ProcessSteps.StepId";
            $result = $wpdb->get_results($sql);
            if(count($result)>0) {
                $hdr = $this->form->section_header('ProcessHeader', 'Your Progress');
                foreach ($result AS $r) {
                    $progress[] = $r->StepName;
                }
                return $hdr . '<ul><li>' . implode('</li>' . "\n" . '<li>', $progress) . '</li></ul>';
            }
        }
    } //End Class
} //End if class exists statement