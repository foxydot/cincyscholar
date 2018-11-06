<?php
class MSDLab_ReportControls{

    /**
     * A reference to an instance of this class.
     */
    private static $instance;
    public $javascript;

    public function __construct() {
        $this->queries = new MSDLAB_Queries();
    }

    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

    if( null == self::$instance ) {
        self::$instance = new MSDLAB_SettingControls();
    }

    return self::$instance;
    }

   public function role_search($title = "Limit To",$button = "SEARCH", $id = "role_search", $class = array('query-filter','role-search'), $roles = array()){
        if(count($roles)<1){
            $roles = array(
                'Entire Database' => '',
                'Incomplete' => 'I',
                'Applicants' => '10',
                'Awardees' => '20',
                'Renewals' => '30',
                'Rejections' => '1',
            );
        }
        $options = array();
        foreach($roles AS $k => $v){
            if(empty( $_POST )) {
                $options[] = '<option value="' . $v . '"' . selected($v, $default, false) . '>' . $k . '</option>';
            } else {
                $options[] = '<option value="' . $v . '"' . selected($v, $_POST[$id . '_input'], false) . '>' . $k . '</option>';
            }
        }

        $label = apply_filters('msdlab_csf_manage_role_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
        $form_field = apply_filters('msdlab_csf_manage_role_search_form_field',$form_field);
        $button = apply_filters('msdlab_csf_manage_role_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_role_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_role_search', $ret);
    }

    public function date_search_type($title = "Limit by Date",$button = "SEARCH", $id = "date_search_type", $class = array('query-filter','date-search-type'), $types = array()){
        if(count($types)<1){
            $types = array(
                'No' => '',
                'Application Date' => 'application',
                'Last Renewal Date' => 'renewal',
            );
        }
        $options = array();
        foreach($types AS $k => $v){
            $options[] = '<option value="'.$v.'"'.selected($v, $_POST[$id.'_input'], false).'>'.$k.'</option>';
        }

        $label = apply_filters('msdlab_csf_manage_date_search_type_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
        $form_field = apply_filters('msdlab_csf_manage_date_search_type_form_field',$form_field);
        $button = apply_filters('msdlab_csf_manage_date_search_type_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_date_search_type_class', $class));
        $this->javascript[] = '$( "#'.$id.'_input" ).change(function(){
            if($(this).val() != ""){
                $(".date-search").show();
            } else {
                $(".date-search").hide();
            }
        });';

        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_date_search_type', $ret);
    }

    public function print_form($id = 'application',$echo = true,$fields = false){
        $ret = array();

        //select populate
        $states = $this->queries->get_select_array_from_db('state', 'StateId', 'State','State');
        $counties = $this->queries->get_select_array_from_db('county', 'CountyId', 'County','County');
        $colleges = $this->queries->get_select_array_from_db('college', 'CollegeId', 'Name','Name',1);
        $highschools = $this->queries->get_select_array_from_db('highschool', 'HighSchoolId', 'SchoolName','SchoolName',1);
        $highschooltypes = $this->queries->get_select_array_from_db('highschooltype', 'HighSchoolTypeId', 'Description','HighSchoolTypeId');
        $majors = $this->queries->get_select_array_from_db('major', 'MajorId', 'MajorName','MajorName',1);
        $ethnicity = $this->queries->get_select_array_from_db('ethnicity', 'EthnicityId', 'Ethnicity','EthnicityId');
        $gender = $this->queries->get_select_array_from_db('sex', 'SexId', 'Sex','SexId');
        $athletics = array('0'=>'Non-athlete','1'=>'Athlete');
        $independence = array('0'=>'Dependent','1'=>'Independant');
        $scholarship = $this->queries->get_select_array_from_db('scholarship', 'ScholarshipId', 'Name','Name',1);
        $fund = $this->queries->get_select_array_from_db('fund', 'FundId', 'Name','FundId');
        $educational_attainment = $this->queries->get_select_array_from_db('educationalattainment', 'EducationalAttainmentId', 'EducationalAttainment','EducationalAttainmentId');
        $bool_options = array('0'=>'No','1'=>'Yes');
        $ret['search_all_button'] = $this->search_button('SEARCH','search_button_top');
        $ret['reset_button_top'] = $this->reset_button();


        $ret['collapse_fields'] = '<div class="collapse-button collapse-fields"><i class="fa fa-compress"><span class="screen-reader-text">Collapse</span> Fields</i></div>';
        $ret['fields_to_get_label'] = '<h4>Columns to return:</h4>';
        $ret[] = '<div class="collapsable collapsable-fields">';
        $ret['applicant_fields'] = $this->search_checkbox_array('applicant_fields',null,'Applicant Fields',$fields['applicant'],null,array('col-sm-12'));
        $ret['renewal_fields'] = $this->search_checkbox_array('renewal_fields',null,'Renewal Fields',$fields['renewal'],null,array('col-sm-12'));
        $ret['financial_fields'] = $this->search_checkbox_array('financial_fields',null,'Financial Fields',$fields['financial'],null,array('col-sm-12'));
        $ret['award_fields'] = $this->search_checkbox_array('award_fields',null,'Award Fields',$fields['award'],null,array('col-sm-12'));
        $ret[] = '</div>';

        $ret['collapse_search'] = '<div class="collapse-button collapse-search"><i class="fa fa-compress"><span class="screen-reader-text">Collapse</span> Search</i></div>';
        $ret['instructional_text'] = '<h4>Search By:</h4>';
        $ret['collapsable'] = '<div class="collapsable collapsable-search">';
        switch($id){
            case 'renewal':
                $ret['search_by_name'] = $this->search_box('Name:','','name_search');
                $ret['search_by_email'] = $this->search_box('Email:','','email_search');
                $ret['renewal_date_search'] = $this->date_search('Renewal Date Between:','','date_search');
                $ret['search_by_city'] = $this->search_box('City:','','city_search');
                $ret['state_search'] = $this->select_search('State: ','state_search', $states);
                $ret['county_search'] = $this->select_search('County: ','county_search', $counties);
                $ret['search_by_zip'] = $this->search_box('ZipCode (comma separated list):','','zip_search');
                $ret['college_search'] = $this->select_search('College: ','college_search', $colleges);
                $ret['gpa_search'] = $this->number_range_search('GPA Between:','','gpa_range_search',array('query-filter','col-sm-6'),0.00,5.00,0.1);
                $ret['major_search'] = $this->select_search('Major: ','major_search', $majors);
                break;
            case 'consolidated':
                $ret[] = '<div class="row personal_info">';
                $ret['search_by_name'] = $this->search_box('Name:','','name_search',array('query-filter','search-box','col-sm-6','col-md-4'));
                $ret['search_by_email'] = $this->search_box('Email:','','email_search',array('query-filter','search-box','col-sm-6','col-md-4'));
                $ret['search_by_studentid'] = $this->search_box('Student ID:','','studentid_search',array('query-filter','search-box','col-sm-6','col-md-4'));
                $ret['search_by_city'] = $this->search_box('City:','','city_search',array('query-filter','search-box','col-sm-6','col-md-3'));
                $ret['state_search'] = $this->select_search('State: ','state_search', $states,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret['county_search'] = $this->select_search('County: ','county_search', $counties,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret['search_by_zip'] = $this->search_box('ZipCode(s):','','zip_search',array('query-filter','search-box','col-sm-6','col-md-3'),'comma separated list');
                $ret['ethnicity_search'] = $this->select_search('Ethnicity: ','ethnicity_search', $ethnicity,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret['gender_search'] = $this->select_search('Gender: ','gender_search', $gender,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret['athlete_search'] = $this->select_search('Athletics:','athlete_search',$athletics,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret['independence_search'] = $this->select_search('Independence:','independence_search',$independence,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret[] = '</div>';
                $ret[] = '<div class="row scholarship_info">';
                $ret['college_search'] = $this->select_search('College: ','college_search', $colleges,array('query-filter','select-search','col-sm-6'));
                $ret['major_search'] = $this->select_search('Major: ','major_search', $majors,array('query-filter','select-search','col-sm-6'));
                $ret['scholarship_search'] = $this->select_search('Scholarship:','scholarship_search',$scholarship,array('query-filter','select-search','col-sm-6','col-md-4'));
                $ret['fund_search'] = $this->select_search('Fund:','fund_search',$fund,array('query-filter','select-search','col-sm-6','col-md-3'));
                $ret['award_date_search'] = $this->date_search('Award Date Between:','','award_date_search',array('query-filter','col-sm-6','col-md-5'),'00/00/0000','00/00/0000');
                $ret['educational_attainment'] = $this->select_search('Year In School','educational_attainment',$educational_attainment,array('query-filter','col-sm-6','col-md-4'));
                $ret['thankyounote_search'] = $this->select_search('Thank You','thankyounote_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['signed_search'] = $this->select_search('Papers Signed','signed_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['gpa1_search'] = $this->number_range_search('GPA1:','','gpa1_range_search',array('query-filter','col-sm-6','col-md-3'),0.00,100.00,0.1);
                $ret['gpa2_search'] = $this->number_range_search('GPA2:','','gpa2_range_search',array('query-filter','col-sm-6','col-md-3'),0.00,100.00,0.1);
                $ret['gpa3_search'] = $this->number_range_search('GPA3:','','gpa3_range_search',array('query-filter','col-sm-6','col-md-3'),0.00,100.00,0.1);
                $ret['gpac_search'] = $this->number_range_search('GPAC:','','gpac_range_search',array('query-filter','col-sm-6','col-md-3'),0.00,100.00,0.1);

                $ret[] = '</div>';
                $ret[] = '<div class="row application_info">';
                $ret['application_date_search'] = $this->date_search('Application Date Between:','','application_date_search',array('query-filter','date-search','col-sm-6'),'00/00/0000','00/00/0000');
                $ret['renewal_date_search'] = $this->date_search('Renewal Date Between:','','renewal_date_search',array('query-filter','date-search','col-sm-6'),'00/00/0000','00/00/0000');
                $ret['complete_search'] = $this->select_search('Complete','complete_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['transcript_search'] = $this->select_search('Transcript','transcript_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['resume_search'] = $this->select_search('Resume','resume_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['sar_search'] = $this->select_search('SAR','sar_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['award_search'] = $this->select_search('Award','award_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['firstgen_search'] = $this->select_search('First Gen','firstgen_search',$bool_options,array('query-filter','col-sm-6','col-md-4'));
                $ret['highschool_search'] = $this->select_search('High School: ','highschool_search', $highschools,array('query-filter','select-search','col-sm-6','col-md-4'));
                $ret['highschool_type_search'] = $this->select_search('High School Type: ','highschooltype_search', $highschooltypes,array('query-filter','select-search','col-sm-6','col-md-4'));
                $ret['gradyear_search'] = $this->number_range_search('Graduation Between:','','gradyear_range_search',array('query-filter','col-sm-6','col-md-4'),date('Y')-20,date('Y'),1);
                $ret['gpa_search'] = $this->number_range_search('HS GPA Between:','','hs_gpa_range_search',array('query-filter','col-sm-6','col-md-4'),0.00,100.00,0.1);

                //TODO: replace with drop down search and parents alive for next year.
                $ret['search_by_employer'] = $this->search_box('Employer:','','employer_search',array('query-filter','search-box','col-sm-6','col-md-4')); //this is handled AFTER the query
                $ret['search_by_CPS_employee'] = $this->boolean_search('CPS Employee:','cps_employee_search',array('col-sm-6','col-md-2')); //this is handled AFTER the query
                //
                $ret[] = '</div>';
                $ret[] = '<div class="row need_info">';
                $ret['direct_need_search'] = $this->number_range_search('Direct Need Between:','','direct_need_search',array('query-filter','col-sm-6'),0.00,1000000.00,1);
                $ret['indirect_need_search'] = $this->number_range_search('Indirect Need Between:','','indirect_need_search',array('query-filter','col-sm-6'),0.00,1000000.00,1);
                $ret[] = '</div>';
                $ret[] = '<div class="row payment_info">';
                $ret['payment_date_search'] = $this->date_search('Payment Date Between:','','payment_date_search',array('query-filter','date-search','col-sm-6'),'00/00/0000','00/00/0000');
                $ret['check_number_search'] = $this->number_range_search('Check Number Between:','','check_number_search',array('query-filter','col-sm-6'));
                $ret[] = '</div>';
                break;
            case 'application':
            default:
                $ret['search_by_name'] = $this->search_box('Name:','','name_search');
                $ret['search_by_email'] = $this->search_box('Email:','','email_search');
                $ret['application_date_search'] = $this->date_search('Application Date Between:','','date_search');
                $ret['search_by_city'] = $this->search_box('City:','','city_search');
                $ret['state_search'] = $this->select_search('State: ','state_search', $states);
                $ret['county_search'] = $this->select_search('County: ','county_search', $counties);
                $ret['search_by_zip'] = $this->search_box('ZipCode (comma separated list):','','zip_search');
                $ret['college_search'] = $this->select_search('College: ','college_search', $colleges);
                $ret['highschool_search'] = $this->select_search('High School: ','highschool_search', $highschools);
                $ret['highschool_type_search'] = $this->select_search('High School Type: ','highschooltype_search', $highschooltypes);
                $ret['gpa_search'] = $this->number_range_search('GPA Between:','','gpa_range_search',array('query-filter','col-sm-6'),0.00,5.00,0.1);
                $ret['gradyear_search'] = $this->number_range_search('Graduation Between:','','gradyear_range_search',array('query-filter','col-sm-6'),date('Y')-20,date('Y'),1);
                $ret['major_search'] = $this->select_search('Major: ','major_search', $majors);
                $ret['ethnicity_search'] = $this->select_search('Ethnicity: ','ethnicity_search', $ethnicity);
                $ret['athlete_search'] = $this->select_search('Athletics:','athlete_search',$athletics);
                $ret['independence_search'] = $this->select_search('Independence:','independence_search',$independence);
                $ret['search_by_employer'] = $this->search_box('Employer:','','employer_search'); //this is handled AFTER the query
                $ret['search_by_CPS_employee'] = $this->boolean_search('CPS Employee:','cps_employee_search'); //this is handled AFTER the query
                /*
        Need (there should be a place in the database where cost of attendance, EFC, grants, loans, federal and state aid are entered and calculated)*/
            break;
        }
              $ret['footer_break'] = '<hr class="break">';
        $ret['search_button'] = $this->search_button('SEARCH','search_button_btm');
        $ret['reset_button'] = $this->reset_button();
        $ret['collapsable_end'] = '</div class="collapsable">';

        $ret['nonce'] = wp_nonce_field( 'records_search' );
        $ret['javascript'] = $this->build_javascript();

        if($echo){
            print $this->form_header($id . '_search_form',array('csf_report_search_form'));
            print implode("\n\r", $ret);
            print $this->form_footer();
        } else {
            return $ret;
        }
    }

    function print_form_custom_searches($id,$echo = true){
        if($id == null){return false;}
        $ret = array();
        //select populate
        $colleges = $this->queries->get_select_array_from_db('college', 'CollegeId', 'Name','Name',1);
        $scholarship = $this->queries->get_select_array_from_db('scholarship', 'ScholarshipId', 'Name','Name',1);
        $fund = $this->queries->get_select_array_from_db('fund', 'FundId', 'Name','FundId');
        $bool_options = array('0'=>'No','1'=>'Yes');
        $paymentkeys = array('1' => '1','1-Adj' => '1-Adj','2' => '2','2-Adj' => '2-Adj','3' => '3','4' => '4','5' => '5');

        switch($id){
            case 'checks_to_print':
                $ret['payment_number'] = $this->select_search('Payment #','payment_number',$paymentkeys);
                break;
        }

        $ret['footer_break'] = '<hr class="break">';
        $ret['search_button'] = $this->search_button('Get Report','search_button_btm');
        $ret['reset_button'] = $this->reset_button();
        $ret['nonce'] = wp_nonce_field( $id );
        $ret['javascript'] = $this->build_javascript();

        if($echo){
            print $this->form_header($id ,array('csf_report_search_form'));
            print implode("\n\r", $ret);
            print $this->form_footer();
        } else {
            return $ret;
        }
    }

    public function search_button($button = "SEARCH", $id = "search_button", $class = array('search-button')){
        $button = apply_filters('msdlab_csf_manage_search_button','<input id="'.$id.'_button" name="'.$id.'_button" type="submit" class="button button-primary" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_search_button_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$button.'</div>';
        return apply_filters('msdlab_csf_manage_search_button', $ret);
    }

    public function search_box($title = "Search Students",$button = "SEARCH", $id = "student_search", $class = array('query-filter','search-box','col-sm-6'), $placeholder = ''){
       $label = apply_filters('msdlab_csf_manage_search_label','<label for="'.$id.'_input">'.$title.'</label>');
       $form_field = apply_filters('msdlab_csf_manage_search_form_field','<input id="'.$id.'_input" name="'.$id.'_input" type="search" value="'.stripslashes($_POST[$id.'_input']).'" placeholder="'.$placeholder.'" />');
       $class = implode(" ",apply_filters('msdlab_csf_manage_search_class', $class));
       $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
       return apply_filters('msdlab_csf_manage_search', $ret);
   }

    public function date_search($title = "Between Dates",$button = "SEARCH", $id = "date_search", $class = array('query-filter','date-search','col-sm-6'), $start_date = FALSE, $end_date = FALSE ){
        $start_date = !$start_date?date("Y-m-d",strtotime('-1 month')):$start_date;
        $end_date = !$end_date?date("Y-m-d"):$end_date;

        $label = apply_filters('msdlab_csf_manage_date_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field_start = apply_filters('msdlab_csf_manage_date_search_start_form_field','<input id="'.$id.'_input_start" name="'.$id.'_input_start" type="date" value="'.$start_date.'" class="datepicker" />');
        $form_field_end = apply_filters('msdlab_csf_manage_date_search_end_form_field','<input id="'.$id.'_input_end" name="'.$id.'_input_end" type="date" value="'.$end_date.'" class="datepicker" />');
        $button = apply_filters('msdlab_csf_manage_date_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_date_search_class', $class));
        //$this->javascript[] = '$( ".datepicker" ).datepicker();';

        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field_start.$form_field_end.'</div>';
        return apply_filters('msdlab_csf_manage_date_search', $ret);
    }

    public function select_search($title = "Select", $id = "select_search", $data = array(), $class = array('query-filter','select-search','col-sm-6')){
        $options = array('<option value=""' . selected("", $default, false) . '>---Select---</option>');

        foreach($data AS $k => $v){
            if(empty( $_POST )) {
                $options[] = '<option value="' . $k . '"' . selected($k, $default, false) . '>' . $v . '</option>';
            } else {
                $options[] = '<option value="' . $k . '"' . selected($k, $_POST[$id . '_input'], false) . '>' . $v . '</option>';
            }
        }

        $label = apply_filters('msdlab_csf_manage_college_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
        $form_field = apply_filters('msdlab_csf_manage_college_search_form_field',$form_field);
        $button = apply_filters('msdlab_csf_manage_college_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_college_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_college_search', $ret);
    }

    public function number_range_search($title = "Between",$button = "SEARCH", $id = "num_range_search", $class = array('query-filter','num-range-search','col-sm-6'), $start_num = FALSE, $end_num = FALSE, $step = 1 ){
        $start_num = $_POST[$id.'_input_start']?$_POST[$id.'_input_start']:$start_num;
        $end_num = $_POST[$id.'_input_send']?$_POST[$id.'_input_end']:$end_num;
        $label = apply_filters('msdlab_csf_manage_num_range_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field_start = apply_filters('msdlab_csf_manage_num_range_search_start_form_field','<input id="'.$id.'_input_start" name="'.$id.'_input_start" type="number" value="'.$start_num.'" step="'.$step.'" class="num-range" />');
        $form_field_end = apply_filters('msdlab_csf_manage_num_range_search_end_form_field','<input id="'.$id.'_input_end" name="'.$id.'_input_end" type="number" value="'.$end_num.'" step="'.$step.'" class="num-range" />');
        $button = apply_filters('msdlab_csf_manage_num_range_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_num_range_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field_start.$form_field_end.'</div>';
        return apply_filters('msdlab_csf_manage_num_range_search', $ret);
    }

    public function boolean_search($title = "",$id = "boolean_search", $class = array('query-filter','search-bool','col-sm-6')){
        $label = apply_filters('msdlab_csf_manage_boolean_search_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_manage_boolean_search_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" value="1" type="checkbox" '.checked(1,$_POST[$id.'_input'],0).'/>');
        $class = implode(" ",apply_filters('msdlab_csf_manage_boolean_search_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_boolean_search_'.$id.'', $ret);
    }

    public function search_checkbox_array($id, $value = null, $title = "", $options = array(), $validation = null, $class = array('checkbox')){
        $vals = array();
        foreach ($_POST[$id.'_input'] AS $v){
            $vals[] = $v;
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        foreach ($options AS $k => $v){
            $options_array[] = '<div class="'.$id.'_'.$k.'_wrapper checkbox-wrapper"><input id="'.$id.'_'.$k.'" name="'.$id.'_input[]" type="checkbox" value="'.$k.'"'.$this->checked_in_array($vals,$k,false).' /> '.$v.'</div>';
        }
        $options_str = '<div class="checkbox-array-options-wrapper"><div class="inner-wrap">'.implode("\n\r",$options_array).'</div></div>';
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $options_str );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function checked_in_array($array,$current,$echo = true){
        if(!is_array($array)){return false;}
        if(in_array($current,$array)){
            if($echo){
                print "checked";
            } else {
                return "checked";
            }
        } else {
            return false;
        }
    }

    public function reset_button($button = "RESET", $id = "reset_button", $class = array('reset-button')){
        $button = apply_filters('msdlab_csf_manage_reset_button','<a href="" id="'.$id.'_button" name="'.$id.'_button" type="reset" class="button button-primary">'.$button.'</a>');
        $class = implode(" ",apply_filters('msdlab_csf_manage_reset_button_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$button.'</div>';
        $this->javascript[] = '';
        return apply_filters('msdlab_csf_manage_reset_button', $ret);
    }

    public function build_javascript(){
        $ret = '
        <script>
  jQuery(function($){
    '.implode(" ",apply_filters('msdlab_csf_manage_search_javascript', $this->javascript)).'
  });
  </script>';
        return $ret;
    }

    public function form_header($id = "csf_form", $class = array()){
        $class = implode(" ",apply_filters('msdlab_'.$id.'_header_class', $class));
        $ret = '<form id="'.$id.'" class="'.$class.'" method="post">';
        return apply_filters('msdlab_'.$id.'_header', $ret);
    }

    public function form_footer(){
        $ret = '</form>';
        return apply_filters('msdlab_csf_manage_form_footer', $ret);
    }
}