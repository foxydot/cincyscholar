<?php
class MSDLAB_SettingControls{

    /**
     * A reference to an instance of this class.
     */
    private static $instance;
    public $javascript;

    public function __construct() {
        if(class_exists('MSDLAB_Queries')){
            $this->queries = new MSDLAB_Queries();
        }
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

    public function print_settings($echo = true){
        $form_id = 'csf_settings_form';
        $ret = array();
        $ret['admin_address'] = $this->settings_textfield("Admin Address","csf_settings_admin_address",array(''),get_option('csf_settings_admin_address'));
        $ret['start_date'] = $this->settings_date("Start Date","csf_settings_start_date",array('datepicker'),get_option('csf_settings_start_date'));
        $ret['end_date'] = $this->settings_date("End Date","csf_settings_end_date",array('datepicker'),get_option('csf_settings_end_date'));
        $ret['alt_text'] = $this->settings_textarea("Text to Display When Not Taking Applications","csf_settings_alt_text",array(''),get_option('csf_settings_alt_text'));
        $ret['renewal_start_date'] = $this->settings_date("Renewal Start Date","csf_settings_renewal_start_date",array('datepicker'),get_option('csf_settings_renewal_start_date'));
        $ret['renewal_end_date'] = $this->settings_date("Renewal End Date","csf_settings_renewal_end_date",array('datepicker'),get_option('csf_settings_renewal_end_date'));
        $ret['renewal_alt_text'] = $this->settings_textarea("Text to Display When Not Taking Renewals","csf_settings_renewal_alt_text",array(''),get_option('csf_settings_renewal_alt_text'));
        $ret['student_welcome_page'] = $this->settings_pageselect("Select Student Portal Welcome Page","csf_settings_student_welcome_page");
        $ret['donor_welcome_page'] = $this->settings_pageselect("Select Donor Portal Welcome Page","csf_settings_donor_welcome_page");
        $ret['button'] = $this->settings_button();
        $ret['nonce'] = wp_nonce_field( 'csf_settings' );
        $ret['javascript'] = $this->build_javascript($form_id);

        if($echo){
            print $this->form_header($form_id);
            print implode("\n\r", $ret);
            print $this->form_footer();
        } else {
            return $ret;
        }
    }

    public function settings_textfield($title = "",$id = "text", $class = array('medium'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="text" value="'.$value.'" placeholder="'.$title.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_date($title = "Date",$id = "date", $class = array('datepicker'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="date" value="'.$value.'" placeholder="'.$title.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    //SETTINGS PANEL

    public function settings_textarea($title = "Text to display out of season",$id = "text", $class = array('textarea'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        ob_start();
        wp_editor( stripcslashes($value), $id.'_input', array('media_buttons' => false,'teeny' => true,'textarea_rows' => 5) );
        $form_field = ob_get_clean();
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_pageselect($title = "Select Page",$id = "pageselect", $class = array('select'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $options = array();
        //iterate through available pages
        $form_field = apply_filters('msdlab_csf_'.$id.'_field',wp_dropdown_pages( array( 'name' => $id.'_input', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => get_option( $id ) ) ) );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_button($title = "Save",$id = "submit", $class = array('submit'), $type = "submit"){
        $form_field = apply_filters('msdlab_csf_'.$id.'_button','<input id="'.$id.'_button" type="'.$type.'" value="'.$title.'" class="button button-primary button-large" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function build_javascript($id = "csf_form"){
        $ret = '
        <script>
  jQuery(function($){
    '.implode(" ",apply_filters('msdlab_'.$id.'_javascript', $this->javascript)).'
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

    public function get_form($options){
        extract($options);
        if(!$form_id){ return false; }
        switch ($form_id){
            case 'csf_college':
                $institution_term_type_options = $this->queries->get_select_array_from_db('institutiontermtype','InstitutionTermTypeId','InstitutionTermType');
                $college_id = isset($data->CollegeId)?$data->CollegeId:$this->queries->get_next_id('college','CollegeId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['college_id'] = $this->settings_hidden('college_CollegeId',$data->CollegeId,'College ID',null,null);
                $ret['name'] = $this->settings_textfield('College Name','college_Name',array('large','setting-field'),$data->Name);
                //TODO: Add state code OR array for figuring out in/out state
                $ret['indirect_cost'] = $this->settings_textfield('Indirect Cost','college_IndirectCost',array('large','setting-field'),$data->IndirectCost);
                $ret['inst_type'] = $this->settings_hidden('college_InstitutionTypeId',0,'Institution Type',null,null);
                $ret['inst_term'] = $this->settings_select('college_InstitutionTermTypeId',$data->InstitutionTermTypeId?$data->InstitutionTermTypeId:1,'Institution Term',array('','Select'),$institution_term_type_options,null, array('large','setting-field'));
                $ret['books'] = $this->settings_textfield('Book Fee','college_BookFee',array('large','setting-field'),$data->BookFee);
                $ret['rboff'] = $this->settings_textfield('Room &amp; Board Off Campus','college_RoomBoardOffCampus',array('large','setting-field'),$data->RoomBoardOffCampus);
                $ret['rbon'] = $this->settings_textfield('Room &amp; Board On Campus','college_RoomBoardOnCampus',array('large','setting-field'),$data->RoomBoardOnCampus);
                $ret['instate'] = $this->settings_textfield('In State Tuition','college_InStateTuition',array('large','setting-field'),$data->InStateTuition);
                $ret['outstate'] = $this->settings_textfield('Out State Tuition','college_OutStateTuition',array('large','setting-field'),$data->OutStateTuition);
                $ret['submit'] = $this->settings_button();
                $ret['delete'] = $this->delete_button('Delete','college_Publish');
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_contact':
                $states = $this->queries->get_select_array_from_db('state','StateId','State','State');
                $colleges = $this->queries->get_select_array_from_db('college','CollegeId','Name','Name',1);
                $ret['hdr'] = $this->form_header($form_id);
                $ret['contact_id'] = $this->settings_hidden('collegecontact_CollegeContactId',$data->CollegeContactId,'Contact ID',null,null);
                $ret['firstname'] = $this->settings_textfield('First Name','collegecontact_FirstName',array('large','setting-field'),$data->FirstName);
                $ret['lastname'] = $this->settings_textfield('Last Name','collegecontact_LastName',array('large','setting-field'),$data->LastName);
                $ret['Address1'] = $this->settings_textfield('Address','collegecontact_Address1',array('large','setting-field'),$data->Address1);
                $ret['Address2'] = $this->settings_textfield('Address','collegecontact_Address2',array('large','setting-field'),$data->Address2);
                $ret['City'] = $this->settings_textfield('City','collegecontact_City',array('large','setting-field'),$data->City);
                $ret['StateId'] = $this->settings_select('collegecontact_StateId',$data->StateId?$data->StateId:'OH','State',array('','Select'),$states,null, array('large','setting-field'));
                $ret['ZipCode'] = $this->settings_textfield('ZipCode','collegecontact_ZipCode',array('large','setting-field'),$data->ZipCode);
                $ret['PhoneNumber'] = $this->settings_textfield('Phone Number','collegecontact_PhoneNumber',array('large','setting-field'),$data->PhoneNumber);
                $ret['FaxNumber'] = $this->settings_textfield('Fax Number','collegecontact_FaxNumber',array('large','setting-field'),$data->FaxNumber);
                $ret['Department'] = $this->settings_textfield('Department','collegecontact_Department',array('large','setting-field'),$data->Department);
                $ret['Email'] = $this->settings_textfield('Email','collegecontact_Email',array('large','setting-field'),$data->Email);
                $ret['CollegeId'] = $this->settings_select('collegecontact_CollegeId',$data->CollegeId?$data->CollegeId:343,'College',array('','Select'),$colleges,null, array('large','setting-field'));
                $ret['Notes'] = $this->settings_textarea('Notes','collegecontact_Notes',array('large','setting-field'),$data->Notes);
                $ret['submit'] = $this->settings_button();
                $ret['delete'] = $this->delete_button('Delete','collegecontact_Publish');
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_highschool':
                $states = $this->queries->get_select_array_from_db('state','StateId','State','State');
                $counties = $this->queries->get_select_array_from_db('county','CountyId','County','StateId');
                $highschooltypes = $this->queries->get_select_array_from_db('highschooltype','HighSchoolTypeId','Type');
                $highschool_id = isset($data->HighSchoolId)?$data->HighSchoolId:$this->queries->get_next_id('highschool','HighSchoolId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['highschool_id'] = $this->settings_hidden('highschool_HighSchoolId',$data->HighSchoolId,'HighSchool ID',null,null);
                $ret['name'] = $this->settings_textfield('HighSchool Name','highschool_SchoolName',array('large','setting-field'),$data->SchoolName);
                $ret['hstype'] = $this->settings_select('highschool_SchoolTypeId',$data->SchoolTypeId?$data->SchoolTypeId:1,'School Type',array('','Select'),$highschooltypes,null, array('large','setting-field'));
                $ret['firstname'] = $this->settings_textfield('Contact First Name','highschool_ContactFirstName',array('large','setting-field'),$data->ContactFirstName);
                $ret['lastname'] = $this->settings_textfield('Contact Last Name','highschool_ContactLastName',array('large','setting-field'),$data->ContactLastName);
                $ret['Address1'] = $this->settings_textfield('Address','highschool_Address1',array('large','setting-field'),$data->Address1);
                $ret['Address2'] = $this->settings_textfield('Address','highschool_Address2',array('large','setting-field'),$data->Address2);
                $ret['City'] = $this->settings_textfield('City','highschool_City',array('large','setting-field'),$data->City);
                $ret['StateId'] = $this->settings_select('highschool_StateId',$data->StateId?$data->StateId:'OH','State',array('','Select'),$states,null, array('large','setting-field'));
                $ret['CountyId'] = $this->settings_select('highschool_CountyId',$data->CountyId?$data->CountyId:'24','County',array('','Select'),$counties,null, array('large','setting-field'));
                $ret['PhoneNumber'] = $this->settings_textfield('Phone Number','highschool_PhoneNumber',array('large','setting-field'),$data->PhoneNumber);
                $ret['Email'] = $this->settings_textfield('Email','highschool_EmailAddress',array('large','setting-field'),$data->EmailAddress);

                $ret['submit'] = $this->settings_button();
                $ret['delete'] = $this->delete_button('Delete','highschool_Publish');
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_county':
                $states = array('IN' => 'Indiana','KY' => 'Kentucky','OH' => 'Ohio');
                $county_id = isset($data->CountyId)?$data->CountyId:$this->queries->get_next_id('county','CountyId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['county_id'] = $this->settings_hidden('county_CountyId',$data->CountyId,'County ID',null,null);
                $ret['name'] = $this->settings_textfield('County Name','county_County',array('large','setting-field'),$data->County);
                $ret['state'] = $this->settings_select('county_StateID',$data->StateID?$data->StateID:'','State',array('','Select'),$states,null, array('large','setting-field'));
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_major':
                $programlength = array(0,1,2,3,4,5,6,7,8);
                $major_id = isset($data->MajorId)?$data->MajorId:$this->queries->get_next_id('major','MajorId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['major_id'] = $this->settings_hidden('major_MajorId',$data->MajorId,'Major ID',null,null);
                $ret['name'] = $this->settings_textfield('Major Name','major_MajorName',array('large','setting-field'),$data->MajorName);
                $ret['programlength'] = $this->settings_select('major_ProgramLength',$data->ProgramLength?$data->ProgramLength:1,'Program Length',array('','Select'),$programlength,null, array('large','setting-field'));
                $ret['submit'] = $this->settings_button();
                $ret['delete'] = $this->delete_button('Delete','major_Publish');
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_scholarship':
                $funds = $this->queries->get_select_array_from_db('fund','FundId','Name','Name');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['scholarship_id'] = $this->settings_hidden('scholarship_ScholarshipId',$data->ScholarshipId,'Scholarship ID',null,null);
                $ret['name'] = $this->settings_textfield('Scholarship Name','scholarship_Name',array('large','setting-field'),$data->Name);
                $ret['fund'] = $this->settings_select('scholarship_FundId',$data->FundId?$data->FundId:1,'Fund',array('','Select'),$funds,null, array('large','setting-field'));
                $ret['renewable'] = $this->settings_select('scholarship_Renewable',$data->Renewable,'Renewable',array('value' => 0,'option' => 'no'),array(0 => 'no',1 => 'yes'),null,array('large','setting-field'));
                $ret['expiration'] = $this->settings_date('Expiration','scholarship_Expiration',array('datepicker'),$data->Expiration);
                $ret['contacts'] = $this->settings_textarea('Scholarship Contacts (comma separated emails)','scholarship_Contacts',null,$data->Contacts);
                $ret['submit'] = $this->settings_button();
                $ret['delete'] = $this->delete_button('Delete','scholarship_Publish');
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_ethnicity':
                $ethnicity_id = isset($data->EthnicityId)?$data->EthnicityId:$this->queries->get_next_id('ethnicity','EthnicityId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['ethnicity_id'] = $this->settings_hidden('ethnicity_EthnicityId',$data->EthnicityId,'Ethnicity ID',null,null);
                $ret['name'] = $this->settings_textfield('Ethnicity Name','ethnicity_Ethnicity',array('large','setting-field'),$data->Ethnicity);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_gender':
                $sex_id = isset($data->SexId)?$data->SexId:$this->queries->get_next_id('sex','SexId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['sex_id'] = $this->settings_hidden('sex_SexId',$data->SexId,'Gender ID',null,null);
                $ret['name'] = $this->settings_textfield('Gender Name','sex_Sex',array('large','setting-field'),$data->Sex);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_fund':
                $fund_id = isset($data->FundId)?$data->FundId:$this->queries->get_next_id('fund','FundId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['fund_id'] = $this->settings_hidden('fund_FundId',$data->FundId,'Fund ID',null,null);
                $ret['name'] = $this->settings_textfield('Fund Name','fund_Name',array('large','setting-field'),$data->Name);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_highschooltype':
                $type_id = isset($data->HighSchoolTypeId)?$data->HighSchoolTypeId:$this->queries->get_next_id('highschooltype','HighSchoolTypeId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['highschooltype_id'] = $this->settings_hidden('highschooltype_HighSchoolTypeId',$data->HighSchoolTypeId,'High School Type ID',null,null);
                $ret['name'] = $this->settings_textfield('Type','highschooltype_Type',array('large','setting-field'),$data->Type);
                $ret['description'] = $this->settings_textarea('Description','highschooltype_Description',null,$data->Description);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_employer':
                $type_id = isset($data->employerid)?$data->employerid:$this->queries->get_next_id('employer','employerid');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['employer_id'] = $this->settings_hidden('employer_employerid',$data->employerid,'Employer ID',null,null);
                $ret['name'] = $this->settings_textfield('Employer Name','employer_employername',array('large','setting-field'),$data->employername);
                $ret['description'] = $this->settings_textarea('Notes','employer_Notes',null,$data->Notes);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_educationalattainment':
                $type_id = isset($data->EducationalAttainmentId)?$data->EducationalAttainmentId:$this->queries->get_next_id('educationalattainment','EducationalAttainmentId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['EducationalAttainmentId_id'] = $this->settings_hidden('educationalattainment_EducationalAttainmentId',$data->EducationalAttainmentId,'Educational Attainment ID',null,null);
                $ret['name'] = $this->settings_textfield('Educational Attainment','educationalattainment_EducationalAttainment',array('large','setting-field'),$data->EducationalAttainment);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_donortype':
                $type_id = isset($data->DonorTypeId)?$data->DonorTypeId:$this->queries->get_next_id('donortype','DonorTypeId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['DonorTypeId'] = $this->settings_hidden('donortype_DonorTypeId',$data->DonorTypeId,'Donor Type ID',null,null);
                $ret['name'] = $this->settings_textfield('Donor Type','donortype_DonorType',array('large','setting-field'),$data->DonorType);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_institutiontermtype':
                $type_id = isset($data->DonorTypeId)?$data->DonorTypeId:$this->queries->get_next_id('institutiontermtype','InstitutionTermTypeId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['InstitutionTermTypeId'] = $this->settings_hidden('institutiontermtype_InstitutionTermTypeId',$data->InstitutionTermTypeId,'Institution Term Type ID',null,null);
                $ret['name'] = $this->settings_textfield('Institution Term Type','institutiontermtype_InstitutionTermType',array('large','setting-field'),$data->InstitutionTermType);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'check_to_update':
                $paymentkeys = array('1' => '1','1-Adj' => '1-Adj','2' => '2','2-Adj' => '2-Adj','3' => '3');
                $colleges = $this->queries->get_select_array_from_db('college','CollegeId','Name','Name',1);
                $ret['hdr'] = $this->form_header($form_id,array('csf_report_search_form'));
                $ret['PaymentKey'] = $this->settings_select('payment_paymentkey', $data['payment_paymentkey_input']?$data['payment_paymentkey_input']:null,'Payment',array('','Select'),$paymentkeys,null, array('large','setting-field'));
                $ret['CollegeId'] = $this->settings_select('payment_CollegeId',$data['payment_CollegeId_input'],'College',array('','Select'),$colleges,null, array('large','setting-field'));
                $ret['PaymentDate'] = $this->settings_date('Payment Date','payment_PaymentDateTime',array('datepicker','large','setting-field'),date('Y-m-d'));
                $ret['CheckNumber'] = $this->settings_textfield('Check Number','payment_CheckNumber',array('large','setting-field'),$this->queries->get_next_check_number());
                $ret['AcademicYear'] = $this->settings_textfield('Academic Year','payment_AcademicYear',array('large','setting-field'),$this->queries->academic_year(time()));
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'check_attachments':
                $ret['hdr'] = $this->form_header($form_id,array('csf_report_search_form'));
                $ret['PaymentDate'] = $this->settings_date('Payment Date','payment_PaymentDateTime',array('datepicker','large','setting-field'),date('Y-m-d'));
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
        }
        return implode("\n",$ret);
    }

    public function settings_hidden($id, $value = null, $title = "", $validation = null, $class = array('hidden')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="hidden" value="'.$value.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_select($id, $value = null, $title = "", $null_option = null, $options = array(), $validation = null, $class = array('select')){
        if(is_null($value)  || empty($value)){
            $value = $_POST[$id.'_input'];
        }
        if($null_option == null){$null_option = 'Select';}
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        $options_str = implode("\n\r",$this->build_options($options,$value,$null_option));
        $select = '<select id="'.$id.'_input" name="'.$id.'_input">'.$options_str.'</select>';
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $select );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function build_options($options,$value,$null_option){
        $ret = array();
        $cur = $options[$value];
        $options = array_unique($options);
        if(!empty($cur)) {
            $options[$value] = $cur;
        }
        if(is_array($null_option)){
            $ret[] = '<option value="'.$null_option['value'].'">'.$null_option['option'].'</option>';
        } else {
            $ret[] = '<option>'.$null_option.'</option>';
        }
        foreach ($options AS $k => $v){
            $ret[] = '<option value="'.$k.'"'.selected($value,$k,false).'>'.$v.'</option>';
        }
        return $ret;
    }

    public function delete_button($title = "Delete",$id = "delete",$class = array('submit'), $type = "submit"){
        $form_field = apply_filters('msdlab_csf_'.$id.'_button','<input id="'.$id.'_input" name="'.$id.'_input" type="hidden" value="1" /><input id="'.$id.'_button" type="'.$type.'" value="'.$title.'" class="button btn-danger button-large" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $script = '<script>
(function($) {
	$("#'.$id.'_button").click(function(e){
	    e.preventDefault();
	    $("#'.$id.'_input").val("0");
	    var c = confirm("Are you sure? This action can not be reversed!");
        if (c == true) {
            $(this).parents("form").submit();
        } else {
	        $("#'.$id.'_input").val("1");
        }
	});
})( jQuery );
</script>';
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$form_field.'</div>'.$script;
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }
}