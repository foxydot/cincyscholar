<?php
class MSDLAB_Report_Output{

    /**
     * A reference to an instance of this class.
     */
    private static $instance;
    private $variable;
    private $export_header;
    private $export_csv;
    private $skipcsv;


    var $sex_array;
    var $ethnicity_array;
    var $states_array;
    var $counties_array;
    var $college_array;
    var $major_array;
    var $educationalattainment_array;
    var $highschool_array;
    var $highschool_type_array;
    var $gradyr_array;
    var $col_gradyr_array;

    public function __construct() {
        if(class_exists('MSDLAB_FormControls')){
            $this->form = new MSDLAB_FormControls();
        }
        if(class_exists('MSDLAB_Queries')){
            $this->queries = new MSDLAB_Queries();
        }
        $this->skipcsv = array('Activities','HardshipNote','CoopStudyAbroadNote');
        add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles_and_scripts'));
    }

    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_Report_Output();
        }

        return self::$instance;

    }

    //util
    private function set_form_select_options(){
        $this->sex_array = $this->queries->get_select_array_from_db('Sex', 'SexId', 'Sex');
        $this->ethnicity_array = $this->queries->get_select_array_from_db('Ethnicity', 'EthnicityId', 'Ethnicity');
        $this->states_array = $this->queries->get_select_array_from_db('State', 'StateId', 'State');
        $this->counties_array = $this->queries->get_select_array_from_db('County', 'CountyId', 'County');
        $this->college_array = $this->queries->get_select_array_from_db('College', 'CollegeId', 'Name','Name',1);
        $this->major_array = $this->queries->get_select_array_from_db('Major', 'MajorId', 'MajorName','MajorName',1);
        $this->educationalattainment_array = $this->queries->get_select_array_from_db('EducationalAttainment', 'EducationalAttainmentId', 'EducationalAttainment');
        $this->highschool_array = $this->queries->get_select_array_from_db('HighSchool', 'HighSchoolId', 'SchoolName','SchoolName',1);
        $this->highschool_type_array = $this->queries->get_select_array_from_db('HighSchoolType', 'HighSchoolTypeId', 'Description','HighSchoolTypeId');
        for ($yr = date("Y")-18; $yr <= date("Y")+2; $yr++) {
            $this->gradyr_array[$yr.'-01-01'] = $yr;
        }
        $this->gradyr_array = array_reverse($this->gradyr_array);
        for ($yr = date("Y"); $yr <= date("Y")+10; $yr++) {
            $this->col_gradyr_array[$yr.'-01-01'] = $yr;
        }
        $this->col_gradyr_array = array_reverse($this->col_gradyr_array);
    }
    function add_admin_styles_and_scripts(){
        global $current_screen;
        $allowedpages = array(
            'csf-management_page_csf-report',
            'csf-management_page_csf-renewals',
            'csf-management_page_csf-need',
            'csf-management_page_csf-students',
        );
        if(in_array($current_screen->id,$allowedpages)){
            wp_enqueue_script('sorttable',plugin_dir_url(__DIR__).'/../js/sorttable.js');
        }
    }

    /**
     * Print a table
     *
     * @param array $fields An array of field objects.
     * @param array $info The result information.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The footer to be printed, or void if the param $echo is true.
     */
    public function print_table($id, $fields, $result, $info, $class = array(), $echo = true){
        $class = implode(" ",apply_filters('msdlab_csf_report_display_table_class', $class));
        $ret = array();
        $ret['start_table'] = '<table id="'.$id.'" class="'.$class.'">';
        $ret['table_header'] = $this->table_header($fields,false);
        $ret['table_data'] = $this->table_data($fields,$result,false);
        $ret['table_footer'] = $this->table_footer($fields,$info,false);
        $ret['end_table'] = '</table>';
        $ret['export'] = $this->print_export_tools($id);

        if($echo){
            print implode("\n\r", $ret);
        } else {
            return $ret;
        }
    }

    /**
     * Create a Table Header for the result set display
     *
     * @param array $fields An array of field objects.
     * @param array $class An array of class names to add to the wrapper.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The header to be printed, or void if the param $echo is true.
     */
    public function table_header($fields, $echo = true){
        $ret = array();
        $exh = array();
        foreach($fields AS $key => $value){
            $ret[] = '<th>'.$value.'</th>';
            if(!in_array($value,$this->skipcsv)) {
                $exh[] = $this->csv_safe($value);
            }
        }

        $this->export_header = implode(",",$exh);

        if($echo){
            print $ret = apply_filters('msdlab_csf_report_display_table_header','<tr>'.implode("\n\r", $ret).'<tr>');
        } else {
            return '<tr>'.implode("\n\r", $ret).'<tr>';
        }
    }

    public function csv_safe($value){
        //$value = preg_replace('%\'%i','‘',$value);
        $value = strip_tags($value,'<p><a>');
        $value = preg_replace("/<a.+href=['|\"]([^\"\']*)['|\"].*>(.+)<\/a>/i",'\2 (\1)',$value);
        $value = preg_replace('^[\r\n]+^',"\n",$value);
        $value = '"'.$value.'"';
        return $value;
    }

    /**
     * Prepare result set in a nice table
     *
     * @param array $fields An array of field objects.
     * @param array $info The result information.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The footer to be printed, or void if the param $echo is true.
     */
    public function table_data($fields, $result, $echo = true){
        $ret = array();
        $ecsv = array();
        $i = 0;
        $portal_page = get_option('csf_settings_student_welcome_page');
        foreach($result as $k => $user){
            $row = array();
            $erow = array();
            foreach ($fields as $key => $value) {
                switch ($value){
                    case 'UserId':
                        $printval = '<a href="?page=student-edit&user_id='.$user->{$value}.'" class="button" target="_blank">View/Edit</a>';
                        break;
                    case 'ApplicantId':
                        $printval = '<a href="'.get_permalink($portal_page).'?applicant_id='.$user->{$value}.'&renewal_id='.$user->RenewalId.'" target="_blank">'.$user->{$value}.'</a>';
                        break;
                    case 'RenewalId':
                        $printval = '<a href="'.get_permalink($portal_page).'?applicant_id='.$user->ApplicantId.'&renewal_id='.$user->{$value}.'" target="_blank">'.$user->{$value}.'</a>';
                        break;
                    case 'CountyId':
                        $printval = $this->queries->get_county_by_id($user->{$value});
                        break;
                    case 'StateId':
                        $printval = $this->queries->get_state_by_id($user->{$value});
                        break;
                    case 'CollegeId':
                        $printval = $this->queries->get_college_by_id($user->{$value});
                        break;
                    case 'MajorId':
                        $printval = $this->queries->get_major_by_id($user->{$value});
                        break;
                    case 'SexId':
                        $printval = $this->queries->get_sex_by_id($user->{$value});
                        break;
                    case 'EducationAttainmentId':
                        $printval = $this->queries->get_educationalattainment_by_id($user->{$value});
                        break;
                    case 'EthnicityId':
                        $printval = $this->queries->get_ethnicity_by_id($user->{$value});
                        break;
                    case 'HighSchoolId':
                        $printval = $this->queries->get_highschool_by_id($user->{$value});
                        break;
                    case 'FirstGenerationStudent':
                    case 'IsIndependent':
                    case 'PlayedHighSchoolSports':
                    case 'CPSPublicSchools':
                    case 'InformationSharingAllowed':
                    case 'IsComplete':
                    case 'ApplicantHaveRead':
                    case 'ApplicantDueDate':
                    case 'ApplicantDocsReq':
                    case 'ApplicantReporting':
                    case 'GuardianHaveRead':
                    case 'GuardianDueDate':
                    case 'GuardianDocsReq':
                    case 'GuardianReporting':
                    case 'Homeowner':
                    case 'InformationSharingAllowedByGuardian':
                        $printval = $user->{$value}>0?'Yes':'No';
                        break;
                    case 'Activities':
                    case 'HardshipNote':
                    case 'CoopStudyAbroadNote':
                        $printval = strip_tags($user->{$value});
                        break;
                    default:
                        $printval = $user->{$value};
                        break;
                }
                $row[] = '<td class="'.$value.'"><div>'.$printval.'</div></td>';
                if(!in_array($value,$this->skipcsv)) {
                    $erow[] = $this->csv_safe($printval);
                }
            }
            $class = $i%2==0?'even':'odd';
            $ret[] = '<tr class="'.$class.'">'.implode("\n\r", $row).'</tr>';
            $ecsv[] = implode(",",$erow);
            $i++;
        }

        $this->export_csv = implode("\n", $ecsv);

        if($echo){
            print implode("\n\r", $ret);
        } else {
            return implode("\n\r", $ret);
        }
    }

    /**
     * Create a Table Footer for the result set display
     *
     * @param array $fields An array of field objects.
     * @param array $info The result information.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The footer to be printed, or void if the param $echo is true.
     */
    public function table_footer($fields, $info, $echo = true){
        $ret = array();
        $numfields = count($fields);
        /*if(count($info)>0) {
            foreach ($info as $key => $value) {
                $ret[] = '<div class=""><label>' . $key . ': </label><span class="">' . $value . '</span></div>';
            }
        }*/

        $ret = apply_filters('msdlab_csf_report_display_table_footer', '<th colspan="'.$numfields.'">'.implode("\r\n",$ret).'</th>');

        if($echo){
            print '<tr>'.$ret.'</tr>';
        } else {
            return '<tr>'.$ret.'</tr>';
        }
        return;
    }

    /**
     *
     */
    public function print_export_tools($id){
        $temp_filename = 'CSF Report '.$id.'_'.date("Y-m-d_H-i",time()).'.csv';
        //create or locate upload dir for tempfiles
        $upload_dir   = wp_upload_dir();
        if ( ! empty( $upload_dir['basedir'] ) ) {
            $temp_dirname = $upload_dir['basedir'].'/exports/temp';
            $temp_url = $upload_dir['baseurl'].'/exports/temp/'.$temp_filename;
            //TODO: add a cron to clean out this directory once a day.
            if ( ! file_exists( $temp_dirname ) ) {
                wp_mkdir_p( $temp_dirname );
            }
        }
        //create an empty file and open for writing
        $temp_file = fopen($temp_dirname.'/'.$temp_filename,'w+b');
        //write to file
        fwrite($temp_file,$this->export_header."\n".$this->export_csv);
        fclose($temp_file);
        $ret['form'] = '<a href="'.$temp_url.'" id="csv_export_'.$id.'" class="button csv-export export-'.$id.'">Export to CSV</a>';
        return implode("\n\r", $ret);
    }

    //student edit panels
    function student_form($student_data){

        $this->set_form_select_options();
        $ret['Applicant_FirstName'] = $this->form->field_textfield('Applicant_FirstName', $student_data['personal']->FirstName ? $student_data['personal']->FirstName : null, 'First Name', null, array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
        $ret['Applicant_MiddleInitial'] = $this->form->field_textfield('Applicant_MiddleInitial', $student_data['personal']->MiddleInitial ? $student_data['personal']->MiddleInitial : null, 'Middle Initial', null, array(), array('col-md-2', 'col-sm-12'));
        $ret['Applicant_LastName'] = $this->form->field_textfield('Applicant_LastName', $student_data['personal']->LastName ? $student_data['personal']->LastName : null, 'Last Name', null, array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));

        $ret['Applicant_Address1'] = $this->form->field_textfield('Applicant_Address1', $student_data['personal']->Address1 ? $student_data['personal']->Address1 : null, 'Address', '123 Any Street', array('type' => 'text', 'minlength' => '2', 'required' => 'required'), array('required', 'col-md-12'));
        $ret['Applicant_Address2'] = $this->form->field_textfield('Applicant_Address2', $student_data['personal']->Address2 ? $student_data['personal']->Address2 : null, '', 'Apartment or Box number', array('type' => 'text'), array('col-md-12'));
        $ret['Applicant_City'] = $this->form->field_textfield('Applicant_City', $student_data['personal']->City ? $student_data['personal']->City : null, 'City', null, array('type' => 'text', 'required' => 'required'), array('required', 'col-md-5', 'col-sm-12'));
        $ret['Applicant_StateId'] = $this->form->field_select('Applicant_StateId', $student_data['personal']->StateId ? $student_data['personal']->StateId : 'OH', 'State', array('option' => 'Select', 'value' => 'OH'), $this->states_array, array('required' => 'required'), array('required', 'col-md-2', 'col-sm-12'));
        $ret['Applicant_CountyId'] = $this->form->field_select('Applicant_CountyId', $student_data['personal']->CountyId ? $student_data['personal']->CountyId : null, 'County', array('option' => 'Select', 'value' => '24'), $this->counties_array, null, array('col-md-3', 'col-sm-12'));
        $ret['Applicant_ZipCode'] = $this->form->field_textfield('Applicant_ZipCode', $student_data['personal']->ZipCode ? $student_data['personal']->ZipCode : null, 'ZIP Code', '00000', array('type' => 'number', 'minlength' => 5, 'maxlength' => 10, 'required' => 'required'), array('required', 'col-md-2', 'col-sm-12'));

        $ret['Applicant_Email'] = $this->form->field_textfield("Applicant_Email", $student_data['personal']->Email ? $student_data['personal']->Email : $current_user->user_email);

        $ret['Applicant_CellPhone'] = $this->form->field_textfield('Applicant_CellPhone', $student_data['personal']->CellPhone ? $student_data['personal']->CellPhone : null, 'Mobile Phone Number', '(000)000-0000', array('required' => 'required', 'type' => 'tel'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Applicant_AlternativePhone'] = $this->form->field_textfield('Applicant_AlternativePhone', $student_data['personal']->AlternativePhone ? $student_data['personal']->AlternativePhone : null, 'Alternative Phone Number', '(000)000-0000', array('type' => 'tel'), array('col-md-6', 'col-sm-12'));

        $ret['Applicant_Last4SSN'] = $this->form->field_textfield('Applicant_Last4SSN', $student_data['personal']->Last4SSN ? $student_data['personal']->Last4SSN : null, 'Last 4 numbers of your SS#', '0000', array('type' => 'number', 'maxlength' => 4, 'minlength' => 4, 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Applicant_DateOfBirth'] = $this->form->field_date('Applicant_DateOfBirth', $student_data['personal']->DateOfBirth ? $student_data['personal']->DateOfBirth : null, 'Date of Birth', array('required' => 'required', 'type' => 'date', 'date' => 'date'), array('datepicker', 'required', 'col-md-6', 'col-sm-12'));

        $ret['Applicant_EthnicityId'] = $this->form->field_select('Applicant_EthnicityId', $student_data['personal']->EthnicityId ? $student_data['personal']->EthnicityId : null, 'Ethnicity', array('option' => 'Select', 'value' => '24'), $this->ethnicity_array, null, array('col-md-6', 'col-sm-12'));
        $ret['Applicant_SexId'] = $this->form->field_radio('Applicant_SexId', $student_data['personal']->SexId ? $student_data['personal']->SexId : null, 'Gender', $student_data['personal']->sex_array, null, array('col-md-6', 'col-sm-12'));
        $ret[] = '<hr />';
        $ret['Applicant_CollegeId'] = $this->form->field_select('Applicant_CollegeId', $student_data['personal']->CollegeId ? $student_data['personal']->CollegeId : null, 'College Applied To or Attending', null, $this->college_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Applicant_OtherSchool'] = $this->form->field_textfield('Applicant_OtherSchool', $student_data['personal']->OtherSchool?$student_data['personal']->OtherSchool:'','Name of Unlisted Institution',null, array('text'=>true),array('col-sm-12','required')); //how are we handling "other" in the new DB?
        $ret['Applicant_MajorId'] = $this->form->field_select('Applicant_MajorId', $student_data['personal']->MajorId ? $student_data['personal']->MajorId : 5122, 'Intended Major (If Uncertain, select Undecided)', null, $this->major_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));

        $ret['ApplicantScholarship_ScholarshipId'] = $this->form->field_select('ApplicantScholarship_ScholarshipId', $student_data['scholarship']->ScholarshipId ? $student_data['scholarship']->ScholarshipId:0, 'Scholarship', null, $this->scholarship_array, array(''), array('col-md-6', 'col-sm-12'));


        $ret[] = '<hr />';
        $ret['Applicant_EducationAttainmentId'] = $this->form->field_select("Applicant_EducationAttainmentId", $student_data['personal']->EducationAttainmentId ? $student_data['personal']->EducationAttainmentId : null, "Year in School Fall Semester, 2018", array('option' => 'Select', 'value' => '5'), $this->educationalattainment_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Applicant_FirstGenerationStudent'] = $this->form->field_boolean('Applicant_FirstGenerationStudent', $student_data['personal']->FirstGenerationStudent ? $student_data['personal']->FirstGenerationStudent : 0, 'Are you the first person in your family to attend college?', null, array('col-md-6', 'col-sm-12'));

        $ret['Applicant_HighSchoolGraduationDate'] = $this->form->field_select('Applicant_HighSchoolGraduationDate', $student_data['personal']->HighSchoolGraduationDate ? $student_data['personal']->HighSchoolGraduationDate : date("Y").'-01-01', "Year of High School Graduation", array('value' => date("Y").'-01-01','option' => date("Y")), $this->gradyr_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));



        return implode("\n",$ret);
    }


    function application_form($student_data){

        $this->set_form_select_options();
        $ret['Applicant_ApplicationDateTime'] = $this->form->field_textfield("Applicant_ApplicationDateTime", (strtotime($student_data['personal']->ApplicationDateTime) > 0) ? $student_data['personal']->ApplicationDateTime : date("Y-m-d H:i:s"),'Application Date');
        $ret['Applicant_IsComplete'] = $this->form->field_boolean('Applicant_IsComplete',1,'Complete');

        $ret['Applicant_ResumeOK'] = $this->form->field_boolean('Applicant_ResumeOK',1,'Resume OK');
        $ret['Applicant_TranscriptOK'] = $this->form->field_boolean('Applicant_TranscriptOK',1,'Transcript OK');
        $ret['Applicant_FinancialAidOK'] = $this->form->field_boolean('Applicant_FinancialAidOK',1,'Financial Aid OK');
        $ret['Applicant_FAFSAOK'] = $this->form->field_boolean('Applicant_FAFSAOK',1,'FAFSA OK');
        $ret['Applicant_Activities'] = $this->form->field_textarea('Applicant_Activities',$student_data['personal']->Activities ? $student_data['personal']->Activities : '',"Please list any activities participated in, with years active.",null,array('col-md-12'));
        $ret['Applicant_PlayedHighSchoolSports'] = $this->form->field_boolean('Applicant_PlayedHighSchoolSports', $student_data['personal']->PlayedHighSchoolSports ? $student_data['personal']->PlayedHighSchoolSports : 0, 'Did you participate in sports while attending High School?');

        $ret[] = '<br />';
        $ret['Applicant_HighSchoolId'] = $this->form->field_select('Applicant_HighSchoolId', $student_data['personal']->HighSchoolId ? $student_data['personal']->HighSchoolId : 136, "High School Attended", $student_data['personal']->HighSchoolId ? $student_data['personal']->HighSchoolId : null, $this->highschool_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['HighSchoolType'] = $this->form->field_select('HighSchoolType', $this->queries->get_highschool_type_by_highschool_id($student_data['personal']->HighSchoolId) , "High School Type", null, $this->highschool_type_array, array('required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Applicant_HighSchoolGPA'] = $this->form->field_textfield('Applicant_HighSchoolGPA', $student_data['personal']->HighSchoolGPA ? $student_data['personal']->HighSchoolGPA : null, 'High School GPA', '0.00', array('required' => 'required', 'type' => 'number', 'minlength' => 1), array('required', 'col-md-6', 'col-sm-12'));

        return implode("\n",$ret);

    }

    function need_form($student_data){
        $college_id = $student_data['personal']->CollegeId;
        $instate_direct = $this->queries->get_college_financials($student_data['personal']->CollegeId, 'InStateTuition') + $this->queries->get_college_financials($student_data['personal']->CollegeId, 'BookFee') + $this->queries->get_college_financials($student_data['personal']->CollegeId, 'RoomBoardOnCampus');
        $instate_indirect = $instate_direct + $this->queries->get_college_financials($student_data['personal']->CollegeId, 'IndirectCost');
        $ret[] = '<div class="col-md-4">';
        $ret['studentneed_DirectCost'] = $this->form->field_textfield('studentneed_DirectCost', $student_data['need']->DirectCost ? $student_data['need']->DirectCost : $instate_direct,  'Direct Cost', '0.00', array('type' => 'number', 'minlength' => 1), array('directcost'));
        $ret['studentneed_IndirectCost'] = $this->form->field_textfield('studentneed_IndirectCost', $student_data['need']->IndirectCost ? $student_data['need']->IndirectCost : $instate_indirect,  'Indirect Cost', '0.00', array('type' => 'number', 'minlength' => 1), array('indirectcost'));
        $ret['studentneed_FamilyContribution'] = $this->form->field_textfield('studentneed_FamilyContribution', $student_data['need']->FamilyContribution ? $student_data['need']->FamilyContribution : '',  'Family Contribution', '0.00', array('type' => 'number', 'minlength' => 1), array('family'));
        $ret['studentneed_Pell'] = $this->form->field_textfield('studentneed_Pell', $student_data['need']->Pell ? $student_data['need']->Pell : '',  'Pell', '0.00', array('type' => 'number', 'minlength' => 1), array('pell'));
        $ret['studentneed_SEOG'] = $this->form->field_textfield('studentneed_SEOG', $student_data['need']->SEOG ? $student_data['need']->SEOG : '',  'SEOG', '0.00', array('type' => 'number', 'minlength' => 1), array('seog'));
        $ret['studentneed_OIG'] = $this->form->field_textfield('studentneed_OIG', $student_data['need']->OIG ? $student_data['need']->OIG : '',  'OIG', '0.00', array('type' => 'number', 'minlength' => 1), array('oig'));
        $ret['studentneed_OSCG'] = $this->form->field_textfield('studentneed_OSCG', $student_data['need']->OSCG ? $student_data['need']->OSCG : '',  'OSCG', '0.00', array('type' => 'number', 'minlength' => 1), array('oscg'));
        $ret['studentneed_Stafford'] = $this->form->field_textfield('studentneed_Stafford', $student_data['need']->Stafford ? $student_data['need']->Stafford : '',  'Stafford Loan', '0.00', array('type' => 'number', 'minlength' => 1), array('stafford'));
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-8">';
        for($f=1;$f<7;$f++){
            $ret['studentneed_ExternalScholarship'.$f] = $this->form->field_textfield('studentneed_ExternalScholarship'.$f, $student_data['need']->ExternalScholarship{$f} ? $student_data['need']->ExternalScholarship{$f} : '',  'External Scholarship '.$f, '',null,array('col-md-8'));
            $ret['studentneed_ExternalScholarshipAmt'.$f] = $this->form->field_textfield('studentneed_ExternalScholarshipAmt'.$f, $student_data['need']->ExternalScholarshipAmt{$f} ? $student_data['need']->ExternalScholarshipAmt{$f} : '',  'Amount '.$f, '0.00', array('type' => 'number', 'minlength' => 1), array('col-md-4'));
        }
        $ret[] = '<hr />';
        $ret['studentneed_DirectNeed'] = $this->form->field_textfield('studentneed_DirectNeed', $student_data['need']->DirectNeed ? $student_data['need']->DirectNeed : '',  'Direct Need', '0.00', array('type' => 'number', 'minlength' => 1), array('directneed','col-md-8'));
        $ret['studentneed_IndirectNeed'] = $this->form->field_textfield('studentneed_IndirectNeed', $student_data['need']->IndirectNeed ? $student_data['need']->IndirectNeed : '',  'Indirect Need', '0.00', array('type' => 'number', 'minlength' => 1), array('indirectneed','col-md-8'));
        $ret['calc_button'] = $this->form->field_button('calculateneed','Calculate Need',array('col-md-4'));
        $ret[] = '</div>';


        $ret['college_financials_grid'] = '<div class="col-md-12">
    <table class="table-grid">
    <tr><th colspan="2">'.$this->queries->get_college_by_id($college_id).'</th></tr>
    <tr><td>Indirect Cost</td><td>'.$this->queries->get_college_financials($college_id,'IndirectCost').'</td></tr>
    <tr><td>Book Fees</td><td>'.$this->queries->get_college_financials($college_id,'BookFee').'</td></tr>
    <tr><td>Off Campus R&B</td><td>'.$this->queries->get_college_financials($college_id,'RoomBoardOffCampus').'</td></tr>
    <tr><td>on Campus R&B</td><td>'.$this->queries->get_college_financials($college_id,'RoomBoardOnCampus').'</td></tr>
    <tr><td>In-state Tuition</td><td>'.$this->queries->get_college_financials($college_id,'InStateTuition').'</td></tr>
    <tr><td>Out-of-state Tuition</td><td>'.$this->queries->get_college_financials($college_id,'OutStateTuition').'</td></tr>
</table>
</div>';


        return implode("\n",$ret);
    }

    function payment_form($student_data){
        $this->set_form_select_options();
        $college_id = $student_data['personal']->CollegeId;
        $payment_keys = array('1','1 Adj','2','2 Adj','3','4','5');
        $ret[] = '<div class="col-md-5">';
        $ret['infoCurrentCollege'] = $this->form->field_textinfo('infoCurrentCollege',$this->queries->get_college_by_id($college_id),'Current School');
        $ret['infoScholarship'] = $this->form->field_textinfo('infoScholarship',$this->queries->get_scholarship_by_id($student_data['award']->ScholarshipId),'Scholarship');
        $ret['infoFund'] = $this->form->field_textinfo('infoFund',$this->queries->get_fund_by_scholarshipid($student_data['award']->ScholarshipId),'Fund');
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-7">';

        $ret[] = '</div>';
        $ret[] = '<div class="col-md-12"><table>';
        $ret[] = '<tr><th>Payment #</th><th>Amount</th><th>Date</th><th>Check #</th><th>School</th><th>Notes</th><th>Refund Req.</th><th>Refund Rec.</th><th>Refund Amt.</th><th>Refund #</th></tr>';
        foreach($payment_keys AS $payment_key){
            $ret[] = '<tr>';
            $ret[] = '<td>'.$payment_key.'</td>';
            $ret['payment_PaymentAmt_'.$payment_key] = '<td>'.$this->form->field_textfield('payment_PaymentAmt_'.$payment_key,$student_data['payment']->payment_PaymentAmt_{$payment_key}).'</td>';
            $ret['payment_PaymentDateTime_'.$payment_key] = '<td>'.$this->form->field_date('payment_PaymentDateTime_'.$payment_key,$student_data['payment']->payment_PaymentDateTime_{$payment_key},'').'</td>';
            $ret['payment_CheckNumber_'.$payment_key] = '<td>'.$this->form->field_textfield('payment_CheckNumber_'.$payment_key,$student_data['payment']->payment_CheckNumber_{$payment_key}).'</td>';
            $ret['payment_CollegeId_'.$payment_key] = '<td>'.$this->form->field_select('payment_CollegeId_'.$payment_key,$student_data['payment']->payment_CollegeId_{$payment_key},'',null,$this->college_array).'</td>';
            $ret['payment_Notes_'.$payment_key] = '<td>'.$this->form->field_textarea_simple('payment_Notes_'.$payment_key,$student_data['payment']->payment_Notes_{$payment_key}).'</td>';
            $ret['payment_RefundRequested_'.$payment_key] = '<td>'.$this->form->field_textfield('payment_RefundRequested_'.$payment_key,$student_data['payment']->payment_RefundRequested_{$payment_key}).'</td>';
            $ret['payment_RefundReceived_'.$payment_key] = '<td>'.$this->form->field_textfield('payment_RefundReceived_'.$payment_key,$student_data['payment']->payment_RefundReceived_{$payment_key}).'</td>';
            $ret['payment_RefundAmt_'.$payment_key] = '<td>'.$this->form->field_textfield('payment_RefundAmt_'.$payment_key,$student_data['payment']->payment_RefundAmt_{$payment_key}).'</td>';
            $ret['payment_RefundNumber_'.$payment_key] = '<td>'.$this->form->field_textfield('payment_RefundNumber_'.$payment_key,$student_data['payment']->payment_RefundNumber_{$payment_key}).'</td>';
            $ret[] = '</tr>';
        }
        $ret[] = '</table></div>';
        return implode("\n",$ret);
    }

    function guardian_form($student_data){
        $ret[] = '<div class="col-md-6">';
        $ret['Guardian_GuardianFullName1'] = $this->form->field_textfield('Guardian_GuardianFullName1', $student_data['financial']->GuardianFullName1 ? $student_data['financial']->GuardianFullName1 : null, "First Guardian Full Name",null,array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Guardian_GuardianFullName2'] = $this->form->field_textfield('Guardian_GuardianFullName2', $student_data['financial']->GuardianFullName2 ? $student_data['financial']->GuardianFullName2 : null, "Second Guardian Full Name",null,null, array('col-md-6', 'col-sm-12'));
        $ret['Guardian_GuardianEmployer1'] = $this->form->field_textfield('Guardian_GuardianEmployer1', $student_data['financial']->GuardianEmployer1 ? $student_data['financial']->GuardianEmployer1 : null, "Place of Employment",null,array('minlength' => '2', 'required' => 'required'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Guardian_GuardianEmployer2'] = $this->form->field_textfield('Guardian_GuardianEmployer2', $student_data['financial']->GuardianEmployer2 ? $student_data['financial']->GuardianEmployer2 : null, "Place of Employment",null,null, array('col-md-6', 'col-sm-12'));
        $ret['Guardian_Homeowner'] = $this->form->field_boolean('Guardian_Homeowner', $student_data['financial']->Homeowner ? $student_data['financial']->Homeowner : 0, "Do the applicant's parents own their home?",null, array('required', 'col-md-12'));
        $ret['Guardian_AmountOwedOnHome'] = $this->form->field_textfield('Guardian_AmountOwedOnHome', $student_data['financial']->AmountOwedOnHome ? $student_data['financial']->AmountOwedOnHome : null, "Amount Owed",'50,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));
        $ret['Guardian_HomeValue'] = $this->form->field_textfield('Guardian_HomeValue', $student_data['financial']->HomeValue ? $student_data['financial']->HomeValue : null, "Current Value",'100,000', array('type' => 'number'), array('col-md-6', 'col-sm-12'));
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-6">';
        $ret['Applicant_FirstGenerationStudent'] = $this->form->field_boolean('Applicant_FirstGenerationStudent', $student_data['personal']->FirstGenerationStudent ? $student_data['personal']->FirstGenerationStudent : 0, 'Are you the first person in your family to attend college?', null, array('col-md-6', 'col-sm-12'));
        $ret['Applicant_IsIndependent'] = $this->form->field_boolean('Applicant_IsIndependent', $student_data['personal']->IsIndependent ? $student_data['personal']->IsIndependent : 0, 'Independent?');
        $ret['Applicant_Employer'] = $this->form->field_textfield('Applicant_Employer', $student_data['personal']->Employer ? $student_data['personal']->Employer : null, "Applicant Employer",null,null, array('col-md-6', 'col-sm-12'));
        $ret[] = '</div>';

        return implode("\n",$ret);
    }

    function other_form($student_data){
        $ret['SRATableHdr'] = '<div class="col-md-6">';

        $ret[] = '<table class="table">
                                <tr class="table-row">
                                    <th class="table-cell"></th>
                                    <th class="table-cell table-header">Student</th>';
            $ret[] = '<th class="table-cell table-header">Guardian</th>';
        $ret[] = '</div>';


        $ret[] = '<tr class="table-row">'; //styling???? add header
        $ret[] = '<td class="table-cell">I/we have read and understand the "IMPORTANT INFORMATION ABOUT THE ON-LINE APPLICATION" prior to opening the application;</td>';
        $ret['Agreements_ApplicantHaveRead'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantHaveRead', $student_data['agreements']->ApplicantHaveRead?$student_data['agreements']->ApplicantHaveRead:0,'',array('required')).'</td>';
            $ret['Agreements_GuardianHaveRead'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianHaveRead', $student_data['agreements']->GuardianHaveRead?$student_data['agreements']->GuardianHaveRead:0,'',array('required')).'</td>';
        $ret[] = '</tr>';

        $ret[] = '<tr class="table-row">';
        $ret[] = '<td class="table-cell">I/we understand that applications submitted after the April 30, 2018 deadline will not be considered;</td>';
        $ret['Agreements_ApplicantDueDate'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantDueDate', $student_data['agreements']->ApplicantDueDate?$student_data['agreements']->ApplicantDueDate:0,'',array('required')).'</td>';
            $ret['Agreements_GuardianDueDate'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianDueDate', $student_data['agreements']->GuardianDueDate?$student_data['agreements']->GuardianDueDate:0,'',array('required')).'</td>';
        $ret[] = '</tr>';


        $ret[] = '<tr class="table-row">';
        $ret[] = '<td class="table-cell">I/we understand that the application is incomplete without my transcript, my Student Aid Report and the financial aid award notification from the school I have chosen to attend;</td>';
        $ret['Agreements_ApplicantDocsReq'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantDocsReq', $student_data['agreements']->ApplicantDocsReq?$student_data['agreements']->ApplicantDocsReq:0,'',array('required')).'</td>';
            $ret['Agreements_GuardianDocsReq'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianDocsReq', $student_data['agreements']->GuardianDocsReq?$student_data['agreements']->GuardianDocsReq:0,'',array('required')).'</td>';
        $ret[] = '</tr>';


        $ret[] = '<tr class="table-row">';
        $ret[] = '<td class="table-cell">I/we will report all other substantial scholarships received (other than state and federal grants and awards).</div>';
        $ret['Agreements_ApplicantReporting'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_ApplicantReporting', $student_data['agreements']->ApplicantReporting?$student_data['agreements']->ApplicantReporting:0,'',array('required')).'</div>';
            $ret['Agreements_GuardianReporting'] = '<td class="table-cell">'.$this->form->field_boolean('Agreements_GuardianReporting', $student_data['agreements']->GuardianReporting?$student_data['agreements']->GuardianReporting:0,'',array('required')).'</div>';
        $ret[] = '</tr>';

        $ret[] = '<tr class="table-row">';
        $ret['InformationSharingCopy'] = '<td class="table-cell">Do you authorize the CSF to share the information on your scholarship application with other foundations looking for prospective recipients?</td>';
        $ret['Applicant_InformationSharingAllowed'] = '<td class="table-cell">'.$this->form->field_boolean('Applicant_InformationSharingAllowed', $student_data['agreements']->InformationSharingAllowed ? $student_data['agreements']->InformationSharingAllowed : 0,'',array('required')).'</td>';
            $ret['Guardian_InformationSharingAllowedByGuardian'] = '<td class="table-cell">'.$this->form->field_boolean('Guardian_InformationSharingAllowedByGuardian', $student_data['agreements']->InformationSharingAllowedByGuardian ? $student_data['agreements']->InformationSharingAllowedByGuardian : 0,'',array('required')).'</td>';
        $ret[] = '</tr>';
        $ret['SRATableFtr'] = '</table>';
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-6">';
        $ret['Applicant_HardshipNote'] = $this->form->field_textarea('Applicant_HardshipNote', $result->HardshipNote ? $result->HardshipNote : null, "If applicable, please use this space to describe how you overcame hardships (family environment, health issues, or physical challenges, etc.) to achieve your dream of pursuing a college education.",null,array('col-md-12'));

        $ret[] = '</div>';

        return implode("\n",$ret);
    }
}