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
    var $scholarship_array;

    public function __construct() {
        if(class_exists('MSDLAB_FormControls')){
            $this->form = new MSDLAB_FormControls();
        }
        if(class_exists('MSDLAB_Queries')){
            $this->queries = new MSDLAB_Queries();
        }
        $this->skipcsv = array('Activities','HardshipNote','CoopStudyAbroadNote');
        add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles_and_scripts'));
        add_action( 'wp_ajax_send_export_email', array(&$this,'send_export_email') );

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
        $this->counties_array = $this->queries->get_select_array_from_db('County', 'CountyId', 'County','County');
        $this->college_array = $this->queries->get_select_array_from_db('College', 'CollegeId', 'Name','Name',1);
        $this->major_array = $this->queries->get_select_array_from_db('Major', 'MajorId', 'MajorName','MajorName',1);
        $this->educationalattainment_array = $this->queries->get_select_array_from_db('EducationalAttainment', 'EducationalAttainmentId', 'EducationalAttainment');
        $this->highschool_array = $this->queries->get_select_array_from_db('HighSchool', 'HighSchoolId', 'SchoolName','SchoolName',1);
        $this->highschool_type_array = $this->queries->get_select_array_from_db('HighSchoolType', 'HighSchoolTypeId', 'Description','HighSchoolTypeId');
        $this->scholarship_array = $this->queries->get_select_array_from_db('scholarship', 'ScholarshipId', 'Name','Name',1);
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
            'csf-management_page_csf-reports',
            'csf-management_page_csf-renewals',
            'csf-management_page_csf-need',
            'csf-management_page_checks-to-print',
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
                        $printval = '<strong>'.$user->{$value}.'</strong><br />';
                        if(current_user_can('manage_csf')) {
                            $printval .= '<a href="?page=student-edit&user_id=' . $user->{$value} . '" class="button" target="_blank">View/Edit</a>';
                        }
                        break;
                    case 'ApplicantId':
                        if(current_user_can('manage_csf')) {
                            $printval = '<a href="' . get_permalink($portal_page) . '?applicant_id=' . $user->{$value} . '&renewal_id=' . $user->RenewalId . '" target="_blank">' . $user->{$value} . '</a>';
                        } else {
                            $printval = $user->{$value};
                        }
                        break;
                    case 'RenewalId':
                        if(current_user_can('manage_csf')) {
                            $printval = '<a href="' . get_permalink($portal_page) . '?applicant_id=' . $user->ApplicantId . '&renewal_id=' . $user->{$value} . '" target="_blank">' . $user->{$value} . '</a>';
                        } else {
                            $printval = $user->{$value};
                        }
                        break;
                    case 'CountyId':
                        $printval = $this->queries->get_county_by_id($user->{$value});
                        break;
                    case 'StateId':
                        $printval = $this->queries->get_state_by_id($user->{$value});
                        break;
                    case 'CollegeId':
                        //if($user->{$value} == '343'){
                          //  $printval = $this->queries->get_other_school($user->ApplicantId);
                        //} else {
                            $printval = $this->queries->get_college_by_id($user->{$value});
                        //}
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
                    case 'ScholarshipId':
                        $printval = $this->queries->get_scholarship_by_id($user->{$value});
                        break;
                    case 'EmployerId':
                        $printval = $this->queries->get_employer_by_id($user->{$value});
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
                    case 'ResumeOK':
                    case 'TranscriptOK':
                    case 'FinancialAidOK':
                    case 'FAFSAOK':
                    case 'ApplicationLocked':
                    case 'AppliedBefore':
                    case 'Rejected':
                    case 'AppliedBefore':
                    case 'TermsAcknowledged':
                    case 'RenewalLocked':
                    case 'Reject':
                    case 'Renew':
                    case 'ThankYou':
                    case 'Signed':
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
            if($user->Reject){$class = 'reject';}
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


    public function print_export_email($id,$data,$emails){
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
        $ret['form'] = '<button id="'.$id.'_email" class="button csv-email email-'.$id.'">Email CSV to Contacts</button>';
        $ret['response_area'] = '<div class="response1"></div>';
        $ret['jquery'] = '<script>
        jQuery(document).ready(function($){    
            $(\'#'.$id.'_email\').click(function(){
                var data = {
                    action: \'send_export_email\',
                    data: '.json_encode($data).',
                    subject: \'Recommendations for '.$data->Name.'\',
                    file: \''.$temp_dirname.'/'.$temp_filename.'\',
                    emails: \''.$emails.'\',
                }
                jQuery.post(ajaxurl, data, function(response) {
                    $(\'.response1\').html(response).addClass(\'notice notice-success\');
                });
            });
        });
        </script>';
        return implode("\n\r", $ret);
    }

    function send_export_email(){
        $data = $_POST['data'];
        if(!file_exists($_POST['file'])){
           // error_log('file '.$_POST['file'].' does not exist');
        } else {
            $attachments = array($_POST['file']);
        }
        $emails = $_POST['emails'];
        $subject = $_POST['subject'];
        $headers[] = 'From: Elizabeth Collins <beth@cincinnatischolarshipfoundation.org>';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'Bcc: beth@cincinnatischolarshipfoundation.org';
        $to = $emails;
        $message = 'Attached please find a list of students recommended for '.$data['Name'].'.';
        if(wp_mail($to, $subject, $message, $headers, $attachments)){
            print "Email Sent.";
        }
    }


    //student edit panels
    function student_form($student_data){
        $renewal = isset($student_data['renewal']->RenewalId)?true:false;
        $this->set_form_select_options();

        $ret['studentneed_ApplicantId'] = $this->form->field_hidden("studentneed_ApplicantId", $student_data['personal']->ApplicantId);
        $ret['studentneed_UserId'] = $this->form->field_hidden("studentneed_UserId", $student_data['personal']->UserId);

        $ret['Applicant_ApplicantId'] = $this->form->field_hidden("Applicant_ApplicantId", $student_data['personal']->ApplicantId);
        $ret['Applicant_UserId'] = $this->form->field_hidden("Applicant_UserId", $student_data['personal']->UserId);
        if($renewal){
            $ret['studentneed_RenewalId'] = $this->form->field_hidden("studentneed_RenewalId", $student_data['renewal']->RenewalId);
            $ret['Renewal_ApplicantId'] = $this->form->field_hidden("Renewal_ApplicantId", $student_data['renewal']->ApplicantId);
            $ret['Renewal_RenewalId'] = $this->form->field_hidden("Renewal_RenewalId", $student_data['renewal']->RenewalId);
            $ret['Renewal_UserId'] = $this->form->field_hidden("Renewal_UserId", $student_data['renewal']->UserId);
            $ret['renewal_LastName'] = $this->form->field_textfield('renewal_LastName', $student_data['renewal']->LastName ? $student_data['renewal']->LastName : null, 'Last Name', null, array('minlength' => '2'), array('required', 'col-md-4', 'col-sm-12'));
            $ret['renewal_MiddleInitial'] = $this->form->field_textfield('renewal_MiddleInitial', $student_data['renewal']->MiddleInitial ? $student_data['renewal']->MiddleInitial : null, 'Middle Initial', null, array(), array('col-md-2', 'col-sm-12'));
            $ret['renewal_FirstName'] = $this->form->field_textfield('renewal_FirstName', $student_data['renewal']->FirstName ? $student_data['renewal']->FirstName : null, 'First Name', null, array('minlength' => '2'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['renewal_StudentId'] = $this->form->field_textfield('renewal_StudentId', $student_data['renewal']->StudentId ? $student_data['renewal']->StudentId : null, 'Student ID', null, array(), array('col-md-3', 'col-sm-12'));
        } else {
            $ret['Applicant_LastName'] = $this->form->field_textfield('Applicant_LastName', $student_data['personal']->LastName ? $student_data['personal']->LastName : null, 'Last Name', null, array('minlength' => '2'), array('required', 'col-md-4', 'col-sm-12'));
            $ret['Applicant_MiddleInitial'] = $this->form->field_textfield('Applicant_MiddleInitial', $student_data['personal']->MiddleInitial ? $student_data['personal']->MiddleInitial : null, 'Middle Initial', null, array(), array('col-md-2', 'col-sm-12'));
            $ret['Applicant_FirstName'] = $this->form->field_textfield('Applicant_FirstName', $student_data['personal']->FirstName ? $student_data['personal']->FirstName : null, 'First Name', null, array('minlength' => '2'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['Applicant_StudentId'] = $this->form->field_textfield('Applicant_StudentId', $student_data['personal']->StudentId ? $student_data['personal']->StudentId : null, 'Student ID', null, array(), array('col-md-3', 'col-sm-12'));
        }

        $ret['Applicant_DateOfBirth'] = $this->form->field_date('Applicant_DateOfBirth', $student_data['personal']->DateOfBirth ? date("Y-m-d", strtotime($student_data['personal']->DateOfBirth)) : null, 'Date of Birth', array('required' => 'required', 'type' => 'date', 'date' => 'date'), array('datepicker', 'required', 'col-md-2', 'col-sm-12'));
        $ret['Applicant_Last4SSN'] = $this->form->field_textfield('Applicant_Last4SSN', $student_data['personal']->Last4SSN ? $student_data['personal']->Last4SSN : null, 'SS# last 4', '0000', array('type' => 'number', 'maxlength' => 4, 'minlength' => 4), array('required', 'col-md-2', 'col-sm-12'));
        $ret['Applicant_EthnicityId'] = $this->form->field_select('Applicant_EthnicityId', $student_data['personal']->EthnicityId ? $student_data['personal']->EthnicityId : null, 'Ethnicity', array('option' => 'Select', 'value' => '24'), $this->ethnicity_array, null, array('col-md-4', 'col-sm-12'));
        $ret['Applicant_SexId'] = $this->form->field_radio('Applicant_SexId', $student_data['personal']->SexId ? $student_data['personal']->SexId : null, 'Gender', $this->sex_array, null, array('col-md-4', 'col-sm-12'));

        $ret[] = '<hr />';
        if($renewal){

            $ret['renewal_Address1'] = $this->form->field_textfield('renewal_Address1', $student_data['renewal']->Address1 ? $student_data['renewal']->Address1 : null, 'Address', '123 Any Street', array('type' => 'text', 'minlength' => '2'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['renewal_Address2'] = $this->form->field_textfield('renewal_Address2', $student_data['renewal']->Address2 ? $student_data['renewal']->Address2 : null, 'Address Line 2', 'Apartment or Box number', array('type' => 'text'), array('col-md-3', 'col-sm-12'));
            $ret['renewal_City'] = $this->form->field_textfield('renewal_City', $student_data['renewal']->City ? $student_data['renewal']->City : null, 'City', null, array('type' => 'text'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['renewal_StateId'] = $this->form->field_select('renewal_StateId', $student_data['renewal']->StateId ? $student_data['renewal']->StateId : 'OH', 'State', array('option' => 'Select', 'value' => 'OH'), $this->states_array, array('required' => 'required'), array('required', 'col-md-1', 'col-sm-12'));
            $ret['renewal_ZipCode'] = $this->form->field_textfield('renewal_ZipCode', $student_data['renewal']->ZipCode ? $student_data['renewal']->ZipCode : null, 'ZIP', '00000', array('type' => 'number', 'minlength' => 5, 'maxlength' => 10), array('required', 'col-md-1', 'col-sm-12'));
            $ret['renewal_CountyId'] = $this->form->field_select('renewal_CountyId', $student_data['renewal']->CountyId ? $student_data['renewal']->CountyId : null, 'County', array('option' => 'Select', 'value' => '24'), $this->counties_array, null, array('col-md-1', 'col-sm-12'));
            $ret[] = '<hr />';


            $ret['renewal_CellPhone'] = $this->form->field_textfield('renewal_CellPhone', $student_data['renewal']->CellPhone ? $student_data['renewal']->CellPhone : null, 'Mobile Phone Number', '(000)000-0000', array('required' => 'required', 'type' => 'tel'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['renewal_AlternativePhone'] = $this->form->field_textfield('renewal_AlternativePhone', $student_data['renewal']->AlternativePhone ? $student_data['renewal']->AlternativePhone : null, 'Alternative Phone Number', '(000)000-0000', array('type' => 'tel'), array('col-md-3', 'col-sm-12'));
            $ret['Applicant_Email'] = $this->form->field_textfield("Applicant_Email", $student_data['personal']->Email ? $student_data['personal']->Email : '','Applicant Email','Email',array('email'),array('required', 'col-md-3', 'col-sm-12'));
            $ret['renewal_Email'] = $this->form->field_textfield("renewal_Email", $student_data['renewal']->Email ? $student_data['renewal']->Email : '','Renewal Email','Email',array('email'),array('required', 'col-md-3','col-sm-12'));

            $ret['Renewal_CollegeId'] = $this->form->field_select('Renewal_CollegeId', $student_data['renewal']->CollegeId ? $student_data['renewal']->CollegeId : null, 'College Attending', null, $this->college_array, array('required' => 'required'), array('required', 'col-md-4', 'col-sm-12'));
            $ret['Renewal_OtherSchool'] = $this->form->field_textfield('Renewal_OtherSchool', $student_data['renewal']->OtherSchool ? $student_data['renewal']->OtherSchool : '', 'Name of Unlisted Institution', null, array('text' => true), array('col-md-4','col-sm-12')); //how are we handling "other" in the new DB?
            $ret['Renewal_MajorId'] = $this->form->field_select('Renewal_MajorId', $student_data['renewal']->MajorId ? $student_data['renewal']->MajorId : 5122, 'Intended Major', null, $this->major_array, array('required' => 'required'), array('required', 'col-md-4', 'col-sm-12'));
        }else {
            $ret['Applicant_Address1'] = $this->form->field_textfield('Applicant_Address1', $student_data['personal']->Address1 ? $student_data['personal']->Address1 : null, 'Address', '123 Any Street', array('type' => 'text', 'minlength' => '2'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['Applicant_Address2'] = $this->form->field_textfield('Applicant_Address2', $student_data['personal']->Address2 ? $student_data['personal']->Address2 : null, 'Address Line 2', 'Apartment or Box number', array('type' => 'text'), array('col-md-3', 'col-sm-12'));
            $ret['Applicant_City'] = $this->form->field_textfield('Applicant_City', $student_data['personal']->City ? $student_data['personal']->City : null, 'City', null, array('type' => 'text'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['Applicant_StateId'] = $this->form->field_select('Applicant_StateId', $student_data['personal']->StateId ? $student_data['personal']->StateId : 'OH', 'State', array('option' => 'Select', 'value' => 'OH'), $this->states_array, array('required' => 'required'), array('required', 'col-md-1', 'col-sm-12'));
            $ret['Applicant_ZipCode'] = $this->form->field_textfield('Applicant_ZipCode', $student_data['personal']->ZipCode ? $student_data['personal']->ZipCode : null, 'ZIP', '00000', array('type' => 'number', 'minlength' => 5, 'maxlength' => 10), array('required', 'col-md-1', 'col-sm-12'));
            $ret['Applicant_CountyId'] = $this->form->field_select('Applicant_CountyId', $student_data['personal']->CountyId ? $student_data['personal']->CountyId : null, 'County', array('option' => 'Select', 'value' => '24'), $this->counties_array, null, array('col-md-1', 'col-sm-12'));
            $ret[] = '<hr />';


            $ret['Applicant_CellPhone'] = $this->form->field_textfield('Applicant_CellPhone', $student_data['personal']->CellPhone ? $student_data['personal']->CellPhone : null, 'Mobile Phone Number', '(000)000-0000', array('required' => 'required', 'type' => 'tel'), array('required', 'col-md-3', 'col-sm-12'));
            $ret['Applicant_AlternativePhone'] = $this->form->field_textfield('Applicant_AlternativePhone', $student_data['personal']->AlternativePhone ? $student_data['personal']->AlternativePhone : null, 'Alternative Phone Number', '(000)000-0000', array('type' => 'tel'), array('col-md-3', 'col-sm-12'));
            $ret['Applicant_Email'] = $this->form->field_textfield("Applicant_Email", $student_data['personal']->Email ? $student_data['personal']->Email : '','Applicant Email','Email',array('email'),array('required', 'col-md-6', 'col-sm-12'));

            $ret['Applicant_CollegeId'] = $this->form->field_select('Applicant_CollegeId', $student_data['personal']->CollegeId ? $student_data['personal']->CollegeId : null, 'College Applied To or Attending', null, $this->college_array, array('required' => 'required'), array('required', 'col-md-4', 'col-sm-12'));
            $ret['Applicant_OtherSchool'] = $this->form->field_textfield('Applicant_OtherSchool', $student_data['personal']->OtherSchool ? $student_data['personal']->OtherSchool : '', 'Name of Unlisted Institution', null, array('text' => true), array('col-md-4','col-sm-12')); //how are we handling "other" in the new DB?
            $ret['Applicant_MajorId'] = $this->form->field_select('Applicant_MajorId', $student_data['personal']->MajorId ? $student_data['personal']->MajorId : 5122, 'Intended Major', null, $this->major_array, array('required' => 'required'), array('required', 'col-md-4', 'col-sm-12'));

            $ret[] = '<hr />';
        }

        $ret[] = '<hr />';
        $scholarship_cnt = 1;
        foreach($student_data['scholarship'] AS $scholarship) {
            $s = $scholarship->AwardId;
            $ret[] = '<div id="scholarship_'.$s.'" class="scholarship-entry">';
            $ret['ApplicantScholarship_ApplicantId_'.$s] = $this->form->field_hidden("ApplicantScholarship_ApplicantId_".$s, $student_data['personal']->ApplicantId);
            $ret['ApplicantScholarship_AwardId_'.$s] = $this->form->field_hidden("ApplicantScholarship_AwardId_".$s, $scholarship->AwardId);

            $ret['ApplicantScholarship_ScholarshipId_'.$s] = $this->form->field_select('ApplicantScholarship_ScholarshipId_'.$s, $scholarship->ScholarshipId ? $scholarship->ScholarshipId : null, 'Scholarship '.$scholarship_cnt, null, $this->scholarship_array, array(), array('col-md-3', 'col-sm-12'));
            $ret['infoFund_'.$s] = $this->form->field_textinfo('infoFund', $this->queries->get_fund_by_scholarshipid($scholarship->ScholarshipId), 'Fund', null, null, array('col-md-2', 'col-sm-12'));
            $ret['ApplicantScholarship_AmountAwarded_'.$s] = $this->form->field_textfield('ApplicantScholarship_AmountAwarded_'.$s, $scholarship->AmountAwarded ? $scholarship->AmountAwarded : null, 'Amount Awarded', '', array('type' => 'number'), array('col-md-2', 'col-sm-12', 'currency'));
            $ret['ApplicantScholarship_AmountActuallyAwarded_'.$s] = $this->form->field_textfield('ApplicantScholarship_AmountActuallyAwarded_'.$s, $scholarship->AmountActuallyAwarded ? $scholarship->AmountActuallyAwarded : null, 'Amount Actually Awarded', '', array('type' => 'number'), array('col-md-2', 'col-sm-12', 'currency'));

            $ret['ApplicantScholarship_DateAwarded_'.$s] = $this->form->field_date("ApplicantScholarship_DateAwarded_".$s, (strtotime($scholarship->DateAwarded) > 0) ? date("Y-m-d", strtotime($scholarship->DateAwarded)) : '', 'Date Awarded', array(), array('datepicker', 'col-md-3', 'col-sm-12'));
            $ret['ApplicantScholarship_Renew_'.$s] = $this->form->field_boolean('ApplicantScholarship_Renew_'.$s, $scholarship->Renew ? $scholarship->Renew : 0, 'Renew', array(), array('col-md-2', 'col-sm-12'));
            $ret['ApplicantScholarship_ThankYou_'.$s] = $this->form->field_boolean('ApplicantScholarship_ThankYou_'.$s, $scholarship->ThankYou ? $scholarship->ThankYou : 0, 'Thank You', array(), array('col-md-2', 'col-sm-12'));
            $ret['ApplicantScholarship_Signed_'.$s] = $this->form->field_boolean('ApplicantScholarship_Signed_'.$s, $scholarship->Signed ? $scholarship->Signed : 0, 'Signed', array(), array('col-md-2', 'col-sm-12'));
            $ret[] = '<div class="gpas gpas-'.$s.'" style="clear:both;">';
            $ret['Applicant_EducationAttainmentId'] = $this->form->field_select("Applicant_EducationAttainmentId", $student_data['personal']->EducationAttainmentId ? $student_data['personal']->EducationAttainmentId : null, "Year in School", array('option' => 'Select', 'value' => '5'), $this->educationalattainment_array, array('required' => 'required'), array('required', 'col-md-3', 'col-sm-12'));
            if($scholarship_cnt == 1) {
                $ret['ApplicantScholarship_GPA1_' . $s] = $this->form->field_textfield('ApplicantScholarship_GPA1_' . $s, $scholarship->GPA1 ? $scholarship->GPA1 : 0, 'GPA 1', '0.00', array('type' => 'number', 'min' => 0.00, 'max' => 100.00, 'step' => 0.01, 'minlength' => 1), array('col-md-2', 'col-sm-12', 'gpa1in'));
                $ret['ApplicantScholarship_GPA2_' . $s] = $this->form->field_textfield('ApplicantScholarship_GPA2_' . $s, $scholarship->GPA2 ? $scholarship->GPA2 : 0, 'GPA 2', '0.00', array('type' => 'number', 'min' => 0.00, 'max' => 100.00, 'step' => 0.01, 'minlength' => 1), array('col-md-2', 'col-sm-12', 'gpa2in'));
                $ret['ApplicantScholarship_GPA3_' . $s] = $this->form->field_textfield('ApplicantScholarship_GPA3_' . $s, $scholarship->GPA3 ? $scholarship->GPA3 : 0, 'GPA 3', '0.00', array('type' => 'number', 'min' => 0.00, 'max' => 100.00, 'step' => 0.01, 'minlength' => 1), array('col-md-2', 'col-sm-12', 'gpa3in'));
                $ret['ApplicantScholarship_GPAC_' . $s] = $this->form->field_textfield('ApplicantScholarship_GPAC_' . $s, $scholarship->GPAC ? $scholarship->GPAC : 0, 'GPA Cumulative', '0.00', array('type' => 'number', 'min' => 0.00, 'max' => 100.00, 'step' => 0.01, 'minlength' => 1), array('col-md-2', 'col-sm-12', 'gpacin'));
            } else {
                $ret['ApplicantScholarship_GPA1_' . $s] = $this->form->field_hidden('ApplicantScholarship_GPA1_' . $s, $scholarship->GPA1 ? $scholarship->GPA1 : 0, 'GPA 1', '0.00', array('col-md-2', 'col-sm-12', 'gpa1'));
                $ret['ApplicantScholarship_GPA2_' . $s] = $this->form->field_hidden('ApplicantScholarship_GPA2_' . $s, $scholarship->GPA2 ? $scholarship->GPA2 : 0, 'GPA 2', '0.00', array('col-md-2', 'col-sm-12', 'gpa2'));
                $ret['ApplicantScholarship_GPA3_' . $s] = $this->form->field_hidden('ApplicantScholarship_GPA3_' . $s, $scholarship->GPA3 ? $scholarship->GPA3 : 0, 'GPA 3', '0.00', array('col-md-2', 'col-sm-12', 'gpa3'));
                $ret['ApplicantScholarship_GPAC_' . $s] = $this->form->field_hidden('ApplicantScholarship_GPAC_' . $s, $scholarship->GPAC ? $scholarship->GPAC : 0, 'GPA Cumulative', '0.00', array('col-md-2', 'col-sm-12', 'gpac'));
            }
            $ret[] = '</div>';
            $ret[] = '</div>';
            $scholarship_cnt++;
        }
        $ret[] = '<div id="scholarship_new" class="scholarship-entry"><div class="col-md-12"><a id="add_scholarship_btn" class="button">Add Scholarship</a></div></div>';
        if ($renewal) {
            $ret['Renewal_YearsWithCSF'] = $this->form->field_textfield('Renewal_YearsWithCSF', $student_data['renewal']->YearsWithCSF ? $student_data['renewal']->YearsWithCSF : 0, 'Years With CSF', '0', array('type' => 'number', 'minlength' => 1), array('col-md-2', 'col-sm-12'));
            $ret['Renewal_AnticipatedGraduationDate'] = $this->form->field_select('Renewal_AnticipatedGraduationDate', $student_data['renewal']->AnticipatedGraduationDate ? date("Y", strtotime($student_data['renewal']->AnticipatedGraduationDate)) : date("Y") . '-01-01', "Anticipated Graduation Date", array('value' => date("Y") . '-01-01', 'option' => date("Y")), $this->col_gradyr_array, array('required' => 'required'), array('required', 'col-md-2', 'col-sm-12'));
        }
        return implode("\n",$ret);
    }


    function application_form($student_data){
        $renewal = isset($student_data['renewal']->RenewalId)?true:false;

        $this->set_form_select_options();
        $ret['Applicant_ApplicationDateTime'] = $this->form->field_date("Applicant_ApplicationDateTime", (strtotime($student_data['personal']->ApplicationDateTime) > 0) ? date("Y-m-d", strtotime($student_data['personal']->ApplicationDateTime)) : '','Application Date',array(),array('datepicker','col-md-2', 'col-sm-12'));
        if($renewal){
            $ret['Renewal_RenewalDateTime'] = $this->form->field_date("Renewal_RenewalDateTime", (strtotime($student_data['renewal']->RenewalDateTime) > 0) ? date("Y-m-d", strtotime($student_data['renewal']->RenewalDateTime)): '','Renewal Date',array(),array('datepicker','col-md-2', 'col-sm-12'));
        }
        $ret['Applicant_IsComplete'] = $this->form->field_boolean('Applicant_IsComplete',$student_data['personal']->IsComplete,'Complete',array(),array('col-md-1', 'col-sm-12'));

        $ret['Applicant_ResumeOK'] = $this->form->field_boolean('Applicant_ResumeOK',$student_data['personal']->ResumeOK,'Resume',array(),array('col-md-1', 'col-sm-12'));
        $ret['Applicant_TranscriptOK'] = $this->form->field_boolean('Applicant_TranscriptOK',$student_data['personal']->TranscriptOK,'Transcript',array(),array('col-md-1', 'col-sm-12'));
        $ret['Applicant_FinancialAidOK'] = $this->form->field_boolean('Applicant_FinancialAidOK',$student_data['personal']->FinancialAidOK,'Fin. Aid',array(),array('col-md-1', 'col-sm-12'));
        $ret['Applicant_FAFSAOK'] = $this->form->field_boolean('Applicant_FAFSAOK',$student_data['personal']->FAFSAOK,'FAFSA',array(),array('col-md-1', 'col-sm-12'));
        $ret['infoAward'] = $this->form->field_textinfo('infoAward',$student_data['scholarship']->AmountAwarded>0 ?'YES':'NO','Award?','','',array('col-md-1', 'col-sm-12'));
        $ret[] = '<hr />';
        $ret['Applicant_HighSchoolId'] = $this->form->field_select('Applicant_HighSchoolId', $student_data['personal']->HighSchoolId ? $student_data['personal']->HighSchoolId : 136, "High School Attended", $student_data['personal']->HighSchoolId ? $student_data['personal']->HighSchoolId : null, $this->highschool_array, array('required' => 'required'), array('required', 'col-md-4', 'col-sm-12'));
        $ret['HighSchoolType'] = $this->form->field_textinfo('HighSchoolType', $this->queries->get_highschool_type_by_highschool_id($student_data['personal']->HighSchoolId) , "High School Type", null, null, array('required', 'col-md-2', 'col-sm-12'));
        $ret['Applicant_HighSchoolGraduationDate'] = $this->form->field_select('Applicant_HighSchoolGraduationDate', $student_data['personal']->HighSchoolGraduationDate ? $student_data['personal']->HighSchoolGraduationDate : date("Y").'-01-01', "Year of High School Graduation", array('value' => date("Y").'-01-01','option' => date("Y")), $this->gradyr_array, array(), array('col-md-3', 'col-sm-12'));
        $ret['Applicant_HighSchoolGPA'] = $this->form->field_textfield('Applicant_HighSchoolGPA', $student_data['personal']->HighSchoolGPA ? $student_data['personal']->HighSchoolGPA : null, 'High School GPA', '0.00', array('required' => 'required', 'type' => 'number', 'min' => 0.00, 'max' => 100.00, 'step'=> 0.01, 'minlength' => 1), array('required', 'col-md-3', 'col-sm-12'));

        $ret['Applicant_PlayedHighSchoolSports'] = $this->form->field_boolean('Applicant_PlayedHighSchoolSports', $student_data['personal']->PlayedHighSchoolSports ? $student_data['personal']->PlayedHighSchoolSports : 0, 'Student Athlete',null,array('col-md-3', 'col-sm-12'));
        $ret['Applicant_FirstGenerationStudent'] = $this->form->field_boolean('Applicant_FirstGenerationStudent', $student_data['personal']->FirstGenerationStudent ? $student_data['personal']->FirstGenerationStudent : 0, 'First generation student', null, array('col-md-3', 'col-sm-12'));
        $ret['Applicant_IsIndependent'] = $this->form->field_boolean('Applicant_IsIndependent', $student_data['personal']->IsIndependent ? $student_data['personal']->IsIndependent : 0, 'Independent?',null,array('col-md-3', 'col-sm-12'));
        $ret[] = '<hr />';

        $ret['Guardian_ApplicantId'] = $this->form->field_hidden("Guardian_ApplicantId", $student_data['personal']->ApplicantId);
        $ret['Guardian_GuardianFullName1'] = $this->form->field_textfield('Guardian_GuardianFullName1', $student_data['financial']->GuardianFullName1 ? $student_data['financial']->GuardianFullName1 : null, "First Guardian Full Name",null,array('minlength' => '2'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Guardian_GuardianEmployer1'] = $this->form->field_textfield('Guardian_GuardianEmployer1', $student_data['financial']->GuardianEmployer1 ? $student_data['financial']->GuardianEmployer1 : null, "Place of Employment",null,array('minlength' => '2'), array('required', 'col-md-6', 'col-sm-12'));
        $ret['Guardian_GuardianFullName2'] = $this->form->field_textfield('Guardian_GuardianFullName2', $student_data['financial']->GuardianFullName2 ? $student_data['financial']->GuardianFullName2 : null, "Second Guardian Full Name",null,null, array('col-md-6', 'col-sm-12'));
        $ret['Guardian_GuardianEmployer2'] = $this->form->field_textfield('Guardian_GuardianEmployer2', $student_data['financial']->GuardianEmployer2 ? $student_data['financial']->GuardianEmployer2 : null, "Place of Employment",null,null, array('col-md-6', 'col-sm-12'));
        $ret[] = '<hr />';

        $ret['Applicant_Employer'] = $this->form->field_textfield('Applicant_Employer', $student_data['personal']->Employer ? $student_data['personal']->Employer : null, "Applicant Place of Employment",null,array('minlength' => '2'), array('col-md-6', 'col-sm-12'));
        $ret[] = '<hr />';

        $ret['Guardian_Homeowner'] = $this->form->field_boolean('Guardian_Homeowner', $student_data['financial']->Homeowner ? $student_data['financial']->Homeowner : 0, "Homeowners",null, array('required', 'col-md-4'));
        $ret['Guardian_AmountOwedOnHome'] = $this->form->field_textfield('Guardian_AmountOwedOnHome', $student_data['financial']->AmountOwedOnHome ? $student_data['financial']->AmountOwedOnHome : null, "Amount Owed",'50,000', array('type' => 'number'), array('col-md-4', 'col-sm-12','currency'));
        $ret['Guardian_HomeValue'] = $this->form->field_textfield('Guardian_HomeValue', $student_data['financial']->HomeValue ? $student_data['financial']->HomeValue : null, "Current Value",'100,000', array('type' => 'number'), array('col-md-4', 'col-sm-12','currency'));

        if($renewal){
            $ret['infoCollegeId'] = $this->form->field_textinfo('infoCollegeId', $this->queries->get_college_by_id($student_data['renewal']->CollegeId), 'College Attending', null, null, array('col-md-3', 'col-sm-12'));
            $ret['infoOtherSchool'] = $this->form->field_textinfo('infoOtherSchool', $student_data['renewal']->OtherSchool ? $student_data['renewal']->OtherSchool : '', 'Name of Unlisted Institution', null, null, array('col-md-3','col-sm-12')); //how are we handling "other" in the new DB?
        } else {
            $ret['infoCollegeId'] = $this->form->field_textinfo('infoCollegeId', $this->queries->get_college_by_id($student_data['personal']->CollegeId), 'College Attending', null, null, array('col-md-3', 'col-sm-12'));
            $ret['infoOtherSchool'] = $this->form->field_textinfo('infoOtherSchool', $student_data['renewal']->OtherSchool ? $student_data['personal']->OtherSchool : '', 'Name of Unlisted Institution', null, null, array('col-md-3','col-sm-12')); //how are we handling "other" in the new DB?
        }
        $ret['Applicant_AppliedBefore'] = $this->form->field_boolean('Applicant_AppliedBefore', $student_data['personal']->AppliedBefore ? $student_data['personal']->AppliedBefore : 0, "Applied to CSF Before?",null, array('col-md-3'));

        $ret['Applicant_Activities'] = $this->form->field_textarea('Applicant_Activities',$student_data['personal']->Activities ? $student_data['personal']->Activities : '',"Activities",null,array('col-md-12'));
        return implode("\n",$ret);

    }

    function need_form($student_data){
        $renewal = isset($student_data['renewal']->RenewalId)?true:false;
        if($renewal){
            $college_id = $student_data['renewal']->CollegeId;
        } else {
            $college_id = $student_data['personal']->CollegeId;
        }
        $instate_direct = (int)$this->queries->get_college_financials($student_data['personal']->CollegeId, 'InStateTuition') + (int) $this->queries->get_college_financials($student_data['personal']->CollegeId, 'BookFee') + (int)$this->queries->get_college_financials($student_data['personal']->CollegeId, 'RoomBoardOnCampus');
        $instate_indirect = $instate_direct + (int)$this->queries->get_college_financials($student_data['personal']->CollegeId, 'IndirectCost');
        $ret[] = '<div class="col-md-4 row">';
        $ret['studentneed_DirectCost'] = $this->form->field_textfield('studentneed_DirectCost', $student_data['need']->DirectCost ? $student_data['need']->DirectCost : $instate_direct,  'Direct Cost', '0.00', array('type' => 'number', 'minlength' => 1), array('directcost','currency'));
        $ret['studentneed_IndirectCost'] = $this->form->field_textfield('studentneed_IndirectCost', $student_data['need']->IndirectCost ? $student_data['need']->IndirectCost : $instate_indirect,  'Indirect Cost', '0.00', array('type' => 'number', 'minlength' => 1), array('indirectcost','currency'));
        $ret['studentneed_FamilyContribution'] = $this->form->field_textfield('studentneed_FamilyContribution', $student_data['need']->FamilyContribution ? $student_data['need']->FamilyContribution : '',  'Family Contribution', '0.00', array('type' => 'number', 'minlength' => 1), array('family','currency'));
        $ret['studentneed_Pell'] = $this->form->field_textfield('studentneed_Pell', $student_data['need']->Pell ? $student_data['need']->Pell : '',  'Pell', '0.00', array('type' => 'number', 'minlength' => 1), array('pell','currency'));
        $ret['studentneed_SEOG'] = $this->form->field_textfield('studentneed_SEOG', $student_data['need']->SEOG ? $student_data['need']->SEOG : '',  'SEOG', '0.00', array('type' => 'number', 'minlength' => 1), array('seog','currency'));
        //$ret['studentneed_OIG'] = $this->form->field_textfield('studentneed_OIG', $student_data['need']->OIG ? $student_data['need']->OIG : '',  'OIG', '0.00', array('type' => 'number', 'minlength' => 1), array('oig','currency'));
        $ret['studentneed_OSCG'] = $this->form->field_textfield('studentneed_OSCG', $student_data['need']->OSCG ? $student_data['need']->OSCG : '',  'State Grant', '0.00', array('type' => 'number', 'minlength' => 1), array('oscg','currency'));
        $ret['studentneed_Stafford'] = $this->form->field_textfield('studentneed_Stafford', $student_data['need']->Stafford ? $student_data['need']->Stafford : '',  'Stafford Loan', '0.00', array('type' => 'number', 'minlength' => 1), array('stafford','currency'));
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-8 row">';
        for($f=1;$f<7;$f++){
            $name = 'ExternalScholarship'.$f;
            $ret['studentneed_ExternalScholarship'.$f] = $this->form->field_textfield('studentneed_ExternalScholarship'.$f, $student_data['need']->$name ? $student_data['need']->$name : '',  'External Scholarship '.$f, '',null,array('col-md-8'));
            $amt = 'ExternalScholarshipAmt'.$f;
            $ret['studentneed_ExternalScholarshipAmt'.$f] = $this->form->field_textfield('studentneed_ExternalScholarshipAmt'.$f, $student_data['need']->$amt ? $student_data['need']->$amt : '',  'Amount '.$f, '0.00', array('type' => 'number', 'minlength' => 1), array('col-md-4','currency'));
        }
        $ret[] = '<hr />';
        $ret['studentneed_DirectNeed'] = $this->form->field_textfield('studentneed_DirectNeed', $student_data['need']->DirectNeed ? $student_data['need']->DirectNeed : '',  'Direct Need', '0.00', array('type' => 'number', 'minlength' => 1), array('directneed','col-md-6','currency'));
        $ret['studentneed_IndirectNeed'] = $this->form->field_textfield('studentneed_IndirectNeed', $student_data['need']->IndirectNeed ? $student_data['need']->IndirectNeed : '',  'Indirect Need', '0.00', array('type' => 'number', 'minlength' => 1), array('indirectneed','col-md-6','currency'));
        $ret['calc_button'] = $this->form->field_button('calculateneed','Calculate Need',array('col-md-4'),'button',false);
        $ret[] = '</div>';

        $ret[] = '<hr />';

        $ret['college_financials_grid'] = '<div class="col-md-3 row">
    <table class="table-grid">
    <tr><th colspan="2">'.$this->queries->get_college_by_id($college_id).'</th></tr>
    <tr><td>Indirect Cost</td><td>$'.$this->queries->get_college_financials($college_id,'IndirectCost').'</td></tr>
    <tr><td>Book Fees</td><td>$'.$this->queries->get_college_financials($college_id,'BookFee').'</td></tr>
    <tr><td>Off Campus R&B</td><td>$'.$this->queries->get_college_financials($college_id,'RoomBoardOffCampus').'</td></tr>
    <tr><td>On Campus R&B</td><td>$'.$this->queries->get_college_financials($college_id,'RoomBoardOnCampus').'</td></tr>
    <tr><td>In-state Tuition</td><td>$'.$this->queries->get_college_financials($college_id,'InStateTuition').'</td></tr>
    <tr><td>Out-of-state Tuition</td><td>$'.$this->queries->get_college_financials($college_id,'OutStateTuition').'</td></tr>
</table>';
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-9 row">';
        $ret['studentneed_Notes'] = $this->form->field_textarea('studentneed_Notes',$student_data['need']->Notes ? $student_data['need']->Notes : '',"Need Notes (CSF only)",null,array('col-md-12'));
        $ret[] = '</div>';

        $ret[] = '<hr />';

        return implode("\n",$ret);
    }

    function payment_form($student_data, $scholarship_key){
        $this->set_form_select_options();
        $renewal = isset($student_data['renewal']->RenewalId)?true:false;
        if($renewal){
            $college_id = $student_data['renewal']->CollegeId;
        } else {
            $college_id = $student_data['personal']->CollegeId;
        }
        $scholarship_id = $student_data['scholarship'][$scholarship_key]->ScholarshipId;
        $award_id       = $student_data['scholarship'][$scholarship_key]->AwardId;
        $paymentkeys = array('1','1-Adj','2','2-Adj','3');
        $ret[] = '<div class="col-md-5 row">';
        $ret['infoCurrentCollege'] = $this->form->field_textinfo('infoCurrentCollege',$this->queries->get_college_by_id($college_id),'School',null,null,array('col-sm-12'));
        $ret['infoScholarshipId'] = $this->form->field_textinfo('infoScholarshipId',$this->queries->get_scholarship_by_id($scholarship_id),'Scholarship',null,null,array('col-sm-12'));
        //$ret['infoFund'] = $this->form->field_textinfo('infoFund',$this->queries->get_fund_by_scholarshipid($student_data['scholarship']->ScholarshipId),'Fund',null,null,array('col-sm-12'));
        $ret['ApplicantScholarship_AmountAwarded'] = $this->form->field_textinfo('ApplicantScholarship_AmountAwarded', $student_data['scholarship'][$scholarship_key]->AmountAwarded ? $student_data['scholarship'][$scholarship_key]->AmountAwarded : null, 'Amount Awarded', '', array(), array('col-md-6', 'col-sm-12', 'currency'));
        $ret['ApplicantScholarship_AmountActuallyAwarded'] = $this->form->field_textinfo('ApplicantScholarship_AmountActuallyAwarded', $student_data['scholarship'][$scholarship_key]->AmountActuallyAwarded ? $student_data['scholarship'][$scholarship_key]->AmountActuallyAwarded : null, 'Amount Actually Awarded', '', array(), array('col-md-6', 'col-sm-12', 'currency'));
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-7 row">';
        $ret['ApplicantScholarship_Notes_'.$award_id] = $this->form->field_textarea('ApplicantScholarship_Notes_'.$award_id, $student_data['scholarship'][$scholarship_key]->Notes ? $student_data['scholarship'][$scholarship_key]->Notes : '', $this->queries->get_scholarship_by_id($student_data['scholarship'][$scholarship_key]->ScholarshipId)." Award Notes (CSF only)", null, array('col-md-12'));
        $ret[] = '</div>';
        $ret[] = '<div class="col-md-12 row"><table>';
        $ret[] = '<tr><th>Payment #</th><th>Amount</th><th>Date</th><th>Check #</th><th>Refund Rec.</th><th>Refund Amt.</th><th>Refund #</th></tr>';
        foreach($paymentkeys AS $paymentkey){
            $ret[] = '<tr>';
            $ret[] = '<td class="key">'.$paymentkey.'</td>';
            $ret['payment_ApplicantId_'.$award_id.'-'.$paymentkey] = $this->form->field_hidden("payment_ApplicantId_".$award_id."-".$paymentkey, $student_data['personal']->ApplicantId);
            $ret['payment_UserId_'.$award_id.'-'.$paymentkey] = $this->form->field_hidden("payment_UserId_".$award_id."-".$paymentkey, $student_data['personal']->UserId);
            $ret['payment_paymentkey_'.$award_id.'-'.$paymentkey] = $this->form->field_hidden("payment_paymentkey_".$award_id."-".$paymentkey, $paymentkey);

            $ret['payment_PaymentAmt_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_textfield('payment_PaymentAmt_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->PaymentAmt,'','0.00',array('type' => 'number'),array('currency','awardcalc','plus')).'</td>';
            $ret['payment_PaymentDateTime_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_date('payment_PaymentDateTime_'.$award_id."-".$paymentkey,date("Y-m-d", strtotime($student_data['payment'][$award_id][$paymentkey]->PaymentDateTime)),'').'</td>';
            $ret['payment_CheckNumber_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_textfield('payment_CheckNumber_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->CheckNumber).'</td>';
            $ret['payment_CollegeId_'.$award_id.'-'.$paymentkey] = $this->form->field_hidden('payment_CollegeId_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->CollegeId?$student_data['payment'][$award_id][$paymentkey]->CollegeId:$college_id);
            //$ret['payment_Notes_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_textarea_simple('payment_Notes_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->Notes).'</td>';
            //$ret['payment_RefundRequested_'.$award_id.'-'.$paymentkey] = '<td>$'.$this->form->field_textfield('payment_RefundRequested_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->RefundRequested).'</td>';
            $ret['payment_RefundReceived_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_date('payment_RefundReceived_'.$award_id."-".$paymentkey,date("Y-m-d", strtotime($student_data['payment'][$award_id][$paymentkey]->RefundReceived)),'').'</td>';
            $ret['payment_RefundAmt_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_textfield('payment_RefundAmt_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->RefundAmt,'','0.00',array('type' => 'number'),array('currency','awardcalc','minus')).'</td>';
            $ret['payment_RefundNumber_'.$award_id.'-'.$paymentkey] = '<td>'.$this->form->field_textfield('payment_RefundNumber_'.$award_id."-".$paymentkey,$student_data['payment'][$award_id][$paymentkey]->RefundNumber).'</td>';
            $ret[] = '</tr>';
        }
        $ret[] = '</table></div>';
        return implode("\n",$ret);
    }



    function other_form($student_data){
        $ret['Agreements_ApplicantId'] = $this->form->field_hidden("Agreements_ApplicantId", $student_data['personal']->ApplicantId);
        $ret['SRATableHdr'] = '<div class="col-md-8 row">';

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
        $ret[] = '<td class="table-cell">I/we understand that applications submitted after the April 30, '.date("Y").' deadline will not be considered;</td>';
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
        $ret['Applicant_HardshipNote'] = $this->form->field_textarea('Applicant_HardshipNote', $student_data['personal']->HardshipNote ? $student_data['personal']->HardshipNote : null, "Hardship Statement",null,array('col-md-12'));

        $ret[] = '</div>';
        $ret[] = '<div class="col-md-4 row">';
        //$ret['Files'] = $this->form->attachment_display('Files',$student_data['docs']);
        if($renewal) {
            $ret[] = $this->form->file_management_front_end('Attachment_',$student_data['docs'],array('col-sm-6'),'renewal');
        } else {
            $ret[] = $this->form->file_management_front_end('Attachment_',$student_data['docs'],array('col-sm-6'),'application');
        }
        $jquery['filemanager'] = $this->form->get_file_manager_ajax('Attachment_',$student_data['docs']);

        $ret[] = '</div>';

        return implode("\n",$ret);
    }

    function action_form($student_data){
        $renewal = isset($student_data['renewal']->RenewalId)?true:false;

        if($renewal) {
            $ret['Applicant_Notes'] = $this->form->field_textinfo('Applicant_Notes',$student_data['personal']->Notes ? $student_data['personal']->Notes : '',"Application Notes (CSF only)",null,null,array('col-md-6'));
            $ret['Renewal_Reject'] = $this->form->field_boolean('Renewal_Reject',$student_data['renewal']->Reject?$student_data['renewal']->Reject:0,'Reject Renewal (please add note below)',array(),array('col-sm-12','reject'),array('true' => 'REJECT', 'false' => 'ACCEPT'));
            $ret['Renewal_Notes'] = $this->form->field_textarea('Renewal_Notes',$student_data['renewal']->Notes ? $student_data['renewal']->Notes : '',"Renewal Notes (CSF only)",null,array('col-md-6'));
        } else {
            $ret['Applicant_Reject'] = $this->form->field_boolean('Applicant_Reject',$student_data['personal']->Reject?$student_data['personal']->Reject:0,'Reject Application (please add note below)',array(),array('col-sm-12','reject'),array('true' => 'REJECT', 'false' => 'ACCEPT'));
            $ret['recommend_UserId'] = $this->form->field_hidden("recommend_UserId", $student_data['personal']->UserId);
            $ret['recommend_ApplicantId'] = $this->form->field_hidden("recommend_ApplicantId", $student_data['personal']->ApplicantId);
            $ret['recommend_RecommendationTime'] = $this->form->field_hidden("recommend_RecommendationTime", date("Y-m-d H:i:s"));
            $ret['recommend_ScholarshipId'] = $this->form->field_checkbox_array('recommend_ScholarshipId',$student_data['recommend'],'Recommended Scholarships',$this->scholarship_array,array(), array('col-sm-12'));
            $ret['Applicant_Notes'] = $this->form->field_textarea('Applicant_Notes',$student_data['personal']->Notes ? $student_data['personal']->Notes : '',"Application Notes (CSF only)",null,array('col-md-12'));
        }
        return implode("\n",$ret);
    }

    function check_table_data($fields,$result,$table_data){
        $ret = array();
        $ecsv = array();
        $i = 0;
        $portal_page = get_option('csf_settings_student_welcome_page');
        $oldcollege = '';
        foreach($result AS $k => $user){
            $row = array();
            $erow = array();
            if($user->CollegeId == '343'){
                $college = $this->queries->get_other_school($user->ApplicantId);
            } else {
                $college = $this->queries->get_college_by_id($user->CollegeId);
            }
            if($oldcollege != $college && $i>0){
                foreach ($fields as $key => $value) {
                    switch($value){
                        case 'CollegeId':
                            $printval = $table_data[$oldcollege]['College'];
                            break;
                        case 'CheckAmount':
                            $printval = '$'.array_sum($table_data[$oldcollege]['CheckAmount']);
                            break;
                        default:
                            $printval = '';
                            break;
                    }
                    $row[] = '<td class="'.$value.'"><div><strong>'.$printval.'</strong></div></td>';
                    if(!in_array($value,$this->skipcsv)) {
                        $erow[] = $this->csv_safe($printval);
                    }
                }
                $ret[] = '<tr class="total '.$class.'">'.implode("\n\r", $row).'</tr>';
                $ecsv[] = implode(",",$erow);

                $row = array();
                $erow = array();
            }
                foreach ($fields as $key => $value) {
                    switch ($value) {
                        //special for checks
                        case 'College':
                            if (is_array($user) && is_string($user[$value])) {
                                $printval = $user[$value];
                            }
                            break;
                        case 'Students':
                            if (is_array($user) && is_array($user[$value])) {
                                $printval = implode(",<br>\n", $user[$value]);
                            }
                            break;
                        case 'CheckAmount':
                            if (is_array($user) && is_array($user[$value])) {
                                $printval = '$' . array_sum($user[$value]);
                            } else {
                                switch ($user->InstitutionTermTypeId) {
                                    case 2:
                                        $printval = '$' . $user->AmountAwarded / 3;
                                        break;
                                    case 3:
                                    default:
                                        $printval = '$' . $user->AmountAwarded / 2;
                                        break;
                                }
                            }
                            break;
                        case "Fund":
                            $printval = $user[$value];
                            break;
                        //normal
                        case 'UserId':
                            $printval = '<strong>' . $user->{$value} . '</strong><br />';
                            if (current_user_can('manage_csf')) {
                                $printval .= '<a href="?page=student-edit&user_id=' . $user->{$value} . '" class="button" target="_blank">View/Edit</a>';
                            }
                            break;
                        case 'ApplicantId':
                            if (current_user_can('manage_csf')) {
                                $printval = '<a href="' . get_permalink($portal_page) . '?applicant_id=' . $user->{$value} . '&renewal_id=' . $user->RenewalId . '" target="_blank">' . $user->{$value} . '</a>';
                            } else {
                                $printval = $user->{$value};
                            }
                            break;
                        case 'RenewalId':
                            if (current_user_can('manage_csf')) {
                                $printval = '<a href="' . get_permalink($portal_page) . '?applicant_id=' . $user->ApplicantId . '&renewal_id=' . $user->{$value} . '" target="_blank">' . $user->{$value} . '</a>';
                            } else {
                                $printval = $user->{$value};
                            }
                            break;
                        case 'CountyId':
                            $printval = $this->queries->get_county_by_id($user->{$value});
                            break;
                        case 'StateId':
                            $printval = $this->queries->get_state_by_id($user->{$value});
                            break;
                        case 'CollegeId':
                            if ($user->{$value} == '343') {
                                $printval = $this->queries->get_other_school($user->ApplicantId);
                            } else {
                                $printval = $this->queries->get_college_by_id($user->{$value});
                            }
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
                        case 'ScholarshipId':
                            $printval = $this->queries->get_scholarship_by_id($user->{$value});
                            break;
                        case 'EmployerId':
                            $printval = $this->queries->get_employer_by_id($user->{$value});
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
                        case 'ResumeOK':
                        case 'TranscriptOK':
                        case 'FinancialAidOK':
                        case 'FAFSAOK':
                        case 'ApplicationLocked':
                        case 'AppliedBefore':
                        case 'Rejected':
                        case 'AppliedBefore':
                        case 'TermsAcknowledged':
                        case 'RenewalLocked':
                        case 'Reject':
                        case 'Renew':
                        case 'ThankYou':
                        case 'Signed':
                            $printval = $user->{$value} > 0 ? 'Yes' : 'No';
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
                    $oldcollege = $college;
                    $row[] = '<td class="' . $value . '"><div>' . $printval . '</div></td>';
                    if (!in_array($value, $this->skipcsv)) {
                        $erow[] = $this->csv_safe($printval);
                    }
                }
            $class = $i%2==0?'even':'odd';
            if($user->Reject){$class = 'reject';}
            $ret[] = '<tr class="'.$class.'">'.implode("\n\r", $row).'</tr>';
            $ecsv[] = implode(",",$erow);
            $i++;
        }
//add on final total
        $row = array();
        $erow = array();
        foreach ($fields as $key => $value) {
            switch($value){
                case 'CollegeId':
                    $printval = $table_data[$oldcollege]['College'];
                    break;
                case 'CheckAmount':
                    $printval = '$'.array_sum($table_data[$oldcollege]['CheckAmount']);
                    break;
                default:
                    $printval = '';
                    break;
            }
            $row[] = '<td class="'.$value.'"><div><strong>'.$printval.'</strong></div></td>';
            if(!in_array($value,$this->skipcsv)) {
                $erow[] = $this->csv_safe($printval);
            }
        }
        $ret[] = '<tr class="total '.$class.'">'.implode("\n\r", $row).'</tr>';
        $ecsv[] = implode(",",$erow);

        $this->export_csv = implode("\n", $ecsv);

        if($echo){
            print implode("\n\r", $ret);
        } else {
            return implode("\n\r", $ret);
        }
    }
}

