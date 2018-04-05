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
            //add_action('admin_menu', array(&$this,'settings_page'));
            add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            add_action('wp_enqueue_scripts',array(&$this,'set_up_globals'));
            //Filters

            //Shortcodes
            add_shortcode('application', array(&$this,'application_shortcode_handler'));

        }

        function set_up_globals(){
            global $current_user,$applicant_id,$user_id;
            $user_id = $current_user->ID;
            $applicant_id = $this->queries->get_applicant_id($user_id);
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

        function application_shortcode_handler($atts,$content){
            extract(shortcode_atts( array(
                'application' => 'default', //default to primary application
            ), $atts ));

            if($content == ''){
                $content = get_option('csf_settings_alt_text');
            }
            $start_date = strtotime(get_option('csf_settings_start_date'));
            $end_date = strtotime(get_option('csf_settings_end_date'));
            $portal_page = get_option('csf_settings_student_welcome_page');
            $today = time();
            if(!is_page($portal_page)){
                if(is_user_logged_in()){
                    return '<a href="'.get_permalink($portal_page).'" class="button">Proceed to Application Portal</a>';
                } else {
                    return '<div class="login-trigger"><span class="button">Login/Register</span></div>';
                }
            }
            if($today >= $start_date && $today <= $end_date){
                if(is_user_logged_in()){
                    $ret = array();
                    if(current_user_can('view_renewal_process')){
                        $ret[] = 'VIEW RENEWAL PROCESS';
                    }
                    if(current_user_can('view_award')){
                        $ret[] = 'VIEW AWARD';
                    }
                    if(current_user_can('submit_application')){
                        $ret[2] = implode("\n\r",$this->get_form('application'));
                    }
                    if(current_user_can('view_application_process')){
                        $ret[1] = $this->queries->get_user_application_status_list();
                    }
                    if(current_user_can('review_application')){
                        $ret[1] = $this->queries->get_user_application_status_list();
                        $ret[2] = implode("\n\r",$this->get_form('application'));
                    }
                    //add admin ability to see based on GET var.
                    sort($ret);
                    return implode("\n\r",$ret);
                } else {
                    return '<div class="login-trigger"><span class="button">Login/Register</span></div>';
                }
            } else {
                return $content;
            }
        }

        //ultilities



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
            $ret['form_header'] = $this->form->form_header($form_id,array($form_id));
            switch($form_id) {
                case 'application':
                    $form_page_number = isset($_POST['form_page_number']) ? $_POST['form_page_number'] : 1;
                    if($this->queries->get_user_application_status()>1){
                        $form_page_number = isset($_POST['form_page_number']) ? $_POST['form_page_number'] : 6;
                    }
                    if(current_user_can('review_application')){
                        $form_page_number = isset($_POST['form_page_number']) ? $_POST['form_page_number'] : 6;
                        $applicant_id = $_GET['applicant_id'];
                    }
                    $step = isset($_POST['form_page_next']) ? $_POST['form_page_next'] : 1;
                    $set['where'] = $applicant_id > 0 ? array('applicant' => 'applicant.ApplicantId = ' . $applicant_id) : array('applicant' => 'applicant.UserId = ' . $user_id);
                    switch ($step) {
                        case 1:
                            break;
                        case 2:
                            $set['where']['applicationprocess'] = 'applicationprocess.ApplicantId = ' . $applicant_id .' AND applicationprocess.ProcessStepId = 1';
                            break;
                        case 3:
                            $set['where']['applicantcollege'] = 'applicantcollege.ApplicantId = ' . $applicant_id;
                            break;
                        case 4:
                            $set['where']['applicantindependencequery'] = 'applicantindependencequery.ApplicantId = ' . $applicant_id;
                            break;
                        case 5:
                            if($this->queries->is_indy($applicant_id)){
                                $set['where']['applicantfinancial'] = 'applicantfinancial.ApplicantId = ' . $applicant_id;
                            } else {
                                $set['where']['guardian'] = 'guardian.ApplicantId = ' . $applicant_id;
                            }
                            break;
                        case 6:
                            if(!$this->queries->is_indy($applicant_id)) {
                                $set['where']['guardian'] = 'guardian.ApplicantId = ' . $applicant_id;
                            }
                            $set['where']['agreements'] = 'agreements.ApplicantId = ' . $applicant_id;
                            break;
                        case 7:
                            break;
                    }
                    if ($_POST['application_form']) {
                        //Do the stuff
                        print $this->queries->set_data($form_id . $form_page_number, $set['where']);
                        if(!$applicant_id){$applicant_id = $this->queries->get_applicant_id($current_user->ID);}
                        if(isset($_POST['UpdateApplicationDate'])){
                            $this->update_application_submission_date($applicant_id,$_POST['UpdateApplicationDate']);
                        }
                        if(isset($_POST['SendEmails'])){
                            $this->send_form_emails($_POST['SendEmails']);
                        }
                        if (!current_user_can('view_application_process') && !current_user_can('csf')) {
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
                    $this->college_array = $this->queries->get_select_array_from_db('College', 'CollegeId', 'Name','Name');
                    $this->major_array = $this->queries->get_select_array_from_db('Major', 'MajorId', 'MajorName','MajorName');
                    $this->educationalattainment_array = $this->queries->get_select_array_from_db('EducationalAttainment', 'EducationalAttainmentId', 'EducationalAttainment');
                    $this->highschool_array = $this->queries->get_select_array_from_db('HighSchool', 'HighSchoolId', 'SchoolName','SchoolName');
                    for ($yr = 2000; $yr <= date("Y"); $yr++) {
                        $this->gradyr_array[$yr.'-01-01'] = $yr;
                    }
                    $this->gradyr_array = array_reverse($this->gradyr_array);
                    //build the jquery
                    $jquery['prev'] = "$('#prevBtn_button').click(function(e){
                        e.preventDefault();
                        $('#".$form_id." #form_page_next').val(".($form_page_number - 1).");
                        $('#".$form_id."').submit();
                    });";
                    //can I bypass save on back button? do I want to?
                    $jquery['save'] = "$('#saveBtn_button').click(function(e){ 
                        e.preventDefault();
                        $('#".$form_id." #form_page_next').val(".($form_page_number + 1).");
                        $('#".$form_id."').submit();
                    });";
                    $jquery['toggleinit'] = "$('.ui-toggle-btn').each(function(){
                        var toggled = $(this).parent().next('.switchable');
                        if($(this).find('input[type=checkbox]').is(':checked')){
                            toggled.slideDown(0);
                        } else {
                            toggled.slideUp(0);
                        }
                    });";
                    $jquery['toggleclick'] = "$('.ui-toggle-btn').click(function(){
                            var toggled = $(this).parent().next('.switchable');
                            if($(this).find('input[type=checkbox]').is(':checked')){
                                toggled.slideDown(500);
                            } else {
                                toggled.slideUp(500);
                            }
                        });";
                    $jquery['phone'] = "$('input[type=tel]').mask('(000) 000-0000');";
                    //jquery to handle file upload hider
                    //TODO: sort out js validation
                    $fwdBtnTitle = "Save & Continue";
                    $backBtnTitle = "Save & Go Back";
                    $ret['form_type'] = $this->form->field_utility('application_form', true);
                    $ret['save_data'] = $this->form->field_utility('save_data', true);
                    $ret['form_page_number'] = $this->form->field_utility('form_page_number', 1);
                    $ret['form_page_next'] = $this->form->field_utility('form_page_next', $form_page_number + 1);
                    $ret['ApplicantId'] = $this->form->field_utility("ApplicantId", $applicant_id); //matching user_id to applicantID. HRM. This is autoincremented in the DB. We will need to create userids for all the old data and start UIDs at a higher number than exisiting applicant IDs
                    $data['where'] = 'applicant.ApplicantId = ' . $applicant_id;

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
                            $ret['Applicant_ApplicationDateTime'] = $this->form->field_hidden("Applicant_ApplicationDateTime", (strtotime($result->ApplicationDateTime) > 0) ? $result->ApplicationDateTime : date("Y-m-d H:i:s"));
                            $ret['Applicant_UserId'] = $this->form->field_hidden("Applicant_UserId", $user_id);
                            $ret['Applicant_Email'] = $this->form->field_hidden("Applicant_Email", $result->Email ? $result->Email : $current_user->user_email);
                            $ret['Applicant_FirstName'] = $this->form->field_textfield('Applicant_FirstName', $result->FirstName ? $result->FirstName : null, 'First Name', null, array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
                            $ret['Applicant_MiddleInitial'] = $this->form->field_textfield('Applicant_MiddleInitial', $result->MiddleInitial ? $result->MiddleInitial : null, 'Middle Initial', null, array(), array('col-md-2', 'col-sm-12'));
                            $ret['Applicant_LastName'] = $this->form->field_textfield('Applicant_LastName', $result->LastName ? $result->LastName : null, 'Last Name', null, array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
                            $ret['Applicant_Last4SSN'] = $this->form->field_textfield('Applicant_Last4SSN', $result->Last4SSN ? $result->Last4SSN : null, 'Last 4 numbers of your SS#', '0000', array('type' => 'number', 'maxlength' => 4, 'minlength' => 4, 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_DateOfBirth'] = $this->form->field_date('Applicant_DateOfBirth', $result->DateOfBirth ? $result->DateOfBirth : null, 'Date of Birth', array('required' => 'required', 'type' => 'date', 'date' => 'date'), array('datepicker', 'required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_Address1'] = $this->form->field_textfield('Applicant_Address1', $result->Address1 ? $result->Address1 : null, 'Address', '123 Any Street', array('type' => 'text', 'minlength' => '2', 'required' => 'required'), array('required', 'col-md-12'));
                            $ret['Applicant_Address2'] = $this->form->field_textfield('Applicant_Address2', $result->Address2 ? $result->Address2 : null, '', 'Apartment or Box number', array('type' => 'text'), array('col-md-12'));
                            $ret['Applicant_City'] = $this->form->field_textfield('Applicant_City', $result->City ? $result->City : null, 'City', null, array('type' => 'text', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
                            $ret['Applicant_StateId'] = $this->form->field_select('Applicant_StateId', $result->StateId ? $result->StateId : 'OH', 'State', array('option' => 'Select', 'value' => 'OH'), $this->states_array, array('required' => 'required'), array('required', 'col-md-2', 'col-sm-12'));
                            $ret['Applicant_CountyId'] = $this->form->field_select('Applicant_CountyId', $result->CountyId ? $result->CountyId : null, 'County', array('option' => 'Select', 'value' => '24'), $this->counties_array, null, array('col-md-3', 'col-sm-12'));
                            $ret['Applicant_ZipCode'] = $this->form->field_textfield('Applicant_ZipCode', $result->ZipCode ? $result->ZipCode : null, 'ZIP Code', '00000', array('type' => 'number', 'minlength' => 5, 'maxlength' => 10, 'required' => 'required'), array('required', 'col-md-2', 'col-sm-12'));
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
                            $data['tables']['Applicant'] = array('MajorId', 'EducationAttainmentId', 'HighSchoolGraduationDate', 'HighSchoolId', 'HighSchoolGraduationDate', 'HighSchoolGPA', 'PlayedHighSchoolSports', 'FirstGenerationStudent','Activities','OtherSchool');
                            $data['tables']['ApplicantCollege'] = array('CollegeId');
                            $data['where'] .= ' AND applicantcollege.ApplicantId = ' . $applicant_id;
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];
                            //the fields

                            $jquery[] = "$('#ApplicantCollege_CollegeId_input').each(function(){
                            var sp = $('.otherwrap');
                            if($(this).val() != '343'){
                                sp.slideUp(0);
                                sp.find($('input')).removeAttr('required');
                            } else {
                                sp.slideDown(0);
                                sp.find($('input')).attr('required','required');
                            }
                        });";
                            $jquery[] = "$('#ApplicantCollege_CollegeId_input').change(function(){
                            var sp = $('.otherwrap');
                            if($(this).val() != 343){
                                sp.slideUp(0);
                                sp.find($('input')).removeAttr('required');
                            } else {
                                sp.slideDown(0);
                                sp.find($('input')).attr('required','required');
                            }
                        });";
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 2);
                            $ret['hdrCollegeInfo'] = $this->form->section_header('hdrCollegeInfo', 'Academic Information');
                            $ret['ApplicantCollege_ApplicantId'] = $this->form->field_hidden("ApplicantCollege_ApplicantId", $applicant_id);
                            $ret['ApplicantCollege_CollegeId'] = $this->form->field_select('ApplicantCollege_CollegeId', $result->CollegeId ? $result->CollegeId : null, 'College Applied To or Attending', null, $this->college_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_MajorId'] = $this->form->field_select('Applicant_MajorId', $result->MajorId ? $result->MajorId : 5122, 'Intended Major (If Uncertain, select Undecided)', null, $this->major_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));

                            $ret['OtherWrapOpen'] = '<div class="otherwrap">';
                            $ret['Applicant_OtherSchool'] = $this->form->field_textfield('Applicant_OtherSchool', $result->OtherSchool?$result->OtherSchool:'','Name of Unlisted Institution',null, array('text'=>true),array('col-sm-12','required')); //how are we handling "other" in the new DB?
                            $ret['OtherWrapClose'] = '</div>';
                            $ret['Applicant_EducationAttainmentId'] = $this->form->field_select("Applicant_EducationAttainmentId", $result->EducationAttainmentId ? $result->EducationAttainmentId : null, "Year in School Fall Semester, 2018", array('option' => 'Select', 'value' => '5'), $this->educationalattainment_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_FirstGenerationStudent'] = $this->form->field_boolean('Applicant_FirstGenerationStudent', $result->FirstGenerationStudent ? $result->FirstGenerationStudent : 0, 'Are you the first person in your family to attend college?', null, array('col-md-6', 'col-sm-12'));
                            $ret[] = '<hr class="clear" />';
                            $ret['Applicant_HighSchoolId'] = $this->form->field_select('Applicant_HighSchoolId', $result->HighSchoolId ? $result->HighSchoolId : 136, "High School Attended", $result->HighSchoolId ? $result->HighSchoolId : null, $this->highschool_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_HighSchoolGraduationDate'] = $this->form->field_select('Applicant_HighSchoolGraduationDate', $result->HighSchoolGraduationDate ? $result->HighSchoolGraduationDate : date("Y").'-01-01', "Year of High School Graduation", array('value' => date("Y").'-01-01','option' => date("Y")), $this->gradyr_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_HighSchoolGPA'] = $this->form->field_textfield('Applicant_HighSchoolGPA', $result->HighSchoolGPA ? $result->HighSchoolGPA : null, 'HS Weighted GPA', '0.00', array('required' => 'required', 'type' => 'number', 'minlength' => 1), array('required', 'col-md-6', 'col-sm-12'));
                            $ret['Applicant_PlayedHighSchoolSports'] = $this->form->field_boolean('Applicant_PlayedHighSchoolSports', $result->PlayedHighSchoolSports ? $result->PlayedHighSchoolSports : 0, 'Did you participate in sports while attending High School?');
                            $ret['Applicant_Activities'] = $this->form->field_textarea('Applicant_Activities',$result->Activities ? $result->Activities : '',"Please list any activities participated in, with years active.",null,array('col-md-12'));
                            break;
                        case 3:
                            //determine independance
                            //sets up the query
                            $data['tables']['Applicant'] = array('IsIndependent');
                            $data['tables']['ApplicantIndependenceQuery'] = array('ApplicantId', 'AdvancedDegree', 'Children', 'Married', 'TwentyFour', 'Veteran', 'Orphan', 'Emancipated', 'Homeless');
                            $data['where'] .= ' AND applicantindependencequery.ApplicantId = ' . $applicant_id;
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];
                            //the fields
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 3);
                            $ret['Applicant_IsIndependent'] = $this->form->field_hidden('Applicant_IsIndependent', $result->IsIndependent ? $result->IsIndependent : 0);
                            $ret['ApplicantIndependenceQuery_ApplicantId'] = $this->form->field_hidden("ApplicantIndependenceQuery_ApplicantId", $applicant_id);
                            $ret[] = "Do any of the following apply to you?";
                            $ret['ApplicantIndependenceQuery_AdvancedDegree'] = $this->form->field_boolean('ApplicantIndependenceQuery_AdvancedDegree', $result->AdvancedDegree ? $result->AdvancedDegree : 0, 'Working on a Master\'s or Doctorate degree?', null, array('indybool'));
                            $ret['ApplicantIndependenceQuery_Children'] = $this->form->field_boolean('ApplicantIndependenceQuery_Children', $result->Children ? $result->Children : 0, 'Have a child or other legal dependants?', null, array('indybool'));
                            $ret['ApplicantIndependenceQuery_Married'] = $this->form->field_boolean('ApplicantIndependenceQuery_Married', $result->Married ? $result->Married : 0, 'Married?', 0, array('indybool'));
                            $ret['ApplicantIndependenceQuery_TwentyFour'] = $this->form->field_boolean('ApplicantIndependenceQuery_TwentyFour', $result->TwentyFour ? $result->TwentyFour : 0, 'At least 24 years old?', null, array('indybool'));
                            $ret['ApplicantIndependenceQuery_Veteran'] = $this->form->field_boolean('ApplicantIndependenceQuery_Veteran', $result->Veteran ? $result->Veteran : 0, 'Veteran of the U.S. Armed Forces?', null, array('indybool'));
                            $ret['ApplicantIndependenceQuery_Orphan'] = $this->form->field_boolean('ApplicantIndependenceQuery_Orphan', $result->Orphan ? $result->Orphan : 0, 'Deceased parents, in foster care, or ward of the court?', null, array('indybool'));
                            $ret['ApplicantIndependenceQuery_Emancipated'] = $this->form->field_boolean('ApplicantIndependenceQuery_Emancipated', $result->Emancipated ? $result->Emancipated : 0, 'An emancipated child as determined by a court judge?', null, array('indybool'));
                            $ret['ApplicantIndependenceQuery_Homeless'] = $this->form->field_boolean('ApplicantIndependenceQuery_Homeless', $result->Homeless ? $result->Homeless : 0, 'Homeless, at risk of being homeless as determined by the director of an HUD approved homeless shelter, testimonial program or high school liason?', null, array('indybool'));
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
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 4);
                            $data['tables']['Applicant'] = array('IsIndependent', 'Employer', 'HardshipNote');

                            //get the indy
                            if($this->queries->is_indy($applicant_id)){
                                //Independent Form
                                //sets up the query
                                $data['tables']['ApplicantFinancial'] = array('ApplicantEmployer','ApplicantIncome','SpouseEmployer','SpouseIncome', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                                $data['where'] .= ' AND applicantfinancial.ApplicantId = ' . $applicant_id;
                                $results = $this->queries->get_result_set($data);
                                $result = $results[0];
                                //form
                                $ret['hdrFinancialInfo'] = $this->form->section_header('hdrFinancialInfo', 'Independent Student Financial Information');
                                $ret['Applicant_Employer'] = $this->form->field_textfield('Applicant_Employer', $result->Employer ? $result->Employer : null, "Applicant Employer",null,null, array('col-md-6', 'col-sm-12'));
                                $ret['ApplicantFinancial_ApplicantId'] = $this->form->field_hidden("ApplicantFinancial_ApplicantId", $applicant_id);
                                $ret['ApplicantFinancial_ApplicantIncome'] = $this->form->field_textfield('ApplicantFinancial_ApplicantIncome', $result->ApplicantIncome ? $result->ApplicantIncome : null, "Applicant Annual Income",'00,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));

                                $ret['ApplicantFinancial_SpouseEmployer'] = $this->form->field_textfield('ApplicantFinancial_SpouseEmployer', $result->SpouseEmployer ? $result->SpouseEmployer : null, "Spouse Employer",null,null, array('col-md-6', 'col-sm-12'));
                                $ret['ApplicantFinancial_SpouseIncome'] = $this->form->field_textfield('ApplicantFinancial_SpouseIncome', $result->SpouseIncome ? $result->SpouseIncome : null, "Spouse Annual Income",'00,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));

                                $ret['ApplicantFinancial_Homeowner'] = $this->form->field_boolean('ApplicantFinancial_Homeowner', $result->Homeowner ? $result->Homeowner : 0, "Is the applicant a homeowner?",null, array('required', 'col-md-12'));
                                $ret[] = '<div class="switchable">';
                                $ret['ApplicantFinancial_HomeValue'] = $this->form->field_textfield('ApplicantFinancial_HomeValue', $result->HomeValue ? $result->HomeValue : null, "Current Value",'100,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));
                                $ret['ApplicantFinancial_AmountOwedOnHome'] = $this->form->field_textfield('ApplicantFinancial_AmountOwedOnHome', $result->AmountOwedOnHome ? $result->AmountOwedOnHome : null, "Amount Owed",'50,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));
                                $ret[] = '</div>';
                            } else {
                                //Dependent Form
                                //sets up the query
                                $data['tables']['Guardian'] = array('CPSPublicSchools','GuardianFullName1', 'GuardianEmployer1', 'GuardianFullName2', 'GuardianEmployer2', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                                $data['where'] .= ' AND guardian.ApplicantId = ' . $applicant_id;
                                $results = $this->queries->get_result_set($data);
                                $result = $results[0];
                                //form
                                $jquery[] = "$('#SingleParent_input[type=checkbox]').each(function(){
                            var sp = $('.second-guardian');
                            if($(this).is(':checked')){
                                sp.slideUp(0);
                            } else {
                                sp.slideDown(0);
                            }
                        });";
                                $jquery[] = "$('#SingleParent_input[type=checkbox]').click(function(){
                            var sp = $('.second-guardian');
                            if($(this).is(':checked')){
                                sp.slideUp(500);
                            } else {
                                sp.slideDown(500);
                            }
                        });";
                                $ret['hdrFinancialInfo'] = $this->form->section_header('hdrFinancialInfo', 'Student Guardianship and Financial Information');
                                $ret['SingleParent'] = $this->form->field_boolean('SingleParent', strlen($result->GuardianFullName2 < 1), "Is this a single parent household?",null, array('required', 'col-md-12'));
                                $ret['Guardian_CPSPublicSchools'] = $this->form->field_boolean('Guardian_CPSPublicSchools', $result->CPSPublicSchools?$result->CPSPublicSchools:0, "Is either of your parents employed by Cincinnati Public Schools?",null, array('required', 'col-md-12'));
                                $ret['Guardian_ApplicantId'] = $this->form->field_hidden("Guardian_ApplicantId", $applicant_id);
                                $ret['Guardian_GuardianFullName1'] = $this->form->field_textfield('Guardian_GuardianFullName1', $result->GuardianFullName1 ? $result->GuardianFullName1 : null, "First Guardian Full Name",null,array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                                $ret['Guardian_GuardianEmployer1'] = $this->form->field_textfield('Guardian_GuardianEmployer1', $result->GuardianEmployer1 ? $result->GuardianEmployer1 : null, "Place of Employment",null,array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
                                $ret[] = '<div class="second-guardian">';
                                $ret['Guardian_GuardianFullName2'] = $this->form->field_textfield('Guardian_GuardianFullName2', $result->GuardianFullName2 ? $result->GuardianFullName2 : null, "Second Guardian Full Name",null,null, array('col-md-6', 'col-sm-12'));
                                $ret['Guardian_GuardianEmployer2'] = $this->form->field_textfield('Guardian_GuardianEmployer2', $result->GuardianEmployer2 ? $result->GuardianEmployer2 : null, "Place of Employment",null,null, array('col-md-6', 'col-sm-12'));
                                $ret[] = '</div>';
                                $ret['Applicant_Employer'] = $this->form->field_textfield('Applicant_Employer', $result->Employer ? $result->Employer : null, "Applicant Employer",null,null, array('col-md-6', 'col-sm-12'));
                                //property
                                $ret['Guardian_Homeowner'] = $this->form->field_boolean('Guardian_Homeowner', $result->Homeowner ? $result->Homeowner : 0, "Do the applicant's parents own their home?",null, array('required', 'col-md-12'));
                                $ret[] = '<div class="switchable">';
                                $ret['Guardian_HomeValue'] = $this->form->field_textfield('Guardian_HomeValue', $result->HomeValue ? $result->HomeValue : null, "Current Value",'100,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));
                                $ret['Guardian_AmountOwedOnHome'] = $this->form->field_textfield('Guardian_AmountOwedOnHome', $result->AmountOwedOnHome ? $result->AmountOwedOnHome : null, "Amount Owed",'50,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));
                                $ret[] = '</div>';
                            }
                            //hardships
                            $ret['Applicant_HardshipNote'] = $this->form->field_textarea('Applicant_HardshipNote', $result->HardshipNote ? $result->HardshipNote : null, "If applicable, please use this space to describe how you overcame hardships (family environment, health issues, or physical challenges, etc.) to achieve your dream of pursuing a college education.",null,array('col-md-12'));

                            break;
                        case 5:
                            //final checks
                            //sets up query
                            $data['tables']['Applicant'] = array('InformationSharingAllowed');
                            if(!$this->queries->is_indy($applicant_id)) {
                                $data['tables']['Guardian'] = array('InformationSharingAllowedByGuardian');
                                $data['where'] .= ' AND guardian.ApplicantId = ' . $applicant_id;
                            }
                            $data['tables']['Agreements'] = array('ApplicantHaveRead','ApplicantDueDate','ApplicantDocsReq','ApplicantReporting','GuardianHaveRead','GuardianDueDate','GuardianDocsReq','GuardianReporting');
                            $data['where'] .= ' AND agreements.ApplicantId = ' . $applicant_id;
                            $results = $this->queries->get_result_set($data);
                            $result = $results[0];

                            $docs['tables']['Attachment'] = array('AttachmentId','AttachmentTypeId','FilePath');
                            $docs['where'] = 'applicantid = '.$applicant_id;
                            $documents = $this->queries->get_result_set($docs);
                            //fields
                            $fwdBtnTitle = "Save & Review";
                            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 5);
                            $ret['hdrAgreements'] = $this->form->section_header('hdrAgreements', 'Documents and Agreements');
                            $ret['Attachment_ApplicantId'] = $this->form->field_hidden("Attachment_ApplicantId", $applicant_id);
                            $ret['AttachmentCopy'] = '<div class="copy col-sm-12">Please upload all documents in PDF format.</div>';

                            $ret[] = '<div class="row">';
                            $ret[] = $this->form->file_management_front_end('Attachment_',$documents,array('col-sm-3'));
                            $jquery['filemanager'] = $this->form->get_file_manager_ajax('Attachment_',$documents);
                            $ret[] = '</div><br /><br />';

                            $ret['SRHeader'] = '<h3>Student Responsibility Agreements</h3>';
                            $ret['Agreements_ApplicantId'] = $this->form->field_hidden("Agreements_ApplicantId", $applicant_id);
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret[] = '<div class="copy">Guardian must agree to all Student Responsibility Agreements for any applicant under 18 years of age.</div>';
                            }
                            $ret['SRATableHdr'] = '<div class="table">';

                            $ret[] = '<table class="table">
                                <tr class="table-row">
                                    <th class="table-cell"></th>
                                    <th class="table-cell table-header">Student</th>';
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret[] = '<th class="table-cell table-header">Guardian</th>';
                            }
                            $ret[] = '</div>';


                            $ret[] = '<tr class="table-row">'; //styling???? add header
                            $ret[] = '<td class="table-cell">I/we have read and understand the "IMPORTANT INFORMATION ABOUT THE ON-LINE APPLICATION" prior to opening the application;</td>';
                            $ret['Agreements_ApplicantHaveRead'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantHaveRead', $result->ApplicantHaveRead?$result->ApplicantHaveRead:0,'',array('required')).'</td>';
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret['Agreements_GuardianHaveRead'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianHaveRead', $result->GuardianHaveRead?$result->GuardianHaveRead:0,'',array('required')).'</td>';
                            }
                            $ret[] = '</tr>';

                            $ret[] = '<tr class="table-row">';
                            $ret[] = '<td class="table-cell">I/we understand that applications submitted after the April 30, 2018 deadline will not be considered;</td>';
                            $ret['Agreements_ApplicantDueDate'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantDueDate', $result->ApplicantDueDate?$result->ApplicantDueDate:0,'',array('required')).'</td>';
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret['Agreements_GuardianDueDate'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianDueDate', $result->GuardianDueDate?$result->GuardianDueDate:0,'',array('required')).'</td>';
                            }
                            $ret[] = '</tr>';


                            $ret[] = '<tr class="table-row">';
                            $ret[] = '<td class="table-cell">I/we understand that the application is incomplete without my transcript, my Student Aid Report and the financial aid award notification from the school I have chosen to attend;</td>';
                            $ret['Agreements_ApplicantDocsReq'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantDocsReq', $result->ApplicantDocsReq?$result->ApplicantDocsReq:0,'',array('required')).'</td>';
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret['Agreements_GuardianDocsReq'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianDocsReq', $result->GuardianDocsReq?$result->GuardianDocsReq:0,'',array('required')).'</td>';
                            }
                            $ret[] = '</tr>';


                            $ret[] = '<tr class="table-row">';
                            $ret[] = '<td class="table-cell">I/we will report all other substantial scholarships received (other than state and federal grants and awards).</div>';
                            $ret['Agreements_ApplicantReporting'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantReporting', $result->ApplicantReporting?$result->ApplicantReporting:0,'',array('required')).'</div>';
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret['Agreements_GuardianReporting'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianReporting', $result->GuardianReporting?$result->GuardianReporting:0,'',array('required')).'</div>';
                            }
                            $ret[] = '</tr>';

                            $ret[] = '<tr class="table-row">';
                            $ret['InformationSharingCopy'] = '<td class="table-cell">Do you authorize the CSF to share the information on your scholarship application with other foundations looking for prospective recipients?</td>';
                            $ret['Applicant_InformationSharingAllowed'] = '<td class="table-cell">'.$this->form->field_boolean('Applicant_InformationSharingAllowed', $result->InformationSharingAllowed ? $result->InformationSharingAllowed : 0,'',array('required')).'</td>';
                            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                                $ret['Guardian_InformationSharingAllowedByGuardian'] = '<td class="table-cell">'.$this->form->field_boolean('Guardian_InformationSharingAllowedByGuardian', $result->InformationSharingAllowedByGuardian ? $result->InformationSharingAllowedByGuardian : 0,'',array('required')).'</td>';
                            }
                            $ret[] = '</tr>';
                            $ret['SRATableFtr'] = '</table>';

                            break;
                        case 6:
                        case 7:
                            $ret['Application'] = $this->get_the_user_application($applicant_id);
                            if($form_page_number == 6){
                                $fwdBtnTitle = "Submit Application";
                                if($this->queries->get_user_application_status()>1){
                                    $backBtnTitle = "Update Application";
                                } else {
                                    $backBtnTitle = "Go Back";
                                }
                                // Add Signing option
                                $ret['hdrSignature'] = $this->form->section_header('hdrSignature', 'Digital Signature and Submission');
                                $ret[] = '<div class="row">';
                                $ret['SigCopy'] = '<div class="copy col-sm-12">Please confirm application is ready for submission by signing with your last name and the last 4 digits of your Social Security Number.</div>';
                                $ret['Applicant_Signature'] = $this->form->field_textfield('Applicant_Signature','','Digital Signature','Lastname 0000',array(),array('required','col-sm-12'));
                                $ret[] = '</div>';
                                //to set the process "in motion"
                                $ret[] = '<div class="" id="ApplicationProcess_Update">';
                                $ret['ApplicationProcess_ApplicantId'] = $this->form->field_hidden("ApplicationProcess_ApplicantId", $applicant_id);
                                $ret['ApplicationProcess_ProcessStepId'] = $this->form->field_hidden("ApplicationProcess_ProcessStepId", 2);
                                $ret['ApplicationProcess_ProcessStepBool'] = $this->form->field_hidden("ApplicationProcess_ProcessStepBool", 1);
                                $ret['SendEmails'] = $this->form->field_utility('SendEmails','application_submitted');
                                $ret['UpdateApplicationDate'] = $this->form->field_utility('UpdateApplicationDate',date('Y-m-d H:i:s'));
                                $ret[] = '</div>';
                            }
                            if($form_page_number == 7){

                            }
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

                    if (($form_page_number != 1 && $form_page_number != 7)){
                        $ftr['prev'] = $this->form->field_button('prevBtn', $backBtnTitle, array('prev', 'btn'),'submit',false);
                    }
                    if($form_page_number != 7) {
                        $ftr['button'] = $this->form->field_button('saveBtn', $fwdBtnTitle, array('submit', 'btn'));
                    }
                    $ret['form_footer'] = $this->form->form_footer('form_footer',implode("\n",$ftr),array('form-footer', 'col-md-12'));

                    $ret['javascript'] = $this->form->build_jquery($form_id,$jquery);
                    break;
                default:
                    break;
            }
            $ret['nonce'] = wp_nonce_field( $form_id . $form_page_number );
            $ret['form_close'] = $this->form->form_close();
            return $ret;
        }

        function get_the_user_application($applicant_id){
            global $applicant_id;
            if(current_user_can('review_application')) {
                $adminview = true;
            }
            $personal['tables']['Applicant'] = array('*');
            $personal['where'] = 'applicant.ApplicantId = ' . $applicant_id;

            $college['tables']['ApplicantCollege'] = array('CollegeId');
            $college['where'] .= 'applicantcollege.ApplicantId = ' . $applicant_id;

            $independence['tables']['ApplicantIndependenceQuery'] = array('ApplicantId', 'AdvancedDegree', 'Children', 'Married', 'TwentyFour', 'Veteran', 'Orphan', 'Emancipated', 'Homeless');
            $independence['where'] .= 'applicantindependencequery.ApplicantId = ' . $applicant_id;

            if($this->queries->is_indy($applicant_id)) {
                $financial['tables']['ApplicantFinancial'] = array('ApplicantEmployer', 'ApplicantIncome', 'SpouseEmployer', 'SpouseIncome', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                $financial['where'] .= 'applicantfinancial.ApplicantId = ' . $applicant_id;
            } else {
                $financial['tables']['Guardian'] = array('CPSPublicSchools','GuardianFullName1', 'GuardianEmployer1', 'GuardianFullName2', 'GuardianEmployer2', 'Homeowner', 'HomeValue', 'AmountOwedOnHome','InformationSharingAllowedByGuardian');
                $financial['where'] .= 'guardian.ApplicantId = ' . $applicant_id;
            }
            $agreements['tables']['Agreements'] = array('ApplicantHaveRead','ApplicantDueDate','ApplicantDocsReq','ApplicantReporting','GuardianHaveRead','GuardianDueDate','GuardianDocsReq','GuardianReporting');
            $agreements['where'] .= 'agreements.ApplicantId = ' . $applicant_id;

            $queries = array('personal','college','independence','financial','agreements');
            foreach($queries AS $query){
                $result_array = $this->queries->get_result_set(${$query});
                $results[$query] = $result_array[0];
            }

            $docs['tables']['Attachment'] = array('AttachmentId','AttachmentTypeId','FilePath');
            $docs['where'] = 'attachment.ApplicantID = '.$applicant_id;
            $documents = $this->queries->get_result_set($docs);

            $applicant_user_id = $this->queries->get_user_id_by_applicant($applicant_id);
            $applicant_user = get_user_by('ID',$applicant_user_id);
            //test to see if there is one of each type
            //if so, set the next process step: documents uploaded.
            $ret['hdrPersInfo'] = $this->form->section_header('hdrPersInfo', 'Personal Information');
            $ret[] = '<div class="row">';
            $ret['form_page_number'] = $this->form->field_utility('form_page_number', 6);
            $ret['Applicant_FirstName'] = $this->form->field_result('Applicant_FirstName', $results['personal']->FirstName ? $results['personal']->FirstName : null, 'First Name', array('required', 'col-md-5', 'col-sm-12'));
            $ret['Applicant_MiddleInitial'] = $this->form->field_result('Applicant_MiddleInitial', $results['personal']->MiddleInitial ? $results['personal']->MiddleInitial : null, 'Middle Initial', array('col-md-2', 'col-sm-12'));
            $ret['Applicant_LastName'] = $this->form->field_result('Applicant_LastName', $results['personal']->LastName ? $results['personal']->LastName : null, 'Last Name', array('required', 'col-md-5', 'col-sm-12'));
            $ret['user_email'] = $this->form->field_result('user_email', '<a href="mailto:'.$applicant_user->user_email.'">'.$applicant_user->user_email.'</a>', 'Applicant Email Address',array('col-md-6', 'col-sm-12'));
            $ret['Applicant_Last4SSN'] = $this->form->field_result('Applicant_Last4SSN', $results['personal']->Last4SSN ? $results['personal']->Last4SSN : null, 'Last 4 numbers of your SS#',  array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_DateOfBirth'] = $this->form->field_result('Applicant_DateOfBirth', $results['personal']->DateOfBirth ? $results['personal']->DateOfBirth : null, 'Date of Birth', array('datepicker', 'required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_Address1'] = $this->form->field_result('Applicant_Address1', $results['personal']->Address1 ? $results['personal']->Address1 : null, 'Address',  array('required', 'col-md-12'));
            $ret['Applicant_Address2'] = $this->form->field_result('Applicant_Address2', $results['personal']->Address2 ? $results['personal']->Address2 : null, '', array('col-md-12'));
            $ret['Applicant_City'] = $this->form->field_result('Applicant_City', $results['personal']->City ? $results['personal']->City : null, 'City',  array('required', 'col-md-5', 'col-sm-12'));
            $ret['Applicant_StateId'] = $this->form->field_result('Applicant_StateId', $results['personal']->StateId ? $this->queries->get_state_by_id($results['personal']->StateId) : 'OH', 'State', array('required', 'col-md-2', 'col-sm-12'));
            $ret['Applicant_CountyId'] = $this->form->field_result('Applicant_CountyId', $results['personal']->CountyId ? $this->queries->get_county_by_id($results['personal']->CountyId) : null, 'County', array('col-md-3', 'col-sm-12'));
            $ret['Applicant_ZipCode'] = $this->form->field_result('Applicant_ZipCode', $results['personal']->ZipCode ? $results['personal']->ZipCode : null, 'ZIP Code', array('required', 'col-md-2', 'col-sm-12'));
            $ret['Applicant_CellPhone'] = $this->form->field_result('Applicant_CellPhone', $results['personal']->CellPhone ? $results['personal']->CellPhone : null, 'Mobile Phone Number', array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_AlternativePhone'] = $this->form->field_result('Applicant_AlternativePhone', $results['personal']->AlternativePhone ? $results['personal']->AlternativePhone : null, 'Alternative Phone Number', array('col-md-6', 'col-sm-12'));
            $ret[] = '</div>';
            //some optional fields
            $ret[] = '<hr class="col-md-12">';
            $ret[] = '<div class="row">';
            $ret['disclaim'] = '<div class="copy col-sm-12">The Cincinnati Scholarship Foundation administers some scholarships that are restricted to members of a certain ethnic background or gender. While you are not required to supply this information, it may be to your advantage to do so.</div>';
            $ret['Applicant_EthnicityId'] = $this->form->field_result('Applicant_EthnicityId', $results['personal']->EthnicityId ? $this->queries->get_ethnicity_by_id($results['personal']->EthnicityId) : null, 'Ethnicity', array('col-md-6', 'col-sm-12'));
            $ret['Applicant_SexId'] = $this->form->field_result('Applicant_SexId', $results['personal']->SexId ?  $this->queries->get_sex_by_id($results['personal']->SexId) : null, 'Gender', array('col-md-6', 'col-sm-12'));
            $ret[] = '</div>';
            $ret['hdrCollegeInfo'] = $this->form->section_header('hdrCollegeInfo', 'Academic Information');

            $ret[] = '<div class="row">';
            $ret['ApplicantCollege_CollegeId'] = $this->form->field_result('ApplicantCollege_CollegeId', $results['college']->CollegeId ? $this->queries->get_college_by_id($results['college']->CollegeId) : '', 'College Applied To or Attending',  array('required', 'col-md-6', 'col-sm-12'));
            if($results['college']->CollegeId == 343){
                $ret['Applicant_OtherSchool'] = $this->form->field_result('Applicant_OtherSchool', $results['personal']->OtherSchool?$results['personal']->OtherSchool:'','Name of Unlisted Institution',array('col-sm-12','required')); //how are we handling "other" in the new DB?
            }
            $ret['Applicant_MajorId'] = $this->form->field_result('Applicant_MajorId', $results['personal']->MajorId ? $this->queries->get_major_by_id($results['personal']->MajorId) : '', 'Intended Major (If Uncertain, select Undecided)', array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_EducationAttainmentId'] = $this->form->field_result("Applicant_EducationAttainmentId", $results['personal']->EducationAttainmentId ? $this->queries->get_educationalattainment_by_id($results['personal']->EducationAttainmentId) : null, "Year in School Fall Semester, 2018", array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_FirstGenerationStudent'] = $this->form->field_result('Applicant_FirstGenerationStudent', $results['personal']->FirstGenerationStudent ? 'YES' : 'NO', 'Are you the first person in your family to attend college?',  array('col-md-6', 'col-sm-12'));
            $ret[] = '<hr class="clear" />';
            $ret['Applicant_HighSchoolId'] = $this->form->field_result('Applicant_HighSchoolId', $results['personal']->HighSchoolId ? $this->queries->get_highschool_by_id($results['personal']->HighSchoolId) : '', "High School Attended",  array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_HighSchoolGraduationDate'] = $this->form->field_result('Applicant_HighSchoolGraduationDate', $results['personal']->HighSchoolGraduationDate ? date("Y",strtotime($results['personal']->HighSchoolGraduationDate)) : '', "Year of High School Graduation", array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_HighSchoolGPA'] = $this->form->field_result('Applicant_HighSchoolGPA', $results['personal']->HighSchoolGPA ? $results['personal']->HighSchoolGPA : null, 'HS Weighted GPA', array('required', 'col-md-6', 'col-sm-12'));
            $ret['Applicant_PlayedHighSchoolSports'] = $this->form->field_result('Applicant_PlayedHighSchoolSports', $results['personal']->PlayedHighSchoolSports ? 'YES' : 'NO', 'Did you participate in sports while attending High School?',array('col-md-6', 'col-sm-12'));
            $ret['Applicant_Activities'] = $this->form->field_result('Applicant_Activities',$results['personal']->Activities ? $results['personal']->Activities : '',"Please list any activities participated in, with years active.",array('col-md-12'));
            $ret[] = '</div>';

            if ($this->queries->is_indy($applicant_id)) {
                $ret['hdrFinancialInfo'] = $this->form->section_header('hdrFinancialInfo', 'Independent Student Financial Information');
                $ret[] = '<div class="row">';
                $ret['FinancialInfoCopy'] = '<div class="copy col-sm-12">You are an <strong>Independent Applicant</strong>.</div>';
                $ret['Applicant_Employer'] = $this->form->field_result('Applicant_Employer', $results['personal']->Employer ? $results['personal']->Employer : null, "Applicant Employer", array('col-md-6', 'col-sm-12'));
                $ret['ApplicantFinancial_ApplicantIncome'] = $this->form->field_result('ApplicantFinancial_ApplicantIncome', $results['financial']->ApplicantIncome ? $results['financial']->ApplicantIncome : null, "Applicant Annual Income", array('col-md-6', 'col-sm-12'));

                $ret['ApplicantFinancial_SpouseEmployer'] = $this->form->field_result('ApplicantFinancial_SpouseEmployer', $results['financial']->SpouseEmployer ? $results['financial']->SpouseEmployer : null, "Spouse Employer", array('col-md-6', 'col-sm-12'));
                $ret['ApplicantFinancial_SpouseIncome'] = $this->form->field_result('ApplicantFinancial_SpouseIncome', $results['financial']->SpouseIncome ? $results['financial']->SpouseIncome : null, "Spouse Annual Income",array('col-md-6', 'col-sm-12'));

                $ret['ApplicantFinancial_Homeowner'] = $this->form->field_result('ApplicantFinancial_Homeowner', $results['financial']->Homeowner ? 'YES' : 'NO', "Is the applicant a homeowner?", array('required', 'col-md-12'));
                if($results['financial']->Homeowner) {
                    $ret['ApplicantFinancial_HomeValue'] = $this->form->field_result('ApplicantFinancial_HomeValue', $results['financial']->HomeValue ? $results['financial']->HomeValue : null, "Current Value",  array('col-md-6', 'col-sm-12'));
                    $ret['ApplicantFinancial_AmountOwedOnHome'] = $this->form->field_result('ApplicantFinancial_AmountOwedOnHome', $results['financial']->AmountOwedOnHome ? $results['financial']->AmountOwedOnHome : null, "Amount Owed",  array('col-md-6', 'col-sm-12'));
                }
            } else {
                $ret['hdrFinancialInfo'] = $this->form->section_header('hdrFinancialInfo', 'Student Guardianship and Financial Information');
                $ret[] = '<div class="row">';
                $ret['FinancialInfoCopy'] = '<div class="copy col-sm-12">You are a <strong>Dependent Applicant</strong>.</div>';
                $ret['Guardian_GuardianFullName1'] = $this->form->field_result('Guardian_GuardianFullName1', $results['financial']->GuardianFullName1 ? $results['financial']->GuardianFullName1 : null, "First Guardian Full Name", array('required', 'col-md-6', 'col-sm-12'));
                $ret['Guardian_GuardianEmployer1'] = $this->form->field_result('Guardian_GuardianEmployer1', $results['financial']->GuardianEmployer1 ? $results['financial']->GuardianEmployer1 : null, "Place of Employment", array('required', 'col-md-6', 'col-sm-12'));
                $ret['Guardian_GuardianFullName2'] = $this->form->field_result('Guardian_GuardianFullName2', $results['financial']->GuardianFullName2 ? $results['financial']->GuardianFullName2 : null, "Second Guardian Full Name", array('col-md-6', 'col-sm-12'));
                $ret['Guardian_GuardianEmployer2'] = $this->form->field_result('Guardian_GuardianEmployer2', $results['financial']->GuardianEmployer2 ? $results['financial']->GuardianEmployer2 : null, "Place of Employment", array('col-md-6', 'col-sm-12'));
                $ret['Applicant_Employer'] = $this->form->field_result('Applicant_Employer', $results['personal']->Employer ? $results['personal']->Employer : null, "Applicant Employer", array('col-md-6', 'col-sm-12'));
                //property
                $ret['Guardian_Homeowner'] = $this->form->field_result('Guardian_Homeowner', $results['financial']->Homeowner ? 'YES' : 'NO', "Do the applicant's parents own their home?",array('required', 'col-md-12'));
                if($results['financial']->Homeowner) {
                    $ret['Guardian_HomeValue'] = $this->form->field_result('Guardian_HomeValue', $results['financial']->HomeValue ? $results['financial']->HomeValue : null, "Current Value",  array('col-md-6', 'col-sm-12'));
                    $ret['Guardian_AmountOwedOnHome'] = $this->form->field_result('Guardian_AmountOwedOnHome', $results['financial']->AmountOwedOnHome ? $results['financial']->AmountOwedOnHome : null, "Amount Owed",  array('col-md-6', 'col-sm-12'));
                }
            }
            //hardships
            $ret['Applicant_HardshipNote'] = $this->form->field_result('Applicant_HardshipNote', $results['personal']->HardshipNote ? $results['personal']->HardshipNote : null, "If applicable, please use this space to describe how you overcame hardships (family environment, health issues, or physical challenges, etc.) to achieve your dream of pursuing a college education.", array('col-md-12'));
            $ret[] = '</div>';

            $ret['hdrAgreements'] = $this->form->section_header('hdrAgreements', 'Documents and Agreements');

            //documents
            $ret['AttachmentDisplay'] = $this->form->attachment_display('AttachmentDisplay',$documents,"Your Uploaded Documents");

            //agreements

            $ret['SRHeader'] = '<h3>Student Responsibility Agreements</h3>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret[] = '<div class="copy">Guardian must agree to all Student Responsibility Agreements for any applicant under 18 years of age.</div>';
            }
            $ret['SRATableHdr'] = '<div class="table">';

            $ret[] = '<table class="table">
                                <tr class="table-row">
                                    <th class="table-cell"></th>
                                    <th class="table-cell table-header">Student</th>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret[] = '<th class="table-cell table-header">Guardian</th>';
            }
            $ret[] = '</div>';


            $ret[] = '<tr class="table-row">'; //styling???? add header
            $ret[] = '<td class="table-cell">I/we have read and understand the "IMPORTANT INFORMATION ABOUT THE ON-LINE APPLICATION" prior to opening the application;</td>';
            $ret['Agreements_ApplicantHaveRead'] = '<td class="table-cell">'.$this->form->field_result('Agreements_ApplicantHaveRead', $results['agreements']->ApplicantHaveRead? 'YES' : 'NO').'</td>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret['Agreements_GuardianHaveRead'] = '<td class="table-cell">'.$this->form->field_result('Agreements_GuardianHaveRead', $results['agreements']->GuardianHaveRead? 'YES' : 'NO').'</td>';
            }
            $ret[] = '</tr>';

            $ret[] = '<tr class="table-row">';
            $ret[] = '<td class="table-cell">I/we understand that applications submitted after the April 30, 2018 deadline will not be considered;</td>';
            $ret['Agreements_ApplicantDueDate'] = '<td class="table-cell">'.$this->form->field_result('Agreements_ApplicantDueDate', $results['agreements']->ApplicantDueDate? 'YES' : 'NO').'</td>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret['Agreements_GuardianDueDate'] = '<td class="table-cell">'.$this->form->field_result('Agreements_GuardianDueDate', $results['agreements']->GuardianDueDate? 'YES' : 'NO').'</td>';
            }
            $ret[] = '</tr>';


            $ret[] = '<tr class="table-row">';
            $ret[] = '<td class="table-cell">I/we understand that the application is incomplete without my transcript, my Student Aid Report and the financial aid award notification from the school I have chosen to attend;</td>';
            $ret['Agreements_ApplicantDocsReq'] = '<td class="table-cell">'.$this->form->field_result('Agreements_ApplicantDocsReq', $results['agreements']->ApplicantDocsReq? 'YES' : 'NO').'</td>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret['Agreements_GuardianDocsReq'] = '<td class="table-cell">'.$this->form->field_result('Agreements_GuardianDocsReq', $results['agreements']->GuardianDocsReq? 'YES' : 'NO').'</td>';
            }
            $ret[] = '</tr>';


            $ret[] = '<tr class="table-row">';
            $ret[] = '<td class="table-cell">I/we will report all other substantial scholarships received (other than state and federal grants and awards).</div>';
            $ret['Agreements_ApplicantReporting'] = '<td class="table-cell">'.$this->form->field_result('Agreements_ApplicantReporting', $results['agreements']->ApplicantReporting? 'YES' : 'NO').'</div>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret['Agreements_GuardianReporting'] = '<td class="table-cell">'.$this->form->field_result('Agreements_GuardianReporting', $results['agreements']->GuardianReporting? 'YES' : 'NO').'</div>';
            }
            $ret[] = '</tr>';

            $ret[] = '<tr class="table-row">';
            $ret['InformationSharingCopy'] = '<td class="table-cell">Do you authorize the CSF to share the information on your scholarship application with other foundations looking for prospective recipients?</td>';
            $ret['Applicant_InformationSharingAllowed'] = '<td class="table-cell">'.$this->form->field_result('Applicant_InformationSharingAllowed', $results['personal']->InformationSharingAllowed ? 'YES' : 'NO').'</td>';
            if(!$this->queries->is_indy($applicant_id) && !$this->queries->is_adult($applicant_id)){
                $ret['Guardian_InformationSharingAllowedByGuardian'] = '<td class="table-cell">'.$this->form->field_result('Guardian_InformationSharingAllowedByGuardian', $results['financial']->InformationSharingAllowedByGuardian ? 'YES' : 'NO').'</td>';
            }
            $ret[] = '</tr>';
            $ret['SRATableFtr'] = '</table>';

            return implode("\n\r",$ret);;
        }

        function send_form_emails($type){
            global $current_user,$applicant_id,$wpdb;
            if(!$applicant_id){$applicant_id = $this->queries->get_applicant_id($current_user->ID);}
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $css = '<style>.table,label{max-width:100%}.row:after,hr{clear:both}td,th{text-align:left}html{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}hr{height:0;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box}input{margin:0;font:inherit;font-family:inherit;line-height:inherit}body,h3{font-family:sans-serif;text-rendering:optimizeLegibility!important;-webkit-font-smoothing:antialiased!important}input::-moz-focus-inner{padding:0;border:0}hr,table{border-collapse:collapse}.table .table,body{background-color:#fff}*,:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}html{-webkit-tap-highlight-color:transparent;font-size:62.5%}h3{color:inherit;margin-top:20px;margin-bottom:10px}.row{margin-right:-15px;margin-left:-15px}.col-md-12,.col-md-2,.col-md-3,.col-md-5,.col-md-6,.col-sm-12,.col-xs-12{position:relative;min-height:1px;padding-right:15px;padding-left:15px}.col-xs-12{float:left;width:100%}@media (min-width:768px){.col-sm-12{float:left;width:100%}}@media (min-width:992px){.col-md-12,.col-md-2,.col-md-3,.col-md-5,.col-md-6{float:left}.col-md-12{width:100%}.col-md-6{width:50%}.col-md-5{width:41.66666667%}.col-md-3{width:25%}.col-md-2{width:16.66666667%}}.table,input,table{width:100%}table{background-color:transparent}.table{margin-bottom:20px}.table>tbody>tr>td,.table>tbody>tr>th{padding:8px;line-height:1.42857143;vertical-align:top;border-top:1px solid #ddd}input,input:focus{border:1px solid #CCC}label{display:inline-block;margin-bottom:5px;font-weight:700}.row:after,.row:before{display:table;content:" "}.hidden{display:none!important}@-ms-viewport{width:device-width}body{font-weight:400;font-size:1em;color:#2A2B30;line-height:1.625;margin:0}input:focus{transition:all .1s ease-in-out;outline:0}hr{border:0;border-top:1px solid #CCC;margin:1em 0}strong{font-weight:700}h3{font-weight:300;line-height:1.2;margin:0 0 10px;font-size:1.8em}input,th{font-weight:400}input{background-color:#FCFCFC;color:#333;font-size:18px;font-size:1.8rem;padding:16px}::-moz-placeholder{color:#333;opacity:1}::-webkit-input-placeholder{color:#333}table{border-spacing:0;line-height:2;margin-bottom:40px;word-break:normal}form h3.section-header,tbody{border-bottom:1px solid #CCC}td{border-top:1px solid #CCC;padding:6px}th{padding:0 6px}td:first-child,th:first-child{padding-left:0}form span.result{padding:0 1em}.documents.grid{margin-bottom:3em}.documents.grid .document.grid-item{text-align:center}.documents.grid .document.grid-item a{border:1px solid #3f829c;padding:1em;display:block}.documents.grid .document.grid-item a:hover{background-color:#ffbe0b}.documents.grid .document.grid-item a i{font-size:2em}</style>';
            switch($type) {
                case 'application_submitted':
                    $emails['user']['header'] = $headers;
                    $emails['user']['to'] = $current_user->display_name . ' <' . $current_user->user_email . '>';
                    $emails['user']['subject'] = 'Your Application has been Submitted';
                    $emails['user']['message'] = 'Your application has been received. If you have any questions regarding the application process, please call or email the Cincinnati Scholarship Foundation.';

                    $adminaddys = explode(',', get_option('csf_settings_admin_address'));
                    foreach ($adminaddys AS $k => $addy) {
                        $emails['admin_' . $k]['header'] = $headers;
                        $emails['admin_' . $k]['to'] = $addy;
                        $emails['admin_' . $k]['subject'] = $current_user->display_name . ' Submitted an Application';
                        $emails['admin_' . $k]['message'] = '<html><head>' . $css . '</head><body>' . $this->get_the_user_application($applicant_id) . '</body></html>';
                    }
                    break;
            }
            foreach($emails AS $email){
                //ts_data($email);
                wp_mail($email['to'],$email['subject'],$email['message'],$email['header']);
            }
        }

        function update_application_submission_date($applicant_id,$time){
            global $wpdb;
            $get = array();
            $get['tables']['Applicant'] = array('ApplicationDateTime','Notes');
            $get['where'] = 'applicant.ApplicantId = ' . $applicant_id;
            $results = $this->queries->get_result_set($get);
            $result = $results[0];

            $notes[] = $result->Notes;
            $notes[] = '"Application started: '.$result->ApplicationDateTime.'"';

            $sql = 'UPDATE applicant SET applicant.Notes = '.implode("\n",$notes).', applicant.ApplicationDateTime = "'.$time.'" WHERE applicant.ApplicantId = '.$applicant_id.';';
            $result = $wpdb->get_results($sql);
            if(is_wp_error($result)){
                error_log('Error updating submission date');
            }
        }

    } //End Class
} //End if class exists statement